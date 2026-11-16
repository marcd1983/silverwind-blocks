<?php

namespace Antlion\SilverwindBlocks\Elements;

use Antlion\SilverwindBlocks\Models\AccordionPanel;
use Antlion\SilverwindBlocks\Traits\ArrangesFields;
use Antlion\SilverwindBlocks\Traits\HasOrderedPanels;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\HasManyList;

/**
 * Expand/collapse items built on native <details>, so they work without
 * JavaScript and are searchable with the browser's find-in-page.
 *
 * @method HasManyList<AccordionPanel> Panels()
 */
class ElementAccordion extends BaseElement
{
    use ArrangesFields;
    use HasOrderedPanels;

    private static string $table_name = 'SWB_ElementAccordion';

    private static string $singular_name = 'Accordion';

    private static string $plural_name = 'Accordions';

    private static string $description = 'Expandable items, e.g. FAQs';

    private static string $icon = 'font-icon-block-accordion';

    private static bool $inline_editable = false;

    private static array $db = [
        'Intro' => 'HTMLText',
        'OpenFirst' => 'Boolean',
        'AllowMultiple' => 'Boolean',
        'EnableFAQSchema' => 'Boolean',
    ];

    private static array $has_many = [
        'Panels' => AccordionPanel::class,
    ];

    private static array $owns = ['Panels'];

    private static array $cascade_deletes = ['Panels'];

    private static array $cascade_duplicates = ['Panels'];

    public function getType(): string
    {
        return _t(__CLASS__ . '.BlockType', 'Accordion');
    }

    public function getCMSFields(): FieldList
    {
        $this->beforeUpdateCMSFields(function (FieldList $fields) {
            $this->introField($fields);
            $this->orderedGrid($fields, 'Panels', _t(__CLASS__ . '.Items', 'Items'));
            $this->addBehaviourFields($fields, [
                CheckboxField::create('OpenFirst', _t(__CLASS__ . '.OpenFirst', 'Open the first item on load')),
                CheckboxField::create('AllowMultiple', _t(__CLASS__ . '.AllowMultiple', 'Allow several items open at once')),
                CheckboxField::create('EnableFAQSchema', _t(__CLASS__ . '.EnableFAQSchema', 'Mark up as FAQs for search engines'))
                    ->setDescription(_t(__CLASS__ . '.EnableFAQSchemaDescription', 'Adds FAQPage structured data. Use only when each item is a question and its answer.')),
            ]);
        });

        return $this->arrangeMainTab(parent::getCMSFields());
    }

    /**
     * schema.org Question nodes for FAQ markup.
     */
    public function getFAQQuestions(string $pageURL): array
    {
        if (!$this->EnableFAQSchema) {
            return [];
        }

        $questions = [];
        foreach ($this->Panels() as $panel) {
            $question = trim((string) $panel->Title);
            $answer = trim($panel->dbObject('Content')->Plain());
            if ($question === '' || $answer === '') {
                continue;
            }
            $questions[] = [
                '@type' => 'Question',
                '@id' => rtrim($pageURL, '/') . '/#' . $panel->getAnchor(),
                'name' => $question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer],
            ];
        }

        return $questions;
    }

    public function getSummary(): string
    {
        return _t(__CLASS__ . '.Summary', '{count} item(s)', ['count' => $this->Panels()->count()]);
    }

    protected function provideBlockSchema(): array
    {
        $schema = parent::provideBlockSchema();
        $schema['content'] = $this->getSummary();

        return $schema;
    }
}
