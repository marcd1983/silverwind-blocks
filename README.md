# Silverwind Blocks

Elemental content blocks for the [Silverwind](../../ss5-site/themes/silverwind) theme, built with
Tailwind CSS 4, daisyUI 5 and Alpine.js. One module, 11 blocks, replaces the separate
`antlion/element-*` and `antlion/swiper-slider` modules for Silverwind sites.

| Block | What it is | Replaces |
|---|---|---|
| Hero | Banner with background image/colour, overlay, text and buttons | element-hero |
| Card | Image, title, text, buttons; vertical, horizontal or overlay (text on the image, with its own overlay colour and strength) | element-card |
| Buttons | A row of buttons | element-buttongroup |
| Link list | Links across, down, or in a collapsible panel | element-menu |
| Accordion | Expandable items on native `<details>`, optional FAQ structured data | element-accordion |
| Tabs | Accessible tabs; `#tab-ID` in the URL opens a tab | element-tab |
| Section | Holds other blocks, **stacked**, in **columns** (each block sets its width) or in a **grid** (equal columns per screen size), optionally on a background colour/image/parallax | element-container, element-grid, element-list |
| Pop-up | Each block inside opens in a native `<dialog>` | element-modal |
| Gallery | Masonry layout with a keyboard/swipe lightbox | element-gallery |
| Carousel | Cards that scroll sideways (Swiper) | element-carousel |
| Slider | Full-width image or video slides, scheduling, cover links (Swiper) | swiper-slider |

A Section without a background is a plain layout wrapper: its padding is
"Automatic", meaning space above and below only when it has a background.
Sections can be nested for unusual layouts, but everyday ones need one level.

Every block also gets, on its **Settings** tab, the title's heading level and
the space above/below it. Links used as buttons get a **Button style**.

## Field conventions

Blocks share one vocabulary, defined in `src/Style.php` and the traits in
`src/Traits/`. A new block should reuse these rather than add its own.

| Field | Values | Used by |
|---|---|---|
| `HorizontalAlignment` | left, center, right | Hero, Card, Slide |
| `VerticalAlignment` | top, middle, bottom (+ stretch on Section) | Hero, Slide, Section |
| `ButtonAlignment` | left, center, right, between | Buttons, Link list, Pop-up |
| `ButtonSize` | sm, md, lg | Buttons, Pop-up, Hero |
| `Tone` | auto, light, dark | Hero, Section, Card, Slide |
| Background (`HasAppearance`) | `BackgroundColor`, `BackgroundImage`, `MobileBackgroundImage`, overlay (`OverlayType` solid or gradient, with its colour/strength/position fields), `Tone` | Hero, Section, Slide; Card and Carousel use the colour, overlay and tone fields |
| Spacing scale | none, sm, md, lg, xl | `Gap` (Section, Carousel), `PaddingY` (Section, plus "auto"), `SpacingTop/Bottom` (all, plus "auto") |
| `ColumnsSmall/Medium/Large` (`HasColumns`) | items per row on phones / tablets / desktops | Section (grid layout), Gallery |
| Card display (`CardDisplay`) | `HorizontalAlignment`, `ImageRatio` (auto, 1x1, 4x3, 3x2, 16x9, 3x4, original), `ImageFit` (cover, contain); Colour group: card colour, overlay (overlay cards: solid or gradient), text colour | Card, Carousel (applies to all its cards) |
| `Intro` | text above a block's items | Accordion, Tabs, Section, Gallery, Carousel |
| Item fields | `Title`, `Content`, `Image`/`BackgroundImage`, `Links` | accordion and tab panels, carousel cards, slides |
| Slideshow (`SwiperSettings`) | `Navigation`, `Pagination`, `Loop`, `Autoplay`, `AutoplayDelay`, `Speed` | Carousel, Slider, page slider |

Where fields go in the CMS (`ArrangesFields`):

- **Main tab:** content first (title, text, items, buttons), then the
  **Layout**, **Background** and **Slideshow** groups, in that order.
  Blocks call `arrangeMainTab()` at the end of `getCMSFields()` so this holds
  even after extensions add fields.
