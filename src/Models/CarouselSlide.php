<?php

namespace Antlion\SilverwindBlocks\Models;

use Antlion\SilverwindBlocks\Elements\ElementCarousel;
use Antlion\SilverwindBlocks\Traits\CardDisplay;
use Antlion\SilverwindBlocks\Traits\HasAppearance;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\LinkField\Form\MultiLinkField;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * A card in a Carousel. Rendered with the Carousel's card style.
 *
 * @method ElementCarousel Carousel()
 */
class CarouselSlide extends DataObject
{
    use CardDisplay;
    use HasAppearance;

    private static string $table_name = 'SWB_CarouselSlide';

    private static string $singular_name = 'Carousel card';

    private static string $plural_name = 'Carousel cards';

    private static array $db = [
        'Sort' => 'Int',
        'Title' => 'Varchar(255)',
        'Content' => 'HTMLText',
    ];

    private static array $has_one = [
        'Carousel' => ElementCarousel::class,
        'Image' => Image::class,
    ];

    private static array $has_many = [
        'Links' => Link::class . '.Owner',
    ];

    private static array $owns = ['Image', 'Links'];

    private static array $cascade_deletes = ['Links'];

    private static array $cascade_duplicates = ['Links'];

    private static array $extensions = [Versioned::class];

    private static string $default_sort = 'Sort';

    private static array $summary_fields = [
        'Image.CMSThumbnail' => 'Image',
        'Title' => 'Title',
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName(['CarouselID', 'Sort', 'Title', 'Content', 'Image', 'Links']);
            $fields->addFieldsToTab('Root.Main', [
                TextField::create('Title', _t(__CLASS__ . '.Title', 'Title')),
                UploadField::create('Image', _t(__CLASS__ . '.Image', 'Image'))
                    ->setFolderName('Uploads/Blocks/Carousel')
                    ->setAllowedFileCategories('image/supported'),
                HTMLEditorField::create('Content', _t(__CLASS__ . '.Content', 'Text'))->setRows(5),
                MultiLinkField::create('Links', _t(__CLASS__ . '.Links', 'Buttons')),
            ]);
        });

        return parent::getCMSFields();
    }

    /** Card.ss contract */
    public function getCardTitle(): ?string
    {
        return $this->Title;
    }

    public function getCardHeading(): string
    {
        return 'h3';
    }

    /** CardDisplay: alignment, image shape and fit come from the carousel */
    protected function cardDisplaySource(): ?DataObject
    {
        $carousel = $this->Carousel();

        return $carousel && $carousel->exists() ? $carousel : null;
    }

    public function getCardStyle(): string
    {
        return $this->cardDisplaySource()?->getCardStyle() ?? 'vertical';
    }

    /** HasAppearance: colours come from the carousel, applied to every card */
    protected function appearanceSetting(string $name): mixed
    {
        return $this->cardDisplaySource()?->getField($name);
    }

    /** Overlay cards treat their own image as their background */
    public function getHasBackgroundImage(): bool
    {
        return $this->cardHasBackgroundImage();
    }

    public function getBackgroundPosition(): string
    {
        return $this->cardImagePosition();
    }
}
