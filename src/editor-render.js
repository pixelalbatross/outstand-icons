/**
 * WordPress dependencies
 */
import domReady from '@wordpress/dom-ready';
import { select, resolveSelect } from '@wordpress/data';

/** Mirrors `InlineIcon::MARKER_CLASS`. @type {string} */
const MARKER_CLASS = 'os-icons-inline';

/** Selector for editor placeholders. @type {string} */
const MARKER_SELECTOR = `img.${MARKER_CLASS}`;

/** ID of the per-document `<style>` holding per-slug mask rules. @type {string} */
const STYLE_ELEMENT_ID = 'os-icons-inline-rules';

/**
 * SVG content by slug. `null` = unregistered (cached to avoid repeat fetches).
 *
 * @type {Map<string,(string|null)>}
 */
const svgCache = new Map();

/**
 * In-flight fetches by slug; dedupes concurrent `resolveSelect` calls.
 *
 * @type {Map<string,Promise<string|null>>}
 */
const pending = new Map();

/**
 * Per-document style element + set of slugs already injected. WeakMap so
 * detached documents are GC'd. `.sheet` re-read on each insert (briefly
 * null in iframes during mount).
 *
 * @type {WeakMap<Document,{styleEl:HTMLStyleElement,known:Set<string>}>}
 */
const docState = new WeakMap();

/**
 * Roots → their attached `MutationObserver`. Lets us disconnect when a
 * root is replaced (e.g. FSE template switch).
 *
 * @type {WeakMap<Node,MutationObserver>}
 */
const watchedRoots = new WeakMap();

/**
 * Iframes → last-attached body. Lets `tryAttach` swap observers when
 * contentDocument is replaced.
 *
 * @type {WeakMap<HTMLIFrameElement,Node>}
 */
const iframeBodies = new WeakMap();

/**
 * Resolve SVG content for a slug. Returns `null` on miss / failure.
 *
 * @param {string} slug Namespaced icon name (e.g. `core/audio`).
 * @return {Promise<string|null>}
 */
async function ensureSvg(slug) {
	if (svgCache.has(slug)) {
		return svgCache.get(slug);
	}

	// Fast path: the data store may already have the record because the
	// picker fetched the collection.
	const sync = select('core').getEntityRecord('root', 'icon', slug);
	if (sync && sync.content) {
		svgCache.set(slug, sync.content);
		return sync.content;
	}

	// Trigger (and dedupe) a real fetch.
	if (!pending.has(slug)) {
		const promise = resolveSelect('core')
			.getEntityRecord('root', 'icon', slug)
			.then((record) => {
				const svg = record && record.content ? record.content : null;
				svgCache.set(slug, svg);
				return svg;
			})
			.catch(() => {
				svgCache.set(slug, null);
				return null;
			})
			.finally(() => {
				pending.delete(slug);
			});
		pending.set(slug, promise);
	}

	return pending.get(slug);
}

/**
 * Lazily create the `<style>` + state cache for a document.
 *
 * @param {Document} doc
 * @return {{styleEl:HTMLStyleElement,known:Set<string>}}
 */
function ensureStyleSheet(doc) {
	let state = docState.get(doc);
	if (state) {
		return state;
	}

	let styleEl = doc.getElementById(STYLE_ELEMENT_ID);
	if (!styleEl) {
		styleEl = doc.createElement('style');
		styleEl.id = STYLE_ELEMENT_ID;
		(doc.head || doc.documentElement).appendChild(styleEl);
	}

	state = { styleEl, known: new Set() };
	docState.set(doc, state);
	return state;
}

/**
 * Insert a per-slug mask-image rule.
 *
 * @param {Document} doc
 * @param {string}   slug
 * @param {string}   svg  SVG markup to mask with.
 * @return {void}
 */
