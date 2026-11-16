<?php

namespace Antlion\SilverwindBlocks\Extensions;

use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\CMS\Controllers\CMSPageEditController;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Extension;

/**
 * Fixes CMSEditLink() for blocks nested inside other blocks (Section,
 * Pop-up), at any depth: Page -> Section -> Section -> Card.
 *
 * @extends Extension<BaseElement>
 */
class NestedEditLinkExtension extends Extension
{
    public function updateCMSEditLink(?string &$link): void
    {
        $owner = $this->getOwner();
        $parent = $owner->getPage();

        if ($parent instanceof BaseElement && $parent->hasMethod('getOwnedAreaRelationName')) {
            // The parent's own link is already corrected by this extension (it recurses).
            $parentItem = preg_replace('#(/item/\d+)/edit/?$#', '$1', (string) $parent->CMSEditLink());

            $link = Controller::join_links(
                $parentItem,
                'ItemEditForm/field/' . $parent->getOwnedAreaRelationName() . '/item/',
                $owner->ID,
                'edit',
            );
        } elseif ($parent instanceof SiteTree) {
            $link = Controller::join_links(
                singleton(CMSPageEditController::class)->Link('EditForm'),
                $parent->ID,
                'field/' . $owner->getAreaRelationName() . '/item/',
                $owner->ID,
                'edit',
            );
        }
    }
}
