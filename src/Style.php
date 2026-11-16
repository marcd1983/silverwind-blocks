<?php

namespace Antlion\SilverwindBlocks;

use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBEnum;

/**
 * The shared design vocabulary for every block: one set of option lists for
 * the CMS and one set of class maps for templates, so the same idea has the
 * same name, values and output everywhere.
 *
 * Class names are written out in full so Tailwind finds them when it scans
 * this file (see the theme's @source lines).
 */
class Style
{
    /**
     * One spacing scale for gaps, padding and margins. Gaps (between blocks
     * inside a Section, between carousel cards) use fixed sizes; padding and
     * margins scale with the theme's Section spacing.
     */
    public const array SPACING = ['none', 'sm', 'md', 'lg', 'xl'];

    private const array GAP = ['none' => 'gap-0', 'sm' => 'gap-4', 'md' => 'gap-8', 'lg' => 'gap-12', 'xl' => 'gap-16'];

    /*
     * Vertical space between and inside blocks is a fraction of the theme's
     * "Section spacing" (--spacing-section, set on the Settings > Theme tab),
     * so changing that one setting rescales every level: large = 1x.
     */
    private const array PADDING_Y = [
        'none' => '',
        'sm' => 'py-[calc(var(--spacing-section)*0.375)]',
        'md' => 'py-[calc(var(--spacing-section)*0.625)]',
        'lg' => 'py-section',
        'xl' => 'py-[calc(var(--spacing-section)*1.5)]',
    ];

    private const array MARGIN_TOP = [
        'none' => 'mt-0',
        'sm' => 'mt-[calc(var(--spacing-section)*0.375)]',
        'md' => 'mt-[calc(var(--spacing-section)*0.625)]',
        'lg' => 'mt-section',
        'xl' => 'mt-[calc(var(--spacing-section)*1.5)]',
    ];

    private const array MARGIN_BOTTOM = [
        'none' => 'mb-0',
        'sm' => 'mb-[calc(var(--spacing-section)*0.375)]',
        'md' => 'mb-[calc(var(--spacing-section)*0.625)]',
        'lg' => 'mb-section',
        'xl' => 'mb-[calc(var(--spacing-section)*1.5)]',
    ];

    /** For Swiper's spaceBetween, which takes pixels */
    private const array PIXELS = ['none' => 0, 'sm' => 12, 'md' => 24, 'lg' => 36, 'xl' => 48];

    private const array TEXT_ALIGN = ['left' => '', 'center' => 'text-center', 'right' => 'text-right'];

    /** Positions a max-width content box (hero, slide) inside its container */
    private const array BOX_POSITION = ['left' => '', 'center' => 'mx-auto', 'right' => 'ml-auto'];

    /** Lines up flex children: button rows, card actions */
    private const array JUSTIFY = ['left' => 'justify-start', 'center' => 'justify-center', 'right' => 'justify-end', 'between' => 'justify-between'];

    private const array ITEMS = ['stretch' => 'items-stretch', 'top' => 'items-start', 'middle' => 'items-center', 'bottom' => 'items-end'];

    private const array BUTTON_SIZE = ['sm' => 'btn-sm', 'md' => '', 'lg' => 'btn-lg'];

    // ---- Enum specs for $db, so every block declares them identically ----

    public const string DB_HORIZONTAL = "Enum('left,center,right','left')";

    public const string DB_BUTTON_ALIGNMENT = "Enum('left,center,right,between','left')";

    public const string DB_VERTICAL = "Enum('top,middle,bottom','middle')";

    public const string DB_VERTICAL_STRETCH = "Enum('stretch,top,middle,bottom','stretch')";

    public const string DB_BUTTON_SIZE = "Enum('sm,md,lg','md')";

    public const string DB_TONE = "Enum('auto,light,dark','auto')";

    public const string DB_SPACING = "Enum('none,sm,md,lg,xl','md')";

    /** Block margins: "auto" leaves spacing to the theme's default rhythm */
    public const string DB_SPACING_AUTO = "Enum('auto,none,sm,md,lg,xl','auto')";

