<?php

namespace Antlion\SilverwindBlocks\Models;

use SilverStripe\Forms\FieldList;
use SilverStripe\LinkField\Models\Link;

/**
 * The button that opens one block inside a Modal block. It never navigates:
 * templates render it as a <button data-modal-target="modal-ID">. Only
 * offered on the trigger field added by ModalTriggerExtension.
 */
class ModalButton extends Link
{
    private static string $table_name = 'SWB_ModalButton';

    private static string $singular_name = 'Modal button';

    private static string $plural_name = 'Modal buttons';

    private static $icon = 'font-icon-block-custom';

    private static bool $allowed_by_default = false;

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName('OpenInNew');
            $fields->dataFieldByName('LinkText')
                ?->setTitle(_t(__CLASS__ . '.Label', 'Button label'))
                ->setDescription(_t(__CLASS__ . '.LabelDescription', 'Leave blank to use the block\'s title.'));
        });

        return parent::getCMSFields();
    }

    public function getURL(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return (string) ($this->LinkText ?: $this->Owner()?->Title);
    }
}
