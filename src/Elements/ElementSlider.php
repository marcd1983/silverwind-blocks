<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Controllers\BlockController;
use Antlion\SilverwindBlocks\Models\Slide;
use Antlion\SilverwindBlocks\Traits\SliderSettings;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\HasManyList;

/**
 * Full-width image/video slider block.
 *
 * @method HasManyList<Slide> Slides()
 */
class ElementSlider extends BaseElement
{
    use SliderSettings;

    private static string $table_name = 'SWB_ElementSlider';

    private static string $singular_name = 'Slider';

    private static string $plural_name = 'Sliders';

    private static string $description = 'Full-width image or video slides';

    private static string $icon = 'font-icon-block-carousel';

    private static string $controller_class = BlockController::class;

    private static bool $uses_swiper = true;

    private static bool $inline_editable = false;

    private static array $db = self::SLIDER_DB;

    private static array $defaults = self::SLIDER_DEFAULTS;

    private static array $has_many = [
        'Slides' => Slide::class . '.Slider',
    ];

    private static array $owns = ['Slides'];

    private static array $cascade_deletes = ['Slides'];

    private static array $cascade_duplicates = ['Slides'];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Slider');
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(fn (FieldList $fields) => $this->sliderFields($fields, 'Root.Main'));

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    public function getHasOwnBackground(): bool
    {
        return true;
    }

    public function getSlideInnerClass(): string
    {
        return $this->getIsFullBleed() ? 'sw-container' : 'px-6 sm:px-12';
    }

    public function getSummary(): string
    {
        return _t(__CLASS__ . '.Summary', '{count} slide(s)', ['count' => $this->Slides()->count()]);
    }

    protected function swiperRecord(): DataObject
    {
        return $this;
    }

    protected function slidesRelation(): string
    {
        return 'Slides';
    }
}