- **Settings tab:** behaviour toggles, then heading level and spacing.
- **A choice that changes what the other fields mean** goes first, above the
  content, as radio buttons, with dependent fields shown or hidden to match
  (e.g. Card's style). Blocks like that are edited in their full form
  (`inline_editable: false`): elemental's inline forms don't run the colour
  picker or show/hide rules.

New records get their `Enum` defaults in memory (`Style::applyEnumDefaults()`).
Silverstripe only applies them in the database, so without it a new block's
dropdowns would show and save their first option.

`tests/BlocksTest.php` checks that every block keeps this arrangement and has
no duplicate fields.

## Width and spacing

The page lays its blocks out full width; each **top-level** block sets its own
**Width** (Layout group): *Site width*, *Narrow* or *Full width*. Hero, Section
and Slider default to full width, everything else to site width. Blocks inside
a Section or Pop-up, and blocks next to a sidebar, fill their container and
don't get the field.

The measurements come from the **Settings > Theme** tab (silverwind-settings),
so changing them there changes every block:

| Theme setting | Used for |
|---|---|
| Site width | "Site width" blocks, page titles, text; inner content of full-width Hero/Section/Slider |
| Side padding | space at the screen edge for all of the above, and "Narrow" blocks |
| Section spacing | the default space between blocks, and the base of the spacing scale: Small ⅜×, Medium ⅝×, Large 1×, Extra large 1½× (block spacing and Section padding) |

"Automatic" space above/below:

- plain blocks are one *Section spacing* apart, with none under the page title
  and one before the footer;
- blocks with their own background (Hero, Slider, a Section with a
  background) touch each other, so coloured bands stack seamlessly, but get
  a *Section spacing* when they follow a plain block.

The theme implements the context rules (`sw-auto-top`, `sw-auto-bottom`,
`sw-band` in its `layout.css`); blocks only output the classes.

## Requirements

- Silverstripe CMS 6, PHP 8.3+, dnadesign/silverstripe-elemental 6
- antlion/silverwind-settings (colour fields and contrast helper)
- A theme that provides Alpine.js and compiles Tailwind from this module's
  templates: Silverwind does both

## Installation

```sh
composer require antlion/silverwind-blocks
vendor/bin/sake db:build --flush
```

Then, in the theme:

1. **Tailwind must scan this module.** Silverwind's `src/css/silverwind.css` has:
   ```css
   @source "../../../../vendor/antlion/silverwind-blocks/templates";
   @source "../../../../vendor/antlion/silverwind-blocks/src";
   ```
   The PHP is scanned too because class maps (column spans, heights, ...)
   live there as full class names. Rebuild the theme after adding blocks or
   changing those maps.
2. **Alpine must start after this module's script.** The theme exposes
   `window.Alpine` and calls `Alpine.start()` on `DOMContentLoaded`; this
   module registers `swTabs`, `swLightbox` and `swSwiper` in `alpine:init`.

### Page hero slider (optional)

```yaml
Page:
  extensions:
    - Antlion\SilverwindBlocks\Extensions\PageSliderExtension
```

Adds a **Hero slider** tab to pages. Output `$PageSlider` in the page
template; Silverwind's `Page.ss` already does.

## How the front end works

- **Styling:** templates use Tailwind/daisyUI classes only. This module ships
  no CSS of its own except Swiper's, which loads only with a Slider or Carousel.
- **JavaScript:** `client/dist/silverwind-blocks.js` (2 KB gzipped) loads only on
  pages with Tabs, Pop-up, Gallery, Carousel or Slider blocks
  (`BlockController`). Swiper (about 34 KB gzipped) is a separate chunk that's
  only downloaded when a slider or carousel is on the page.
- **Without JavaScript:** accordions and link lists work; tabs show the first
  tab; pop-ups and the lightbox don't open.
- **Reduced motion:** visitors who prefer it get no autoplay and no background
  video. Every autoplaying slider has a pause button.
- **Text colour on backgrounds:** "Automatic" picks light or dark text from
  the background colour or image. The `sw-tone-light` / `sw-tone-dark` classes
  come from the theme.

### Pop-ups and modal links

Each block inside a Pop-up gets `<dialog id="modal-{ID}">`. Open one from
anywhere with any element carrying `data-modal-target="modal-{ID}"`, or with
`#modal-{ID}` in the URL. The older `data-remodal-target` attribute also works.
The ids are listed on the Pop-up block in the CMS.

### Structured data

`FAQSchemaExtension` (applied to `SiteTree`) adds a schema.org `FAQPage`
node, built from Accordion blocks with "Mark up as FAQs" ticked, including
accordions nested inside other blocks. It hooks
`updateSchemaGraph(array &$graph, string &$pageURL)`, which your site's
JSON-LD code must call on the page.

## Templates

Override any template in your theme at the same path:

- `templates/Antlion/SilverwindBlocks/Elements/*.ss`: one per block
- `templates/Includes/SilverwindBlocks/`:
  - `Button.ss`: a Link as a button, or a `<button>` for modal triggers
  - `Card.ss`: the four card styles, shared by Card and Carousel
  - `Slider.ss`, `Slide.ss`, `SwiperControls.ss`: Slider and page slider
  - `MenuLink.ss`

## Development

```sh
npm install
npm run build      # or: npm run dev (watch)
```

`client/dist/` is committed so composer installs don't need Node.

Tests:

```sh
vendor/bin/phpunit vendor/antlion/silverwind-blocks/tests
```
# silverwind-blocks
