/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { Popover } from '@wordpress/components';
import { insertObject, useAnchor } from '@wordpress/rich-text';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Picker from './picker';
import { FORMAT_NAME, format } from './format';

/**
 * Toolbar button icon. Copied from `core/icon` block for visual consistency.
 *
 * @type {JSX.Element}
 */
const icon = (
	<svg
		xmlns="http://www.w3.org/2000/svg"
		viewBox="0 0 24 24"
		width="24"
		height="24"
		fill="none"
		aria-hidden="true"
		focusable="false"
	>
		<path
			d="M6 9.5h3.5V6H6v3.5Zm5 .5a1 1 0 0 1-.898.995L10 11H5.5l-.103-.005a1 1 0 0 1-.892-.893L4.5 10V5.5a1 1 0 0 1 1-1H10a1 1 0 0 1 1 1V10ZM18.25 7.75a2 2 0 1 0-4 0 2 2 0 0 0 4 0Zm1.5 0a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0ZM6.88 13.535a1 1 0 0 1 1.74 0l2.534 4.472a1 1 0 0 1-.87 1.493H5.216a1 1 0 0 1-.87-1.493l2.534-4.472ZM6.074 18h3.352L7.75 15.041l-1.676 2.96ZM14.952 13h2.596a1 1 0 0 1 .866.5l1.298 2.25a1 1 0 0 1 0 1L18.414 19l-.074.11a1 1 0 0 1-.792.39h-2.596a1 1 0 0 1-.792-.39l-.074-.11-1.298-2.25a1.001 1.001 0 0 1 0-1l1.298-2.25a1 1 0 0 1 .866-.5Zm-.72 3.25 1.01 1.75h2.017l1.009-1.75-1.01-1.75h-2.017l-1.01 1.75Z"
			fill="currentColor"
		/>
	</svg>
);

/**
 * Toolbar button + picker popover.
 *
 * @param {Object}                props
 * @param {Object}                props.value
 * @param {Function}              props.onChange
 * @param {boolean}               props.isActive
 * @param {{current:HTMLElement}} props.contentRef
 * @return {JSX.Element}
 */
export default function Edit({ value, onChange, isActive, contentRef }) {
	const [isOpen, setOpen] = useState(false);

	// Anchor popover to caret. Mirrors core/format-library/image.
	const popoverAnchor = useAnchor({
		editableContentElement: contentRef?.current,
		settings: format,
	});

	/**
	 * Insert picked icon, close popover.
	 *
	 * @param {{name:string,label:string,content:string}} pickedIcon
	 * @return {void}
	 */
	const handleSelect = (pickedIcon) => {
		const newValue = insertObject(value, {
			type: FORMAT_NAME,
			attributes: {
				dataIcon: pickedIcon.name,
				ariaHidden: 'true',
			},
		});
		onChange(newValue);
		setOpen(false);
	};

	return (
		<>
			<RichTextToolbarButton
				icon={icon}
				title={__('Inline icon', 'outstand-icons')}
				onClick={() => setOpen((v) => !v)}
				isActive={isActive}
			/>
			{isOpen && (
				<Popover anchor={popoverAnchor} onClose={() => setOpen(false)} placement="bottom">
					<Picker onSelect={handleSelect} />
				</Popover>
			)}
		</>
	);
}
