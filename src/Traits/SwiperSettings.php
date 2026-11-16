<?php

namespace Antlion\SilverwindBlocks\Traits;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\NumericField;
use SilverStripe\ORM\DataObject;

/**
 * Settings shared by the Carousel and the Slider (and page slider), with the
 * same names, defaults, labels and order. Each adds its own extras.
 */
trait SwiperSettings
{
    public const array SWIPER_DB = [
        'Loop' => 'Boolean',
        'Speed' => 'Int',
        'Navigation' => 'Boolean',
        'Pagination' => "Enum('none,bullets,fraction','bullets')",
        'Scrollbar' => 'Boolean',
        'Autoplay' => 'Boolean',
        'AutoplayDelay' => 'Int',
        'AutoplayProgress' => 'Boolean',
    ];

    public const array SWIPER_DEFAULTS = [
        'Loop' => true,
        'Speed' => 600,
        'Navigation' => true,
        'Pagination' => 'bullets',
        'Scrollbar' => false,
        'Autoplay' => false,
        'AutoplayDelay' => 6000,
        'AutoplayProgress' => false,
    ];

    /** The object holding the settings: the block itself, or the page for the page slider */
    abstract protected function swiperRecord(): DataObject;

    /**
     * The shared settings in Slideshow-group order: dropdowns ($first, e.g.
     * the slider's effect, then pagination), checkboxes ($afterLoop after
     * Loop, $lastCheckboxes at the end), then the timings.
     *
     * @param FormField[] $first
     * @param FormField[] $afterLoop
     * @param FormField[] $lastCheckboxes
     * @return FormField[]
     */
    protected function swiperFields(array $first = [], array $afterLoop = [], array $lastCheckboxes = []): array
    {
        return [
            ...$first,
            DropdownField::create('Pagination', _t(self::class . '.Pagination', 'Pagination'), [
                'none' => _t(self::class . '.PaginationNone', 'None'),
                'bullets' => _t(self::class . '.PaginationBullets', 'Dots'),
                'fraction' => _t(self::class . '.PaginationFraction', 'Fraction (e.g. 2 / 5)'),
            ]),
            CheckboxField::create('Navigation', _t(self::class . '.Navigation', 'Previous/next arrows')),
            CheckboxField::create('Scrollbar', _t(self::class . '.Scrollbar', 'Scrollbar')),
            CheckboxField::create('Loop', _t(self::class . '.Loop', 'Loop back to the start')),
            ...$afterLoop,
            CheckboxField::create('Autoplay', _t(self::class . '.Autoplay', 'Play automatically'))
                ->setDescription(_t(self::class . '.AutoplayDescription', 'Never for visitors who prefer reduced motion. A pause button is always shown.')),
            CheckboxField::create('AutoplayProgress', _t(self::class . '.AutoplayProgress', 'Show autoplay progress'))
                ->setDescription(_t(self::class . '.AutoplayProgressDescription', 'A countdown ring around the pause button.')),
            ...$lastCheckboxes,
            NumericField::create('Speed', _t(self::class . '.Speed', 'Transition speed (ms)')),
            NumericField::create('AutoplayDelay', _t(self::class . '.AutoplayDelay', 'Autoplay delay (ms)'))
                ->setDescription(_t(self::class . '.AutoplayDelayDescription', 'How long each slide shows before moving on, e.g. 6000 = 6 seconds.')),
        ];
    }

    /**
     * Options common to both, given how many slides there are and how many
     * are visible at once (loop needs more slides than are visible).
     */
    protected function swiperBaseOptions(int $count, float $maxVisible = 1): array
    {
        $r = $this->swiperRecord();
        $options = [
            'speed' => (int) ($r->Speed ?: 600),
            'loop' => (bool) $r->Loop && $count > ceil($maxVisible),
            'navigation' => (bool) $r->Navigation && $count > 1,
            'pagination' => $this->hasPagination() && $count > 1 ? $r->Pagination : false,
            'scrollbar' => (bool) $r->Scrollbar && $count > 1,
        ];

        if ($r->Autoplay && $count > 1) {
            $options['autoplay'] = [
                'delay' => (int) ($r->AutoplayDelay ?: 6000),
                'disableOnInteraction' => false,
                'pauseOnMouseEnter' => true,
            ];
        }

        return $options;
    }

    /** Dots or fraction, for the controls template */
    public function getShowPagination(): bool
    {
        return $this->hasPagination();
    }

    private function hasPagination(): bool
    {
        return in_array($this->swiperRecord()->Pagination, ['bullets', 'fraction'], true);
    }

    public function getSwiperOptionsJSON(): string
    {
        return json_encode($this->getSwiperOptions(), JSON_UNESCAPED_SLASHES | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG) ?: '{}';
    }
}
