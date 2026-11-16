<?php

namespace Antlion\SilverwindBlocks\Extensions;

use Antlion\SilverwindBlocks\Elements\ElementSection;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;

/**
 * Column width for blocks inside a Section set to the "Columns" layout. Only
 * shown in the CMS for those blocks; everywhere else it has no effect.
 *
 * @extends Extension<BaseElement>
 */
class GridColumnExtension extends Extension
{
    private static array $db = [
        'GridSpan' => 'Varchar(4)',
    ];

    /** Mobile is always full width; the span applies from the md breakpoint. */
    private const array SPANS = [
        '1' => 'md:col-span-1',
        '2' => 'md:col-span-2',
        '3' => 'md:col-span-3',
        '4' => 'md:col-span-4',
        '5' => 'md:col-span-5',
        '6' => 'md:col-span-6',
        '7' => 'md:col-span-7',
        '8' => 'md:col-span-8',
        '9' => 'md:col-span-9',
        '10' => 'md:col-span-10',
        '11' => 'md:col-span-11',
        '12' => 'md:col-span-12',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->removeByName('GridSpan');

        if (!$this->isInGrid()) {
            return;
        }

        $options = [];
        foreach (array_keys(self::SPANS) as $cols) {
            $options[$cols] = match ((int) $cols) {
                3 => '3 of 12 (quarter)',
                4 => '4 of 12 (third)',
                6 => '6 of 12 (half)',
                8 => '8 of 12 (two thirds)',
                9 => '9 of 12 (three quarters)',
                12 => '12 of 12 (full width)',
                default => "{$cols} of 12",
            };
        }

        $fields->addFieldToTab(
            'Root.Main',
            DropdownField::create('GridSpan', _t(__CLASS__ . '.GridSpan', 'Column width'), $options)
                ->setEmptyString(_t(__CLASS__ . '.GridSpanDefault', 'Full width'))
                ->setDescription(_t(__CLASS__ . '.GridSpanDescription', 'Width inside the section on tablets and up. Phones always show blocks full width.')),
        );
    }

    public function getGridSpanClass(): string
    {
        return self::SPANS[$this->getOwner()->getField('GridSpan')] ?? 'md:col-span-12';
    }

    private function isInGrid(): bool
    {
        $parent = $this->getOwner()->getPage();

        return $parent instanceof ElementSection && $parent->Layout === 'columns';
    }
}
