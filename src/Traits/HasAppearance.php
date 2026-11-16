<?php

namespace Antlion\SilverwindBlocks\Traits;

use Antlion\SilverwindBlocks\Style;
use Antlion\SilverwindSettings\Colour;
use Antlion\SilverwindSettings\Forms\ThemeColorField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\NumericField;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * One definition of the background fields, used by Hero, Section and Slide
 * (all of them) and Card (colour and text colour only):
 *
 *   private static array $db = [...self::BACKGROUND_DB, ...];
 *   private static array $has_one = [...self::BACKGROUND_IMAGES, ...];
 *
 * The overlay is a solid tint or a two-colour gradient; each gradient end
 * has its own colour, strength and position. Classes spread
 * GRADIENT_DEFAULTS into their $defaults.
 *
 * Tone "auto" picks light text on dark colours/images and dark text on light
 * ones. The sw-tone-* classes come from the theme and recolour rich text too.
 */
trait HasAppearance
{
    public const array BACKGROUND_DB = [
        'BackgroundColor' => 'Varchar(7)',
        'OverlayColor' => 'Varchar(7)',
        'OverlayOpacity' => 'Int',
        'OverlayType' => "Enum('solid,gradient','solid')",
        'GradientDirection' => "Enum('top,bottom,left,right,top-right,top-left,bottom-right,bottom-left','top')",
        'GradientStartColor' => 'Varchar(7)',
        'GradientStartOpacity' => 'Int',
        'GradientStartPoint' => 'Int',
        'GradientEndColor' => 'Varchar(7)',
        'GradientEndOpacity' => 'Int',
        'GradientEndPoint' => 'Int',
        'Tone' => Style::DB_TONE,
    ];

    /** Dark at the start (bottom by default), fading out by the end */
    public const array GRADIENT_DEFAULTS = [
        'GradientStartOpacity' => 70,
        'GradientStartPoint' => 0,
        'GradientEndOpacity' => 0,
        'GradientEndPoint' => 100,
    ];

    /** Direction value => CSS linear-gradient direction (towards the end colour) */
    public const array GRADIENT_DIRECTIONS = [
        'top' => 'to top',
        'bottom' => 'to bottom',
        'left' => 'to left',
        'right' => 'to right',
        'top-right' => 'to top right',
        'top-left' => 'to top left',
        'bottom-right' => 'to bottom right',
        'bottom-left' => 'to bottom left',
    ];

    public const array BACKGROUND_IMAGES = [
        'BackgroundImage' => Image::class,
        'MobileBackgroundImage' => Image::class,
    ];

    public const array BACKGROUND_FIELD_NAMES = [
        'BackgroundColor', 'OverlayColor', 'OverlayOpacity', 'Tone', 'BackgroundImage', 'MobileBackgroundImage',
        'OverlayType', 'GradientDirection', 'GradientStartColor', 'GradientStartOpacity', 'GradientStartPoint',
        'GradientEndColor', 'GradientEndOpacity', 'GradientEndPoint',
    ];

    /**
     * The Background group's fields, in a fixed order.
     *
     * @return FormField[]
     */
    protected function backgroundFields(string $folder, bool $withImage = true): array
    {
        $fields = [];

        if ($withImage) {
            $fields[] = UploadField::create('BackgroundImage', _t(self::class . '.BackgroundImage', 'Image'))
                ->setFolderName($folder)
                ->setAllowedFileCategories('image/supported')
                ->setDescription(_t(self::class . '.BackgroundImageDescription', 'Wide images work best, at least 2000px across.'));
            $fields[] = UploadField::create('MobileBackgroundImage', _t(self::class . '.MobileBackgroundImage', 'Phone image (optional)'))
                ->setFolderName($folder)
                ->setAllowedFileCategories('image/supported')
                ->setDescription(_t(self::class . '.MobileBackgroundImageDescription', 'A taller crop for phones. Uses the image above if empty.'));
        }

        $fields[] = ThemeColorField::create('BackgroundColor', _t(self::class . '.BackgroundColor', 'Colour'))
            ->setDescription($withImage
                ? _t(self::class . '.BackgroundColorWithImage', 'Shown when there\'s no image, or while it loads.')
                : _t(self::class . '.BackgroundColorDescription', 'Leave empty for the default.'));

        if ($withImage) {
            array_push($fields, ...$this->overlayFields());
        }

        $fields[] = DropdownField::create('Tone', _t(self::class . '.Tone', 'Text colour'), Style::toneOptions())
            ->setDescription(_t(self::class . '.ToneDescription', 'Automatic uses light text on dark colours and images.'));

        return $fields;
    }