    // ---- CMS option lists ----

    public static function horizontalOptions(): array
    {
        return [
            'left' => _t(__CLASS__ . '.Left', 'Left'),
            'center' => _t(__CLASS__ . '.Center', 'Centre'),
            'right' => _t(__CLASS__ . '.Right', 'Right'),
        ];
    }

    public static function buttonAlignmentOptions(): array
    {
        return self::horizontalOptions() + ['between' => _t(__CLASS__ . '.Between', 'Spread out')];
    }

    public static function verticalOptions(bool $withStretch = false): array
    {
        $options = [
            'top' => _t(__CLASS__ . '.Top', 'Top'),
            'middle' => _t(__CLASS__ . '.Middle', 'Middle'),
            'bottom' => _t(__CLASS__ . '.Bottom', 'Bottom'),
        ];

        return $withStretch ? ['stretch' => _t(__CLASS__ . '.Stretch', 'Equal height')] + $options : $options;
    }

    public static function buttonSizeOptions(): array
    {
        return [
            'sm' => _t(__CLASS__ . '.Small', 'Small'),
            'md' => _t(__CLASS__ . '.Medium', 'Medium'),
            'lg' => _t(__CLASS__ . '.Large', 'Large'),
        ];
    }

    public static function toneOptions(): array
    {
        return [
            'auto' => _t(__CLASS__ . '.ToneAuto', 'Automatic'),
            'light' => _t(__CLASS__ . '.ToneLight', 'Light text'),
            'dark' => _t(__CLASS__ . '.ToneDark', 'Dark text'),
        ];
    }

    public static function spacingOptions(bool $withAuto = false): array
    {
        $options = [
            'none' => _t(__CLASS__ . '.SpacingNone', 'None'),
            'sm' => _t(__CLASS__ . '.SpacingSmall', 'Small'),
            'md' => _t(__CLASS__ . '.SpacingMedium', 'Medium'),
            'lg' => _t(__CLASS__ . '.SpacingLarge', 'Large'),
            'xl' => _t(__CLASS__ . '.SpacingXLarge', 'Extra large'),
        ];

        return $withAuto ? ['auto' => _t(__CLASS__ . '.SpacingAuto', 'Automatic')] + $options : $options;
    }

    /**
     * Give a new, unsaved record its Enum defaults. Silverstripe only applies
     * them in the database, so without this a new block's dropdowns show their
     * first option (and save it) instead of the intended default.
     */
    public static function applyEnumDefaults(DataObject $record): void
    {
        foreach (array_keys($record->getSchema()->databaseFields(get_class($record)) ?? []) as $name) {
            if ($record->getField($name) !== null) {
                continue;
            }
            $field = $record->dbObject($name);
            if ($field instanceof DBEnum && $field->getDefault() !== null) {
                $record->setField($name, $field->getDefault());
            }
        }
    }

    // ---- Template class maps ----

    public static function gap(?string $value): string
    {
        return self::GAP[$value] ?? self::GAP['md'];
    }

    public static function paddingY(?string $value): string
    {
        return self::PADDING_Y[$value] ?? self::PADDING_Y['lg'];
    }

    public static function marginTop(?string $value): string
    {
        return self::MARGIN_TOP[$value] ?? '';
    }

    public static function marginBottom(?string $value): string
    {
        return self::MARGIN_BOTTOM[$value] ?? '';
    }

    public static function pixels(?string $value): int
    {
        return self::PIXELS[$value] ?? self::PIXELS['md'];
    }

    public static function textAlign(?string $value): string
    {
        return self::TEXT_ALIGN[$value] ?? '';
    }

    public static function boxPosition(?string $value): string
    {
        return self::BOX_POSITION[$value] ?? '';
    }

    public static function justify(?string $value): string
    {
        return self::JUSTIFY[$value] ?? self::JUSTIFY['left'];
    }

    public static function items(?string $value): string
    {
        return self::ITEMS[$value] ?? self::ITEMS['middle'];
    }

    public static function buttonSize(?string $value): string
    {
        return self::BUTTON_SIZE[$value] ?? '';
    }
}
