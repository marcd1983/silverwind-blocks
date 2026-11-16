<?php

namespace Antlion\SilverwindBlocks\Controllers;

use DNADesign\Elemental\Controllers\ElementController;
use SilverStripe\View\Requirements;

/**
 * Controller for blocks that need front-end behaviour (tabs, modals,
 * lightbox, sliders). Loads the blocks script once per page, and the Swiper
 * stylesheet for blocks that set `uses_swiper: true`.
 *
 * Runs on every render (elemental calls doInit() when it creates the
 * controller), so it still works when a block's template output is cached.
 */
class BlockController extends ElementController
{
    public const string SCRIPT = 'antlion/silverwind-blocks: client/dist/silverwind-blocks.js';

    public const string SWIPER_CSS = 'antlion/silverwind-blocks: client/dist/silverwind-blocks.css';

    protected function init(): void
    {
        parent::init();

        Requirements::javascript(self::SCRIPT, ['type' => 'module']);

        if ($this->getElement()->config()->get('uses_swiper')) {
            Requirements::css(self::SWIPER_CSS);
        }
    }
}
