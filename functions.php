<?php
/**
 * rp-theme functions and definitions.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package WordPress
 * @subpackage rp-theme
 * @since rp-theme 1.0
 */

require_once get_theme_file_path( 'inc/categories.php' );

if ( ! function_exists( 'rp_theme_post_format_setup' ) ) :
	/**
	 * Adds theme support for post formats.
	 *
	 * @since rp-theme 1.0
	 *
	 * @return void
	 */
	function rp_theme_post_format_setup() {
		add_theme_support( 'post-formats', array( 'aside', 'audio', 'chat', 'gallery', 'image', 'link', 'quote', 'status', 'video' ) );
	}
endif;
add_action( 'after_setup_theme', 'rp_theme_post_format_setup' );

if ( ! function_exists( 'rp_theme_editor_style' ) ) :
	/**
	 * Enqueues editor-style.css in the editors.
	 *
	 * @since rp-theme 1.0
	 *
	 * @return void
	 */
	function rp_theme_editor_style() {
		add_editor_style( 'assets/css/editor-style.css' );
	}
endif;
add_action( 'after_setup_theme', 'rp_theme_editor_style' );

if ( ! function_exists( 'rp_theme_enqueue_styles' ) ) :
	/**
	 * Enqueues the built stylesheet from public/style.css.
	 *
	 * @since rp-theme 1.0
	 *
	 * @return void
	 */
	function rp_theme_enqueue_styles() {
		$file = 'public/style.css';

		if ( ! file_exists( get_theme_file_path( $file ) ) ) {
			$file = 'style.css';
		}

		wp_enqueue_style(
			'rp-theme-style',
			get_theme_file_uri( $file ),
			array(),
			wp_get_theme()->get( 'Version' )
		);
		wp_style_add_data(
			'rp-theme-style',
			'path',
			get_theme_file_path( $file )
		);
	}
endif;
add_action( 'wp_enqueue_scripts', 'rp_theme_enqueue_styles' );

if ( ! function_exists( 'rp_theme_enqueue_interactivity' ) ) :
	/**
	 * Enqueues the interactivity module for scroll animations.
	 *
	 * @since rp-theme 1.0
	 *
	 * @return void
	 */
	function rp_theme_enqueue_interactivity() {
		$file       = 'public/js/interactivity.js';
		$asset_file = 'public/js/interactivity.asset.php';
		$asset_path = get_theme_file_path( $asset_file );
		$deps       = array();
		$version    = wp_get_theme()->get( 'Version' );

		if ( file_exists( $asset_path ) ) {
			$asset   = require $asset_path;
			$deps    = $asset['dependencies'] ?? array();
			$version = $asset['version'] ?? $version;
		}

		if ( file_exists( get_theme_file_path( $file ) ) ) {
			wp_enqueue_script_module(
				'rp-theme-interactivity',
				get_theme_file_uri( $file ),
				$deps,
				$version
			);
		}
	}
endif;
add_action( 'wp_enqueue_scripts', 'rp_theme_enqueue_interactivity' );

if ( ! function_exists( 'rp_theme_block_styles' ) ) :
	/**
	 * Registers custom block styles.
	 *
	 * @since rp-theme 1.0
	 *
	 * @return void
	 */
	function rp_theme_block_styles() {
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'rp-theme' ),
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
	}
endif;
add_action( 'init', 'rp_theme_block_styles' );

if ( ! function_exists( 'rp_theme_pattern_categories' ) ) :
	/**
	 * Registers pattern categories.
	 *
	 * @since rp-theme 1.0
	 *
	 * @return void
	 */
	function rp_theme_pattern_categories() {

		register_block_pattern_category(
			'rp_theme_page',
			array(
				'label'       => __( 'Pages', 'rp-theme' ),
				'description' => __( 'A collection of full page layouts.', 'rp-theme' ),
			)
		);

		register_block_pattern_category(
			'rp_theme_post-format',
			array(
				'label'       => __( 'Post formats', 'rp-theme' ),
				'description' => __( 'A collection of post format patterns.', 'rp-theme' ),
			)
		);
	}
endif;
add_action( 'init', 'rp_theme_pattern_categories' );

if ( ! function_exists( 'rp_theme_register_block_bindings' ) ) :
	/**
	 * Registers the post format block binding source.
	 *
	 * @since rp-theme 1.0
	 *
	 * @return void
	 */
	function rp_theme_register_block_bindings() {
		register_block_bindings_source(
			'rp-theme/format',
			array(
				'label'              => _x( 'Post format name', 'Label for the block binding placeholder in the editor', 'rp-theme' ),
				'get_value_callback' => 'rp_theme_format_binding',
			)
		);
	}
endif;
add_action( 'init', 'rp_theme_register_block_bindings' );

if ( ! function_exists( 'rp_theme_format_binding' ) ) :
	/**
	 * Callback function for the post format name block binding source.
	 *
	 * @since rp-theme 1.0
	 *
	 * @return string|void Post format name, or nothing if the format is 'standard'.
	 */
	function rp_theme_format_binding() {
		$post_format_slug = get_post_format();

		if ( $post_format_slug && 'standard' !== $post_format_slug ) {
			return get_post_format_string( $post_format_slug );
		}
	}
endif;
