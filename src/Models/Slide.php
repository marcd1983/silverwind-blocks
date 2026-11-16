<?php

namespace Antlion\SilverwindBlocks\Models;

use Antlion\SilverwindBlocks\Elements\ElementSlider;
use Antlion\SilverwindBlocks\Style;
use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\HasAppearance;
use Antlion\SilverwindBlocks\Traits\StyleAccessors;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Validation\ValidationResult;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldGroup;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\NumericField;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\ToggleCompositeField;
use SilverStripe\LinkField\Form\LinkField;
use SilverStripe\LinkField\Form\MultiLinkField;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;
use UncleCheese\DisplayLogic\Forms\Wrapper;

/**
 * One slide of a full-width slider: an image (with optional phone image) or
 * a muted looping video, with optional title, text and buttons, or a cover
 * link that makes the whole slide clickable. Can be scheduled.
 *
 * Uses the same Title / Content / Links fields as every other item, and the
 * same Background and alignment fields as the Hero.
 *
 * Belongs to either a Slider block or a page (hero slider).
 */
class Slide extends DataObject
{
    use ArrangesFields;
    use HasAppearance;
    use StyleAccessors;

    private static string $table_name = 'SWB_Slide';

    private static string $singular_name = 'Slide';

    private static string $plural_name = 'Slides';

    private static array $db = [
        ...self::BACKGROUND_DB,
        'Sort' => 'Int',
        'Title' => 'Varchar(255)',
        'Content' => 'HTMLText',
        'MediaType' => "Enum('image,video','image')",
        'VideoStart' => 'Int',
        'VideoEnd' => 'Int',
        'HorizontalAlignment' => Style::DB_HORIZONTAL,
        'VerticalAlignment' => Style::DB_VERTICAL,
        'ContentPanel' => 'Boolean',
        'StartDate' => 'Date',
        'EndDate' => 'Date',
    ];

    private static array $has_one = [
        ...self::BACKGROUND_IMAGES,
        'VideoMP4' => File::class,
        'VideoWebM' => File::class,
        'CoverLink' => Link::class,
        'Slider' => ElementSlider::class,
        'Page' => SiteTree::class,
    ];

    private static array $has_many = [
        'Links' => Link::class . '.Owner',
    ];

    private static array $owns = ['BackgroundImage', 'MobileBackgroundImage', 'VideoMP4', 'VideoWebM', 'CoverLink', 'Links'];

    private static array $cascade_deletes = ['CoverLink', 'Links'];

    private static array $cascade_duplicates = ['CoverLink', 'Links'];

    private static array $extensions = [Versioned::class];

    private static string $default_sort = 'Sort';

    private static array $defaults = [
        ...self::GRADIENT_DEFAULTS,
        'OverlayOpacity' => 30,
    ];

    private static array $summary_fields = [
        'CMSThumb' => 'Preview',
        'Title' => 'Title',
        'MediaType' => 'Type',
        'StartDate.Nice' => 'Starts',
        'EndDate.Nice' => 'Ends',
    ];

