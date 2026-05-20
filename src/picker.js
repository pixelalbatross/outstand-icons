/**
 * WordPress dependencies
 */
import { useState, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { TextControl, Button, Spinner } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Render the search-and-grid icon picker.
 *
 * @deprecated Pending Gutenberg PR #76787. Replace with `IconPickerModal` imported from `@wordpress/block-editor` once it ships.
 *
 * @param {Object}   props          Component props.
 * @param {Function} props.onSelect Invoked with the picked icon record.
 * @return {JSX.Element} Loading state, empty state, or the populated grid.
 */
export default function Picker({ onSelect }) {
	const [query, setQuery] = useState('');

	// Fetch every registered icon in one round trip. The collection is
	// small (dozens to low hundreds) and shared with the core icon block
	// picker, so it's typically already in cache by the time the popover
	// opens.
	const icons = useSelect(
		(select) => select('core').getEntityRecords('root', 'icon', { per_page: -1 }),
		[],
	);

	// In-memory case-insensitive filter on name + label. useMemo so we
	// don't re-filter on every keystroke in unrelated state.
	const filtered = useMemo(() => {
		if (!icons) {
			return null;
		}

		const q = query.trim().toLowerCase();
		if (!q) {
			return icons;
		}

		return icons.filter(
			(i) =>
				i.name.toLowerCase().includes(q) || (i.label && i.label.toLowerCase().includes(q)),
		);
	}, [icons, query]);

	if (icons === null || icons === undefined) {
		return (
			<div className="os-icons-picker is-loading">
				<Spinner />
			</div>
		);
	}

	if (icons.length === 0) {
		return (
			<div className="os-icons-picker is-empty">
				<p>{__('No icons available.', 'outstand-icons')}</p>
			</div>
		);
	}

	return (
		<div className="os-icons-picker">
			<TextControl
				label={__('Search icons', 'outstand-icons')}
				value={query}
				onChange={setQuery}
				__nextHasNoMarginBottom
				__next40pxDefaultSize
			/>
			<div className="os-icons-picker__grid">
				{filtered.map((icon) => (
					<Button
						key={icon.name}
						label={icon.label || icon.name}
						className="os-icons-picker__tile"
						onClick={() => onSelect(icon)}
					>
						{/* SVG comes from the REST controller which kses-sanitizes
						    it server-side, so dangerouslySetInnerHTML is safe at
						    this trust boundary. */}
						<span
							className="os-icons-picker__preview"
							dangerouslySetInnerHTML={{ __html: icon.content || '' }}
						/>
					</Button>
				))}
			</div>
		</div>
	);
}
