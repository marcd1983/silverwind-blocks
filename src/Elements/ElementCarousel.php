<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Controllers\BlockController;
use Antlion\SilverwindBlocks\Models\CarouselSlide;
use Antlion\SilverwindBlocks\Style;
use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\CardDisplay;
use Antlion\SilverwindBlocks\Traits\HasAppearance;
use Antlion\SilverwindBlocks\Traits\HasOrderedPanels;
use Antlion\SilverwindBlocks\Traits\SwiperSettings;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;

/**
 * A row of cards that scrolls sideways, several visible at once.
 *
 * @method HasManyList<CarouselSlide> Slides()
 */
class ElementCarousel extends BaseElement
{
    use ArrangesFields;
    use CardDisplay;
    use HasAppearance;
    use HasOrderedPanels;
    use SwiperSettings;

    private static string $table_name = 'SWB_ElementCarousel';

    private static string $singular_name = 'Carousel';

    private static string $plural_name = 'Carousels';

    private static string $description = 'Cards that scroll sideways';

    private static string $icon = 'font-icon-block-carousel';

    private static string $controller_class = BlockController::class;

    private static bool $uses_swiper = true;

    private static bool $inline_editable = false;

    private static array $db = [
        ...self::SWIPER_DB,
        'Intro' => 'HTMLText',
        'CardStyle' => "Enum('vertical,horizontal,overlay','vertical')",
        ...self::CARD_DISPLAY_DB,
        // Colour for all the cards: card colour, overlay, text colour
        ...self::BACKGROUND_DB,
        'PerViewSmall' => 'Decimal(3,1)',
        'PerViewMedium' => 'Decimal(3,1)',
        'PerViewLarge' => 'Decimal(3,1)',
        'Gap' => Style::DB_SPACING,
        'CenteredSlides' => 'Boolean',
        'FreeMode' => 'Boolean',
    ];

    private static array $defaults = [
        ...self::SWIPER_DEFAULTS,
        ...self::GRADIENT_DEFAULTS,
        'OverlayOpacity' => 55,
        'Loop' => false,
        'Speed' => 500,
        'PerViewSmall' => 1.2,
        'PerViewMedium' => 2,
        'PerViewLarge' => 3,
    ];

    private static array $has_many = [
        'Slides' => CarouselSlide::class,
    ];

    private static array $owns = ['Slides'];

    private static array $cascade_deletes = ['Slides'];

    private static array $cascade_duplicates = ['Slides'];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Carousel');
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([...array_keys(self::SWIPER_DB), 'CenteredSlides', 'FreeMode']);
            $this->introField($fields);
            $this->orderedGrid($fields, 'Slides', _t(__CLASS__ . '.Cards', 'Cards'));

            $this->addLayoutGroup($fields, [
                DropdownField::create('CardStyle', _t(__CLASS__ . '.CardStyle', 'Card style'), ElementCard::cardStyleOptions()),
                // The same card options as the Card block, applied to every card
                ...$this->cardDisplayFields(),
                FieldGroup::create(_t(__CLASS__ . '.PerView', 'Cards visible'), [
                    NumericField::create('PerViewSmall', _t(__CLASS__ . '.Phones', 'Phones'))->setScale(1),
                    NumericField::create('PerViewMedium', _t(__CLASS__ . '.Tablets', 'Tablets'))->setScale(1),
                    NumericField::create('PerViewLarge', _t(__CLASS__ . '.Desktops', 'Desktops'))->setScale(1),
                ])->setName('PerView')->setDescription(_t(__CLASS__ . '.PerViewDescription', 'Fractions (e.g. 1.2) show part of the next card as a hint to scroll.')),
                DropdownField::create('Gap', _t(__CLASS__ . '.Gap', 'Space between cards'), Style::spacingOptions()),
            ]);

            // Under Layout: the same Colour options as the Card block, for every card
            $fields->removeByName(self::BACKGROUND_FIELD_NAMES);
            $this->addBackgroundGroup($fields, $this->cardColourFields());
            $fields->fieldByName('Root.Main.BlockBackground')?->setTitle(_t(__CLASS__ . '.ColourGroup', 'Colour'));

            $this->addSlideshowGroup($fields, [
                ...$this->swiperFields([], [
                    CheckboxField::create('CenteredSlides', _t(__CLASS__ . '.Centered', 'Centre the active card')),
                    CheckboxField::create('FreeMode', _t(__CLASS__ . '.FreeMode', 'Free scrolling (no snapping)')),
                ]),
            ]);
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    public function getSwiperOptions(): array
    {
        $perView = fn ($value, $fallback) => max(1, round((float) ($value ?: $fallback), 1));
        $small = $perView($this->PerViewSmall, 1.2);
        $medium = $perView($this->PerViewMedium, 2);
        $large = $perView($this->PerViewLarge, 3);

        return $this->swiperBaseOptions($this->Slides()->count(), max($small, $medium, $large)) + [
            'spaceBetween' => Style::pixels($this->Gap),
            'slidesPerView' => $small,
            'breakpoints' => [
                640 => ['slidesPerView' => $medium],
                1024 => ['slidesPerView' => $large],
            ],
            'centeredSlides' => (bool) $this->CenteredSlides,
            'freeMode' => (bool) $this->FreeMode,
        ];
    }

    public function getSummary(): string
    {
        return _t(__CLASS__ . '.Summary', '{count} card(s)', ['count' => $this->Slides()->count()]);
    }

    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        $this->OverlayOpacity = max(0, min(100, (int) $this->OverlayOpacity));
    }

    /** CardDisplay: the carousel holds the settings for all its cards */
    protected function cardDisplaySource(): ?DataObject
    {
        return $this;
    }

    /** Also the getter for the CardStyle field, so it must read the raw value */
    public function getCardStyle(): string
    {
        return $this->getField('CardStyle') ?: 'vertical';
    }

    protected function swiperRecord(): DataObject
    {
        return $this;
    }

    protected function provideBlockSchema(): array
    {
        $schema = parent::provideBlockSchema();
        $schema['content'] = $this->getSummary();

        return $schema;
    }
}
