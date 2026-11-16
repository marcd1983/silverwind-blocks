<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Style;
use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\StyleAccessors;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\LinkField\Form\MultiLinkField;
use SilverStripe\LinkField\Models\Link;

class ElementMenu extends BaseElement
{
    use ArrangesFields;
    use StyleAccessors;

    private static string $table_name = 'SWB_ElementMenu';

    private static string $singular_name = 'Link list';

    private static string $plural_name = 'Link lists';

    private static string $description = 'A list of links, across or down, optionally collapsible';

    private static string $icon = 'font-icon-menu';

    private static array $db = [
        'Orientation' => "Enum('horizontal,vertical,collapsible','vertical')",
        'ButtonAlignment' => Style::DB_BUTTON_ALIGNMENT,
    ];

    private static array $has_many = [
        'Links' => Link::class . '.Owner',
    ];

    private static array $owns = ['Links'];

    private static array $cascade_deletes = ['Links'];

    private static array $cascade_duplicates = ['Links'];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Link list');
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName('Links');
            $fields->addFieldToTab('Root.Main', MultiLinkField::create('Links', _t(__CLASS__ . '.Links', 'Links')));
            $this->addLayoutGroup($fields, [
                DropdownField::create('Orientation', _t(__CLASS__ . '.Orientation', 'Layout'), [
                    'vertical' => _t(__CLASS__ . '.Vertical', 'Down the page'),
                    'horizontal' => _t(__CLASS__ . '.Horizontal', 'Across the page'),
                    'collapsible' => _t(__CLASS__ . '.Collapsible', 'Collapsible (title opens the list)'),
                ]),
                DropdownField::create('ButtonAlignment', _t(__CLASS__ . '.ButtonAlignment', 'Alignment'), Style::buttonAlignmentOptions())
                    ->displayIf('Orientation')->isEqualTo('horizontal')->end(),
            ]);
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    public function getIsHorizontal(): bool
    {
        return $this->Orientation === 'horizontal';
    }

    public function getSummary(): string
    {
        return _t(__CLASS__ . '.Summary', '{count} link(s)', ['count' => $this->Links()->count()]);
    }
}
