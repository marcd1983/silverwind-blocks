<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Style;
use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\StyleAccessors;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\LinkField\Form\MultiLinkField;
use SilverStripe\LinkField\Models\Link;

class ElementButtonGroup extends BaseElement
{
    use ArrangesFields;
    use StyleAccessors;

    private static string $table_name = 'SWB_ElementButtonGroup';

    private static string $singular_name = 'Buttons';

    private static string $plural_name = 'Buttons';

    private static string $description = 'A row of buttons';

    private static string $icon = 'font-icon-block-link';

    private static array $db = [
        'ButtonAlignment' => Style::DB_BUTTON_ALIGNMENT,
        'ButtonSize' => Style::DB_BUTTON_SIZE,
        'Stacked' => 'Boolean',
    ];

    private static array $has_many = [
        'Links' => Link::class . '.Owner',
    ];

    private static array $owns = ['Links'];

    private static array $cascade_deletes = ['Links'];

    private static array $cascade_duplicates = ['Links'];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Buttons');
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName('Links');
            $fields->addFieldToTab('Root.Main', MultiLinkField::create('Links', _t(__CLASS__ . '.Links', 'Buttons')));
            $this->addLayoutGroup($fields, [
                DropdownField::create('ButtonAlignment', _t(__CLASS__ . '.ButtonAlignment', 'Alignment'), Style::buttonAlignmentOptions()),
                DropdownField::create('ButtonSize', _t(__CLASS__ . '.ButtonSize', 'Button size'), Style::buttonSizeOptions()),
                CheckboxField::create('Stacked', _t(__CLASS__ . '.Stacked', 'Stack full width on phones')),
            ]);
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    public function getSummary(): string
    {
        return _t(__CLASS__ . '.Summary', '{count} button(s)', ['count' => $this->Links()->count()]);
    }
}
