<?php

namespace Antlion\SilverwindBlocks\Traits;

use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\TextField;
use SilverStripe\LinkField\Form\MultiLinkField;

/**
 * CMS fields for accordion and tab panels (Title, Content, Image, Links).
 */
trait PanelFields
{
    protected function panelFields(FieldList $fields, string $parentRelationField, string $folder): void
    {
        $fields->removeByName([$parentRelationField, 'Sort', 'Title', 'Content', 'Image', 'Links']);

        $fields->addFieldsToTab('Root.Main', [
            TextField::create('Title', _t(self::class . '.Title', 'Title'))->setMaxLength(255),
            HTMLEditorField::create('Content', _t(self::class . '.Content', 'Content'))->setRows(8),
            UploadField::create('Image', _t(self::class . '.Image', 'Image'))
                ->setFolderName($folder)
                ->setAllowedFileCategories('image/supported'),
            MultiLinkField::create('Links', _t(self::class . '.Links', 'Buttons')),
        ]);
    }

    public function getCMSThumbnail()
    {
        return $this->Image()->exists() ? $this->Image()->CMSThumbnail() : null;
    }
}
