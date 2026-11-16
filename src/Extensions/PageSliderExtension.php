<?php

namespace Antlion\SilverwindBlocks\Extensions;

use Antlion\SilverwindBlocks\Controllers\BlockController;
use Antlion\SilverwindBlocks\Models\Slide;
use Antlion\SilverwindBlocks\Traits\SliderSettings;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\Requirements;

/**
 * Adds a "Hero slider" tab to pages. Apply to your Page class and output
 * $PageSlider in the page template (Silverwind's Page.ss already does).
 *
 *     Page:
 *       extensions:
 *         - Antlion\SilverwindBlocks\Extensions\PageSliderExtension
 *
 * @extends Extension<SiteTree>
 */
class PageSliderExtension extends Extension
{
    use SliderSettings;

    private static array $db = self::SLIDER_DB;

    private static array $defaults = self::SLIDER_DEFAULTS;

    private static array $has_many = [
        'HeroSlides' => Slide::class . '.Page',
    ];

    private static array $owns = ['HeroSlides'];

    private static array $cascade_deletes = ['HeroSlides'];

    private static array $cascade_duplicates = ['HeroSlides'];

    public function updateCMSFields(FieldList $fields): void
    {
        $this->sliderFields($fields, 'Root.HeroSlider');
        $fields->fieldByName('Root.HeroSlider')?->setTitle(_t(__CLASS__ . '.Tab', 'Hero slider'));
    }

    /**
     * The rendered slider, or null when the page has no active slides.
     */
    public function PageSlider(): ?DBHTMLText
    {
        if (!$this->getActiveSlides()->exists()) {
            return null;
        }

        Requirements::javascript(BlockController::SCRIPT, ['type' => 'module']);
        Requirements::css(BlockController::SWIPER_CSS);

        return $this->getOwner()->renderWith('Includes/SilverwindBlocks/Slider', [
            'Slider' => $this->getOwner(),
            'SliderID' => 'page-slider-' . $this->getOwner()->ID,
            'IsPageSlider' => true,
        ]);
    }

    /** The page slider sits above the page content, always full width */
    public function getSlideInnerClass(): string
    {
        return 'sw-container';
    }

    protected function swiperRecord(): DataObject
    {
        return $this->getOwner();
    }

    protected function slidesRelation(): string
    {
        return 'HeroSlides';
    }
}
