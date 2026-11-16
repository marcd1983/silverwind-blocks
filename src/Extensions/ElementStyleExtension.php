<?php

namespace Antlion\SilverwindBlocks\Extensions;

use Antlion\SilverwindBlocks\Style;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Settings shared by every block: heading level, width and spacing.
 *
 * Width and "automatic" spacing only apply to top-level blocks in a page's
 * main area (not nested blocks, not sidebars): the page lays them out
 * full width and each block decides how wide it is.
 *
 *   Width   site width (the Theme tab's "Site width"), narrow, or full width
 *   Space   automatic: plain blocks are one "Section spacing" apart, blocks
 *           with their own background (hero, section with a background,
 *           slider) touch; or a fixed level from the shared scale
 *
 * @extends Extension<BaseElement>
 */
class ElementStyleExtension extends Extension
{
    use Configurable;

    private static array $db = [
        'HeadingTag' => "Enum('h1,h2,h3,h4,h5,h6','h2')",
        'BlockWidth' => "Enum('contained,narrow,full','contained')",
        'SpacingTop' => Style::DB_SPACING_AUTO,
        'SpacingBottom' => Style::DB_SPACING_AUTO,
    ];

    /** Default heading level per block class, e.g. cards default to h3 */
    private static array $default_headings = [];

    private static string $global_default_heading = 'h2';

    /** Default width per block class, e.g. heroes are full width */
    private static array $default_widths = [];

    /** The page's main block area; others (e.g. a sidebar) don't get widths */
    private static string $main_area_relation = 'ElementalArea';

    private const array TAGS = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    private const array WIDTHS = [
        'contained' => 'sw-container',
        'narrow' => 'mx-auto w-full max-w-narrow px-gutter',
        'full' => 'w-full',
    ];

    public function onAfterPopulateDefaults(): void
    {
        $owner = $this->getOwner();
        $owner->setField('HeadingTag', $this->defaultHeading());
        $width = static::config()->get('default_widths')[get_class($owner)] ?? null;
        if ($width && isset(self::WIDTHS[$width])) {
            $owner->setField('BlockWidth', $width);
        }
        Style::applyEnumDefaults($owner);
    }

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->removeByName(['HeadingTag', 'BlockWidth', 'SpacingTop', 'SpacingBottom']);

        $fields->addFieldToTab('Root.Settings', DropdownField::create('HeadingTag', _t(__CLASS__ . '.HeadingTag', 'Title heading level'), array_combine(self::TAGS, self::TAGS))
            ->setDescription(_t(__CLASS__ . '.HeadingTagDescription', 'Use one h1 per page; nest levels in order for accessibility.')));

        if ($this->isTopLevel()) {
            // Blocks using ArrangesFields move this into their Layout group
            $fields->addFieldToTab('Root.Settings', DropdownField::create('BlockWidth', _t(__CLASS__ . '.BlockWidth', 'Width'), [
                'contained' => _t(__CLASS__ . '.WidthContained', 'Site width'),
                'narrow' => _t(__CLASS__ . '.WidthNarrow', 'Narrow (reading width)'),
                'full' => _t(__CLASS__ . '.WidthFull', 'Full width'),
            ])->setDescription(_t(__CLASS__ . '.BlockWidthDescription', 'Site width is set on the Settings > Theme tab.')));
        }

        $spacing = Style::spacingOptions(true);
        $description = _t(__CLASS__ . '.SpacingDescription', 'Sizes follow "Section spacing" on the Settings > Theme tab. Automatic: one section spacing between blocks; blocks with a background touch.');
        $fields->addFieldsToTab('Root.Settings', [
            DropdownField::create('SpacingTop', _t(__CLASS__ . '.SpacingTop', 'Space above'), $spacing)->setDescription($description),
            DropdownField::create('SpacingBottom', _t(__CLASS__ . '.SpacingBottom', 'Space below'), $spacing),
        ]);
    }

    public function getHeadingLevel(): string
    {
        $tag = strtolower((string) $this->getOwner()->getField('HeadingTag'));

        return in_array($tag, self::TAGS, true) ? $tag : $this->defaultHeading();
    }

    /**
     * The block title as a heading, or null when there's no title or it's
     * hidden. Usage: $RenderedTitle or $RenderedTitle('text-4xl font-bold').
     */
    public function RenderedTitle(?string $classes = null): ?DBHTMLText
    {
        $owner = $this->getOwner();
        if (!$owner->Title || !$owner->ShowTitle) {
            return null;
        }

        $classes = htmlspecialchars($classes ?? 'mb-6 text-2xl font-bold tracking-tight sm:text-3xl', ENT_QUOTES);

        return DBField::create_field('HTMLText', sprintf(
            '<%1$s class="%2$s">%3$s</%1$s>',
            $this->getHeadingLevel(),
            $classes,
            $owner->obj('Title')->XML(),
        ));
    }

    /**
     * Whether this block sits directly in a page's main area, where width and
     * automatic spacing apply.
     */
    public function isTopLevel(): bool
    {
        $owner = $this->getOwner();
        $page = $owner->getPage();
        if (!$page instanceof SiteTree || $owner->getAreaRelationName() !== static::config()->get('main_area_relation')) {
            return false;
        }

        // Blocks in a main column next to a sidebar can't change width
        $sidebar = $page->hasField('SidebarPosition') ? (string) $page->getField('SidebarPosition') : '';

        return $sidebar === '' || $sidebar === 'none';
    }

    /** Full width and top-level: inner content should use the site container */
    public function getIsFullBleed(): bool
    {
        return $this->isTopLevel() && $this->getOwner()->getField('BlockWidth') === 'full';
    }

    /** Width classes for the element holder (see the theme's ElementHolder.ss) */
    public function getBlockWidthClass(): string
    {
        return $this->isTopLevel() ? (self::WIDTHS[$this->getOwner()->getField('BlockWidth')] ?? self::WIDTHS['contained']) : '';
    }

    /**
     * Margin classes for the element holder. With automatic spacing:
     *
     *   plain blocks   sw-auto-top + one section spacing above, sw-auto-bottom
     *                  (the theme drops the top margin under the page title and
     *                  adds a bottom one before the footer)
     *   bands          sw-band: no margin, so consecutive bands touch; the
     *                  theme adds a section spacing when one follows a plain block
     */
    public function getSpacingClasses(): string
    {
        $owner = $this->getOwner();
        $top = $owner->getField('SpacingTop') ?: 'auto';
        $bottom = $owner->getField('SpacingBottom') ?: 'auto';
        $topLevel = $this->isTopLevel();
        $band = $owner->hasMethod('getHasOwnBackground') && $owner->getHasOwnBackground();

        $classes = [];
        if ($top !== 'auto') {
            $classes[] = Style::marginTop($top);
        } elseif ($topLevel) {
            $classes[] = $band ? 'sw-band' : 'sw-auto-top ' . Style::marginTop('lg');
        }
        if ($bottom !== 'auto') {
            $classes[] = Style::marginBottom($bottom);
        } elseif ($topLevel && !$band) {
            $classes[] = 'sw-auto-bottom';
        }

        return implode(' ', $classes);
    }

    private function defaultHeading(): string
    {
        $byClass = static::config()->get('default_headings') ?: [];
        $tag = $byClass[get_class($this->getOwner())] ?? static::config()->get('global_default_heading');

        return in_array($tag, self::TAGS, true) ? $tag : 'h2';
    }
}
