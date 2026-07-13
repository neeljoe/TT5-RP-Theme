<?php
/**
 * Title: Front Page Hero
 * Slug: rp-theme/front-page-hero
 * Categories: banner
 * Description: Hero section for the front page with theme image and text.
 *
 * @package WordPress
 * @subpackage rp-theme
 * @since rp-theme 1.0
 */

?>

<!-- wp:group {"style":{"spacing":{"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group" style="margin-top:0"><!-- wp:group {"align":"wide","className":"test-overflow","style":{"border":{"radius":{"topLeft":"32px","topRight":"32px","bottomLeft":"32px","bottomRight":"32px"},"width":"1px"}},"borderColor":"accent-4","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide test-overflow has-border-color has-accent-4-border-color" style="border-width:1px;border-top-left-radius:32px;border-top-right-radius:32px;border-bottom-left-radius:32px;border-bottom-right-radius:32px"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/new-hero.jpg' ) ); ?>" alt="<?php echo esc_attr_x( 'Runner on a trail', 'Hero image alt text', 'rp-theme' ); ?>" /></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%"} -->
<div class="wp-block-column is-vertically-aligned-center" style="flex-basis:50%"><!-- wp:heading {"level":1,"fontSize":"xx-large"} -->
<h1 class="wp-block-heading has-xx-large-font-size"><?php echo esc_html_x( 'Run Better. Run Smarter.', 'Hero heading', 'rp-theme' ); ?></h1>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size"><?php echo esc_html_x( 'Evidence-based running advice for every level — from your first mile to your fastest race.', 'Hero paragraph', 'rp-theme' ); ?></p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/category/beginner-running/"><?php esc_html_e( 'Start Exploring', 'rp-theme' ); ?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->