function injectRule(doc, slug, svg) {
	const state = ensureStyleSheet(doc);
	if (state.known.has(slug)) {
		return;
	}

	// SVG attribute names are case-sensitive. Core's `wp_kses` sanitizer
	// lowercases `viewBox` to `viewbox`. When the SVG is parsed as HTML (the
	// picker preview, the frontend span) the parser restores the canonical
	// case, but a `mask-image` data URI is parsed as a standalone image and
	// keeps the broken `viewbox` — dropping the coordinate system so the icon
	// renders unscaled and clipped. Restore it before encoding.
	const normalized = svg.replace(/\bviewbox=/g, 'viewBox=');
	const dataUri = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(normalized);
	const escapedSlug =
		typeof CSS !== 'undefined' && typeof CSS.escape === 'function'
			? CSS.escape(slug)
			: slug.replace(/["\\\n\r\t]/g, (c) => '\\' + c);
	const selector = `${MARKER_SELECTOR}[data-icon="${escapedSlug}"]`;
	const rule =
		`${selector}{` +
		`background-color:currentColor;` +
		`-webkit-mask-image:url("${dataUri}");` +
		`mask-image:url("${dataUri}");` +
		`}`;

	const sheet = state.styleEl.sheet;
	if (!sheet) {
		// `.sheet` null until <style> is attached + parsed; retry next sweep.
		return;
	}

	try {
		sheet.insertRule(rule, sheet.cssRules.length);
		// Mark known only on success so transient errors stay retryable.
		state.known.add(slug);
	} catch (e) {
		// Quota / transient; retry next sweep.
	}
}

/**
 * Resolve + inject rule for one placeholder. No-op if rule already exists.
 *
 * @param {HTMLImageElement} img
 * @return {Promise<void>}
 */
async function processImg(img) {
	const slug = img.getAttribute('data-icon');
	if (!slug) {
		return;
	}

	const doc = img.ownerDocument;
	const state = docState.get(doc);
	if (state && state.known.has(slug)) {
		return;
	}

	const svg = await ensureSvg(slug);
	if (!svg) {
		return;
	}

	injectRule(doc, slug, svg);
}

/**
 * Process every placeholder in a subtree.
 *
 * @param {Element} root
 * @return {void}
 */
function sweep(root) {
	const imgs = root.querySelectorAll(MARKER_SELECTOR);
	for (const img of imgs) {
		processImg(img);
	}
}

/**
 * Process a mutation target + its descendants without re-scanning the root.
 *
 * @param {Node} node
 * @return {void}
 */
function processSubtree(node) {
	if (node.nodeType !== 1) {
		return;
	}

	if (node.matches && node.matches(MARKER_SELECTOR)) {
		processImg(node);
	}

	if (node.querySelectorAll) {
		const imgs = node.querySelectorAll(MARKER_SELECTOR);
		for (const img of imgs) {
			processImg(img);
		}
	}
}

/**
 * Attach a MutationObserver to a root. Deduped via `watchedRoots`.
 *
 * @param {Element} root
 * @return {void}
 */
function watchRoot(root) {
	if (watchedRoots.has(root)) {
		return;
	}

	sweep(root);

	const observer = new MutationObserver((mutations) => {
		for (const m of mutations) {
			if (m.type === 'childList') {
				for (const node of m.addedNodes) {
					processSubtree(node);
				}
			} else if (
				m.type === 'attributes' &&
				m.attributeName === 'data-icon' &&
				m.target.matches?.(MARKER_SELECTOR)
			) {
				processImg(m.target);
			}
		}
	});

	observer.observe(root, {
		childList: true,
		subtree: true,
		attributes: true,
		attributeFilter: ['data-icon'],
	});

	watchedRoots.set(root, observer);
}

/**
 * Watch canvas iframe(s) + parent doc, with rAF fallback for late-mounting
 * iframes (blob-URL iframes can fire `load` before our listener attaches).
 *
 * @return {void}
 */
function findAndWatchCanvas() {
	/** Attach observers to every editor-canvas iframe. Idempotent. */
	const tryAttach = () => {
		// Re-check each call so iframes whose body lands later get watched.
		const iframes = document.querySelectorAll('iframe[name="editor-canvas"]');
		iframes.forEach((iframe) => {
			let body = null;

			try {
				body = iframe.contentDocument?.body || null;
			} catch (_) {
				return;
			}

			if (body) {
				// Body replaced (FSE template swap) — disconnect orphan.
				const previous = iframeBodies.get(iframe);
				if (previous && previous !== body) {
					const oldObserver = watchedRoots.get(previous);
					if (oldObserver) {
						oldObserver.disconnect();
						watchedRoots.delete(previous);
					}
				}
				iframeBodies.set(iframe, body);
				watchRoot(body);
				return;
			}

			if (!iframeBodies.has(iframe)) {
				iframeBodies.set(iframe, null);
				iframe.addEventListener('load', () => {
					if (iframe.contentDocument?.body) {
						iframeBodies.set(iframe, iframe.contentDocument.body);
						watchRoot(iframe.contentDocument.body);
					}
				});
			}
		});
	};

	tryAttach();
	watchRoot(document.body);

	// rAF-coalesced re-attach: admin mutates constantly; once/frame is enough.
	let scheduled = false;
	const schedule = () => {
		if (scheduled) {
			return;
		}

		scheduled = true;
		requestAnimationFrame(() => {
			scheduled = false;
			tryAttach();
		});
	};

	const docObserver = new MutationObserver(schedule);
	docObserver.observe(document.body, { childList: true, subtree: true });

	// rAF loop to bridge the gap between domReady and the first mutation.
	let frames = 0;
	const tick = () => {
		tryAttach();
		if (++frames < 60) {
			requestAnimationFrame(tick);
		}
	};

	requestAnimationFrame(tick);
}

/**
 * Start the editor renderer on domReady. Called from `src/index.js`.
 *
 * @return {void}
 */
export function startEditorRender() {
	domReady(findAndWatchCanvas);
}
