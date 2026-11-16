<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\CardDisplay;
use Antlion\SilverwindBlocks\Traits\HasAppearance;
use Antlion\SilverwindBlocks\Traits\StyleAccessors;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Core\Convert;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\LinkField\Form\MultiLinkField;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\ORM\DataObject;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * Image, title, text and buttons in one of four card styles. The same
 * styles are used for Carousel cards (Includes/SilverwindBlocks/Card.ss).
 */
class ElementCard extends BaseElement
{
    use ArrangesFields;
    use CardDisplay;
    use HasAppearance;
    use StyleAccessors;

    private static string $table_name = 'SWB_ElementCard';

    private static string $singular_name = 'Card';

    private static string $plural_name = 'Cards';

    private static string $description = 'Image, title, text and buttons';

    private static string $icon = 'font-icon-block-user-4';

    /**
     * Edited in its own form, not inline: inline (React) forms don't run the
     * colour picker or the show/hide rules for style-specific fields.
     */
    private static bool $inline_editable = false;

    /**
     * The shared background fields: card colour and text colour for every
     * style; the overlay (solid or gradient) for overlay cards, where it
     * tints the image so the text on it stays readable.
     */
    private static array $db = [
        ...self::BACKGROUND_DB,
        ...self::CARD_DISPLAY_DB,
        'Content' => 'HTMLText',
        'CardStyle' => "Enum('vertical,horizontal,overlay','vertical')",
    ];

    private static array $defaults = [
        ...self::GRADIENT_DEFAULTS,
        'OverlayOpacity' => 55,
    ];

    private static array $has_one = [
        'Image' => Image::class,
    ];

    private static array $has_many = [
        'Links' => Link::class . '.Owner',
    ];

    private static array $owns = ['Image', 'Links'];

    private static array $cascade_deletes = ['Links'];

    private static array $cascade_duplicates = ['Links'];

    /** Styles where the card is a panel with its own colour */
    public const array PANEL_STYLES = ['vertical', 'horizontal'];

    /** Styles where the text sits on the image */
    public const array OVERLAY_STYLES = ['overlay'];

    public const array STYLES = [
        'vertical' => 'Vertical: image above text',
        'horizontal' => 'Horizontal: image beside text',
        'overlay' => 'Overlay: text on the image',
    ];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Card');
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName(['Links', 'Image', 'Content', 'CardStyle', ...array_keys(self::CARD_DISPLAY_DB), ...self::BACKGROUND_FIELD_NAMES]);

            // In the order a card reads: style, image, text, buttons. Notes
            // that only apply to some styles show and hide with the choice.
            $fields->addFieldsToTab('Root.Main', [
                OptionsetField::create('CardStyle', _t(__CLASS__ . '.CardStyle', 'Style'), self::cardStyleOptions()),
                UploadField::create('Image', _t(__CLASS__ . '.Image', 'Image'))
                    ->setFolderName('Uploads/Blocks/Card')
                    ->setAllowedFileCategories('image/supported')
                    ->setAllowedMaxFileNumber(1)
                    ->setDescription(_t(
                        __CLASS__ . '.ImageDescription',
                        'Its shape and fit are set under Layout. When it\'s cropped, set a focus point on the image to control which part stays.',
                    )),
                ...($this->ImageID ? [] : [$this->styleNote('CardImageNote', 'alert alert-warning', self::OVERLAY_STYLES, _t(
                    __CLASS__ . '.ImageNeeded',
                    'Overlay cards show their text on the image. Without one they show just the card colour.',
                ))]),
                HTMLEditorField::create('Content', _t(__CLASS__ . '.Content', 'Text'))->setRows(5),
                MultiLinkField::create('Links', _t(__CLASS__ . '.Links', 'Buttons')),
                $this->styleNote('CardLinksNote', 'form__field-description', self::PANEL_STYLES, _t(
                    __CLASS__ . '.LinksPanel',
                    'Buttons show under the text, and the image links to the first one.',
                )),
                $this->styleNote('CardLinksOverlayNote', 'form__field-description', self::OVERLAY_STYLES, _t(
                    __CLASS__ . '.LinksOverlay',
                    'The whole card links to the first button, which shows as a "read more" link. Other buttons only show if the first one opens a pop-up.',
                )),
            ]);

            $this->addLayoutGroup($fields, $this->cardDisplayFields());

            // Card and text colour apply to every style; the overlay only to overlay cards
            $this->addBackgroundGroup($fields, $this->cardColourFields());
            $fields->fieldByName('Root.Main.BlockBackground')?->setTitle(_t(__CLASS__ . '.ColourGroup', 'Colour'));
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        $this->OverlayOpacity = max(0, min(100, (int) $this->OverlayOpacity));
    }

    /** HasAppearance: overlay cards treat their image as their background */
    public function getHasBackgroundImage(): bool
    {
        return $this->cardHasBackgroundImage();
    }

    public function getBackgroundPosition(): string
    {
        return $this->cardImagePosition();
    }

    /** A note shown only for some card styles */
    private function styleNote(string $name, string $class, array $styles, string $text): Wrapper
    {
        $note = LiteralField::create($name, '<p class="' . $class . '">' . Convert::raw2xml($text) . '</p>');

        return $this->forCardStyles(Wrapper::create($note)->setName($name . 'Wrapper'), $styles);
    }

    public static function cardStyleOptions(): array
    {
        $options = [];
        foreach (self::STYLES as $key => $label) {
            $options[$key] = _t(__CLASS__ . '.Style_' . $key, $label);
        }

        return $options;
    }

    /** Card.ss contract (shared with CarouselSlide) */
    public function getCardTitle(): ?string
    {
        return $this->ShowTitle ? $this->Title : null;
    }

    public function getCardHeading(): string
    {
        return $this->getHeadingLevel();
    }

    /** CardDisplay: a card holds its own display settings */
    protected function cardDisplaySource(): ?DataObject
    {
        return $this;
    }

    /** Also the getter for the CardStyle field, so it must read the raw value */
    public function getCardStyle(): string
    {
        return $this->getField('CardStyle') ?: 'vertical';
    }

    /** Shown in the page's block list: style, buttons and the start of the text */
    public function getSummary(): string
    {
        $parts = [self::cardStyleOptions()[$this->CardStyle] ?? ''];
        $parts[0] = strtok($parts[0], ':') ?: $parts[0];
        if ($count = $this->Links()->count()) {
            $parts[] = _t(__CLASS__ . '.SummaryButtons', '{count} button(s)', ['count' => $count]);
        }
        if (!$this->Image()->exists()) {
            $parts[] = _t(__CLASS__ . '.SummaryNoImage', 'no image');
        }
        if ($text = (string) $this->dbObject('Content')->Summary(12)) {
            $parts[] = $text;
        }

        return implode(' · ', array_filter($parts));
    }

    protected function provideBlockSchema(): array
    {
        $schema = parent::provideBlockSchema();
        $schema['content'] = $this->getSummary();
        if ($this->Image()->exists()) {
            $schema['fileUrl'] = $this->Image()->CMSThumbnail()?->getURL();
            $schema['fileTitle'] = $this->Image()->Title;
        }

        return $schema;
    }
}