    public function getCMSThumb()
    {
        $image = $this->BackgroundImage();

        return $image->exists() ? $image->CMSThumbnail() : ($this->MediaType === 'video' ? '(video)' : '');
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName([
                ...array_keys(static::config()->get('db')),
                ...self::BACKGROUND_FIELD_NAMES,
                'VideoMP4', 'VideoWebM', 'CoverLink', 'CoverLinkID', 'SliderID', 'PageID', 'Links',
            ]);

            $mp4 = UploadField::create('VideoMP4', _t(__CLASS__ . '.VideoMP4', 'Video (MP4)'))->setFolderName('Uploads/Blocks/Slides');
            $mp4->getValidator()->setAllowedExtensions(['mp4']);
            $webm = UploadField::create('VideoWebM', _t(__CLASS__ . '.VideoWebM', 'Video (WebM, optional)'))->setFolderName('Uploads/Blocks/Slides');
            $webm->getValidator()->setAllowedExtensions(['webm']);

            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Title', _t(__CLASS__ . '.Title', 'Title'))
                    ->setDescription(_t(__CLASS__ . '.TitleDescription', 'Shown as the slide\'s heading. Also names the slide in the CMS.')),
                HTMLEditorField::create('Content', _t(__CLASS__ . '.Content', 'Text'))->setRows(4),
                MultiLinkField::create('Links', _t(__CLASS__ . '.Links', 'Buttons')),
                LinkField::create('CoverLink', _t(__CLASS__ . '.CoverLink', 'Make the whole slide a link'))
                    ->setDescription(_t(__CLASS__ . '.CoverLinkDescription', 'Use this or buttons, not both.')),
            ]);

            $this->addLayoutGroup($fields, [
                DropdownField::create('HorizontalAlignment', _t(__CLASS__ . '.HorizontalAlignment', 'Horizontal alignment'), Style::horizontalOptions()),
                DropdownField::create('VerticalAlignment', _t(__CLASS__ . '.VerticalAlignment', 'Vertical alignment'), Style::verticalOptions()),
                CheckboxField::create('ContentPanel', _t(__CLASS__ . '.ContentPanel', 'Put the text on a panel')),
            ]);

            // Background type first, then what that type needs: the video
            // files and play section, or the phone crop. The main image is
            // used by both: the slide image, or the video's poster.
            $background = [];
            foreach ($this->backgroundFields('Uploads/Blocks/Slides') as $field) {
                if ($field->getName() === 'BackgroundImage') {
                    $background[] = $field;
                    $background[] = Wrapper::create(LiteralField::create('PosterNote', '<p class="form__field-description">'
                        . _t(__CLASS__ . '.PosterNote', 'For a video this is the poster: shown while the video loads, and instead of it for visitors who prefer reduced motion.')
                        . '</p>'))->setName('PosterNoteWrapper')->displayIf('MediaType')->isEqualTo('video')->end();
                } elseif ($field->getName() === 'MobileBackgroundImage') {
                    // Video slides don't use a phone crop
                    $background[] = $field->displayIf('MediaType')->isEqualTo('image')->end();
                } else {
                    $background[] = $field;
                }
            }

            $this->addBackgroundGroup($fields, [
                DropdownField::create('MediaType', _t(__CLASS__ . '.MediaType', 'Background type'), [
                    'image' => _t(__CLASS__ . '.Image', 'Image'),
                    'video' => _t(__CLASS__ . '.Video', 'Video'),
                ]),
                Wrapper::create([
                    $mp4,
                    $webm,
                    FieldGroup::create(_t(__CLASS__ . '.VideoRange', 'Play section (seconds)'), [
                        NumericField::create('VideoStart', _t(__CLASS__ . '.VideoStart', 'From')),
                        NumericField::create('VideoEnd', _t(__CLASS__ . '.VideoEnd', 'To (0 = end)')),
                    ])->setName('VideoRange'),
                ])->setName('VideoFields')->displayIf('MediaType')->isEqualTo('video')->end(),
                ...$background,
            ]);

            $fields->addFieldToTab('Root.Main', ToggleCompositeField::create('BlockSchedule', _t(__CLASS__ . '.Schedule', 'Schedule'), [
                DateField::create('StartDate', _t(__CLASS__ . '.StartDate', 'Show from')),
                DateField::create('EndDate', _t(__CLASS__ . '.EndDate', 'Show until (inclusive)')),
            ])->setHeadingLevel(4));
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    public function populateDefaults(): static
    {
        parent::populateDefaults();
        Style::applyEnumDefaults($this);

        return $this;
    }

    public function getTitle(): string
    {
        return (string) ($this->getField('Title') ?: _t(__CLASS__ . '.Untitled', 'Slide'));
    }

    public function validate(): ValidationResult
    {
        $result = parent::validate();

        if ($this->CoverLinkID && $this->Links()->exists()) {
            $result->addError(_t(__CLASS__ . '.CoverOrButtons', 'Use either a cover link or buttons, not both.'));
        }
        if ($this->MediaType === 'video' && !$this->VideoMP4ID && !$this->VideoWebMID) {
            $result->addFieldError('VideoMP4', _t(__CLASS__ . '.VideoRequired', 'Upload an MP4 or WebM video, or switch the background to Image.'));
        }
        if ($this->VideoEnd && $this->VideoEnd < $this->VideoStart) {
            $result->addFieldError('VideoEnd', _t(__CLASS__ . '.VideoRangeError', 'The end time must be after the start.'));
        }
        if ($this->StartDate && $this->EndDate && $this->EndDate < $this->StartDate) {
            $result->addFieldError('EndDate', _t(__CLASS__ . '.DateRange', 'The end date must be on or after the start date.'));
        }

        return $result;
    }

    public function onBeforeWrite(): void
    {
        parent::onBeforeWrite();
        $this->OverlayOpacity = max(0, min(100, (int) $this->OverlayOpacity));
    }

    /** Slides always sit on an image or video, so auto tone means light text */
    public function getHasBackgroundImage(): bool
    {
        return true;
    }

    public function getIsVideo(): bool
    {
        return $this->MediaType === 'video';
    }

    /** The heading only, when the slide has a real title (getTitle() falls back to "Slide") */
    public function getHeading(): ?string
    {
        return $this->getField('Title') ?: null;
    }

    /** Cropped to the slider's desktop shape */
    public function getDesktopImage(): ?AssetContainer
    {
        [$w, $h] = $this->dimensions('Desktop', 1920, 700);

        return $this->BackgroundImage()->exists() ? $this->BackgroundImage()->FocusFill($w, $h) : null;
    }

    /** Cropped to the slider's phone shape; falls back to the main image */
    public function getMobileImage(): ?AssetContainer
    {
        $source = $this->MobileBackgroundImage()->exists() ? $this->MobileBackgroundImage() : $this->BackgroundImage();
        [$w, $h] = $this->dimensions('Mobile', 960, 1200);

        return $source->exists() ? $source->FocusFill($w, $h) : null;
    }

    public function getAltText(): string
    {
        return (string) ($this->getField('Title') ?: $this->BackgroundImage()->Title);
    }

    private function dimensions(string $prefix, int $defaultW, int $defaultH): array
    {
        $owner = $this->SliderID ? $this->Slider() : ($this->PageID ? $this->Page() : null);

        return [
            (int) ($owner?->{$prefix . 'Width'} ?: $defaultW),
            (int) ($owner?->{$prefix . 'Height'} ?: $defaultH),
        ];
    }
}
