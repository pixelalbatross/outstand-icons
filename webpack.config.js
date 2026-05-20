/**
 * WordPress dependencies.
 */
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		'inline-icon': './src/index.js',
	},
	output: {
		...defaultConfig.output,
		path: require( 'path' ).resolve( __dirname, 'build/js' ),
	},
};
