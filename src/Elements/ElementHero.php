<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Style;
use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\HasAppearance;
use Antlion\SilverwindBlocks\Traits\StyleAccessors;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\LinkField\Form\MultiLinkField;
use SilverStripe\LinkField\Models\Link;

/**
 * Large banner: title, text and buttons over a background image or colour.
 */
class ElementHero extends BaseElement
{
    use ArrangesFields;
    use HasAppearance;
    use StyleAccessors;

    private static string $table_name = 'SWB_ElementHero';

    private static string $singular_name = 'Hero';

    private static string $plural_name = 'Heroes';

    private static string $description = 'Banner with a background image, text and buttons';

    private static string $icon = 'font-icon-block-banner';

    private static bool $inline_editable = false;

    private static array $db = [
        ...self::BACKGROUND_DB,
        'Content' => 'HTMLText',
        'Height' => "Enum('auto,short,medium,tall,screen','tall')",
        'HorizontalAlignment' => Style::DB_HORIZONTAL,
        'VerticalAlignment' => Style::DB_VERTICAL,
        'ButtonSize' => "Enum('sm,md,lg','lg')",
    ];

    private static array $has_one = self::BACKGROUND_IMAGES;

    private static array $has_many = [
        'Links' => Link::class . '.Owner',
    ];

    private static array $owns = ['BackgroundImage', 'MobileBackgroundImage', 'Links'];

    private static array $cascade_deletes = ['Links'];

    private static array $cascade_duplicates = ['Links'];

    private static array $defaults = [
        ...self::GRADIENT_DEFAULTS,
        'OverlayOpacity' => 40,
    ];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Hero');
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName(['Links', ...self::BACKGROUND_FIELD_NAMES]);
            $fields->addFieldToTab('Root.Main', MultiLinkField::create('Links', _t(__CLASS__ . '.Links', 'Buttons')));

            $this->addLayoutGroup($fields, [
                DropdownField::create('Height', _t(__CLASS__ . '.Height', 'Height'), [
                    'auto' => _t(__CLASS__ . '.HeightAuto', 'Fit content'),
                    'short' => _t(__CLASS__ . '.HeightShort', 'Short'),
                    'medium' => _t(__CLASS__ . '.HeightMedium', 'Medium'),
                    'tall' => _t(__CLASS__ . '.HeightTall', 'Tall'),
                    'screen' => _t(__CLASS__ . '.HeightScreen', 'Full screen'),
                ]),
                DropdownField::create('HorizontalAlignment', _t(__CLASS__ . '.HorizontalAlignment', 'Horizontal alignment'), Style::horizontalOptions()),
                DropdownField::create('VerticalAlignment', _t(__CLASS__ . '.VerticalAlignment', 'Vertical alignment'), Style::verticalOptions()),
                DropdownField::create('ButtonSize', _t(__CLASS__ . '.ButtonSize', 'Button size'), Style::buttonSizeOptions()),
            ]);
            $this->addBackgroundGroup($fields, $this->backgroundFields('Uploads/Blocks/Hero'));
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    public function getHasOwnBackground(): bool
    {
        return true;
    }

    /** Full-width heroes line their text up with the site container */
    public function getInnerClass(): string
    {
        return $this->getIsFullBleed() ? 'sw-container' : 'px-6 sm:px-12';
    }

    public function getHeightClass(): string
    {
        return match ($this->Height) {
            'short' => 'min-h-72',
            'medium' => 'min-h-[28rem]',
            'tall' => 'min-h-[36rem]',
            'screen' => 'min-h-[calc(100svh-5rem)]',
            default => '',
        };
    }
}
