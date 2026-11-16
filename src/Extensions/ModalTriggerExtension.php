<?php

namespace Antlion\SilverwindBlocks\Extensions;

use Antlion\SilverwindBlocks\Elements\ElementModal;
use Antlion\SilverwindBlocks\Models\ModalButton;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Core\Convert;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\LinkField\Form\LinkField;

/**
 * Every block placed inside a Modal block opens in its own dialog. This adds
 * the button that opens it (label + style) and the dialog's id.
 *
 * @extends Extension<BaseElement>
 */
class ModalTriggerExtension extends Extension
{
    private static array $has_one = [
        'ModalTrigger' => ModalButton::class,
    ];

    private static array $owns = ['ModalTrigger'];

    private static array $cascade_deletes = ['ModalTrigger'];

    private static array $cascade_duplicates = ['ModalTrigger'];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->removeByName(['ModalTrigger', 'ModalTriggerID', 'ModalTriggerNotice']);

        if (!$this->isInModal()) {
            return;
        }

        $id = Convert::raw2xml($this->getModalIdentifier());
        $fields->addFieldToTab('Root.Main', LiteralField::create('ModalTriggerNotice', '<p class="alert alert-info">'
            . _t(__CLASS__ . '.Notice', 'This block opens in a pop-up (<code>{id}</code>). Link to it from anywhere with a "Modal trigger" link using that id.', ['id' => $id])
            . '</p>'), 'Title');

        $fields->addFieldToTab('Root.Main', LinkField::create('ModalTrigger', _t(__CLASS__ . '.Button', 'Button that opens it'))
            ->setAllowedTypes([ModalButton::class])
            ->setDescription(_t(__CLASS__ . '.ButtonDescription', 'Optional. Without it the button shows the block\'s title in the default style.')));
    }

    public function getModalIdentifier(): string
    {
        return 'modal-' . $this->getOwner()->ID;
    }

    public function getModalTriggerLabel(): string
    {
        $owner = $this->getOwner();
        $trigger = $owner->ModalTrigger();

        return (string) (($trigger->exists() ? $trigger->LinkText : null) ?: $owner->Title ?: $owner->getType());
    }

    public function getModalTriggerClass(): string
    {
        $trigger = $this->getOwner()->ModalTrigger();

        return $trigger->exists() && $trigger->hasMethod('getButtonClass') ? $trigger->getButtonClass() : 'btn btn-primary';
    }

    private function isInModal(): bool
    {
        $area = $this->getOwner()->Parent();

        return $area && $area->exists() && is_a((string) $area->OwnerClassName, ElementModal::class, true);
    }
}
