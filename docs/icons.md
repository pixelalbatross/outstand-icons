# Inline Icons

Insert icons inline in any rich-text field (paragraph, heading, list, button, etc.) via the **Inline icon** button in the rich-text toolbar. The picker lists every icon registered with the core `core/icon` block registry — the icons that ship with WordPress (`core/*`) plus any registered by other plugins. This plugin only consumes the registry; it does not register or remove icons.

## Inserting an icon

Place the cursor in any rich-text field, open the **Inline icon** toolbar button, search, and pick. Saved markup is minimal — no `src`, no inline styles, just the slug in `data-icon` plus a single CSS class:

```html
<img class="os-icons-inline" data-icon="my-plugin/star" aria-hidden="true">
```

The `os-icons-inline` class is the identity marker — it disambiguates the format from `core/image` (rich-text requires a unique `(tagName, className)` pair) and is what both the PHP `render_block` swap and the editor-canvas placeholder renderer key off (boundary-matched to avoid substring collisions). The `data-icon` attribute carries the slug verbatim. A small `wp_kses_allowed_html` filter permits `data-icon` on `<img>` in the `post` context so the placeholder round-trips through sanitization; `<img>`, `class`, and `aria-hidden` are already in the default allowlist. (The `widget` context has no `<img>` in its base allowlist, so inline icons are not supported there.)

## Rendering

The frontend `render_block` filter swaps every placeholder `<img>` for `<span class="os-icons-inline" data-icon="…" aria-hidden="true"><svg>…</svg></span>`, so the visible HTML inherits `currentColor`. In the editor canvas a per-slug CSS rule paints the same shape via `background-color: currentColor` + `mask-image` (selector `img.os-icons-inline[data-icon="…"]`); an inline `content: url("data:image/svg+xml,…")` on `img.os-icons-inline` replaces the replaced-element source with a transparent 1×1 SVG so the browser's broken-image glyph never appears (including during text selection, before the per-slug rule resolves).

If a placeholder's slug is not present in the registry, the `<img>` is left untouched — nothing is rendered for it.
