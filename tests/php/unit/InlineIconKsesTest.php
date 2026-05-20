<?php

namespace Outstand\WP\Icons\Tests\Unit;

class InlineIconKsesTest extends TestCase {

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->inline->register();
	}

	/**
	 * @return void
	 */
	public function test_allows_data_icon_attr_on_img_in_post_context(): void {
		$tags = wp_kses_allowed_html( 'post' );
		$this->assertArrayHasKey( 'img', $tags );
		$this->assertArrayHasKey( 'data-icon', $tags['img'] );
		$this->assertTrue( $tags['img']['data-icon'] );
	}

	/**
	 * The `widget` context has no `<img>` in its base allowlist, so the filter
	 * leaves it untouched — `data-icon` is not added.
	 *
	 * @return void
	 */
	public function test_does_not_add_data_icon_in_widget_context(): void {
		$tags = wp_kses_allowed_html( 'widget' );
		$this->assertArrayNotHasKey( 'data-icon', $tags['img'] ?? [] );
	}

	/**
	 * @return void
	 */
	public function test_does_not_modify_other_contexts(): void {
		$tags = wp_kses_allowed_html( 'data' );
		$this->assertArrayNotHasKey( 'data-icon', $tags['img'] ?? [] );
	}

	/**
	 * @return void
	 */
	public function test_does_not_modify_comment_context(): void {
		$tags = wp_kses_allowed_html( 'comment' );
		$this->assertArrayNotHasKey( 'data-icon', $tags['img'] ?? [] );
	}

	/**
	 * @return void
	 */
	public function test_data_icon_survives_wp_kses_post(): void {
		$raw      = '<p>Hi <img class="os-icons-inline" data-icon="my/star" aria-hidden="true"> there.</p>';
		$filtered = wp_kses_post( $raw );
		$this->assertStringContainsString( 'class="os-icons-inline"', $filtered );
		$this->assertStringContainsString( 'data-icon="my/star"', $filtered );
		$this->assertStringContainsString( 'aria-hidden="true"', $filtered );
	}

	/**
	 * `aria-hidden` is in the WP core kses 'img' allowlist by default. We
	 * don't need to add it; this test pins that down so a future kses
	 * regression here would be loud.
	 *
	 * @return void
	 */
	public function test_aria_hidden_already_allowed_on_img(): void {
		$tags = wp_kses_allowed_html( 'post' );
		$this->assertArrayHasKey( 'aria-hidden', $tags['img'] ?? [] );
	}
}
