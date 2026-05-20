<?php

namespace Outstand\WP\Icons\Tests\Unit;

class InlineIconRenderTest extends TestCase {

	/**
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();
		$this->seed_icon(
			'test/star',
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><path d="M0 0h10v10H0z"/></svg>'
		);
	}

	/**
	 * @return void
	 */
	public function test_replaces_placeholder_img_with_svg(): void {
		$content = '<p>Foo <img class="os-icons-inline" data-icon="test/star" aria-hidden="true"> bar.</p>';
		$result  = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$this->assertStringContainsString( '<svg', $result );
		$this->assertStringContainsString( 'viewBox="0 0 10 10"', $result );
		$this->assertStringContainsString( 'class="os-icons-inline"', $result );
		$this->assertStringContainsString( 'data-icon="test/star"', $result );
		$this->assertStringNotContainsString( '<img', $result );
	}

	/**
	 * @return void
	 */
	public function test_skips_blocks_without_marker_class(): void {
		$content = '<p>Plain paragraph.</p>';
		$result  = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$this->assertSame( $content, $result );
	}

	/**
	 * @return void
	 */
	public function test_leaves_unknown_slug_unchanged(): void {
		$content = '<p><img class="os-icons-inline" data-icon="missing/icon" aria-hidden="true"></p>';
		$result  = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$this->assertSame( $content, $result );
		$this->assertStringNotContainsString( '<svg', $result );
	}

	/**
	 * @return void
	 */
	public function test_memoizes_repeated_slug(): void {
		$content   = '<p>'
			. '<img class="os-icons-inline" data-icon="test/star" aria-hidden="true"> and '
			. '<img class="os-icons-inline" data-icon="test/star" aria-hidden="true">'
			. '</p>';
		$result    = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$svg_count = substr_count( $result, '<svg' );
		$this->assertSame( 2, $svg_count );
	}

	/**
	 * @return void
	 */
	public function test_ignores_imgs_with_data_icon_but_no_marker_class(): void {
		// A third-party `<img data-icon>` should not be swapped.
		$content = '<p><img data-icon="test/star" aria-hidden="true"></p>';
		$result  = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$this->assertSame( $content, $result );
		$this->assertStringNotContainsString( '<svg', $result );
	}

	/**
	 * @return void
	 */
	public function test_does_not_match_substring_class(): void {
		$content = '<p><img class="my-os-icons-inline" data-icon="test/star" aria-hidden="true"></p>';
		$result  = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$this->assertSame( $content, $result );
		$this->assertStringNotContainsString( '<svg', $result );
	}

	/**
	 * @return void
	 */
	public function test_leaves_unrelated_imgs_in_mixed_content(): void {
		$content = '<p>'
			. '<img src="https://example.com/photo.jpg" alt="photo"> followed by '
			. '<img class="os-icons-inline" data-icon="test/star" aria-hidden="true">'
			. '</p>';
		$result  = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$this->assertStringContainsString( '<img src="https://example.com/photo.jpg"', $result );
		$this->assertStringContainsString( '<svg', $result );
		$this->assertSame( 1, substr_count( $result, '<svg' ) );
	}

	/**
	 * @return void
	 */
	public function test_matches_xhtml_self_closing_form(): void {
		$content = '<p><img class="os-icons-inline" data-icon="test/star" aria-hidden="true" /></p>';
		$result  = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$this->assertStringContainsString( '<svg', $result );
	}

	/**
	 * @return void
	 */
	public function test_replaces_multiple_distinct_slugs_in_one_block(): void {
		$this->seed_icon(
			'test/circle',
			'<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M10 0L0 10 10 20 20 10z"/></svg>'
		);

		$content = '<p>'
			. '<img class="os-icons-inline" data-icon="test/star" aria-hidden="true"> and '
			. '<img class="os-icons-inline" data-icon="test/circle" aria-hidden="true">'
			. '</p>';
		$result  = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$this->assertSame( 2, substr_count( $result, '<svg' ) );
		$this->assertStringContainsString( 'viewBox="0 0 10 10"', $result );
		$this->assertStringContainsString( 'viewBox="0 0 20 20"', $result );
	}

	/**
	 * @return void
	 */
	public function test_swaps_when_marker_class_alongside_others(): void {
		$content = '<p><img class="extra-class os-icons-inline another" data-icon="test/star" aria-hidden="true"></p>';
		$result  = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$this->assertStringContainsString( '<svg', $result );
		$this->assertStringContainsString( 'viewBox="0 0 10 10"', $result );
	}

	/**
	 * @return void
	 */
	public function test_handles_reordered_attributes(): void {
		$content = '<p><img aria-hidden="true" data-icon="test/star" class="os-icons-inline"></p>';
		$result  = $this->inline->inject_svgs( $content, [ 'blockName' => 'core/paragraph' ] );
		$this->assertStringContainsString( '<svg', $result );
		$this->assertStringContainsString( 'viewBox="0 0 10 10"', $result );
	}
}