    /** @return FormField[] Overlay type, then the solid or the gradient settings */
    private function overlayFields(): array
    {
        $strength = _t(self::class . '.StrengthDescription', '0 = clear, 100 = solid colour.');
        $position = _t(self::class . '.PointDescription', '0–100% of the way across, in the gradient\'s direction.');

        return [
            DropdownField::create('OverlayType', _t(self::class . '.OverlayType', 'Overlay'), [
                'solid' => _t(self::class . '.OverlaySolid', 'Solid colour'),
                'gradient' => _t(self::class . '.OverlayGradient', 'Gradient'),
            ])->setDescription(_t(self::class . '.OverlayTypeDescription', 'Darkens or tints the image so text stays readable.')),
            Wrapper::create([
                ThemeColorField::create('OverlayColor', _t(self::class . '.OverlayColor', 'Overlay colour'))
                    ->setDescription(_t(self::class . '.OverlayColorDescription', 'Defaults to black.')),
                NumericField::create('OverlayOpacity', _t(self::class . '.OverlayOpacity', 'Overlay strength (0–100)'))
                    ->setDescription(_t(self::class . '.OverlayOpacityDescription', 'Around 40 suits most photos.')),
            ])->setName('OverlaySolidFields')->displayIf('OverlayType')->isEqualTo('solid')->end(),
            Wrapper::create([
                DropdownField::create('GradientDirection', _t(self::class . '.GradientDirection', 'Gradient direction'), [
                    'top' => _t(self::class . '.DirTop', 'Bottom to top'),
                    'bottom' => _t(self::class . '.DirBottom', 'Top to bottom'),
                    'right' => _t(self::class . '.DirRight', 'Left to right'),
                    'left' => _t(self::class . '.DirLeft', 'Right to left'),
                    'top-right' => _t(self::class . '.DirTopRight', 'Bottom left to top right'),
                    'top-left' => _t(self::class . '.DirTopLeft', 'Bottom right to top left'),
                    'bottom-right' => _t(self::class . '.DirBottomRight', 'Top left to bottom right'),
                    'bottom-left' => _t(self::class . '.DirBottomLeft', 'Top right to bottom left'),
                ]),
                $this->gradientEndGroup('Start', _t(self::class . '.GradientStart', 'Start'), $strength, $position),
                $this->gradientEndGroup('End', _t(self::class . '.GradientEnd', 'End'), $strength, $position),
            ])->setName('OverlayGradientFields')->displayIf('OverlayType')->isEqualTo('gradient')->end(),
        ];
    }

    /**
     * One end of the gradient as a single row: colour, strength, position.
     * The shared hints go on the group rather than repeating on each field.
     */
    private function gradientEndGroup(string $which, string $title, string $strength, string $position): FieldGroup
    {
        return FieldGroup::create($title, [
            ThemeColorField::create("Gradient{$which}Color", _t(self::class . '.GradientColor', 'Colour')),
            NumericField::create("Gradient{$which}Opacity", _t(self::class . '.GradientOpacity', 'Strength (0–100)')),
            NumericField::create("Gradient{$which}Point", _t(self::class . '.GradientPoint', 'Position (%)')),
        ])
            ->setName("Gradient{$which}")
            ->setDescription(_t(self::class . '.GradientColorDescription', 'Colour defaults to black.') . ' ' . $strength . ' ' . $position);
    }

    /**
     * Every colour/overlay value is read through here, so a record can take
     * its appearance from another one (carousel cards use their carousel's).
     */
    protected function appearanceSetting(string $name): mixed
    {
        return $this->getField($name);
    }

