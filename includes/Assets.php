<?php

namespace Outstand\WP\Icons;

class Assets extends BaseModule {
	use GetAssetInfo;

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {

		$this->setup_asset_vars(
			dist_path: OS_ICONS_DIST_PATH,
			fallback_version: OS_ICONS_VERSION
		);

		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_scripts' ] );
		add_action( 'enqueue_block_assets', [ $this, 'enqueue_block_styles' ] );
	}

	/**
	 * Enqueue the inline-icon editor script and editor-only styles.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_scripts(): void {

		wp_enqueue_script(
			'os-icons-inline-icon',
			OS_ICONS_DIST_URL . 'js/inline-icon.js',
			$this->get_asset_info( 'inline-icon', 'dependencies' ),
			$this->get_asset_info( 'inline-icon', 'version' ),
			true
		);

		wp_set_script_translations(
			'os-icons-inline-icon',
			'outstand-icons',
			OS_ICONS_PATH . 'languages'
		);

		$editor_style = OS_ICONS_DIST_PATH . 'js/inline-icon.css';
		if ( file_exists( $editor_style ) ) {
			wp_enqueue_style(
				'os-icons-inline-icon-editor',
				OS_ICONS_DIST_URL . 'js/inline-icon.css',
				[],
				$this->get_asset_info( 'inline-icon', 'version' )
			);
		}
	}

	/**
	 * Enqueue the frontend stylesheet on both the editor canvas and the frontend.
	 *
	 * @return void
	 */
	public function enqueue_block_styles(): void {

		$style_file = OS_ICONS_DIST_PATH . 'js/style-inline-icon.css';
		if ( ! file_exists( $style_file ) ) {
			return;
		}

		wp_enqueue_style(
			'os-icons-inline-icon',
			OS_ICONS_DIST_URL . 'js/style-inline-icon.css',
			[],
			$this->get_asset_info( 'inline-icon', 'version' )
		);
	}
}
