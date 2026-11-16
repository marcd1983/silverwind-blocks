<?php

namespace Antlion\SilverwindBlocks\Traits;

use Antlion\SilverwindBlocks\Style;

/**
 * Template accessors for the shared style fields. A block only needs to
 * declare the fields it uses; the rest return ''.
 *
 *   HorizontalAlignment  $TextAlignClass, $BoxPositionClass
 *   VerticalAlignment    $VerticalAlignClass
 *   ButtonAlignment      $ButtonJustifyClass
 *   ButtonSize           $ButtonSizeClass
 */
trait StyleAccessors
{
    public function getTextAlignClass(): string
    {
        return Style::textAlign($this->getField('HorizontalAlignment'));
    }

    public function getBoxPositionClass(): string
    {
        return Style::boxPosition($this->getField('HorizontalAlignment'));
    }

    /** Button rows inside an aligned text box follow the text alignment */
    public function getContentJustifyClass(): string
    {
        return Style::justify($this->getField('HorizontalAlignment'));
    }

    public function getVerticalAlignClass(): string
    {
        return Style::items($this->getField('VerticalAlignment'));
    }

    public function getButtonJustifyClass(): string
    {
        return Style::justify($this->getField('ButtonAlignment'));
    }

    public function getButtonSizeClass(): string
    {
        return Style::buttonSize($this->getField('ButtonSize'));
    }
}