    public function getToneClass(): string
    {
        return match ($this->appearanceSetting('Tone')) {
            'light' => 'sw-tone-light',
            'dark' => 'sw-tone-dark',
            default => $this->autoTone(),
        };
    }

    public function getBackgroundHex(): ?string
    {
        return Colour::normalise($this->appearanceSetting('BackgroundColor'));
    }

    public function getBackgroundStyle(): string
    {
        $hex = $this->getBackgroundHex();

        return $hex ? "background-color:{$hex}" : '';
    }

    public function getOverlayStyle(): string
    {
        if ($this->appearanceSetting('OverlayType') === 'gradient') {
            return $this->gradientStyle();
        }

        $opacity = max(0, min(100, (int) $this->appearanceSetting('OverlayOpacity'))) / 100;
        if ($opacity <= 0) {
            return '';
        }
        $hex = Colour::normalise($this->appearanceSetting('OverlayColor')) ?? '#000000';

        return "background-color:{$hex};opacity:{$opacity}";
    }

    /** e.g. "background-image:linear-gradient(to top,#000000b3 0%,#00000000 100%)", or '' when both ends are clear */
    private function gradientStyle(): string
    {
        $start = $this->gradientEnd('Start');
        $end = $this->gradientEnd('End');
        if ($start['alpha'] <= 0 && $end['alpha'] <= 0) {
            return '';
        }
        $direction = self::GRADIENT_DIRECTIONS[$this->appearanceSetting('GradientDirection')] ?? 'to top';
        $stop = fn (array $e) => $e['hex'] . str_pad(dechex((int) round($e['alpha'] * 255)), 2, '0', STR_PAD_LEFT) . ' ' . $e['point'] . '%';

        return "background-image:linear-gradient({$direction},{$stop($start)},{$stop($end)})";
    }

    /** @return array{hex: string, alpha: float, point: int} One end of the gradient, clamped */
    private function gradientEnd(string $which): array
    {
        $clamp = fn ($value) => max(0, min(100, (int) $value));

        return [
            'hex' => Colour::normalise($this->appearanceSetting("Gradient{$which}Color")) ?? '#000000',
            'alpha' => $clamp($this->appearanceSetting("Gradient{$which}Opacity")) / 100,
            'point' => $clamp($this->appearanceSetting("Gradient{$which}Point")),
        ];
    }

    public function getHasBackgroundImage(): bool
    {
        return $this->hasMethod('BackgroundImage') && $this->BackgroundImage()->exists();
    }

    /**
     * CSS object-position for the background image, honouring
     * jonom/focuspoint when installed.
     */
    public function getBackgroundPosition(): string
    {
        $image = $this->getHasBackgroundImage() ? $this->BackgroundImage() : null;
        if ($image && $image->hasField('FocusPoint') && ($point = $image->dbObject('FocusPoint'))) {
            return $point->PercentageX() . '% ' . $point->PercentageY() . '%';
        }

        return '50% 50%';
    }

    private function autoTone(): string
    {
        if ($this->getHasBackgroundImage()) {
            // A light overlay strong enough to dominate the photo means dark text.
            // For a gradient, judge by its stronger end.
            if ($this->appearanceSetting('OverlayType') === 'gradient') {
                $start = $this->gradientEnd('Start');
                $end = $this->gradientEnd('End');
                $strongest = $start['alpha'] >= $end['alpha'] ? $start : $end;
                $overlay = $strongest['hex'];
                $opacity = (int) round($strongest['alpha'] * 100);
            } else {
                $overlay = Colour::normalise($this->appearanceSetting('OverlayColor')) ?? '#000000';
                $opacity = (int) $this->appearanceSetting('OverlayOpacity');
            }

            return ($opacity >= 50 && !Colour::isDark($overlay)) ? 'sw-tone-dark' : 'sw-tone-light';
        }

        $background = $this->getBackgroundHex();
        if ($background) {
            return Colour::isDark($background) ? 'sw-tone-light' : 'sw-tone-dark';
        }

        return '';
    }
}
