<?php

namespace Antlion\SilverwindBlocks\Traits;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RecordEditor;
use SilverStripe\Forms\LiteralField;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

/**
 * Drag-to-reorder GridField for a block's has_many items.
 */
trait HasOrderedPanels
{
    protected function orderedGrid(FieldList $fields, string $relation, string $title, string $sortField = 'Sort'): void
    {
        $fields->removeByName($relation);

        if (!$this->isInDB()) {
            $fields->addFieldToTab('Root.Main', LiteralField::create(
                $relation . 'SaveFirst',
                '<p class="alert alert-info">' . _t(self::class . '.SaveFirst', 'Save this block to start adding {items}.', ['items' => strtolower($title)]) . '</p>',
            ));

            return;
        }

        $config = GridFieldConfig_RecordEditor::create(50)
            ->addComponent(GridFieldOrderableRows::create($sortField))
            ->removeComponentsByType(GridFieldAddExistingAutocompleter::class);

        $fields->addFieldToTab('Root.Main', GridField::create($relation, $title, $this->$relation(), $config));
    }
}
