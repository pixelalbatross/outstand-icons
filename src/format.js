/**
 * Internal dependencies
 */
import Edit from './edit';

/**
 * Unique identifier for the inline icon format.
 *
 * @type {string}
 */
export const FORMAT_NAME = 'outstand/inline-icon';

/**
 * Rich-text format config.
 *
 * `object: true` = inline replacement. `tagName: 'img'` required: object
 * formats serialize without a closing tag, so the tag must be void.
 * `className` required to disambiguate from `core/image` (rich-text needs
 * a unique `(tagName, className)` pair); also the identity marker keyed
 * off by the PHP swap and the editor placeholder renderer.
 *
 * Slug stored in `data-icon`. Needs `InlineIcon::allow_data_icon_attr` to
 * survive kses; class/aria-hidden/img already in default allowlist.
 *
 * @type {Object}
 */
export const format = {
	title: 'Inline icon',
	tagName: 'img',
	className: 'os-icons-inline',
	attributes: {
		dataIcon: 'data-icon',
		ariaHidden: 'aria-hidden',
	},
	object: true,
	edit: Edit,
};
