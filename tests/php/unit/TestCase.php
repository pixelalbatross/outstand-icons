<?php

namespace Outstand\WP\Icons\Tests\Unit;

use Outstand\WP\Icons\InlineIcon;

abstract class TestCase extends \WP_UnitTestCase {

	/**
	 * Module under test.
	 *
	 * @var InlineIcon
	 */
	protected InlineIcon $inline;

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		if ( ! class_exists( '\WP_Icons_Registry' ) ) {
			$this->markTestSkipped( 'WP_Icons_Registry is not available in this environment.' );
		}

		$this->inline = new InlineIcon();
	}

	/**
	 * Register an icon directly into the core registry so InlineIcon can
	 * resolve it. Independent of any icon-registration plugin.
	 *
	 * @param  string $name    Namespaced icon name.
	 * @param  string $content SVG markup.
	 * @return void
	 */
	protected function seed_icon( string $name, string $content ): void {

		$registry = \WP_Icons_Registry::get_instance();

		if ( function_exists( 'wp_register_icon' ) && function_exists( 'wp_register_icon_collection' ) && false !== strpos( $name, '/' ) ) {
			[ $collection, $slug ] = explode( '/', $name, 2 );
			if ( ! ( function_exists( 'wp_get_icon_collection' ) && wp_get_icon_collection( $collection ) ) ) {
				wp_register_icon_collection( $collection, [ 'label' => $collection ] );
			}
			wp_register_icon( $slug, $collection, [ 'label' => $name, 'content' => $content ] );
			return;
		}

		$method = new \ReflectionMethod( $registry, 'register' );
		$method->invoke( $registry, $name, [ 'label' => $name, 'content' => $content ] );
	}
}
