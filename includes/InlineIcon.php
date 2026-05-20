<?php

namespace Outstand\WP\Icons;

class InlineIcon extends BaseModule {

	/**
	 * Identity marker on stored placeholders and rendered output spans.
	 * Mirrored in the JS format definition; do not change here alone.
	 */
	const MARKER_CLASS = 'os-icons-inline';

	/**
	 * {@inheritDoc}
	 */
	public function register(): void {
		add_filter( 'wp_kses_allowed_html', [ $this, 'allow_data_icon_attr' ], 10, 2 );
		add_filter( 'render_block', [ $this, 'inject_svgs' ], 10, 2 );
	}

	/**
	 * Allow `data-icon` on `<img>` in the `post` kses context so placeholders
	 * survive sanitization. Other contexts unchanged. The `widget` context is
	 * intentionally excluded: it has no `<img>` in its base allowlist, so an
	 * inline-icon placeholder is stripped there regardless of `data-icon`.
	 *
	 * @param  array<string,array<string,bool>> $tags    Allowed tags.
	 * @param  string                           $context Context.
	 * @return array<string,array<string,bool>>
	 */
	public function allow_data_icon_attr( array $tags, string $context ): array {

		if ( 'post' !== $context ) {
			return $tags;
		}

		if ( ! isset( $tags['img'] ) || ! is_array( $tags['img'] ) ) {
			return $tags;
		}

		$tags['img']['data-icon'] = true;

		return $tags;
	}

	/**
	 * Swap `<img class="os-icons-inline" data-icon="...">` placeholders
	 * for `<span class="os-icons-inline" data-icon="..." aria-hidden="true">SVG</span>`.
	 * Class boundary-matched to avoid substring collisions.
	 *
	 * @param  string $block_content Rendered block HTML.
	 * @param  array  $_block        Parsed block (unused; required by filter signature).
	 * @return string
	 */
	public function inject_svgs( $block_content, $_block ): string { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed -- required by filter signature.

		if ( ! is_string( $block_content ) || '' === $block_content ) {
			return (string) $block_content;
		}

		if ( false === strpos( $block_content, self::MARKER_CLASS ) ) {
			return $block_content;
		}

		if ( ! class_exists( '\WP_Icons_Registry' ) ) {
			return $block_content;
		}

		$registry     = \WP_Icons_Registry::get_instance();
		static $cache = [];

		// Identify slugs first; perform swap with targeted regex below.
		$slugs     = [];
		$processor = new \WP_HTML_Tag_Processor( $block_content );

		while ( $processor->next_tag( 'img' ) ) {

			$class = $processor->get_attribute( 'class' );
			if ( ! is_string( $class ) || 1 !== preg_match( '/(?<![\w-])' . preg_quote( self::MARKER_CLASS, '/' ) . '(?![\w-])/', $class ) ) {
				continue;
			}

			$slug = $processor->get_attribute( 'data-icon' );
			if ( ! is_string( $slug ) || '' === $slug ) {
				continue;
			}

			if ( ! array_key_exists( $slug, $cache ) ) {
				$icon           = $registry->get_registered_icon( $slug );
				$cache[ $slug ] = is_array( $icon ) && ! empty( $icon['content'] ) ? $icon['content'] : null;
			}

			if ( null === $cache[ $slug ] ) {
				continue;
			}

			$slugs[ $slug ] = true;
		}

		if ( empty( $slugs ) ) {
			return $block_content;
		}

		$marker_class = self::MARKER_CLASS;
		$rendered     = array_intersect_key( $cache, $slugs );

		$result = preg_replace_callback(
			'/<img\b[^>]*>/i',
			static function ( array $matches ) use ( $rendered, $marker_class ): string {
				$tag = $matches[0];

				if ( false === strpos( $tag, $marker_class ) ) {
					return $tag;
				}

				$probe = new \WP_HTML_Tag_Processor( $tag );
				if ( ! $probe->next_tag( 'img' ) ) {
					return $tag;
				}

				$class = $probe->get_attribute( 'class' );
				if ( ! is_string( $class ) || 1 !== preg_match( '/(?<![\w-])' . preg_quote( $marker_class, '/' ) . '(?![\w-])/', $class ) ) {
					return $tag;
				}

				$slug = $probe->get_attribute( 'data-icon' );
				if ( ! is_string( $slug ) || ! isset( $rendered[ $slug ] ) ) {
					return $tag;
				}

				return sprintf(
					'<span class="%s" data-icon="%s" aria-hidden="true">%s</span>',
					$marker_class,
					esc_attr( $slug ),
					$rendered[ $slug ]
				);
			},
			$block_content
		);

		return is_string( $result ) ? $result : $block_content;
	}
}
