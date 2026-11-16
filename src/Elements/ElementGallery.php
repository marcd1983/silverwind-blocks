<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Controllers\BlockController;
use Antlion\SilverwindBlocks\Models\GalleryImage;
use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\HasColumns;
use Antlion\SilverwindBlocks\Traits\HasOrderedPanels;
use Colymba\BulkUpload\BulkUploader;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\ORM\HasManyList;

/**
 * Masonry-style image grid with an optional full-screen lightbox.
 *
 * @method HasManyList<GalleryImage> Images()
 */
class ElementGallery extends BaseElement
{
    use ArrangesFields;
    use HasColumns;
    use HasOrderedPanels;

    private static string $table_name = 'SWB_ElementGallery';

    private static string $singular_name = 'Gallery';

    private static string $plural_name = 'Galleries';

    private static string $description = 'Image gallery with a lightbox';

    private static string $icon = 'font-icon-p-gallery';

    private static string $controller_class = BlockController::class;

    private static bool $inline_editable = false;

    private static array $db = [
        ...self::COLUMNS_DB,
        'Intro' => 'HTMLText',
        'Lightbox' => 'Boolean',
    ];

    private static array $defaults = [
        'ColumnsSmall' => 2,
        'ColumnsMedium' => 3,
        'ColumnsLarge' => 4,
        'Lightbox' => true,
    ];

    private static array $has_many = [
        'Images' => GalleryImage::class,
    ];

    private static array $owns = ['Images'];

    private static array $cascade_deletes = ['Images'];

    private static array $cascade_duplicates = ['Images'];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Gallery');
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $this->introField($fields);
            $this->orderedGrid($fields, 'Images', _t(__CLASS__ . '.Images', 'Images'));

            /** @var GridField|null $grid */
            $grid = $fields->dataFieldByName('Images');
            if ($grid && class_exists(BulkUploader::class)) {
                $uploader = new BulkUploader();
                $uploader->setUfSetup('setFolderName', 'Uploads/Blocks/Gallery');
                $grid->getConfig()->addComponent($uploader);
            }

            $this->addLayoutGroup($fields, [$this->columnsField(_t(__CLASS__ . '.Columns', 'Images per row'))]);
            $this->addBehaviourFields($fields, [
                CheckboxField::create('Lightbox', _t(__CLASS__ . '.Lightbox', 'Open images in a full-screen viewer')),
            ]);
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    /** CSS multi-column layout, so images keep their own heights (masonry) */
    public function getColumnClasses(): string
    {
        return $this->columnClasses('columns');
    }

    public function getVisibleImages(): HasManyList
    {
        return $this->Images()->filter('ImageID:GreaterThan', 0);
    }

    public function getSummary(): string
    {
        return _t(__CLASS__ . '.Summary', '{count} image(s)', ['count' => $this->Images()->count()]);
    }

    protected function provideBlockSchema(): array
    {
        $schema = parent::provideBlockSchema();
        $schema['content'] = $this->getSummary();

        return $schema;
    }
}
