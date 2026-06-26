<?php
/**
 * Auto-seed categories on theme activation.
 *
 * @package rp-theme
 * @since rp-theme 1.0
 */

if ( ! function_exists( 'rp_theme_seed_categories' ) ) :
	/**
	 * Creates the initial set of categories for the site.
	 *
	 * Runs once on theme activation via after_switch_theme hook.
	 *
	 * @since rp-theme 1.0
	 *
	 * @return void
	 */
	function rp_theme_seed_categories() {
		if ( get_option( 'rp_theme_categories_seeded' ) ) {
			return;
		}

		$categories = array(
			array(
				'name' => 'Beginner Running',
				'slug' => 'beginner-running',
			),
			array(
				'name' => 'Training',
				'slug' => 'training',
			),
			array(
				'name' => 'Running Form',
				'slug' => 'running-form',
			),
			array(
				'name' => 'Running Science',
				'slug' => 'running-science',
			),
			array(
				'name' => 'Nutrition',
				'slug' => 'nutrition',
			),
			array(
				'name' => 'Strength & Mobility',
				'slug' => 'strength-mobility',
			),
			array(
				'name' => 'Injury Prevention',
				'slug' => 'injury-prevention',
			),
			array(
				'name' => 'Racing',
				'slug' => 'racing',
			),
			array(
				'name' => 'Trail Running',
				'slug' => 'trail-running',
			),
			array(
				'name' => 'Gear',
				'slug' => 'gear',
			),
			array(
				'name' => 'Running Psychology',
				'slug' => 'running-psychology',
			),
			array(
				'name' => 'Running History & Culture',
				'slug' => 'running-history-culture',
			),
		);

		foreach ( $categories as $cat ) {
			$existing = term_exists( $cat['slug'], 'category' );

			if ( ! $existing ) {
				wp_insert_term(
					$cat['name'],
					'category',
					array(
						'slug'        => $cat['slug'],
						'description' => '',
					)
				);
			}
		}

		update_option( 'rp_theme_categories_seeded', true );
	}
endif;
add_action( 'after_switch_theme', 'rp_theme_seed_categories' );
