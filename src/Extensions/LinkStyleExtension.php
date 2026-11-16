<?php

namespace Antlion\SilverwindBlocks\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\LinkField\Models\Link;

/**
 * Button style for links rendered as buttons (block buttons, header buttons).
 * Usage in templates: class="$ButtonClass".
 *
 * @extends Extension<Link>
 */
class LinkStyleExtension extends Extension
{
    private static array $db = [
        'ButtonStyle' => 'Varchar(16)',
        'ExtraClass' => 'Varchar(255)',
    ];

    public const array STYLES = [
        'primary' => 'btn btn-primary',
        'secondary' => 'btn btn-secondary',
        'accent' => 'btn btn-accent',
        'neutral' => 'btn btn-neutral',
        'outline' => 'btn btn-outline',
        'outline-primary' => 'btn btn-outline btn-primary',
        'soft' => 'btn btn-soft btn-primary',
        'ghost' => 'btn btn-ghost',
        'link' => 'btn btn-link px-0',
    ];

    public function updateCMSFields(FieldList $fields): void
    {
        $fields->removeByName(['ButtonStyle', 'ExtraClass']);

        $fields->addFieldsToTab('Root.Main', [
            DropdownField::create('ButtonStyle', _t(__CLASS__ . '.ButtonStyle', 'Button style'), [
                'primary' => _t(__CLASS__ . '.Primary', 'Primary'),
                'secondary' => _t(__CLASS__ . '.Secondary', 'Secondary'),
                'accent' => _t(__CLASS__ . '.Accent', 'Accent'),
                'neutral' => _t(__CLASS__ . '.Neutral', 'Neutral (dark)'),
                'outline' => _t(__CLASS__ . '.Outline', 'Outline'),
                'outline-primary' => _t(__CLASS__ . '.OutlinePrimary', 'Outline (primary)'),
                'soft' => _t(__CLASS__ . '.Soft', 'Soft (primary)'),
                'ghost' => _t(__CLASS__ . '.Ghost', 'Ghost'),
                'link' => _t(__CLASS__ . '.Link', 'Text link'),
            ])->setEmptyString(_t(__CLASS__ . '.Default', 'Default')),
            TextField::create('ExtraClass', _t(__CLASS__ . '.ExtraClass', 'Extra CSS classes'))
                ->setDescription(_t(__CLASS__ . '.ExtraClassDescription', 'Optional, for developers.')),
        ]);
    }

    public function getButtonClass(): string
    {
        $owner = $this->getOwner();
        $classes = self::STYLES[$owner->getField('ButtonStyle')] ?? 'btn';
        $extra = preg_replace('/[^A-Za-z0-9_\-:\/\[\]. ]/', '', (string) $owner->getField('ExtraClass'));

        return trim($classes . ' ' . $extra);
    }
}
