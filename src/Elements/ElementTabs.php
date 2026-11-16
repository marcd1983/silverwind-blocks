<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Controllers\BlockController;
use Antlion\SilverwindBlocks\Models\TabPanel;
use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\HasOrderedPanels;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\HasManyList;

/**
 * @method HasManyList<TabPanel> Panels()
 */
class ElementTabs extends BaseElement
{
    use ArrangesFields;
    use HasOrderedPanels;

    private static string $table_name = 'SWB_ElementTabs';

    private static string $singular_name = 'Tabs';

    private static string $plural_name = 'Tabs';

    private static string $description = 'Content split into tabs';

    private static string $icon = 'font-icon-block-tabs';

    private static string $controller_class = BlockController::class;

    private static bool $inline_editable = false;

    private static array $db = [
        'Intro' => 'HTMLText',
        'TabStyle' => "Enum('border,lift,box','border')",
    ];

    private static array $has_many = [
        'Panels' => TabPanel::class,
    ];

    private static array $owns = ['Panels'];

    private static array $cascade_deletes = ['Panels'];

    private static array $cascade_duplicates = ['Panels'];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Tabs');
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $this->introField($fields);
            $this->orderedGrid($fields, 'Panels', _t(__CLASS__ . '.Tabs', 'Tabs'));
            $this->addLayoutGroup($fields, [
                DropdownField::create('TabStyle', _t(__CLASS__ . '.TabStyle', 'Tab style'), [
                    'border' => _t(__CLASS__ . '.Underline', 'Underline'),
                    'lift' => _t(__CLASS__ . '.Lifted', 'Folder tabs'),
                    'box' => _t(__CLASS__ . '.Box', 'Pills'),
                ]),
            ]);
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    public function getTabStyleClass(): string
    {
        return match ($this->TabStyle) {
            'lift' => 'tabs-lift',
            'box' => 'tabs-box',
            default => 'tabs-border',
        };
    }

    public function getSummary(): string
    {
        return _t(__CLASS__ . '.Summary', '{count} tab(s)', ['count' => $this->Panels()->count()]);
    }

    protected function provideBlockSchema(): array
    {
        $schema = parent::provideBlockSchema();
        $schema['content'] = $this->getSummary();

        return $schema;
    }
}
