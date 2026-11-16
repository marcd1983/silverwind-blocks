<?php

namespace Antlion\SilverwindBlocks\Models;

use Antlion\SilverwindBlocks\Elements\ElementGallery;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * @method ElementGallery Gallery()
 * @method Image Image()
 */
class GalleryImage extends DataObject
{
    private static string $table_name = 'SWB_GalleryImage';

    private static string $singular_name = 'Gallery image';

    private static string $plural_name = 'Gallery images';

    private static array $db = [
        'Sort' => 'Int',
        'AltText' => 'Varchar(255)',
        'Caption' => 'Varchar(500)',
    ];

    private static array $has_one = [
        'Gallery' => ElementGallery::class,
        'Image' => Image::class,
    ];

    private static array $owns = ['Image'];

    private static array $extensions = [Versioned::class];

    private static string $default_sort = 'Sort';

    private static array $summary_fields = [
        'Image.CMSThumbnail' => 'Image',
        'AltText' => 'Description',
        'Caption' => 'Caption',
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $fields->removeByName(['GalleryID', 'Sort', 'Image', 'AltText', 'Caption']);
            $fields->addFieldsToTab('Root.Main', [
                UploadField::create('Image', _t(__CLASS__ . '.Image', 'Image'))
                    ->setFolderName('Uploads/Blocks/Gallery')
                    ->setAllowedFileCategories('image/supported'),
                TextField::create('AltText', _t(__CLASS__ . '.AltText', 'Description (alt text)'))
                    ->setDescription(_t(__CLASS__ . '.AltTextDescription', 'What the image shows, for people who can\'t see it. Defaults to the file title.')),
                TextField::create('Caption', _t(__CLASS__ . '.Caption', 'Caption'))
                    ->setDescription(_t(__CLASS__ . '.CaptionDescription', 'Optional, shown in the enlarged view.')),
            ]);
        });

        return parent::getCMSFields();
    }

    public function getTitle(): string
    {
        return (string) ($this->AltText ?: $this->Image()->Title ?: _t(__CLASS__ . '.Untitled', 'Image'));
    }

    public function getAltTextNice(): string
    {
        return (string) ($this->AltText ?: $this->Image()->Title);
    }
}
