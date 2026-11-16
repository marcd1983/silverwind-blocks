<?php

namespace Antlion\SilverwindBlocks\Traits;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\FieldType\DBDatetime;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Full-width image/video sliders: the Slider block and the page hero slider.
 * Users provide swiperRecord() and slidesRelation().
 */
trait SliderSettings
{
    use ArrangesFields;
    use SwiperSettings;

    public const array SLIDER_DB = [
        ...self::SWIPER_DB,
        'Effect' => "Enum('slide,fade,coverflow,flip,cube,creative,cards','slide')",
        'LazyLoad' => 'Boolean',
        'DesktopWidth' => 'Int',
        'DesktopHeight' => 'Int',
        'MobileWidth' => 'Int',
        'MobileHeight' => 'Int',
    ];

    public const array SLIDER_DEFAULTS = [
        ...self::SWIPER_DEFAULTS,
        'Autoplay' => true,
        'LazyLoad' => true,
        'DesktopWidth' => 1920,
        'DesktopHeight' => 700,
        'MobileWidth' => 960,
        'MobileHeight' => 1200,
    ];

    abstract protected function slidesRelation(): string;

    /**
     * Slides grid on $tab, slider options in a group under it.
     */
    protected function sliderFields(FieldList $fields, string $tab): void
    {
        $record = $this->swiperRecord();
        $relation = $this->slidesRelation();
        $fields->removeByName(array_merge(array_keys(self::SLIDER_DB), [$relation]));

        if ($record->isInDB()) {
            $config = GridFieldConfig_RecordEditor::create(50)
                ->addComponent(GridFieldOrderableRows::create('Sort'))
                ->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
            $fields->addFieldToTab($tab, GridField::create($relation, _t(self::class . '.Slides', 'Slides'), $record->$relation(), $config));
        }

        $this->addSlideshowGroup($fields, $this->swiperFields(
            [
                DropdownField::create('Effect', _t(self::class . '.Effect', 'Effect'), [
                    'slide' => 'Slide', 'fade' => 'Fade', 'coverflow' => 'Coverflow', 'flip' => 'Flip',
                    'cube' => 'Cube', 'creative' => 'Creative', 'cards' => 'Cards',
                ]),
            ],
            [],
            [
                CheckboxField::create('LazyLoad', _t(self::class . '.LazyLoad', 'Lazy load images'))
                    ->setDescription(_t(self::class . '.LazyLoadDescription', 'Images after the first load as they come up, with a spinner while loading. Faster first page load.')),
            ],
        ));
        $this->addImageSizeGroup($fields, [
            FieldGroup::create(_t(self::class . '.DesktopSize', 'Desktop size (px)'), [
                NumericField::create('DesktopWidth', _t(self::class . '.Width', 'Width')),
                NumericField::create('DesktopHeight', _t(self::class . '.Height', 'Height')),
            ])->setName('DesktopSize')->setDescription(_t(self::class . '.SizeDescription', 'Sets the slider\'s shape; images are cropped to fit.')),
            FieldGroup::create(_t(self::class . '.MobileSize', 'Phone size (px)'), [
                NumericField::create('MobileWidth', _t(self::class . '.Width', 'Width')),
                NumericField::create('MobileHeight', _t(self::class . '.Height', 'Height')),
            ])->setName('MobileSize'),
        ]);

        if ($tab !== 'Root.Main') {
            // The page slider lives on its own tab
            foreach (['BlockSlideshow', 'BlockImageSize'] as $name) {
                if ($group = $fields->fieldByName('Root.Main.' . $name)) {
                    $fields->removeByName($name);
                    $fields->addFieldToTab($tab, $group);
                }
            }
        }
    }

    public function getSwiperOptions(): array
    {
        $r = $this->swiperRecord();
        $options = ['effect' => $r->Effect ?: 'slide'] + $this->swiperBaseOptions($this->getActiveSlides()->count());

        if ($r->LazyLoad) {
            // Load the slides either side of the active one ahead of time
            $options['lazyPreloadPrevNext'] = 1;
        }

        if ($r->Effect === 'fade') {
            $options['fadeEffect'] = ['crossFade' => true];
        }

        return $options;
    }

    /**
     * Slides scheduled for today: within their optional start/end dates.
     */
    public function getActiveSlides(): DataList
    {
        $today = DBDatetime::now()->Format('y-MM-dd');
        $relation = $this->slidesRelation();

        return $this->swiperRecord()->$relation()
            ->filterAny(['StartDate' => null, 'StartDate:LessThanOrEqual' => $today])
            ->filterAny(['EndDate' => null, 'EndDate:GreaterThanOrEqual' => $today]);
    }

    /** Desktop / phone aspect ratios, e.g. "1920 / 700", for CSS aspect-ratio */
    public function getDesktopRatio(): string
    {
        $r = $this->swiperRecord();

        return max(1, (int) $r->DesktopWidth ?: 1920) . ' / ' . max(1, (int) $r->DesktopHeight ?: 700);
    }

    public function getMobileRatio(): string
    {
        $r = $this->swiperRecord();

        return max(1, (int) $r->MobileWidth ?: 960) . ' / ' . max(1, (int) $r->MobileHeight ?: 1200);
    }

    /**
     * Cache key that changes with the settings, any slide or slide button,
     * and the date (scheduled slides switch on/off at midnight).
     */
    public function getSlidesCacheKey(): string
    {
        $r = $this->swiperRecord();
        $relation = $this->slidesRelation();
        $slides = $r->$relation();

        return md5(implode('|', [
            get_class($r),
            $r->ID,
            $r->LastEdited,
            $slides->count(),
            $slides->max('LastEdited'),
            $slides->exists() ? $slides->relation('Links')->max('LastEdited') : '',
            $slides->exists() ? $slides->relation('Links')->count() : 0,
            DBDatetime::now()->Format('y-MM-dd'),
        ]));
    }
}
