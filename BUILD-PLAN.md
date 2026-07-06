# RunPartner — Full Website Build Plan

> Build plan based on WordPress Developer Blog articles:
> - [Refactoring the multi-block plugin](https://developer.wordpress.org/news/2025/08/refactoring-the-multi-block-plugin-build-smarter-register-cleaner-scale-easier/) (Aug 2025)
> - [Dynamically loading template parts in block themes](https://developer.wordpress.org/news/2026/06/dynamically-loading-template-parts-in-block-themes/) (Jun 2026)
>
> **Theme:** TT5-RP-Theme (Twenty Twenty-Five derivative)  
> **Plugins:** RP-Multi-Block (4 custom Interactivity API blocks), Novamira, Instant Images  
> **Content:** 70 published posts across 8 categories  
> **WordPress:** 7.0 | **PHP:** 8.2

---

## Table of Contents

1. [Site Overview](#1-site-overview)
2. [Architecture Principles](#2-architecture-principles)
3. [Phase 1: Dynamic Template Part System](#3-phase-1-dynamic-template-part-system)
4. [Phase 2: Front Page Template](#4-phase-2-front-page-template)
5. [Phase 3: Single Post Template Overhaul](#5-phase-3-single-post-template-overhaul)
6. [Phase 4: Archive Templates Overhaul](#6-phase-4-archive-templates-overhaul)
7. [Phase 5: Other Templates](#7-phase-5-other-templates)
8. [Phase 6: Template Parts & Patterns Cleanup](#8-phase-6-template-parts--patterns-cleanup)
9. [Phase 7: Content Refinement](#9-phase-7-content-refinement)
10. [Phase 8: Testing & Launch](#10-phase-8-testing--launch)
11. [File Inventory](#11-file-inventory)

---

## 1. Site Overview

### Design System (from `theme.json`)

| Token | Value |
|---|---|
| **Body font** | Lato, sans-serif (300 weight) |
| **Heading font** | Montserrat, sans-serif (700 weight, uppercase) |
| **Mono font** | Fira Code, monospace |
| **Base color** | `#FFFFFF` |
| **Contrast color** | `#111111` |
| **Accent 1** | `#ffb703` (yellow/gold) |
| **Accent 2** | `#219ebc` (teal/blue) |
| **Accent 3** | `#023047` (dark navy) |
| **Accent 4** | `#686868` (gray) |
| **Content width** | 645px |
| **Wide width** | 1340px |

### Categories & Content

| # | Category | Slug | Posts | Focus |
|---|---|---|---|---|
| 1 | Beginner Running | `beginner-running` | 7 | How to start, first week, gear, mistakes |
| 2 | Training | `training` | 9 | Easy runs, intervals, tempo, schedules |
| 3 | Running Form | `running-form` | 9 | Foot strike, cadence, posture, breathing |
| 4 | Strength & Mobility | `strength-mobility` | 10 | Strength training, core, warm-ups, stretching |
| 5 | Injury Prevention | `injury-prevention` | 8 | Injuries, recovery, fatigue, mileage safety |
| 6 | Nutrition | `nutrition` | 11 | Hydration, gels, protein, carbs, supplements |
| 7 | Running Science | `running-science` | 10 | VO₂ max, lactate threshold, running economy |
| 8 | Running Psychology | `running-psychology` | 6 | Mindset, motivation, race anxiety, plateaus |

### Custom Blocks (from RP-Multi-Block plugin)

| Block | Name | Description |
|---|---|---|
| `disney/search-toggle` | Search Toggle | Opens a sliding search panel |
| `disney/search-panel` | Search Panel | Sliding search overlay panel |
| `disney/mobile-menu-toggle` | Mobile Menu Toggle | Hamburger button for mobile nav |
| `disney/mobile-menu` | Mobile Menu | Accordion mobile navigation menu |

All use the **Interactivity API** (`@wordpress/interactivity`) with shared `disney` store namespace.

---

## 2. Architecture Principles

### Principle 1: Block Data Interception (from Justin Tadlock article)

Use `render_block_data` filter to dynamically swap template part slugs before rendering. This avoids duplicating entire templates just to change one section.

```
parts/{slug}.html               ← default (fallback)
parts/{slug}-{context}.html     ← context-specific override
```

### Principle 2: Template Parts as Override Layers

Every template part has a naming convention:
- `sidebar-post.html` — default sidebar for single posts
- `sidebar-post-beginner-running.html` — override for Beginner Running category
- `banner-archive.html` — default archive banner
- `banner-archive-training.html` — override for Training category

This gives **graceful fallback**: if no override exists, the default loads.

### Principle 3: Templates Compose, Not Duplicate

- `templates/` — top-level page structure (skeleton)
- `parts/` — reusable sections (header, footer, sidebar, banner)
- `patterns/` — reusable block layouts (query loops, CTAs, hero)

Templates should be lean — just compose parts and patterns.

---

## 3. Phase 1: Dynamic Template Part System

**Goal:** Build the core `render_block_data` filter that powers context-aware template parts.

### New file: `inc/dynamic-template-parts.php`

```php
<?php
/**
 * Dynamic template part loading via render_block_data filter.
 *
 * Swaps template part slugs based on page context:
 *   parts/{slug}.html           → default
 *   parts/{slug}-{context}.html → context-specific override
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

    // Determine context based on current page view.
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

    $parts_dir = get_block_theme_folders()['wp_template_part'];
    $context_slug = "{$slug}-{$context}";

    if ( locate_template( "{$parts_dir}/{$context_slug}.html", false, false ) ) {
        $parsed_block['attrs']['slug'] = $context_slug;
    }

    return $parsed_block;
}
```

### Modification to `functions.php`

Add require at top (after existing `categories.php` line):

```php
require_once get_theme_file_path( 'inc/dynamic-template-parts.php' );
```

### What this enables

Once this filter is active, adding `<!-- wp:template-part {"slug":"sidebar-post"} /-->` to any template will:
- Load `parts/sidebar-post.html` by default
- Load `parts/sidebar-post-beginner-running.html` if viewing a Beginner Running post (and the file exists)
- Fall back gracefully to the default if no override exists

The same pattern works for **any** template part: headers, footers, banners, etc.

---

## 4. Phase 2: Front Page Template

**Goal:** Transform `templates/front-page.html` from a single-post layout into a curated landing page.

### Current state

```html
header → featured image → post title → post content → footer
```

### Target layout

```
┌──────────────────────────────────────────────────┐
│                  HEADER                           │
├──────────────────────────────────────────────────┤
│                                                  │
│   HERO — Full-width cover block                  │
│   "Run Better. Run Smarter."                     │
│   "Evidence-based running advice for every level" │
│   [Start Exploring] CTA button                   │
│                                                  │
├──────────────────────────────────────────────────┤
│                                                  │
│   INTRO BANNER — Constrained heading + paragraph  │
│   "Welcome to [Site Name]"                       │
│   Brief about our mission covering all aspects   │
│   of running: training, form, nutrition, etc.    │
│                                                  │
├──────────────────────────────────────────────────┤
│                                                  │
│   TOPIC CATEGORIES — Grid layout, 4 columns      │
│   ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────┐│
│   │ Beginner │ │ Training │ │  Form    │ │Str&Mb││
│   └──────────┘ └──────────┘ └──────────┘ └──────┘│
│   ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────┐│
│   │ Injury   │ │Nutrition │ │ Science  │ │Psych ││
│   └──────────┘ └──────────┘ └──────────┘ └──────┘│
│   Each: gradient/placeholder image, name,          │
│   post count, link to /category/{slug}/           │
│                                                  │
├──────────────────────────────────────────────────┤
│                                                  │
│   LATEST ARTICLES — wp:query loop                 │
│   "Latest from the Blog" heading                  │
│   6 most recent posts in 3-column grid            │
│   Each: featured image, title, date, excerpt      │
│                                                  │
├──────────────────────────────────────────────────┤
│                                                  │
│   NEWSLETTER CTA — pattern                        │
│   "Get Running Tips in Your Inbox"                │
│   [Subscribe] button                              │
│                                                  │
├──────────────────────────────────────────────────┤
│                  FOOTER                           │
└──────────────────────────────────────────────────┘
```

### Block markup for `templates/front-page.html`

```html
<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:cover {"align":"full","minHeight":600,"contentPosition":"center center","style":{"spacing":{"padding":{"top":"var:preset|spacing|80","bottom":"var:preset|spacing|80"}}}} -->
<div class="wp-block-cover alignfull" style="padding-top:var(--wp--preset--spacing--80);padding-bottom:var(--wp--preset--spacing--80);min-height:600px">
    <span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim"></span>
    <div class="wp-block-cover__inner-container">
        <!-- wp:heading {"textAlign":"center","level":1,"fontSize":"xx-large"} -->
        <h1 class="wp-block-heading has-text-align-center has-xx-large-font-size">Run Better. Run Smarter.</h1>
        <!-- /wp:heading -->
        <!-- wp:paragraph {"align":"center","fontSize":"large"} -->
        <p class="has-text-align-center has-large-font-size">Evidence-based running advice for every level — from your first mile to your fastest race.</p>
        <!-- /wp:paragraph -->
        <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
        <div class="wp-block-buttons">
            <!-- wp:button -->
            <div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/category/beginner-running/">Start Exploring</a></div>
            <!-- /wp:button -->
        </div>
        <!-- /wp:buttons -->
    </div>
</div>
<!-- /wp:cover -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
    <!-- wp:paragraph {"align":"center","fontSize":"large"} -->
    <p class="has-text-align-center has-large-font-size">Welcome to <strong>RunPartner</strong> — your library of evidence-based running knowledge. Whether you're lacing up for the first time or chasing a new PR, we cover training, form, strength, nutrition, science, psychology, and injury prevention.</p>
    <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-bottom:var(--wp--preset--spacing--70)">
    <!-- wp:heading {"textAlign":"center","fontSize":"x-large"} -->
    <h2 class="wp-block-heading has-text-align-center has-x-large-font-size">Explore by Topic</h2>
    <!-- /wp:heading -->

    <!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"grid","minimumColumnWidth":"16rem"}} -->
    <div class="wp-block-group alignwide">
        <!-- Category cards — each is a grouped cover block linking to /category/{slug}/ -->

        <!-- wp:cover {"dimRatio":30,"isUserOverlayColor":true,"customOverlayColor":"#023047","minHeight":200,"layout":{"type":"constrained"}} -->
        <div class="wp-block-cover" style="min-height:200px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim" style="background-color:#023047"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","placeholder":"Category name","fontSize":"large"} --><p class="has-text-align-center has-large-font-size"><a href="/category/beginner-running/" style="color:#ffffff">Beginner Running</a></p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size" style="color:#ffffffcc">7 articles</p><!-- /wp:paragraph --></div></div>
        <!-- /wp:cover -->

        <!-- wp:cover {"dimRatio":30,"isUserOverlayColor":true,"customOverlayColor":"#219ebc","minHeight":200,"layout":{"type":"constrained"}} -->
        <div class="wp-block-cover" style="min-height:200px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim" style="background-color:#219ebc"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","fontSize":"large"} --><p class="has-text-align-center has-large-font-size"><a href="/category/training/" style="color:#ffffff">Training</a></p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size" style="color:#ffffffcc">9 articles</p><!-- /wp:paragraph --></div></div>
        <!-- /wp:cover -->

        <!-- wp:cover {"dimRatio":30,"isUserOverlayColor":true,"customOverlayColor":"#ffb703","minHeight":200,"layout":{"type":"constrained"}} -->
        <div class="wp-block-cover" style="min-height:200px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim" style="background-color:#ffb703"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","fontSize":"large"} --><p class="has-text-align-center has-large-font-size"><a href="/category/running-form/" style="color:#111111">Running Form</a></p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size" style="color:#111111cc">9 articles</p><!-- /wp:paragraph --></div></div>
        <!-- /wp:cover -->

        <!-- wp:cover {"dimRatio":30,"isUserOverlayColor":true,"customOverlayColor":"#fb8500","minHeight":200,"layout":{"type":"constrained"}} -->
        <div class="wp-block-cover" style="min-height:200px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim" style="background-color:#fb8500"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","fontSize":"large"} --><p class="has-text-align-center has-large-font-size"><a href="/category/strength-mobility/" style="color:#ffffff">Strength &amp; Mobility</a></p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size" style="color:#ffffffcc">10 articles</p><!-- /wp:paragraph --></div></div>
        <!-- /wp:cover -->

        <!-- wp:cover {"dimRatio":30,"isUserOverlayColor":true,"customOverlayColor":"#8ecae6","minHeight":200,"layout":{"type":"constrained"}} -->
        <div class="wp-block-cover" style="min-height:200px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim" style="background-color:#8ecae6"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","fontSize":"large"} --><p class="has-text-align-center has-large-font-size"><a href="/category/injury-prevention/" style="color:#111111">Injury Prevention</a></p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size" style="color:#111111cc">8 articles</p><!-- /wp:paragraph --></div></div>
        <!-- /wp:cover -->

        <!-- wp:cover {"dimRatio":30,"isUserOverlayColor":true,"customOverlayColor":"#023047","minHeight":200,"layout":{"type":"constrained"}} -->
        <div class="wp-block-cover" style="min-height:200px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim" style="background-color:#023047"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","fontSize":"large"} --><p class="has-text-align-center has-large-font-size"><a href="/category/nutrition/" style="color:#ffffff">Nutrition</a></p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size" style="color:#ffffffcc">11 articles</p><!-- /wp:paragraph --></div></div>
        <!-- /wp:cover -->

        <!-- wp:cover {"dimRatio":30,"isUserOverlayColor":true,"customOverlayColor":"#219ebc","minHeight":200,"layout":{"type":"constrained"}} -->
        <div class="wp-block-cover" style="min-height:200px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim" style="background-color:#219ebc"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","fontSize":"large"} --><p class="has-text-align-center has-large-font-size"><a href="/category/running-science/" style="color:#ffffff">Running Science</a></p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size" style="color:#ffffffcc">10 articles</p><!-- /wp:paragraph --></div></div>
        <!-- /wp:cover -->

        <!-- wp:cover {"dimRatio":30,"isUserOverlayColor":true,"customOverlayColor":"#686868","minHeight":200,"layout":{"type":"constrained"}} -->
        <div class="wp-block-cover" style="min-height:200px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-30 has-background-dim" style="background-color:#686868"></span><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","fontSize":"large"} --><p class="has-text-align-center has-large-font-size"><a href="/category/running-psychology/" style="color:#ffffff">Running Psychology</a></p><!-- /wp:paragraph --><!-- wp:paragraph {"align":"center","fontSize":"small"} --><p class="has-text-align-center has-small-font-size" style="color:#ffffffcc">6 articles</p><!-- /wp:paragraph --></div></div>
        <!-- /wp:cover -->
    </div>
    <!-- /wp:group -->
</div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"bottom":"var:preset|spacing|70"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-bottom:var(--wp--preset--spacing--70)">
    <!-- wp:heading {"textAlign":"center","fontSize":"x-large"} -->
    <h2 class="wp-block-heading has-text-align-center has-x-large-font-size">Latest Articles</h2>
    <!-- /wp:heading -->

    <!-- wp:query {"queryId":1,"query":{"perPage":6,"pages":1,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"align":"wide"} -->
    <div class="wp-block-query alignwide">
        <!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
            <!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group">
                <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","style":{"border":{"radius":"8px"}}} /-->
                <!-- wp:post-title {"isLink":true,"level":3,"fontSize":"large"} /-->
                <!-- wp:post-date {"fontSize":"small"} /-->
                <!-- wp:post-excerpt {"fontSize":"small"} /-->
            </div>
            <!-- /wp:group -->
        <!-- /wp:post-template -->
    </div>
    <!-- /wp:query -->
</div>
<!-- /wp:group -->

<!-- wp:pattern {"slug":"rp-theme/cta-newsletter"} /-->

<!-- wp:template-part {"slug":"footer"} /-->
```

---

## 5. Phase 3: Single Post Template Overhaul

**Goal:** Add category-aware sidebar sidebar to single posts.

### Files to create

| File | Type | Content |
|---|---|---|
| `templates/single.html` | Modify | Add sidebar column |
| `parts/sidebar-post.html` | Create | Default — latest 4 posts + subscribe |
| `parts/sidebar-post-beginner-running.html` | Create | "Start Here" links + gear picks |
| `parts/sidebar-post-training.html` | Create | Training plan recommendations |
| `parts/sidebar-post-running-form.html` | Create | Form checklist + video links |
| `parts/sidebar-post-strength-mobility.html` | Create | Exercise of the week |
| `parts/sidebar-post-injury-prevention.html` | Create | Prevention checklist |
| `parts/sidebar-post-nutrition.html` | Create | Featured recipe + supplement guide |
| `parts/sidebar-post-running-science.html` | Create | Key terms + deeper reading |
| `parts/sidebar-post-running-psychology.html` | Create | Top mindset articles |

### New `templates/single.html` structure

```html
<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="margin-top:var(--wp--preset--spacing--60)">
    <!-- wp:post-featured-image {"align":"full","aspectRatio":"3/1","style":{"border":{"radius":"0px"}}} /-->

    <!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|50","left":"var:preset|spacing|60"}}}} -->
    <div class="wp-block-columns alignwide">
        <!-- wp:column {"width":"66%"} -->
        <div class="wp-block-column" style="flex-basis:66%">
            <!-- wp:post-title {"level":1} /-->
            <!-- wp:pattern {"slug":"rp-theme/hidden-written-by"} /-->
            <!-- wp:post-content {"layout":{"type":"constrained"}} /-->
            <!-- wp:post-terms {"term":"post_tag","separator":"  ","className":"is-style-post-terms-1"} /-->
            <!-- wp:pattern {"slug":"rp-theme/post-navigation"} /-->
        </div>
        <!-- /wp:column -->

        <!-- wp:column {"width":"33%"} -->
        <div class="wp-block-column" style="flex-basis:33%">
            <!-- wp:template-part {"slug":"sidebar-post"} /-->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->

    <!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}}} -->
    <div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
        <!-- wp:pattern {"slug":"rp-theme/comments"} /-->
    </div>
    <!-- /wp:group -->
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->
```

The `sidebar-post` template part will be dynamically swapped by the `render_block_data` filter (Phase 1) based on which category the post belongs to.

### Sidebar template part content

#### Default `parts/sidebar-post.html`

```html
<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"flex","orientation":"vertical"}} -->
<div class="wp-block-group">
    <!-- wp:heading {"level":3,"fontSize":"large"} -->
    <h3 class="wp-block-heading has-large-font-size">Latest Posts</h3>
    <!-- /wp:heading -->

    <!-- wp:query {"queryId":2,"query":{"perPage":4,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true}} -->
    <div class="wp-block-query">
        <!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"default"}} -->
            <!-- wp:post-title {"level":4,"isLink":true,"fontSize":"medium"} /-->
            <!-- wp:post-date {"fontSize":"small"} /-->
        <!-- /wp:post-template -->
    </div>
    <!-- /wp:query -->

    <!-- wp:separator /-->

    <!-- wp:heading {"level":3,"fontSize":"large"} -->
    <h3 class="wp-block-heading has-large-font-size">Subscribe</h3>
    <!-- /wp:heading -->
    <!-- wp:paragraph {"fontSize":"small"} -->
    <p class="has-small-font-size">Get the latest running tips delivered to your inbox.</p>
    <!-- /wp:paragraph -->
    <!-- wp:buttons -->
    <div class="wp-block-buttons">
        <!-- wp:button {"width":100} -->
        <div class="wp-block-button has-custom-width wp-block-button__width-100"><a class="wp-block-button__link wp-element-button">Subscribe</a></div>
        <!-- /wp:button -->
    </div>
    <!-- /wp:buttons -->
</div>
<!-- /wp:group -->
```

#### Category-specific sidebars follow the same pattern but with tailored content.

---

## 6. Phase 4: Archive Templates Overhaul

**Goal:** Give each category archive its own visual identity with category banners.

### Files to create

| File | Action |
|---|---|
| `templates/archive.html` | Rewrite — add banner template-part |
| `parts/banner-archive.html` | Create — default fallback |
| `parts/banner-archive-beginner-running.html` | Create |
| `parts/banner-archive-training.html` | Create |
| `parts/banner-archive-running-form.html` | Create |
| `parts/banner-archive-strength-mobility.html` | Create |
| `parts/banner-archive-injury-prevention.html` | Create |
| `parts/banner-archive-nutrition.html` | Create |
| `parts/banner-archive-running-science.html` | Create |
| `parts/banner-archive-running-psychology.html` | Create |

### New `templates/archive.html`

```html
<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:template-part {"slug":"banner-archive"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
    <!-- wp:query {"queryId":3,"query":{"perPage":12,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":true},"align":"wide"} -->
    <div class="wp-block-query alignwide">
        <!-- wp:post-template {"layout":{"type":"grid","columnCount":3}} -->
            <!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group">
                <!-- wp:post-featured-image {"isLink":true,"aspectRatio":"16/9","style":{"border":{"radius":"8px"}}} /-->
                <!-- wp:post-title {"isLink":true,"level":3,"fontSize":"large"} /-->
                <!-- wp:post-date {"fontSize":"small"} /-->
                <!-- wp:post-excerpt {"fontSize":"small"} /-->
            </div>
            <!-- /wp:group -->
        <!-- /wp:post-template -->

        <!-- wp:query-pagination -->
            <!-- wp:query-pagination-previous /-->
            <!-- wp:query-pagination-numbers /-->
            <!-- wp:query-pagination-next /-->
        <!-- /wp:query-pagination -->
    </div>
    <!-- /wp:query -->
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->
```

### Banner template part example — `parts/banner-archive-training.html`

```html
<!-- wp:cover {"align":"full","minHeight":300,"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60"}}}} -->
<div class="wp-block-cover alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);min-height:300px">
    <span aria-hidden="true" class="wp-block-cover__background has-background-dim-40 has-background-dim" style="background-color:#219ebc"></span>
    <div class="wp-block-cover__inner-container">
        <!-- wp:query-title {"type":"archive","textAlign":"center","fontSize":"xx-large"} /-->
        <!-- wp:term-description {"textAlign":"center","fontSize":"large"} /-->
    </div>
</div>
<!-- /wp:cover -->
```

The `render_block_data` filter (Phase 1) will automatically swap `banner-archive` to `banner-archive-{slug}` on category pages.

---

## 7. Phase 5: Other Templates

### `templates/home.html` (Blog index / posts page)

**Current:** Same as `index.html` — just blog heading + query loop.  
**Target:** Refine with a "Latest Articles" heading and paginated 3-column grid.

Block markup:

```html
<!-- wp:template-part {"slug":"header"} /-->

<!-- wp:group {"tagName":"main","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"},"padding":{"bottom":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="margin-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
    <!-- wp:heading {"textAlign":"center","level":1,"fontSize":"x-large"} -->
    <h1 class="wp-block-heading has-text-align-center has-x-large-font-size">Latest Articles</h1>
    <!-- /wp:heading -->

    <!-- wp:spacer {"height":"var:preset|spacing|50"} -->
    <div style="height:var(--wp--preset--spacing--50)" aria-hidden="true" class="wp-block-spacer"></div>
    <!-- /wp:spacer -->

    <!-- wp:pattern {"slug":"rp-theme/template-query-loop"} /-->
</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer"} /-->
```

### `templates/index.html`

Keep as fallback for custom post types. Can remain similar to current version or match `home.html`.

### `templates/search.html`

Improve the query results display — keep as-is but ensure the query loop uses the same 3-column grid pattern as archives.

### `templates/page.html` & `templates/page-no-title.html`

Keep as-is — pages are generally simple, single-column layouts.

### `templates/404.html`

Keep as-is — the 404 pattern already has a good layout with image, message, and search form.

---

## 8. Phase 6: Template Parts & Patterns Cleanup

### Template parts registry (`theme.json`)

Currently registered template parts:

| Name | Area | Status |
|---|---|---|
| `header` | header | ✅ Used |
| `vertical-header` | header | ❌ Unused — consider removing |
| `header-large-title` | header | ❌ Unused — consider removing |
| `footer` | footer | ✅ Used |
| `footer-columns` | footer | ❌ Unused — keep? |
| `footer-newsletter` | footer | ❌ Unused — keep? |
| `sidebar` | uncategorized | ❌ Currently unused, but will be used after Phase 4 |

**Action:** Remove `vertical-header` and `header-large-title` if not needed. Keep `sidebar` since Phase 3 will use it.

### Pattern audit

98 patterns exist. Many are Twenty Twenty-Five defaults irrelevant to a running site. Patterns to potentially remove (or leave if they don't cause issues):

- Flower/nature shop patterns: `page-landing-book`, `hero-book`, `banner-about-book`, `page-shop-home`, `page-business-home`
- Podcast patterns: `hero-podcast`, `page-landing-podcast`
- Event patterns: `event-3-col`, `event-rsvp`, `event-schedule`
- Photo blog variants: `template-*-photo-blog` series

**Action:** Leave patterns in place (they're inert) but don't reference them in templates.

---

## 9. Phase 7: Content Refinement

### Category descriptions

Set meaningful descriptions for each category (displayed by `wp:term-description` on archive pages):

| Category | Description |
|---|---|
| Beginner Running | New to running? Start here. From your first mile to building a consistent habit — evidence-based advice for every beginner. |
| Training | Training plans, workouts, and scheduling guidance. Easy runs, intervals, tempo runs, and long runs explained. |
| Running Form | Run more efficiently. Understand foot strike, cadence, posture, and breathing to move better and reduce injury risk. |
| Strength & Mobility | Build a stronger running body. Strength training, warm-ups, mobility work, and recovery routines for runners. |
| Injury Prevention | Stay healthy and run longer. Learn about injury causes, prevention strategies, safe mileage increases, and recovery. |
| Nutrition | Fuel your runs. Evidence-based guidance on hydration, carbs, protein, supplements, and race-day nutrition. |
| Running Science | The science behind every stride. VO₂ max, lactate threshold, running economy, and how your body adapts to training. |
| Running Psychology | The mental side of running. Build motivation, overcome plateaus, race with confidence, and develop a resilient mindset. |

### Navigation menu

Update the primary navigation (currently `ref:24`) to include links to each category archive and key pages.

---

## 10. Phase 8: Testing & Launch

### Build verification

```bash
npm run build
```

Verify no webpack errors. Confirm `public/js/interactivity.js` and `public/style.css` are generated.

### Template testing checklist

- [ ] **Front page** — hero renders, category grid links work, latest posts query returns 6 posts, newsletter CTA visible
- [ ] **Single post (Beginner Running)** — correct sidebar loads, layout is two-column
- [ ] **Single post (Training)** — training sidebar loads, different from default
- [ ] **Single post (each category)** — verify sidebar for each of the 8 categories
- [ ] **Archive (each category)** — correct banner loads, query shows category posts, pagination works
- [ ] **Home/blog index** — heading shows, posts list correctly
- [ ] **Search** — results display, no-results message shows
- [ ] **Page** — content renders in single column
- [ ] **404** — error message and search form show
- [ ] **Mobile** — layout stacks correctly, sidebar drops below content, nav hamburger works

### Content checks

- [ ] All posts have featured images (where applicable)
- [ ] Category descriptions are set
- [ ] Navigation links resolve to correct URLs
- [ ] Tags display correctly on single posts

---

## 11. File Inventory

### Final file structure (after all phases)

```
theme-root/
├── BUILD-PLAN.md                   ← This document
├── functions.php                   ← Add require for dynamic-template-parts.php
├── inc/
│   ├── categories.php              ← Existing — seed categories
│   └── dynamic-template-parts.php  ← NEW — render_block_data filter
├── templates/
│   ├── 404.html                    ← Keep as-is
│   ├── archive.html                ← REWRITE — add banner-archive + 3-col grid
│   ├── front-page.html             ← REWRITE — hero + grid + query + newsletter
│   ├── home.html                   ← REWRITE — refined blog index
│   ├── index.html                  ← Keep as fallback
│   ├── page.html                   ← Keep as-is
│   ├── page-no-title.html          ← Keep as-is
│   ├── search.html                 ← Minor refine
│   └── single.html                 ← REWRITE — add sidebar column
├── parts/
│   ├── header.html                 ← Keep (references header pattern)
│   ├── footer.html                 ← Keep (references footer pattern)
│   ├── sidebar.html                ← Keep (was orphan, now usable)
│   ├── sidebar-post.html           ← NEW — default sidebar
│   ├── sidebar-post-{slug}.html    ← NEW — 8 category-specific sidebars
│   ├── banner-archive.html         ← NEW — default archive banner
│   └── banner-archive-{slug}.html  ← NEW — 8 category-specific banners
├── patterns/                       ← Mostly keep; add cta-newsletter refinement
└── style.css                       ← Theme stylesheet
```

### New files total

| Phase | New files |
|---|---|
| 1 | 1 (`inc/dynamic-template-parts.php`) |
| 2 | 0 |
| 3 | 9 (`parts/sidebar-post*.html`) |
| 4 | 9 (`parts/banner-archive*.html`) |
| **Total** | **19 new files** |

### Modified files total

| Phase | Modified files |
|---|---|
| 1 | 1 (`functions.php`) |
| 2 | 1 (`templates/front-page.html`) |
| 3 | 1 (`templates/single.html`) |
| 4 | 1 (`templates/archive.html`) |
| 5 | 1 (`templates/home.html`) |
| **Total** | **5 modified files** |
