<?php

namespace Antlion\SilverwindBlocks\Traits;

use Antlion\SilverwindBlocks\Style;
use Antlion\SilverwindSettings\Forms\ThemeColorField;
use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FormField;
use SilverStripe\ORM\DataObject;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * How a card shows its text and image, shared by the Card block and the
 * Carousel (which applies one setting to all its cards):
 *
 *   HorizontalAlignment  text alignment
 *   ImageRatio           the image's shape: automatic (4:3, or square for
 *                        overlay cards), a fixed ratio, or the original shape
 *   ImageFit             cover (fill the shape, cropping around the focus
 *                        point) or contain (show the whole image)
 *
 * and the Colour group: card colour, overlay (overlay cards only: solid or
 * gradient, from HasAppearance) and text colour.
 *
 * The record holding the settings (cardDisplaySource) is the Card itself, or
 * the Carousel for a carousel card. Templates (Card.ss) use $CardImage,
 * $CardFrameClass, $CardImageClass, $CardTextAlignClass, $CardJustifyClass,
 * $CardToneClass and $CardOverlayStyle.
 *
 * Classes using this also use HasAppearance.
 */
trait CardDisplay
{
    public const array CARD_DISPLAY_DB = [
        'HorizontalAlignment' => Style::DB_HORIZONTAL,
        'ImageRatio' => "Enum('auto,1x1,4x3,3x2,16x9,3x4,original','auto')",
        'ImageFit' => "Enum('cover,contain','cover')",
    ];

    /** Ratio => [image width, image height, aspect class] */
    private const array CARD_RATIOS = [
        '1x1' => [800, 800, 'aspect-square'],
        '4x3' => [800, 600, 'aspect-[4/3]'],
        '3x2' => [900, 600, 'aspect-[3/2]'],
        '16x9' => [960, 540, 'aspect-video'],
        '3x4' => [600, 800, 'aspect-[3/4]'],
    ];

    /** Width for images kept at their original shape */
    private const int CARD_ORIGINAL_WIDTH = 900;

    /** The record holding the card settings; null when there isn't one yet */
    abstract protected function cardDisplaySource(): ?DataObject;

    /** The card style the settings apply to: vertical, horizontal or overlay */
    abstract public function getCardStyle(): string;

    /** @return FormField[] For the Layout group, in this order */
    protected function cardDisplayFields(): array
    {
        return [
            DropdownField::create('HorizontalAlignment', _t(self::class . '.HorizontalAlignment', 'Text alignment'), Style::horizontalOptions()),
            DropdownField::create('ImageRatio', _t(self::class . '.ImageRatio', 'Image shape'), [
                'auto' => _t(self::class . '.RatioAuto', 'Automatic (4:3, square for overlay cards)'),
                '1x1' => _t(self::class . '.Ratio1x1', 'Square (1:1)'),
                '4x3' => _t(self::class . '.Ratio4x3', 'Landscape (4:3)'),
                '3x2' => _t(self::class . '.Ratio3x2', 'Landscape (3:2)'),
                '16x9' => _t(self::class . '.Ratio16x9', 'Wide (16:9)'),
                '3x4' => _t(self::class . '.Ratio3x4', 'Portrait (3:4)'),
                'original' => _t(self::class . '.RatioOriginal', 'Original shape of each image'),
            ]),
            DropdownField::create('ImageFit', _t(self::class . '.ImageFit', 'Image fit'), [
                'cover' => _t(self::class . '.FitCover', 'Fill the shape (crops around the focus point)'),
                'contain' => _t(self::class . '.FitContain', 'Show the whole image (may leave space around it)'),
            ])->setDescription(_t(self::class . '.ImageFitDescription', 'Has no effect with "Original shape".')),
        ];
    }

