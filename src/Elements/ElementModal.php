<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Controllers\BlockController;
use Antlion\SilverwindBlocks\Style;
use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\StyleAccessors;
use DNADesign\Elemental\Extensions\ElementalAreasExtension;
use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;

/**
 * Buttons that each open a block in a pop-up dialog. Every block added
 * inside becomes one button + one <dialog id="modal-{ID}">.
 *
 * @method ElementalArea ModalContent()
 */
class ElementModal extends BaseElement
{
    use ArrangesFields;
    use StyleAccessors;

    private static string $table_name = 'SWB_ElementModal';

    private static string $singular_name = 'Pop-up';

    private static string $plural_name = 'Pop-ups';

    private static string $description = 'Buttons that open blocks in a pop-up';

    private static string $icon = 'font-icon-block-custom';

    private static string $controller_class = BlockController::class;

    private static bool $inline_editable = false;

    private static array $db = [
        'ButtonAlignment' => Style::DB_BUTTON_ALIGNMENT,
        'ButtonSize' => Style::DB_BUTTON_SIZE,
        'DialogSize' => "Enum('sm,md,lg,xl','md')",
    ];

    private static array $has_one = [
        'ModalContent' => ElementalArea::class,
    ];

    private static array $owns = ['ModalContent'];

    private static array $cascade_deletes = ['ModalContent'];

    private static array $cascade_duplicates = ['ModalContent'];

    private static array $extensions = [ElementalAreasExtension::class];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Pop-up');
    }

    public function getOwnedAreaRelationName(): string
    {
        return 'ModalContent';
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName('ModalIdentifiers');
            $fields->addFieldToTab('Root.Main', LiteralField::create('ModalIdentifiers', $this->identifierList()));
            $this->addLayoutGroup($fields, [
                DropdownField::create('ButtonAlignment', _t(__CLASS__ . '.ButtonAlignment', 'Button alignment'), Style::buttonAlignmentOptions()),
                DropdownField::create('ButtonSize', _t(__CLASS__ . '.ButtonSize', 'Button size'), Style::buttonSizeOptions()),
                DropdownField::create('DialogSize', _t(__CLASS__ . '.DialogSize', 'Pop-up width'), [
                    'sm' => _t(__CLASS__ . '.Small', 'Small'),
                    'md' => _t(__CLASS__ . '.Medium', 'Medium'),
                    'lg' => _t(__CLASS__ . '.Large', 'Large'),
                    'xl' => _t(__CLASS__ . '.XLarge', 'Extra large'),
                ]),
            ]);
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    public function getDialogSizeClass(): string
    {
        return match ($this->DialogSize) {
            'sm' => 'max-w-md',
            'lg' => 'max-w-5xl',
            'xl' => 'max-w-7xl',
            default => 'max-w-3xl',
        };
    }

    public function getSummary(): string
    {
        return _t(__CLASS__ . '.Summary', '{count} pop-up(s)', ['count' => $this->ModalContent()->Elements()->count()]);
    }

    private function identifierList(): string
    {
        if (!$this->isInDB() || !$this->ModalContent()->Elements()->count()) {
            return '<p class="alert alert-info">' . _t(__CLASS__ . '.AddBlocks', 'Add blocks below; each one opens in its own pop-up.') . '</p>';
        }

        $rows = '';
        foreach ($this->ModalContent()->Elements() as $element) {
            $rows .= '<li>' . Convert::raw2xml($element->Title ?: $element->getType())
                . ': <code>' . Convert::raw2xml($element->getModalIdentifier()) . '</code></li>';
        }

        return '<div class="form-group field readonly"><label class="form__field-label">'
            . _t(__CLASS__ . '.Ids', 'Pop-up ids')
            . '</label><div class="form__field-holder"><p>'
            . _t(__CLASS__ . '.IdsHelp', 'Use these with a "Modal trigger" link to open a pop-up from elsewhere on the page.')
            . "</p><ul>{$rows}</ul></div></div>";
    }
}
