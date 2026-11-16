<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Style;
use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\HasAppearance;
use Antlion\SilverwindBlocks\Traits\HasColumns;
use Antlion\SilverwindBlocks\Traits\StyleAccessors;
use DNADesign\Elemental\Extensions\ElementalAreasExtension;
use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * The one layout block: holds other blocks, optionally on a background.
 *
 *   Stacked   one block after another
 *   Columns   side by side; each block inside sets its own width (GridColumnExtension)
 *   Grid      equal columns, a set number per row per screen size
 *
 * Without a background it's a plain layout wrapper; with one it's a
 * coloured or photographic band. "Automatic" padding follows that.
 *
 * @method ElementalArea Elements()
 */
class ElementSection extends BaseElement
{
    use ArrangesFields;
    use HasAppearance;
    use HasColumns;
    use StyleAccessors;

    private static string $table_name = 'SWB_ElementSection';

    private static string $singular_name = 'Section';

    private static string $plural_name = 'Sections';

    private static string $description = 'Holds other blocks: stacked, in columns or in a grid, optionally on a background';

    private static string $icon = 'font-icon-block-layout-5';

    private static bool $inline_editable = false;

    public const array LAYOUTS = ['stacked', 'columns', 'grid'];

    private static array $db = [
        ...self::BACKGROUND_DB,
        ...self::COLUMNS_DB,
        'Intro' => 'HTMLText',
        'Layout' => "Enum('stacked,columns,grid','stacked')",
        'Gap' => Style::DB_SPACING,
        'VerticalAlignment' => Style::DB_VERTICAL_STRETCH,
        'ContentWidth' => "Enum('contained,narrow,full','contained')",
        'PaddingY' => "Enum('auto,none,sm,md,lg,xl','auto')",
        'BackgroundFixed' => 'Boolean',
    ];

    private static array $defaults = [
        ...self::GRADIENT_DEFAULTS,
        'ColumnsSmall' => 1,
        'ColumnsMedium' => 2,
        'ColumnsLarge' => 3,
    ];

    private static array $has_one = [
        ...self::BACKGROUND_IMAGES,
        'Elements' => ElementalArea::class,
    ];

    private static array $owns = ['BackgroundImage', 'MobileBackgroundImage', 'Elements'];

    private static array $cascade_deletes = ['Elements'];

    private static array $cascade_duplicates = ['Elements'];

    private static array $extensions = [ElementalAreasExtension::class];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Section');
    }

    public function getOwnedAreaRelationName(): string
    {
        return 'Elements';
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName(self::BACKGROUND_FIELD_NAMES);
            $this->introField($fields);

            $this->addLayoutGroup($fields, [
                DropdownField::create('Layout', _t(__CLASS__ . '.Layout', 'Arrange blocks'), [
                    'stacked' => _t(__CLASS__ . '.Stacked', 'Stacked: one after another'),
                    'columns' => _t(__CLASS__ . '.Columns', 'Columns: side by side, each block sets its width'),
                    'grid' => _t(__CLASS__ . '.Grid', 'Grid: equal columns, a set number per row'),
                ]),
                // In a Wrapper: display-logic's FieldGroup template drops the
                // show/hide attributes, so a FieldGroup's own rule never shows it
                Wrapper::create($this->columnsField(_t(__CLASS__ . '.PerRow', 'Blocks per row')))
                    ->setName('ColumnsWrapper')
                    ->displayIf('Layout')->isEqualTo('grid')->end(),
                DropdownField::create('Gap', _t(__CLASS__ . '.Gap', 'Space between blocks'), Style::spacingOptions()),
                DropdownField::create('VerticalAlignment', _t(__CLASS__ . '.VerticalAlignment', 'Vertical alignment'), Style::verticalOptions(true))
                    ->displayIf('Layout')->isNotEqualTo('stacked')->end(),
                DropdownField::create('ContentWidth', _t(__CLASS__ . '.ContentWidth', 'Width of the blocks inside'), [
                    'contained' => _t(__CLASS__ . '.Contained', 'Site width'),
                    'narrow' => _t(__CLASS__ . '.Narrow', 'Narrow (reading width)'),
                    'full' => _t(__CLASS__ . '.Full', 'Edge to edge'),
                ])->setDescription(_t(__CLASS__ . '.ContentWidthDescription', '"Width" above sets how far the background reaches; this sets the blocks inside it.')),
                DropdownField::create('PaddingY', _t(__CLASS__ . '.PaddingY', 'Space above and below'), Style::spacingOptions(true))
                    ->setDescription(_t(__CLASS__ . '.PaddingYDescription', 'Automatic adds space when the section has a background, and none when it doesn\'t.')),
            ]);

            $this->addBackgroundGroup($fields, [
                ...$this->backgroundFields('Uploads/Blocks/Section'),
                CheckboxField::create('BackgroundFixed', _t(__CLASS__ . '.BackgroundFixed', 'Keep the image still while scrolling (parallax)')),
            ]);
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    public function getSummary(): string
    {
        $layout = match ($this->Layout) {
            'columns' => _t(__CLASS__ . '.SummaryColumns', 'in columns'),
            'grid' => _t(__CLASS__ . '.SummaryGrid', 'in a grid'),
            default => _t(__CLASS__ . '.SummaryStacked', 'stacked'),
        };

        return _t(__CLASS__ . '.Summary', '{count} block(s), {layout}', [
            'count' => $this->Elements()->Elements()->count(),
            'layout' => $layout,
        ]);
    }

    public function getIsColumns(): bool
    {
        return $this->Layout === 'columns';
    }

    public function getHasBackground(): bool
    {
        return $this->getHasBackgroundImage() || $this->getBackgroundHex() !== null;
    }

    /** Classes for the element that directly holds the inner blocks */
    public function getLayoutClasses(): string
    {
        $gap = Style::gap($this->Gap);

        return match ($this->Layout) {
            'columns' => "grid grid-cols-12 {$gap} " . $this->getVerticalAlignClass(),
            'grid' => "grid {$gap} " . $this->columnClasses('grid') . ' ' . $this->getVerticalAlignClass(),
            default => "flex flex-col {$gap}",
        };
    }

    public function getPaddingClass(): string
    {
        if ($this->PaddingY === 'auto' || !$this->PaddingY) {
            return $this->getHasBackground() ? Style::paddingY('lg') : '';
        }

        return Style::paddingY($this->PaddingY);
    }

    /** Counts as a band for automatic spacing only when it has a background */
    public function getHasOwnBackground(): bool
    {
        return $this->getHasBackground();
    }

    /**
     * Width of the blocks inside. A full-width section uses the site
     * container; otherwise the section is already as wide as it should be,
     * so it only adds side padding when it has a background to pad from.
     */
    public function getInnerWidthClass(): string
    {
        $padded = $this->getIsFullBleed() || $this->getHasBackground();

        return match ($this->ContentWidth) {
            'narrow' => 'mx-auto w-full max-w-narrow' . ($padded ? ' px-gutter' : ''),
            'full' => 'w-full',
            default => $this->getIsFullBleed() ? 'sw-container' : ($padded ? 'px-6 sm:px-10' : ''),
        };
    }
}
