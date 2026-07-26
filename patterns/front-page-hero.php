<?php
/**
 * Title: Front Page Hero
 * Slug: rp-theme/front-page-hero
 * Categories: rp_theme_page
 * Description: Hero section for the front page with image and call to action.
 *
 * @package WordPress
 * @subpackage RP_Theme
 * @since rp-theme 1.0
 */

?>
<!-- wp:group {"style":{"spacing":{"margin":{"top":"0"}}},"layout":{"type":"constrained","contentSize":"1280px"}} -->
<div class="wp-block-group" style="margin-top:0"><!-- wp:group {"align":"wide","className":"test-overflow","style":{"border":{"radius":{"topLeft":"32px","topRight":"32px","bottomLeft":"32px","bottomRight":"32px"},"width":"1px"}},"borderColor":"accent-4","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide test-overflow has-border-color has-accent-4-border-color" style="border-width:1px;border-top-left-radius:32px;border-top-right-radius:32px;border-bottom-left-radius:32px;border-bottom-right-radius:32px"><!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column -->
<div class="wp-block-column"><!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="http://runpartner.local/wp-content/themes/TT5-RP-Theme/assets/images/new-hero.jpg" alt="Runner on a trail"/></figure>
<!-- /wp:image --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"50%","style":{"spacing":{"padding":{"right":"var:preset|spacing|50","top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50"}}}} -->
<div class="wp-block-column is-vertically-aligned-center" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50);flex-basis:50%"><!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"level":1,"style":{"elements":{"link":{"color":{"text":"var:preset|color|accent-3"}}}},"textColor":"accent-3","fontSize":"xx-large"} -->
<h1 class="wp-block-heading has-accent-3-color has-text-color has-link-color has-xx-large-font-size">Run Better.</h1>
<!-- /wp:heading -->

<!-- wp:heading {"level":1,"style":{"elements":{"link":{"color":{"text":"var:preset|color|accent-3"}}}},"textColor":"accent-3"} -->
<h1 class="wp-block-heading has-accent-3-color has-text-color has-link-color">Run Smarter.</h1>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"style":{"elements":{"link":{"color":{"text":"var:preset|color|contrast"}}}},"textColor":"contrast","fontSize":"large"} -->
<p class="has-contrast-color has-text-color has-link-color has-large-font-size">Whether you're preparing for your first 5K or your next marathon, runpartner helps you understand training, improve your technique, choose the right gear, and enjoy running with confidence.</p>
<!-- /wp:paragraph -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button -->
<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/learn">Explore</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->