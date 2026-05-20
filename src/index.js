/**
 * WordPress dependencies
 */
import { registerFormatType } from '@wordpress/rich-text';

/**
 * Internal dependencies
 */
import { FORMAT_NAME, format } from './format';
import { startEditorRender } from './editor-render';
import './style.css';
import './editor.css';

registerFormatType(FORMAT_NAME, format);
startEditorRender();
