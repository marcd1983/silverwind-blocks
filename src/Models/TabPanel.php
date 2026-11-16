<?php

namespace Antlion\SilverwindBlocks\Models;

use Antlion\SilverwindBlocks\Elements\ElementTabs;
use Antlion\SilverwindBlocks\Traits\PanelFields;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\FieldList;
use SilverStripe\LinkField\Models\Link;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

/**
 * @method ElementTabs Tabs()
 */
class TabPanel extends DataObject
{
    use PanelFields;

    private static string $table_name = 'SWB_TabPanel';

    private static string $singular_name = 'Tab';

    private static string $plural_name = 'Tabs';

    private static array $db = [
        'Sort' => 'Int',
        'Title' => 'Varchar(255)',
        'Content' => 'HTMLText',
    ];

    private static array $has_one = [
        'Tabs' => ElementTabs::class,
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
        $this->beforeUpdateCMSFields(fn (FieldList $fields) => $this->panelFields($fields, 'TabsID', 'Uploads/Blocks/Tabs'));

        return parent::getCMSFields();
    }

    /** URL fragment that opens this tab, e.g. /page#tab-12 */
    public function getAnchor(): string
    {
        return 'tab-' . $this->ID;
    }
}
