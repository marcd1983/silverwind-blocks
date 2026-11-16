<?php

namespace Antlion\SilverwindBlocks\Extensions;

use Antlion\SilverwindBlocks\Elements\ElementAccordion;
use DNADesign\Elemental\Models\BaseElement;
use DNADesign\Elemental\Models\ElementalArea;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Core\Extension;

/**
 * Adds a schema.org FAQPage node for Accordion blocks that have FAQ markup
 * turned on, including accordions nested in Sections, Columns, etc.
 *
 * Hooks into `updateSchemaGraph(array &$graph, string &$pageURL)`, which the
 * site's structured-data code calls on the page.
 *
 * @extends Extension<SiteTree>
 */
class FAQSchemaExtension extends Extension
{
    private const int MAX_DEPTH = 5;

    public function updateSchemaGraph(array &$graph, string &$pageURL): void
    {
        $questions = [];
        foreach ($this->accordions() as $accordion) {
            array_push($questions, ...$accordion->getFAQQuestions($pageURL));
        }

        if ($questions) {
            $graph[] = [
                '@type' => 'FAQPage',
                '@id' => rtrim($pageURL, '/') . '/#faq',
                'mainEntity' => $questions,
            ];
        }
    }

    /**
     * @return ElementAccordion[]
     */
    public function accordions(): array
    {
        $owner = $this->getOwner();
        $found = [];

        foreach ($owner->hasOne() as $relation => $class) {
            if (is_a($class, ElementalArea::class, true)) {
                $this->collect($owner->$relation(), $found, 0);
            }
        }

        return $found;
    }

    private function collect(?ElementalArea $area, array &$found, int $depth): void
    {
        if (!$area || !$area->exists() || $depth > self::MAX_DEPTH) {
            return;
        }

        /** @var BaseElement $element */
        foreach ($area->Elements() as $element) {
            if ($element instanceof ElementAccordion) {
                $found[] = $element;
            } elseif ($element->hasMethod('getOwnedAreaRelationName')) {
                $relation = $element->getOwnedAreaRelationName();
                $this->collect($element->$relation(), $found, $depth + 1);
            }
        }
    }
}
