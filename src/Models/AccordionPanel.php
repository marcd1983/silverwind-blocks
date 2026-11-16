<?php

namespace Antlion\SilverwindBlocks\Models;

use Antlion\SilverwindBlocks\Elements\ElementAccordion;
use Antlion\SilverwindBlocks\Traits\PanelFields;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * @method ElementAccordion Accordion()
 */
class AccordionPanel extends DataObject
{
    use PanelFields;

    private static string $table_name = 'SWB_AccordionPanel';

    private static string $singular_name = 'Accordion item';

    private static string $plural_name = 'Accordion items';

    private static array $db = [
        'Sort' => 'Int',
        'Title' => 'Varchar(255)',
        'Content' => 'HTMLText',
    ];

    private static array $has_one = [
        'Accordion' => ElementAccordion::class,
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
        'Title' => 'Title',
        'Content.Summary' => 'Content',
    ];

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(fn (FieldList $fields) => $this->panelFields($fields, 'AccordionID', 'Uploads/Blocks/Accordion'));

        return parent::getCMSFields();
    }

    /** Anchor id, also used as the FAQ question @id */
    public function getAnchor(): string
    {
        return 'q-' . $this->AccordionID . '-' . $this->ID;
    }
}
