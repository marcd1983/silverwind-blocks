<?php

namespace Antlion\SilverwindBlocks\Traits;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormField;
use SilverStripe\Forms\HTMLEditor\HTMLEditorField;
use SilverStripe\Forms\ToggleCompositeField;

/**
 * Puts block fields in the same places on every block:
 *
 *   Main tab      content: title, text, items, buttons
 *                 "Layout" group: alignment, sizes, styles, columns
 *                 "Background" group: colour, image, overlay, text colour
 *                 "Slideshow" group: carousel/slider behaviour
 *                 "Slide dimensions" group: slider shape
 *   Settings tab  behaviour toggles, then heading level and spacing
 *
 * Blocks finish getCMSFields() with arrangeMainTab(), which runs after
 * extensions (e.g. elemental adding a nested block area) and puts the groups
 * last, in that order.
 */
trait ArrangesFields
{
    public const array GROUP_ORDER = ['BlockLayout', 'BlockBackground', 'BlockSlideshow', 'BlockImageSize', 'BlockSchedule'];

    /** @param FormField[] $fields */
    protected function addLayoutGroup(FieldList $list, array $fields): void
    {
        $this->addGroup($list, 'BlockLayout', _t(self::class . '.LayoutGroup', 'Layout'), $fields);
    }

    /** @param FormField[] $fields */
    protected function addBackgroundGroup(FieldList $list, array $fields): void
    {
        $this->addGroup($list, 'BlockBackground', _t(self::class . '.BackgroundGroup', 'Background'), $fields);
    }

    /** @param FormField[] $fields */
    protected function addSlideshowGroup(FieldList $list, array $fields): void
    {
        $this->addGroup($list, 'BlockSlideshow', _t(self::class . '.SlideshowGroup', 'Slideshow'), $fields);
    }

    /** @param FormField[] $fields */
    protected function addImageSizeGroup(FieldList $list, array $fields): void
    {
        $this->addGroup($list, 'BlockImageSize', _t(self::class . '.ImageSizeGroup', 'Slide dimensions'), $fields);
    }

    /**
     * Moves the groups to the end of the Main tab in a fixed order, and puts
     * the block's width (top-level blocks) and column width (blocks in a
     * Columns-layout Section) at the top of the Layout group.
     */
    protected function arrangeMainTab(FieldList $list): FieldList
    {
        foreach (['GridSpan', 'BlockWidth'] as $name) {
            $field = $list->dataFieldByName($name);
            if (!$field) {
                continue;
            }
            $list->removeByName($name);
            $layout = $list->fieldByName('Root.Main.BlockLayout');
            if ($layout) {
                $layout->getChildren()->unshift($field);
            } else {
                $this->addLayoutGroup($list, [$field]);
            }
        }

        foreach (self::GROUP_ORDER as $name) {
            if ($group = $list->fieldByName('Root.Main.' . $name)) {
                $list->removeByName($name);
                $list->addFieldToTab('Root.Main', $group);
            }
        }

        return $list;
    }

    /**
     * Behaviour settings go first on the Settings tab, above the shared
     * heading/spacing fields.
     *
     * @param FormField[] $fields
     */
    protected function addBehaviourFields(FieldList $list, array $fields): void
    {
        $list->removeByName($this->namesIn($fields));
        $before = $list->fieldByName('Root.Settings.HeadingTag') ? 'HeadingTag' : null;
        foreach ($fields as $field) {
            $list->addFieldToTab('Root.Settings', $field, $before);
        }
    }

    /**
     * The optional text shown above a block's items. Call it before adding
     * the items so it sits between the title and them.
     */
    protected function introField(FieldList $list): void
    {
        $list->removeByName('Intro');
        $list->addFieldToTab('Root.Main', HTMLEditorField::create('Intro', _t(self::class . '.Intro', 'Intro'))
            ->setDescription(_t(self::class . '.IntroDescription', 'Optional text above the items.'))
            ->setRows(4));
    }

    /**
     * Names of the given fields and of any fields nested inside them (e.g. a
     * FieldGroup's children), so their scaffolded copies can be removed.
     *
     * @param FormField[] $fields
     */
    private function namesIn(array $fields): array
    {
        return array_unique([
            ...array_map(fn (FormField $f) => $f->getName(), $fields),
            ...array_keys(FieldList::create($fields)->dataFields()),
        ]);
    }

    /** @param FormField[] $fields */
    private function addGroup(FieldList $list, string $name, string $title, array $fields): void
    {
        $list->removeByName([$name, ...$this->namesIn($fields)]);
        $list->addFieldToTab('Root.Main', ToggleCompositeField::create($name, $title, $fields)->setHeadingLevel(4));
    }
}