    /**
     * The Colour group's fields: card colour and text colour for every
     * style, the overlay only for overlay cards (shown/hidden by the
     * CardStyle field on the same form).
     *
     * @return FormField[]
     */
    protected function cardColourFields(): array
    {
        return [
            ThemeColorField::create('BackgroundColor', _t(self::class . '.CardColour', 'Card colour'))
                ->setDescription(_t(self::class . '.CardColourDescription', 'Leave empty for the default. On overlay cards it shows behind the image, and instead of it when there\'s no image.')),
            // Same overlay fields as Hero, Section and Slide: solid or gradient
            $this->forCardStyles(Wrapper::create($this->overlayFields())->setName('CardOverlay'), ['overlay']),
            DropdownField::create('Tone', _t(self::class . '.Tone', 'Text colour'), Style::toneOptions())
                ->setDescription(_t(self::class . '.CardToneDescription', 'Automatic picks light or dark text to suit the card colour, or the image and overlay.')),
        ];
    }

    /** Show a field only for the given card styles */
    protected function forCardStyles(Wrapper $wrapper, array $styles): Wrapper
    {
        $criteria = $wrapper->displayIf('CardStyle')->isEqualTo(array_shift($styles));
        foreach ($styles as $style) {
            $criteria = $criteria->orIf('CardStyle')->isEqualTo($style);
        }

        return $criteria->end();
    }

    /**
     * Text colour for the card. An overlay card with no colour and no image
     * is the default dark panel, so it needs light text.
     */
    public function getCardToneClass(): string
    {
        $tone = $this->getToneClass();

        return ($tone === '' && $this->getCardStyle() === 'overlay') ? 'sw-tone-light' : $tone;
    }

    public function getCardOverlayStyle(): string
    {
        return $this->getOverlayStyle();
    }

    /** Overlay cards treat their image as their background, for automatic text colour */
    protected function cardHasBackgroundImage(): bool
    {
        return $this->getCardStyle() === 'overlay' && $this->hasMethod('Image') && $this->Image()->exists();
    }

    /** object-position for the card image, honouring jonom/focuspoint */
    protected function cardImagePosition(): string
    {
        $image = $this->hasMethod('Image') ? $this->Image() : null;
        if ($image && $image->exists() && $image->hasField('FocusPoint') && ($point = $image->dbObject('FocusPoint'))) {
            return $point->PercentageX() . '% ' . $point->PercentageY() . '%';
        }

        return '50% 50%';
    }

    /**
     * The card image, sized for its shape, or null without an image. Covered
     * images are cropped on the server (around the focus point, when
     * jonom/focuspoint is installed); contained ones are scaled to fit.
     */
    public function getCardImage(): ?AssetContainer
    {
        $image = $this->hasMethod('Image') ? $this->Image() : null;
        if (!$image instanceof Image || !$image->exists()) {
            return null;
        }

        $ratio = $this->cardRatio();
        if (!$ratio) {
            return $image->ScaleMaxWidth(self::CARD_ORIGINAL_WIDTH);
        }

        [$width, $height] = $ratio;
        if ($this->cardSetting('ImageFit') === 'contain') {
            return $image->Fit($width, $height);
        }

        return $image->hasMethod('FocusFill') ? $image->FocusFill($width, $height) : $image->Fill($width, $height);
    }

    /** Aspect-ratio class for the image's frame, or '' for the original shape */
    public function getCardFrameClass(): string
    {
        return $this->cardRatio()[2] ?? '';
    }

    public function getCardImageClass(): string
    {
        return $this->cardSetting('ImageFit') === 'contain' && $this->cardRatio() ? 'object-contain' : 'object-cover';
    }

    public function getCardTextAlignClass(): string
    {
        return Style::textAlign($this->cardSetting('HorizontalAlignment'));
    }

    public function getCardJustifyClass(): string
    {
        return Style::justify($this->cardSetting('HorizontalAlignment'));
    }

    /** @return array{int, int, string}|null [width, height, class], or null for the original shape */
    private function cardRatio(): ?array
    {
        $key = $this->cardSetting('ImageRatio') ?: 'auto';
        if ($key === 'original') {
            return null;
        }
        if ($key === 'auto') {
            $key = $this->getCardStyle() === 'overlay' ? '1x1' : '4x3';
        }

        return self::CARD_RATIOS[$key] ?? self::CARD_RATIOS['4x3'];
    }

    private function cardSetting(string $name): ?string
    {
        $source = $this->cardDisplaySource();

        return $source ? $source->getField($name) : null;
    }
}
