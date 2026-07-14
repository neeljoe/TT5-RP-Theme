# TT5-RP-Theme (rp-theme)

Project documentation for the RunPartner WordPress block theme.

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Directory Structure](#2-directory-structure)
3. [Build System](#3-build-system)
4. [Design System](#4-design-system)
5. [Templates & Parts](#5-templates--parts)
6. [Custom Functionality](#6-custom-functionality)
7. [CSS Architecture](#7-css-architecture)
8. [JavaScript](#8-javascript)
9. [Style Variations](#9-style-variations)
10. [Plugins & Block Architecture](#10-plugins--block-architecture)
11. [RP-Multi-Block Deep Dive](#11-rp-multi-block-deep-dive)
12. [Theme-Plugin Integration](#12-theme-plugin-integration)
13. [Known Issues & Gotchas](#13-known-issues--gotchas)
14. [Dynamic Template Part Loading](#14-dynamic-template-part-loading)
15. [Improvement Opportunities](#15-improvement-opportunities)

---

## 1. Project Overview

**rp-theme** is a WordPress Full Site Editing (FSE) block theme built for the **RunPartner** project — a running-focused content and blog website.

| Property | Value |
|----------|-------|
| Theme Name | rp-theme |
| Slug | `TT5-RP-Theme` |
| Parent Theme | [Twenty Twenty-Five](https://wordpress.org/themes/twentytwentyfive/) |
| Type | Block theme (Full Site Editing) |
| theme.json Version | 3 (WP 7.0 schema) |
| WP Requires | 7.0+ |
| PHP Requires | 7.2+ |
| Version | 1.0 |
| License | GPL-2.0-or-later |
| Text Domain | `rp-theme` |

The theme is a heavily customized fork of Twenty Twenty-Five, adapted for running content with category-specific sidebars, archive banners, a custom scroll animation system, and integration with the RP-Multi-Block plugin for navigation.

---

## 2. Directory Structure

```
TT5-RP-Theme/
├── assets/
│   ├── css/
│   │   └── editor-style.css          # Editor-only link style fix
│   ├── fonts/
│   │   ├── fira-code/                 # Monospace (variable font)
│   │   ├── fira-sans/                 # Sans-serif (18 static files)
│   │   ├── lato/                      # Body font (10 files, wts 100-900)
│   │   ├── manrope/                   # Alternate (variable font)
│   │   └── montserrat/               # Headings font (7 files, wts 300-900)
│   └── images/                        # 38 .webp + 2 .jpg assets
├── inc/
│   ├── categories.php                 # Auto-seeds 12 running categories
│   └── dynamic-template-parts.php     # render_block_data filter for category swaps
├── parts/                             # 23 template parts (.html)
├── patterns/                          # 6 active patterns (.php)
├── public/                            # Built/minified output
│   ├── js/
│   │   ├── interactivity.js           # Compiled ES module
│   │   └── interactivity.asset.php    # Dependency manifest
│   └── style.css                      # Minified CSS output
├── resources/
│   ├── js/
│   │   └── interactivity.js           # JS source (11 lines)
│   └── styles/
│       ├── style.css                  # CSS entry (imports 8 modules)
│       ├── variables.css
│       ├── animations.css
│       ├── animation-classes.css
│       ├── utilities.css
│       ├── blocks.css
│       ├── navigation.css
│       ├── layout.css
│       └── responsive.css
├── styles/                            # 32 style variations
│   ├── 01-evening.json .. 08-midnight.json
│   ├── blocks/                        # 4 block-level variations
│   ├── colors/                        # 8 color-only variations
│   ├── sections/                      # 5 section variations
│   └── typography/                    # 7 typography variations
├── templates/                         # 9 templates (.html)
├── functions.php                      # Theme setup and enqueues
├── theme.json                         # Global settings & styles
├── style.css                          # Theme metadata only (no CSS rules)
├── package.json                       # npm config and scripts
├── webpack.config.js                  # Custom Webpack for ES module output
├── postcss.config.js                  # PostCSS pipeline config
├── contributing.txt                   # Build instructions
├── readme.txt                         # Theme readme
└── screenshot.png                     # Theme screenshot
```

### Key Root Files

| File | Purpose |
|------|---------|
| `theme.json` | Central configuration: colors, typography, spacing, layout, block styles, template parts, custom templates |
| `functions.php` | Enqueues styles/scripts, registers post formats, block styles, pattern categories, block bindings |
| `style.css` | **Metadata only** — no actual CSS rules. All styles live in `resources/styles/` → `public/style.css` |
| `package.json` | npm dependencies and build scripts |
| `webpack.config.js` | Extends `@wordpress/scripts` with ES module output |
| `postcss.config.js` | PostCSS plugins: import, preset-env (stage 3), cssnano (production) |

---

## 3. Build System

### CSS Pipeline

```
resources/styles/style.css  (entry — imports 8 modules)
        ↓ postcss-import (resolves @import)
        ↓ postcss-preset-env (stage 3 features)
        ↓ cssnano (production only)
        ↓
public/style.css  (minified output)
```

**Source modules** (imported in order):

| # | Module | Purpose |
|---|--------|---------|
| 1 | `variables.css` | CSS custom properties (`--rp-transition-fast/normal/slow`) |
| 2 | `animations.css` | 8 `@keyframes` definitions |
| 3 | `animation-classes.css` | Scroll animation utility classes |
| 4 | `utilities.css` | Hover effects, gradients, focus, selection |
| 5 | `blocks.css` | Block-level styles (links, images, quotes, code, comments) |
| 6 | `navigation.css` | Submenu spacing and outline offsets |
| 7 | `layout.css` | Sticky header, font smoothing, auto-margin footer |
| 8 | `responsive.css` | `prefers-reduced-motion`, mobile breakpoints |

### JavaScript Pipeline

```
resources/js/interactivity.js  (source — 11 lines)
        ↓ @wordpress/scripts (Webpack)
        ↓ experiments: outputModule: true
        ↓
public/js/interactivity.js  (ES module output)
public/js/interactivity.asset.php  (dependency manifest)
```

### npm Scripts

| Command | Description |
|---------|-------------|
| `npm run start` | Watch both CSS and JS in parallel |
| `npm run build` | Production build (`build:styles` then `build:js`) |
| `npm run build:js` | Webpack build only |
| `npm run build:styles` | PostCSS production build (`NODE_ENV=production`) |
| `npm run watch:styles` | PostCSS watch mode |

### Enqueue Logic (functions.php)

- **Styles:** `public/style.css` is enqueued; falls back to `style.css` if built file doesn't exist
- **JS:** `public/js/interactivity.js` enqueued via `wp_enqueue_script_module()` as an ES module
- **Editor:** `assets/css/editor-style.css` enqueued via `add_editor_style()`

---

## 4. Design System

### Color Palette

| Slug | Hex | Name | Usage |
|------|-----|------|-------|
| `base` | `#FFFFFF` | White | Background |
| `contrast` | `#111111` | Near-black | Body text, buttons |
| `accent-1` | `#ffb703` | Golden Yellow | Primary accent, CTA hover |
| `accent-2` | `#219ebc` | Teal | Links, selection color |
| `accent-3` | `#023047` | Dark Navy | Link hover, dark sections |
| `accent-4` | `#686868` | Medium Gray | Meta text, secondary |
| `accent-5` | `#FBFAF3` | Off-white/Cream | Code backgrounds |
| `accent-6` | `color-mix(...)` | 20% currentColor | Borders, subtle dividers |
| `light-blue` | `#8ecae6` | Light Blue | Accent |
| `dark-orange` | `#fb8500` | Dark Orange | Accent |

### Typography

| Role | Family | Weights | Notes |
|------|--------|---------|-------|
| Body | Lato | 100, 300, 400, 700, 900 + italic | Default weight 300, line-height 1.4 |
| Headings | Montserrat | 300–900 | Weight 700, uppercase, letter-spacing -0.1px, line-height 1.125 |
| Code | Fira Code | Variable (300–700) | Monospace |
| Alternate | Manrope | Variable (200–800) | Available via style variations |

All fonts are self-hosted as `.woff2` — no Google Fonts dependency.

### Fluid Font Sizes

| Slug | Min | Max |
|------|-----|-----|
| `small` | 0.875rem | 0.875rem (fixed) |
| `medium` | 1rem | 1.125rem |
| `large` | 1.125rem | 1.375rem |
| `x-large` | 1.75rem | 2rem |
| `xx-large` | 2.15rem | 3rem |

### Spacing Scale

| Slug | Name | Value |
|------|------|-------|
| `20` | Tiny | 10px |
| `30` | X-Small | 20px |
| `40` | Small | 30px |
| `50` | Regular | `clamp(30px, 5vw, 50px)` |
| `60` | Large | `clamp(30px, 7vw, 70px)` |
| `70` | X-Large | `clamp(50px, 7vw, 90px)` |
| `80` | XX-Large | `clamp(70px, 10vw, 140px)` |

### Layout

- Content size: `720px`
- Wide size: `1200px`
- `useRootPaddingAwareAlignments: true`
- Root padding: Regular spacing (`clamp(30px, 5vw, 50px)`)

---

## 5. Templates & Parts

### Templates (9)

| Template | File | Description |
|----------|------|-------------|
| Index | `index.html` | Blog listing with header, blog heading, query loop, footer |
| Home | `home.html` | Blog home (same as index) |
| Front Page | `front-page.html` | Landing page: header → `rp-theme/front-page-hero` pattern → footer |
| Single | `single.html` | Single post: title, featured image, author, content, tags, nav, comments, more posts |
| Page | `page.html` | Page: featured image, title, content |
| Page No Title | `page-no-title.html` | Custom template — page content without title |
| Archive | `archive.html` | Archive: title, term description, query loop |
| Search | `search.html` | Search results with form, query loop, more posts |
| 404 | `404.html` | 404 page with image, message, search form |

### Template Parts (23)

#### Structural Parts (5)
| Part | File | Description |
|------|------|-------------|
| Header | `header.html` | Delegates to `rp-theme/header` pattern (nav, search, mobile menu) |
| Footer | `footer.html` | Delegates to `rp-theme/footer` pattern |
| Footer Columns | `footer-columns.html` | Delegates to `rp-theme/footer-columns` pattern |
| Footer Newsletter | `footer-newsletter.html` | Delegates to `rp-theme/footer-newsletter` pattern |
| Sidebar | `sidebar.html` | Delegates to `rp-theme/hidden-sidebar` pattern |

#### Default Post Sidebar (1)
| Part | File | Description |
|------|------|-------------|
| Sidebar Post | `sidebar-post.html` | Latest posts list + subscribe CTA |

#### Category-Specific Post Sidebars (8)
| Part | Category |
|------|----------|
| `sidebar-post-beginner-running.html` | Beginner Running |
| `sidebar-post-training.html` | Training |
| `sidebar-post-nutrition.html` | Nutrition |
| `sidebar-post-running-form.html` | Running Form |
| `sidebar-post-running-science.html` | Running Science |
| `sidebar-post-strength-mobility.html` | Strength & Mobility |
| `sidebar-post-injury-prevention.html` | Injury Prevention |
| `sidebar-post-running-psychology.html` | Running Psychology |

#### Default Archive Banner (1)
| Part | File | Description |
|------|------|-------------|
| Banner Archive | `banner-archive.html` | Cover block with dim background, query title, term description |

#### Category-Specific Archive Banners (8)
| Part | Category |
|------|----------|
| `banner-archive-beginner-running.html` | Beginner Running |
| `banner-archive-training.html` | Training |
| `banner-archive-nutrition.html` | Nutrition |
| `banner-archive-running-form.html` | Running Form |
| `banner-archive-running-science.html` | Running Science |
| `banner-archive-strength-mobility.html` | Strength & Mobility |
| `banner-archive-injury-prevention.html` | Injury Prevention |
| `banner-archive-running-psychology.html` | Running Psychology |

### Naming Convention

Category-specific template parts follow the pattern: `{base-slug}-{category-slug}.html`

The dynamic template part system (see [Section 14](#14-dynamic-template-part-loading)) automatically swaps the base slug for the category variant at render time.

---

## 6. Custom Functionality

### Category Auto-Seeding (`inc/categories.php`)

Runs once on theme activation via `after_switch_theme` hook. Creates 12 running-specific categories:

1. Beginner Running
2. Training
3. Running Form
4. Running Science
5. Nutrition
6. Strength & Mobility
7. Injury Prevention
8. Racing
9. Trail Running
10. Gear
11. Running Psychology
12. Running History & Culture

Uses `rp_theme_categories_seeded` option to prevent duplicate creation. New categories added after activation must be created manually.

### Dynamic Template Parts (`inc/dynamic-template-parts.php`)

A `render_block_data` filter that swaps template part slugs based on context. See [Section 14](#14-dynamic-template-part-loading) for full details.

### Block Bindings

Registers a custom block binding source `rp-theme/format` that outputs the current post format name (e.g., "Aside", "Audio", "Chat"). Returns nothing for "standard" format.

### Custom Block Styles

- `checkmark-list` on `core/list` — Changes list marker to checkmark unicode character (✓)

### Pattern Categories

- `rp_theme_page` — "Pages" (full page layouts)
- `rp_theme_post-format` — "Post formats"

### Post Format Support

9 formats: aside, audio, chat, gallery, image, link, quote, status, video

---

## 7. CSS Architecture

### Module System

The theme uses a modular CSS architecture with 8 files imported in a specific order through `resources/styles/style.css`. This is processed by PostCSS into a single minified output at `public/style.css`.

### CSS Custom Properties (`variables.css`)

```css
:root {
    --rp-transition-fast: 150ms ease;
    --rp-transition-normal: 300ms ease;
    --rp-transition-slow: 500ms ease;
}
```

These are used throughout the animation and utility classes.

### Animation System (`animations.css` + `animation-classes.css`)

**8 Keyframes:**
`fadeUp`, `fadeIn`, `slideInLeft`, `slideInRight`, `scaleUp`, `float`, `pulse`, `shimmer`

**Utility Classes:**

| Class | Description |
|-------|-------------|
| `.fade-up` | Fades in + slides up on `.is-visible` |
| `.fade-in` | Fades in on `.is-visible` |
| `.stagger-children > *` | Staggered child animations (100ms increments, up to 10 children) |
| `.animate-on-scroll` | Generic scroll-triggered fade-up (JS-powered via IntersectionObserver) |
| `.skeleton` | Shimmer loading placeholder |

### Utility Classes (`utilities.css`)

| Class | Effect |
|-------|--------|
| `.hover-lift` | translateY(-2px) on hover |
| `.hover-glow-primary` | Box-shadow glow with accent-1 color |
| `.hover-glow-accent` | Box-shadow glow with accent-2 color |
| `.text-gradient` | Gradient text (accent-1 → accent-2) |

Also includes global focus outlines and selection color styling.

### Block Styles (`blocks.css`)

- Link underline thickness and offset
- Image zoom on hover (`.image-zoom`)
- Blockquote decorative quotation marks
- Code block scrollbar styling
- Comment form input border-radius and label sizing
- `text-wrap: pretty` on headings and paragraphs

### Navigation (`navigation.css`)

- Submenu container padding and border removal
- Submenu item spacing (3px margin)
- Outline offset adjustments for navigation items

### Layout (`layout.css`)

- Font smoothing (webkit + moz)
- Sticky header with `z-index: 100` and base background
- Auto-margin footer via `margin-top: auto`

### Responsive (`responsive.css`)

- `prefers-reduced-motion: reduce` — Disables all animations and transitions, sets opacity to 1
- `max-width: 768px` — Removes stagger animation delays on mobile

---

## 8. JavaScript

### Source (`resources/js/interactivity.js`)

11 lines of vanilla JavaScript implementing IntersectionObserver-based scroll animations:

```javascript
document.querySelectorAll('.animate-on-scroll').forEach((el) => {
    const observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting) {
                el.classList.add('is-visible');
            }
        },
        { threshold: 0.05, rootMargin: '-20px' }
    );
    observer.observe(el);
});
```

### Behavior

- Selects all `.animate-on-scroll` elements
- Adds `.is-visible` when element enters viewport (5% threshold, -20px root margin)
- Once visible, stays visible (no observer disconnect — intentional for CSS transition)

### Build

- Webpack entry: `resources/js/interactivity.js`
- Output: `public/js/interactivity.js` (ES module)
- Dependencies auto-detected in `public/js/interactivity.asset.php`

### Enqueue

```php
wp_enqueue_script_module( 'rp-theme-interactivity', ... );
```

Enqueued as an ES module via `wp_enqueue_script_module()` — requires WP 7.0+.

---

## 9. Style Variations

The theme ships with 32 style variation files organized in 5 categories.

### Full Theme.json Overrides (8)

Located in `styles/`. Each overrides color palette and sometimes typography:

| File | Name | Description |
|------|------|-------------|
| `01-evening.json` | Evening | Dark theme with purple accents |
| `02-noon.json` | Noon | — |
| `03-dusk.json` | Dusk | — |
| `04-afternoon.json` | Afternoon | — |
| `05-twilight.json` | Twilight | — |
| `06-morning.json` | Morning | — |
| `07-sunrise.json` | Sunrise | — |
| `08-midnight.json` | Midnight | Purple/neon theme, Literata + Fira Sans fonts, duotone filter |

### Color-Only Variations (8)

Located in `styles/colors/`. Palette overrides only — same 8 names as above.

### Typography Variations (7)

Located in `styles/typography/`. Font family + size overrides (e.g., Beiruti & Literata, etc.)

### Section Variations (5)

Located in `styles/sections/`. Block-level style variations for `core/group`, `core/columns`, `core/column` — each applying different background/text color combinations.

### Block-Level Variations (4)

Located in `styles/blocks/`:

| File | Purpose |
|------|---------|
| `01-display.json` | Large fluid typography for headings/paragraphs |
| `02-subtitle.json` | Subtitle styling |
| `03-annotation.json` | Annotation styling |
| `post-terms-1.json` | Post terms styling |

---

## 10. Plugins & Block Architecture

| Plugin | Version | Purpose | Blocks? | Critical? |
|--------|---------|---------|---------|-----------|
| **RP-Multi-Block** | 2.1.0 | Custom navigation blocks for theme header | 4 (`disney/*`) | **YES** |
| **Novamira** | 1.9.0 | MCP server for AI agent WordPress access | None | Dev tool |
| **Instant Images** | 7.2.0 | Stock photo uploads (Unsplash, Pexels, etc.) | 1 (license-gated) | Utility |
| **WordPress Importer** | 0.9.5 | WXR content import | None | Cleanup candidate |

### Plugin-Theme Dependency Map

```
TT5-RP-Theme
    └── patterns/header.php
            ├── wp:disney/search-toggle    ← RP-Multi-Block
            ├── wp:disney/mobile-menu-toggle ← RP-Multi-Block
            ├── wp:disney/search-panel      ← RP-Multi-Block
            └── wp:disney/mobile-menu       ← RP-Multi-Block
```

The theme **cannot function properly** without RP-Multi-Block active — the header will have missing blocks.

---

## 11. RP-Multi-Block Deep Dive

### Overview

| Property | Value |
|----------|-------|
| Plugin Name | RP Advanced Multi Block |
| Version | 2.1.0 |
| Namespace | `disney/` (legacy name) |
| Scaffolded with | `@wordpress/create-block` interactive template |
| WP Requires | 6.8+ |
| PHP Requires | 7.4+ |

### Blocks

| Block | Slug | Purpose |
|-------|------|---------|
| Mobile Menu Toggle | `disney/mobile-menu-toggle` | Hamburger button, toggles mobile menu |
| Mobile Menu | `disney/mobile-menu` | Accordion panel with Learn/Gears subsections |
| Search Toggle | `disney/search-toggle` | Button that opens sliding search panel |
| Search Panel | `disney/search-panel` | Sliding search form |

### Block Registration Pattern

Uses the **blocks manifest** pattern for efficient bulk registration:

```php
// Three-tier WP compatibility:
if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
    // WP 6.8+
    wp_register_block_types_from_metadata_collection( $build_dir, $manifest );
} elseif ( function_exists( 'wp_register_block_metadata_collection' ) ) {
    // WP 6.7
    wp_register_block_metadata_collection( $build_dir, $manifest );
    // + loop
} else {
    // WP 5.5-6.6
    // loop with register_block_type_from_metadata()
}
```

3 deprecated blocks are explicitly excluded from registration: `event-location`, `event-month`, `event-distances`.

### Interactivity API Architecture

All 4 blocks share a **single Interactivity API store** named `'disney'`:

| State Property | Set By | Purpose |
|----------------|--------|---------|
| `mobileMenuOpen` | `mobile-menu-toggle` | Controls mobile menu visibility |
| `isSearchOpen` | `search-toggle` | Controls search panel visibility |
| `learnOpen` | `mobile-menu` | Controls "Learn" accordion |
| `gearsOpen` | `mobile-menu` | Controls "Gears" accordion |

**Cross-block state management:**
- Toggling mobile menu closes search (`state.isSearchOpen = false`)
- Toggling search closes mobile menu (`state.mobileMenuOpen = false`)

### Server-Side Rendering

Each block has a `render.php` that:
1. Initializes state via `wp_interactivity_state('disney', ...)`
2. Outputs HTML with `data-wp-interactive="disney"` directive
3. Uses `data-wp-on--click`, `data-wp-bind--hidden`, `data-wp-class--*` directives
4. Uses `getContext()` and `getElement()` for context-aware behavior

### Source Structure

```
src/
├── editor-script.js              # Editor-wide script (placeholder)
├── frontend-script.js            # Frontend-wide script (placeholder)
└── blocks/
    ├── mobile-menu-toggle/
    │   ├── block.json            # disney/mobile-menu-toggle
    │   ├── index.js              # Client-side registration
    │   ├── edit.js               # Editor preview
    │   ├── render.php            # Server-side render + Interactivity API
    │   ├── view.js               # Frontend: toggleMobileMenu action
    │   ├── style.scss            # Hamburger button styles
    │   └── editor.scss           # Editor-only styles
    ├── search-toggle/
    │   ├── block.json, index.js, edit.js, render.php, view.js
    │   ├── style.scss, editor.scss, README.md
    ├── mobile-menu/
    │   ├── block.json, index.js, edit.js, render.php, view.js
    │   ├── style.scss, editor.scss
    └── search-panel/
        ├── block.json, index.js, edit.js, render.php, view.js
        ├── style.scss, editor.scss, README.md
```

### Build System

- **Tool:** `@wordpress/scripts` v32.1.0+
- **Custom webpack:** Extends default config with `editor-script` and `frontend-script` entry points
- **Build command:** `wp-scripts build --experimental-modules --blocks-manifest`
- **Output:** `build/` directory with compiled JS, CSS, RTL variants, asset PHP files

---

## 12. Theme-Plugin Integration

### Header Architecture

The theme's header (`patterns/header.php`) is the integration point:

```
┌─────────────────────────────────────────────────┐
│  Site Title          Navigation    [Search] [☰] │
│                             ↑            ↑    ↑  │
│                    core/navigation  search  mobile│
│                                    toggle  toggle │
├─────────────────────────────────────────────────┤
│  [Search Panel — sliding]                        │
│  ↑ disney/search-panel                          │
├─────────────────────────────────────────────────┤
│  [Mobile Menu — accordion, hidden on desktop]    │
│  ↑ disney/mobile-menu                           │
│    ├── Learn (barefoot running, running form,    │
│    │         science)                            │
│    ├── Gears (trail running shoes, running shoes)│
│    ├── Shop                                      │
│    └── About                                     │
└─────────────────────────────────────────────────┘
```

### Block References in Header Pattern

```html
<!-- wp:disney/search-toggle /-->
<!-- wp:disney/mobile-menu-toggle /-->
<!-- wp:disney/search-panel /-->
<!-- wp:disney/mobile-menu {"metadata":{"blockVisibility":{"viewport":{"desktop":false}}}} /-->
```

The mobile menu is hidden on desktop via `blockVisibility.viewport.desktop: false`.

### State Flow

```
User clicks hamburger (mobile-menu-toggle)
    → state.mobileMenuOpen = true
    → state.isSearchOpen = false (closes search if open)
    → mobile-menu panel becomes visible (data-wp-bind--hidden)

User clicks search icon (search-toggle)
    → state.isSearchOpen = true
    → state.mobileMenuOpen = false (closes menu if open)
    → search-panel slides open (data-wp-class--is-open)

User clicks "Learn" in mobile menu (mobile-menu)
    → state.learnOpen = true
    → state.gearsOpen = false (accordion behavior)
    → smooth scroll to section
```

---

## 13. Known Issues & Gotchas

### Parent Theme Dependency

Templates reference `twentytwentyfive/*` patterns that are defined in the **parent theme**, not in this theme:

| Pattern Slug | Referenced In |
|--------------|--------------|
| `twentytwentyfive/template-query-loop` | index.html, home.html, archive.html, search.html |
| `twentytwentyfive/hidden-blog-heading` | index.html, home.html |
| `twentytwentyfive/hidden-search` | search.html |
| `twentytwentyfive/more-posts` | search.html, single.html |
| `twentytwentyfive/hidden-written-by` | single.html |
| `twentytwentyfive/post-navigation` | single.html |
| `twentytwentyfive/comments` | single.html |
| `twentytwentyfive/hidden-404` | 404.html |

If Twenty Twenty-Five is deactivated, these templates will break.

### "disney" Namespace

The RP-Multi-Block plugin uses `disney/` as its block namespace. This is a legacy/placeholder name from initial development. All Interactivity API stores, `data-wp-interactive` attributes, and block registrations use this namespace.

### Category Seeding

The `after_switch_theme` hook runs only once. Categories created by `inc/categories.php` exist, but the corresponding template part files (e.g., `sidebar-post-{category}.html`) must exist in `parts/` for the dynamic swap to work. New categories won't automatically have matching template parts.

### Deprecated Blocks

RP-Multi-Block keeps source files for 3 deprecated blocks (`event-location`, `event-month`, `event-distances`) but explicitly excludes them from registration. These are dead code in the `src/` directory.

### Image Assets

The theme ships with 38 images in `assets/images/`. Most are inherited from Twenty Twenty-Five and may not be relevant to RunPartner content (flowers, buildings, ruins, etc.).

### readme.txt PHP Version

`readme.txt` says `Requires PHP: 5.7` while `style.css` says `Requires PHP: 7.2`. The `style.css` value is correct.

---

## 14. Dynamic Template Part Loading

### The Technique

WordPress allows intercepting block data before rendering via the [`render_block_data`](https://developer.wordpress.org/reference/hooks/render_block_data/) filter hook. For `core/template-part` blocks, you can swap the `slug` attribute to load a completely different template part file at render time.

**Reference:** [Dynamically loading template parts in block themes](https://developer.wordpress.org/news/2026/06/dynamically-loading-template-parts-in-block-themes/) — Justin Tadlock, WordPress Developer Blog, June 18, 2026.

### How It Works

1. The filter fires for **every block** on every page
2. We bail early if it's not a `core/template-part` block
3. We determine the current context (post category or archive category)
4. We construct a candidate slug: `{original-slug}-{category-slug}`
5. We check if that file exists in `parts/`
6. If it exists, we swap the slug → WordPress loads the category-specific file
7. If not, the original template part loads unchanged (fallback)

### Theme Implementation (`inc/dynamic-template-parts.php`)

```php
add_filter( 'render_block_data', 'rp_theme_dynamic_template_part' );

function rp_theme_dynamic_template_part( array $parsed_block ): array {
    // Only process core/template-part blocks
    if ( ( $parsed_block['blockName'] ?? '' ) !== 'core/template-part' ) {
        return $parsed_block;
    }

    $slug    = $parsed_block['attrs']['slug'] ?? '';
    $context = '';

    // On single posts: use first category slug
    if ( is_singular( 'post' ) ) {
        $post = get_queried_object();
        if ( $post instanceof WP_Post ) {
            $categories = get_the_category( $post->ID );
            if ( ! empty( $categories ) ) {
                $context = $categories[0]->slug;
            }
        }
    // On category archives: use "archive-{category-slug}"
    } elseif ( is_category() ) {
        $category = get_queried_object();
        if ( $category instanceof WP_Term ) {
            $context = 'archive-' . $category->slug;
        }
    }

    if ( ! $context ) {
        return $parsed_block;
    }

    $parts_dir    = get_block_theme_folders()['wp_template_part'];
    $context_slug = "{$slug}-{$context}";

    if ( file_exists( get_theme_file_path( "{$parts_dir}/{$context_slug}.html" ) ) ) {
        $parsed_block['attrs']['slug'] = $context_slug;
    }

    return $parsed_block;
}
```

### Article's Approach vs Theme's Extended Approach

| Aspect | Article (Justin Tadlock) | Theme Implementation |
|--------|--------------------------|----------------------|
| **Slug targeting** | Specific slug (`sidebar-post`) | Any template part (no slug filter) |
| **Context** | Single posts only | Single posts + category archives |
| **Category handling** | Loops through all categories, first match wins | Takes first category only |
| **File check** | `locate_template()` | `file_exists(get_theme_file_path())` |
| **Archive support** | None | Yes — `archive-` prefix convention |

### Context Examples

| Page Context | Template Part | Swapped To |
|-------------|---------------|------------|
| Single post in "Training" category | `sidebar-post` | `sidebar-post-training` |
| Single post in "Nutrition" category | `banner-archive` | `banner-archive-nutrition` |
| Training category archive | `banner-archive` | `banner-archive-training` |
| Single post, no category | `sidebar-post` | `sidebar-post` (unchanged) |
| Page (not a post) | any | unchanged |

### Template Parts Using This System

**16 category-specific variants** from 2 base slugs:
- `sidebar-post` → 8 category-specific sidebars
- `banner-archive` → 8 category-specific archive banners

---

## 15. Improvement Opportunities

### Slug Whitelisting

The current implementation runs `file_exists()` for **every** template part on **every** page render. Adding a whitelist would skip unnecessary checks:

```php
$swappable = ['sidebar-post', 'banner-archive'];
if ( ! in_array( $slug, $swappable, true ) ) {
    return $parsed_block;
}
```

### `locate_template()` vs `file_exists()`

The article uses `locate_template()` which respects child theme overrides. The current implementation uses `file_exists(get_theme_file_path())`. Switching to `locate_template()` would be more correct for child theme compatibility.

### Category Priority Ordering

The current implementation takes the first category from `get_the_category()`. The article loops through all categories and checks each for a matching template part. Implementing the loop would allow fallback behavior and priority control.

### Extend to Other Template Parts

The dynamic swap system could be extended to headers and footers — e.g., a `header-training.html` with a teal accent for the Training category. Currently only sidebars and archive banners use this.

### Deprecated Blocks Cleanup

RP-Multi-Block keeps 3 deprecated block source files (`event-location`, `event-month`, `event-distances`) that are excluded from registration. These could be removed from `src/` or documented with a deprecation notice.

### readme.txt PHP Version

`readme.txt` lists `Requires PHP: 5.7` — should be corrected to `7.2` to match `style.css`.
