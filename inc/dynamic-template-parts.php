<?php
/**
 * Dynamic template part loading via render_block_data filter.
 *
 * Swaps template part slugs based on page context:
 *   parts/{slug}.html           -> default
 *   parts/{slug}-{context}.html -> context-specific override
 *
 * @package rp-theme
 * @since rp-theme 1.0
 */

add_filter( 'render_block_data', 'rp_theme_dynamic_template_part' );

function rp_theme_dynamic_template_part( array $parsed_block ): array {
	if ( ( $parsed_block['blockName'] ?? '' ) !== 'core/template-part' ) {
		return $parsed_block;
	}

	$slug   = $parsed_block['attrs']['slug'] ?? '';
	$context = '';

	if ( is_singular( 'post' ) ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$categories = get_the_category( $post->ID );
			if ( ! empty( $categories ) ) {
				$context = $categories[0]->slug;
			}
		}
	} elseif ( is_category() ) {
		$category = get_queried_object();
		if ( $category instanceof WP_Term ) {
			$context = 'archive-' . $category->slug;
		}
	}

	if ( ! $context ) {
		return $parsed_block;
	}

	$parts_dir   = get_block_theme_folders()['wp_template_part'];
	$context_slug = "{$slug}-{$context}";

	if ( file_exists( get_theme_file_path( "{$parts_dir}/{$context_slug}.html" ) ) ) {
		$parsed_block['attrs']['slug'] = $context_slug;
	}

	return $parsed_block;
}
