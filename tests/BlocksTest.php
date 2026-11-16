<?php

namespace Antlion\SilverwindBlocks\Tests;

use Antlion\SilverwindBlocks\Elements\ElementAccordion;
use Antlion\SilverwindBlocks\Elements\ElementCard;
use Antlion\SilverwindBlocks\Elements\ElementCarousel;
use Antlion\SilverwindBlocks\Elements\ElementGallery;
use Antlion\SilverwindBlocks\Elements\ElementHero;
use Antlion\SilverwindBlocks\Elements\ElementSection;
use Antlion\SilverwindBlocks\Elements\ElementSlider;
use Antlion\SilverwindBlocks\Extensions\FAQSchemaExtension;
use Antlion\SilverwindBlocks\Models\AccordionPanel;
use Antlion\SilverwindBlocks\Models\CarouselSlide;
use Antlion\SilverwindBlocks\Models\Slide;
use Antlion\SilverwindBlocks\Style;
use DNADesign\Elemental\Models\BaseElement;
use SilverStripe\Core\ClassInfo;
use SilverStripe\Forms\ToggleCompositeField;
use DNADesign\Elemental\Extensions\ElementalPageExtension;
use DNADesign\Elemental\Models\ElementalArea;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\LinkField\Models\ExternalLink;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Versioned\Versioned;

class BlocksTest extends SapphireTest
{
    protected $usesDatabase = true;

    protected static $required_extensions = [
        SiteTree::class => [ElementalPageExtension::class],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        // Elemental only creates nested areas when writing in the draft stage
        Versioned::set_stage(Versioned::DRAFT);
    }

    public function testHeroToneFollowsBackground(): void
    {
        $this->assertSame('sw-tone-light', ElementHero::create(['BackgroundColor' => '#0b1f33'])->getToneClass());
        $this->assertSame('sw-tone-dark', ElementHero::create(['BackgroundColor' => '#fef3c7'])->getToneClass());
        $this->assertSame('', ElementHero::create()->getToneClass(), 'No background: inherit the page text colour');
        $this->assertSame('sw-tone-dark', ElementHero::create(['BackgroundColor' => '#0b1f33', 'Tone' => 'dark'])->getToneClass(), 'Explicit tone wins');
    }

    public function testInvalidColoursNeverReachInlineStyles(): void
    {
        $hero = ElementHero::create([
            'BackgroundColor' => 'red;background:url(x)',
            'OverlayColor' => '"><script>',
            'OverlayOpacity' => 250,
        ]);

        $this->assertSame('', $hero->getBackgroundStyle());
        $this->assertSame('background-color:#000000;opacity:1', $hero->getOverlayStyle());
        $this->assertSame('', ElementHero::create(['OverlayOpacity' => 0])->getOverlayStyle());
    }

    public function testRenderedTitleUsesHeadingLevelAndEscapes(): void
    {
        $card = ElementCard::create(['Title' => 'Fish & <Chips>', 'ShowTitle' => true]);
        $this->assertSame('h3', $card->getHeadingLevel(), 'Cards default to h3 via config');

        $html = (string) $card->RenderedTitle('text-xl');
        $this->assertSame('<h3 class="text-xl">Fish &amp; &lt;Chips&gt;</h3>', $html);

        $this->assertNull(ElementCard::create(['Title' => 'Hidden', 'ShowTitle' => false])->RenderedTitle());
    }

    public function testSpacingClasses(): void
    {
        $this->assertSame('mt-section mb-0', ElementCard::create(['SpacingTop' => 'lg', 'SpacingBottom' => 'none'])->getSpacingClasses());
        $this->assertSame('', ElementCard::create(['SpacingTop' => 'bogus'])->getSpacingClasses(), 'Unsaved, not on a page: automatic spacing does nothing');
    }

    public function testSectionLayouts(): void
    {
        $this->assertSame('flex flex-col gap-8', ElementSection::create()->getLayoutClasses(), 'Stacked by default');
        $this->assertSame(
            'grid grid-cols-12 gap-4 items-start',
            ElementSection::create(['Layout' => 'columns', 'Gap' => 'sm', 'VerticalAlignment' => 'top'])->getLayoutClasses(),
        );
        $this->assertSame(
            'grid gap-8 grid-cols-2 md:grid-cols-2 lg:grid-cols-4 items-stretch',
            ElementSection::create(['Layout' => 'grid', 'ColumnsSmall' => 2, 'ColumnsMedium' => 9, 'ColumnsLarge' => 4])->getLayoutClasses(),
            'Out-of-range column counts fall back',
        );
    }

    public function testSectionPaddingIsAutomaticByDefault(): void
    {
        $this->assertSame('', ElementSection::create()->getPaddingClass(), 'No background: no padding');
        $this->assertSame('py-section', ElementSection::create(['BackgroundColor' => '#0b1f33'])->getPaddingClass());
        $this->assertSame('py-[calc(var(--spacing-section)*0.375)]', ElementSection::create(['BackgroundColor' => '#0b1f33', 'PaddingY' => 'sm'])->getPaddingClass());
        $this->assertSame('', ElementSection::create(['PaddingY' => 'none', 'BackgroundColor' => '#000'])->getPaddingClass());
    }

    public function testColumnWidthOnlyAppliesInColumnsLayout(): void
    {
        $columns = ElementSection::create(['Layout' => 'columns']);
        $columns->write();
        $card = ElementCard::create(['GridSpan' => '6', 'ParentID' => $columns->Elements()->ID]);
        $card->write();

        $this->assertSame('md:col-span-6', $card->getGridSpanClass());
        $this->assertNotNull($card->getCMSFields()->dataFieldByName('GridSpan'));

        $stacked = ElementSection::create(['Layout' => 'stacked']);
        $stacked->write();
        $inStacked = ElementCard::create(['ParentID' => $stacked->Elements()->ID]);
        $inStacked->write();
        $this->assertNull($inStacked->getCMSFields()->dataFieldByName('GridSpan'), 'Stacked sections have no column widths');

        $this->assertNull(ElementCard::create()->getCMSFields()->dataFieldByName('GridSpan'));
        $this->assertSame('md:col-span-12', ElementCard::create(['GridSpan' => '13'])->getGridSpanClass());
    }

    public function testButtonClassSanitisesExtraClasses(): void
    {
        $link = ExternalLink::create(['ButtonStyle' => 'outline-primary', 'ExtraClass' => 'w-full" onclick="x()']);

        $this->assertSame('btn btn-outline btn-primary w-full onclickx', $link->getButtonClass());
        $this->assertSame('btn', ExternalLink::create(['ButtonStyle' => 'nope'])->getButtonClass());
    }

    public function testCarouselDisablesLoopWithTooFewCards(): void
    {
        $carousel = ElementCarousel::create(['Loop' => true, 'PerViewLarge' => 3, 'Autoplay' => true]);
        $carousel->write();
        foreach (range(1, 3) as $i) {
            CarouselSlide::create(['CarouselID' => $carousel->ID, 'Title' => "Card $i"])->write();
        }

        $options = $carousel->getSwiperOptions();
        $this->assertFalse($options['loop'], '3 cards can\'t loop when 3 are visible');
        $this->assertSame(6000, $options['autoplay']['delay'], 'Shared Swiper default');
        $this->assertSame(24, $options['spaceBetween'], 'Gap uses the shared spacing scale (md)');
        $this->assertSame(['slidesPerView' => 3.0], $options['breakpoints'][1024]);

        CarouselSlide::create(['CarouselID' => $carousel->ID, 'Title' => 'Card 4'])->write();
        $this->assertTrue($carousel->getSwiperOptions()['loop']);
    }

    public function testSliderOnlyShowsScheduledSlidesForToday(): void
    {
        DBDatetime::set_mock_now('2026-06-15 12:00:00');
        $slider = ElementSlider::create();
        $slider->write();
        $make = fn (string $name, ?string $start, ?string $end) => Slide::create([
            'SliderID' => $slider->ID, 'Title' => $name, 'StartDate' => $start, 'EndDate' => $end,
        ])->write();
        $make('always', null, null);
        $make('started', '2026-06-01', null);
        $make('ends today', null, '2026-06-15');
        $make('future', '2026-07-01', null);
        $make('expired', null, '2026-06-14');

        $this->assertEqualsCanonicalizing(['always', 'started', 'ends today'], $slider->getActiveSlides()->column('Title'));
        $this->assertTrue($slider->getSwiperOptions()['loop']);
        $this->assertSame('1920 / 700', $slider->getDesktopRatio());
        DBDatetime::clear_mock_now();
    }

    public function testSlideValidation(): void
    {
        $result = Slide::create(['MediaType' => 'video', 'VideoStart' => 10, 'VideoEnd' => 5])->validate();

        $this->assertFalse($result->isValid());
        $fields = array_column($result->getMessages(), 'fieldName');
        $this->assertContains('VideoMP4', $fields);
        $this->assertContains('VideoEnd', $fields);
    }

    public function testFAQSchemaFindsNestedAccordions(): void
    {
        $page = SiteTree::create(['Title' => 'FAQ page']);
        $page->write();
        $section = ElementSection::create(['ParentID' => $page->ElementalArea()->ID]);
        $section->write();
        $faq = ElementAccordion::create(['ParentID' => $section->Elements()->ID, 'EnableFAQSchema' => true]);
        $faq->write();
        AccordionPanel::create(['AccordionID' => $faq->ID, 'Title' => 'Why?', 'Content' => '<p>Because.</p>'])->write();
        AccordionPanel::create(['AccordionID' => $faq->ID, 'Title' => 'Empty answer', 'Content' => ''])->write();
        $off = ElementAccordion::create(['ParentID' => $page->ElementalArea()->ID, 'EnableFAQSchema' => false]);
        $off->write();
        AccordionPanel::create(['AccordionID' => $off->ID, 'Title' => 'Not FAQ', 'Content' => '<p>No.</p>'])->write();

        $graph = [];
        $url = 'https://example.com/faq/';
        $page->extend('updateSchemaGraph', $graph, $url);

        $this->assertCount(1, $graph);
        $this->assertSame('FAQPage', $graph[0]['@type']);
        $this->assertCount(1, $graph[0]['mainEntity'], 'Empty answers and disabled accordions are skipped');
        $this->assertSame('Why?', $graph[0]['mainEntity'][0]['name']);
        $this->assertSame('Because.', $graph[0]['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function testModalTriggerLabelFallsBackToTitle(): void
    {
        $card = ElementCard::create(['Title' => 'Terms']);
        $card->write();

        $this->assertSame('modal-' . $card->ID, $card->getModalIdentifier());
        $this->assertSame('Terms', $card->getModalTriggerLabel());
        $this->assertSame('btn btn-primary', $card->getModalTriggerClass());
    }

    public function testSharedAlignmentFieldsMapTheSameWayEverywhere(): void
    {
        $hero = ElementHero::create(['HorizontalAlignment' => 'right', 'VerticalAlignment' => 'bottom']);
        $slide = Slide::create(['HorizontalAlignment' => 'right', 'VerticalAlignment' => 'bottom']);
        $card = ElementCard::create(['HorizontalAlignment' => 'right']);

        foreach ([$hero, $slide] as $item) {
            $this->assertSame('text-right', $item->getTextAlignClass());
            $this->assertSame('ml-auto', $item->getBoxPositionClass());
            $this->assertSame('items-end', $item->getVerticalAlignClass());
        }
        $this->assertSame('justify-end', $card->getContentJustifyClass());
        $this->assertSame('btn-lg', ElementHero::create()->getButtonSizeClass(), 'Hero buttons default to large');
        $this->assertSame('', ElementCard::create()->getButtonSizeClass(), 'Blocks without the field get no class');
    }

    public function testNewBlocksStartWithTheirEnumDefaults(): void
    {
        $hero = ElementHero::create();
        $this->assertSame('tall', $hero->Height);
        $this->assertSame('middle', $hero->VerticalAlignment);
        $this->assertSame('lg', $hero->ButtonSize);
        $this->assertSame('auto', $hero->SpacingTop);
        $this->assertSame('middle', Slide::create()->VerticalAlignment);
        $this->assertSame('stretch', ElementSection::create()->VerticalAlignment);
        $this->assertSame('auto', ElementSection::create()->PaddingY);
        $this->assertSame('center', ElementHero::create(['HorizontalAlignment' => 'center'])->HorizontalAlignment, 'Given values are kept');
    }

    public function testSlideToneDefaultsToLightOnItsImage(): void
    {
        $this->assertSame('sw-tone-light', Slide::create()->getToneClass());
        $this->assertSame('sw-tone-dark', Slide::create(['Tone' => 'dark'])->getToneClass());
    }

    public function testSpacingScaleIsSharedAndBoundsChecked(): void
    {
        $this->assertSame('gap-12', Style::gap('lg'));
        $this->assertSame('gap-8', Style::gap('nonsense'));
        $this->assertSame('py-[calc(var(--spacing-section)*1.5)]', ElementSection::create(['PaddingY' => 'xl'])->getPaddingClass(), 'Padding scales with the theme\'s Section spacing');
        $this->assertSame(48, Style::pixels('xl'));
        $this->assertSame(
            ['none', 'sm', 'md', 'lg', 'xl'],
            array_keys(Style::spacingOptions()),
        );
    }

    public function testGalleryColumnsUseThePerScreenFields(): void
    {
        $this->assertSame(
            'columns-2 md:columns-3 lg:columns-5',
            ElementGallery::create(['ColumnsSmall' => 2, 'ColumnsMedium' => 3, 'ColumnsLarge' => 5])->getColumnClasses(),
        );
    }

    /**
     * Every block: no duplicate fields, and the Main tab ends with the shared
     * groups in the agreed order (Layout, Background, Slideshow).
     */
    public function testEveryBlockArrangesItsFieldsTheSameWay(): void
    {
        $blocks = array_filter(
            ClassInfo::subclassesFor(BaseElement::class, false),
            fn (string $class) => str_starts_with($class, 'Antlion\\SilverwindBlocks\\'),
        );
        $this->assertCount(11, $blocks);

        foreach ($blocks as $class) {
            $fields = $class::create()->getCMSFields();
            $names = array_keys($fields->dataFields());
            $this->assertSame($names, array_values(array_unique($names)), "$class has duplicate fields");

            $main = $fields->fieldByName('Root.Main')->Fields();
            $groups = [];
            $seenGroup = false;
            foreach ($main as $field) {
                if ($field instanceof ToggleCompositeField && in_array($field->getName(), ElementHero::GROUP_ORDER, true)) {
                    $groups[] = $field->getName();
                    $seenGroup = true;
                } else {
                    $this->assertFalse($seenGroup, "$class has {$field->getName()} after its groups");
                }
            }
            $expected = array_values(array_intersect(ElementHero::GROUP_ORDER, $groups));
            $this->assertSame($expected, $groups, "$class groups are out of order");
        }
    }

    public function testWidthAndAutomaticSpacingOnlyApplyToTopLevelBlocks(): void
    {
        $page = SiteTree::create(['Title' => 'Widths']);
        $page->write();
        $area = $page->ElementalArea()->ID;

        $hero = ElementHero::create(['ParentID' => $area]);
        $hero->write();
        $card = ElementCard::create(['ParentID' => $area, 'BlockWidth' => 'narrow']);
        $card->write();
        $plainSection = ElementSection::create(['ParentID' => $area]);
        $plainSection->write();
        $bandSection = ElementSection::create(['ParentID' => $area, 'BackgroundColor' => '#0b1f33']);
        $bandSection->write();
        $nested = ElementCard::create(['ParentID' => $bandSection->Elements()->ID, 'BlockWidth' => 'narrow']);
        $nested->write();

        // Defaults per class
        $this->assertSame('full', $hero->BlockWidth);
        $this->assertSame('full', $plainSection->BlockWidth);
        $this->assertSame('contained', ElementCard::create()->BlockWidth);

        // Widths
        $this->assertSame('w-full', $hero->getBlockWidthClass());
        $this->assertTrue($hero->getIsFullBleed());
        $this->assertSame('sw-container', $hero->getInnerClass(), 'Full-width hero lines its text up with the site');
        $this->assertSame('mx-auto w-full max-w-narrow px-gutter', $card->getBlockWidthClass());
        $this->assertSame('', $nested->getBlockWidthClass(), 'Nested blocks fill their parent');
        $this->assertNull($nested->getCMSFields()->dataFieldByName('BlockWidth'));
        $this->assertNotNull($card->getCMSFields()->dataFieldByName('BlockWidth'));

        // Automatic spacing: plain blocks are spaced, bands touch, nested blocks leave it to the section
        $this->assertSame('sw-auto-top mt-section sw-auto-bottom', $card->getSpacingClasses());
        $this->assertSame('sw-auto-top mt-section sw-auto-bottom', $plainSection->getSpacingClasses(), 'A section without a background is a plain block');
        $this->assertSame('sw-band', $bandSection->getSpacingClasses());
        $this->assertSame('sw-band', $hero->getSpacingClasses());
        $this->assertSame('', $nested->getSpacingClasses());
        $this->assertSame('mt-0', ElementHero::create(['ParentID' => $area, 'SpacingTop' => 'none'])->getSpacingClasses());

        // Inner widths follow the section's own width
        $this->assertSame('sw-container', $bandSection->getInnerWidthClass());
        $boxed = ElementSection::create(['ParentID' => $area, 'BlockWidth' => 'contained', 'BackgroundColor' => '#000000']);
        $boxed->write();
        $this->assertSame('px-6 sm:px-10', $boxed->getInnerWidthClass(), 'A site-width band pads its content instead of nesting another container');
    }

    public function testSidebarLayoutsDisableWidths(): void
    {
        $page = SiteTree::create(['Title' => 'Sidebar']);
        $page->write();
        $card = ElementCard::create(['ParentID' => $page->ElementalArea()->ID]);
        $card->write();
        $this->assertSame('sw-container', $card->getBlockWidthClass());

        $card->getPage()->setField('SidebarPosition', 'left');
        $this->assertSame('', $card->getBlockWidthClass(), 'Next to a sidebar the block fills its column');
    }

    public function testCardFormFollowsTheCardsReadingOrder(): void
    {
        $fields = ElementCard::create()->getCMSFields();
        $main = array_map(fn ($f) => $f->getName(), $fields->fieldByName('Root.Main')->Fields()->toArray());
        $position = fn (string $name) => array_search($name, $main, true);

        $this->assertLessThan($position('Image'), $position('CardStyle'), 'Style comes first');
        $this->assertLessThan($position('Content'), $position('Image'));
        $this->assertLessThan($position('Links'), $position('Content'));
        $this->assertNotFalse($position('CardImageNoteWrapper'), 'No image yet: overlay styles warn');
        $this->assertFalse(ElementCard::create()->inlineEditable(), 'Full form, so the colour picker and style rules work');

        $withImage = ElementCard::create(['ImageID' => 1]);
        $this->assertNotContains('CardImageNoteWrapper', array_map(fn ($f) => $f->getName(), $withImage->getCMSFields()->fieldByName('Root.Main')->Fields()->toArray()));
    }

    public function testCardSummary(): void
    {
        $card = ElementCard::create(['CardStyle' => 'overlay', 'Content' => '<p>Short text here.</p>']);
        $card->write();
        ExternalLink::create(['OwnerID' => $card->ID, 'OwnerClass' => ElementCard::class, 'OwnerRelation' => 'Links', 'ExternalUrl' => 'https://example.com'])->write();

        $this->assertSame('Overlay · 1 button(s) · no image · Short text here.', $card->getSummary());
    }

    public function testCardStylesNoLongerIncludeGradient(): void
    {
        $this->assertSame(['vertical', 'horizontal', 'overlay'], array_keys(ElementCard::cardStyleOptions()));
        $this->assertSame(['vertical', 'horizontal', 'overlay'], array_values(ElementCard::create()->dbObject('CardStyle')->enumValues()));
        $this->assertSame(['vertical', 'horizontal', 'overlay'], array_values(ElementCarousel::create()->dbObject('CardStyle')->enumValues()));
    }

    public function testOverlayCardColours(): void
    {
        // No image, no colour: the default dark panel, light text
        $plain = ElementCard::create(['CardStyle' => 'overlay']);
        $this->assertSame('sw-tone-light', $plain->getCardToneClass());
        $this->assertSame('background-color:#000000;opacity:0.55', $plain->getCardOverlayStyle(), 'Default overlay is 55% black');

        // No image, light card colour: dark text, like a panel card
        $light = ElementCard::create(['CardStyle' => 'overlay', 'BackgroundColor' => '#fef3c7']);
        $this->assertSame('sw-tone-dark', $light->getCardToneClass());
        $this->assertSame('background-color:#fef3c7', $light->getBackgroundStyle());

        // Custom overlay colour and strength; explicit text colour wins
        $tinted = ElementCard::create(['CardStyle' => 'overlay', 'OverlayColor' => '#005ae1', 'OverlayOpacity' => 30, 'Tone' => 'dark']);
        $this->assertSame('background-color:#005ae1;opacity:0.3', $tinted->getCardOverlayStyle());
        $this->assertSame('sw-tone-dark', $tinted->getCardToneClass());
        $this->assertSame('', ElementCard::create(['CardStyle' => 'overlay', 'OverlayOpacity' => 0])->getCardOverlayStyle(), '0 means no overlay');

        // Panel styles keep their own colour rules
        $this->assertSame('', ElementCard::create(['CardStyle' => 'vertical'])->getCardToneClass());
        $this->assertFalse(ElementCard::create(['CardStyle' => 'vertical'])->getHasBackgroundImage(), 'Only overlay cards use their image as a background');

        // Carousel cards take their colours from the carousel (testCarouselCardsUseTheCarouselsColours)
    }

    public function testOverlayFieldsOnlyShowForOverlayCards(): void
    {
        $fields = ElementCard::create()->getCMSFields();
        $group = $fields->fieldByName('Root.Main.BlockBackground')->getChildren();

        $this->assertNotNull($group->dataFieldByName('BackgroundColor'), 'Card colour applies to every style');
        $this->assertNotNull($group->dataFieldByName('Tone'), 'Text colour applies to every style');
        $overlay = $group->fieldByName('CardOverlay');
        $this->assertNotNull($overlay);
        $this->assertNotNull($overlay->getChildren()->dataFieldByName('OverlayColor'));
        $this->assertStringContainsString("evaluateEqualTo('overlay')", $overlay->getDisplayLogicCriteria()->toScript());
    }

    public function testSlideBackgroundPutsTheChosenTypesFieldsFirst(): void
    {
        $group = Slide::create()->getCMSFields()->fieldByName('Root.Main.BlockBackground')->getChildren();
        $names = array_map(fn ($f) => $f->getName(), $group->toArray());
        $only = fn (string $name) => $group->fieldByName($name)->getDisplayLogicCriteria()?->toScript() ?? '';

        $this->assertSame(['MediaType', 'VideoFields', 'BackgroundImage'], array_slice($names, 0, 3), 'Type, then the video files, then the image/poster');
        $this->assertStringContainsString("evaluateEqualTo('video')", $only('VideoFields'));
        $this->assertStringContainsString("evaluateEqualTo('video')", $only('PosterNoteWrapper'));
        $this->assertStringContainsString("evaluateEqualTo('image')", $only('MobileBackgroundImage'), 'Video slides don\'t use a phone crop');
        $this->assertSame('', $only('BackgroundImage'), 'The image is used by both types (as the video poster)');
    }

    public function testOverlayCardsCanUseAGradient(): void
    {
        $card = ElementCard::create([
            'CardStyle' => 'overlay',
            'OverlayType' => 'gradient',
            'GradientDirection' => 'top',
            'GradientStartColor' => '#000000',
            'GradientStartOpacity' => 80,
            'GradientEndOpacity' => 0,
        ]);

        $this->assertSame(
            'background-image:linear-gradient(to top,#000000cc 0%,#00000000 100%)',
            $card->getCardOverlayStyle(),
            'Same gradient output as Hero, Section and Slide',
        );

        $overlay = $card->getCMSFields()->fieldByName('Root.Main.BlockBackground')->getChildren()->fieldByName('CardOverlay');
        foreach (['OverlayType', 'OverlayColor', 'OverlayOpacity', 'GradientDirection', 'GradientStartColor', 'GradientEndPoint'] as $name) {
            $this->assertNotNull($overlay->getChildren()->dataFieldByName($name), "Card overlay has $name");
        }
        $this->assertSame(70, (int) ElementCard::create()->GradientStartOpacity, 'Shared gradient defaults');
    }

    public function testCardImageShapeAndFit(): void
    {
        // Automatic shape follows the style
        $this->assertSame('aspect-[4/3]', ElementCard::create(['CardStyle' => 'vertical'])->getCardFrameClass());
        $this->assertSame('aspect-square', ElementCard::create(['CardStyle' => 'overlay'])->getCardFrameClass());

        // Fixed shapes and original
        $this->assertSame('aspect-video', ElementCard::create(['ImageRatio' => '16x9'])->getCardFrameClass());
        $this->assertSame('aspect-[3/4]', ElementCard::create(['ImageRatio' => '3x4', 'CardStyle' => 'overlay'])->getCardFrameClass());
        $this->assertSame('', ElementCard::create(['ImageRatio' => 'original'])->getCardFrameClass());

        // Fit: contain only means something with a fixed shape
        $this->assertSame('object-cover', ElementCard::create()->getCardImageClass());
        $this->assertSame('object-contain', ElementCard::create(['ImageFit' => 'contain'])->getCardImageClass());
        $this->assertSame('object-cover', ElementCard::create(['ImageFit' => 'contain', 'ImageRatio' => 'original'])->getCardImageClass());

        $this->assertSame('text-center', ElementCard::create(['HorizontalAlignment' => 'center'])->getCardTextAlignClass());
        $this->assertNull(ElementCard::create()->getCardImage(), 'No image, nothing to size');
    }

    public function testCarouselCardsUseTheCarouselsCardSettings(): void
    {
        $carousel = ElementCarousel::create([
            'CardStyle' => 'overlay',
            'ImageRatio' => '3x2',
            'ImageFit' => 'contain',
            'HorizontalAlignment' => 'right',
        ]);
        $carousel->write();
        $slide = CarouselSlide::create(['CarouselID' => $carousel->ID, 'Title' => 'Card']);
        $slide->write();

        $this->assertSame('overlay', $slide->getCardStyle());
        $this->assertSame('aspect-[3/2]', $slide->getCardFrameClass());
        $this->assertSame('object-contain', $slide->getCardImageClass());
        $this->assertSame('text-right', $slide->getCardTextAlignClass());
        $this->assertSame('justify-end', $slide->getCardJustifyClass());

        $orphan = CarouselSlide::create();
        $this->assertSame('vertical', $orphan->getCardStyle(), 'A card without a carousel falls back to defaults');
        $this->assertSame('aspect-[4/3]', $orphan->getCardFrameClass());

        $layout = $carousel->getCMSFields()->fieldByName('Root.Main.BlockLayout')->getChildren();
        foreach (['CardStyle', 'HorizontalAlignment', 'ImageRatio', 'ImageFit'] as $name) {
            $this->assertNotNull($layout->dataFieldByName($name), "Carousel Layout has $name");
        }
    }

    public function testCarouselCardsUseTheCarouselsColours(): void
    {
        $carousel = ElementCarousel::create(['CardStyle' => 'overlay']);
        $carousel->write();
        $slide = CarouselSlide::create(['CarouselID' => $carousel->ID]);
        $slide->write();

        // New carousels start like overlay cards: 55% black, light text
        $this->assertSame('background-color:#000000;opacity:0.55', $slide->getCardOverlayStyle());
        $this->assertSame('sw-tone-light', $slide->getCardToneClass());

        // Colours set on the carousel apply to every card
        $carousel->update(['BackgroundColor' => '#fef3c7', 'OverlayType' => 'gradient', 'GradientStartOpacity' => 80, 'GradientEndOpacity' => 0])->write();
        $slide = CarouselSlide::get()->byID($slide->ID);
        $this->assertSame('background-color:#fef3c7', $slide->getBackgroundStyle());
        $this->assertStringStartsWith('background-image:linear-gradient(to top,#000000cc 0%', $slide->getCardOverlayStyle());
        $this->assertSame('sw-tone-dark', $slide->getCardToneClass(), 'No image: text suits the light card colour');

        $carousel->update(['CardStyle' => 'vertical', 'BackgroundColor' => '#0b1f33'])->write();
        $slide = CarouselSlide::get()->byID($slide->ID);
        $this->assertSame('sw-tone-light', $slide->getToneClass(), 'Panel cards too');

        // The Colour group sits right after Layout, before Slideshow
        $main = array_map(fn ($f) => $f->getName(), $carousel->getCMSFields()->fieldByName('Root.Main')->Fields()->toArray());
        $groups = array_values(array_intersect($main, ['BlockLayout', 'BlockBackground', 'BlockSlideshow']));
        $this->assertSame(['BlockLayout', 'BlockBackground', 'BlockSlideshow'], $groups);
        $colour = $carousel->getCMSFields()->fieldByName('Root.Main.BlockBackground');
        $this->assertSame('Colour', $colour->Title());
        foreach (['BackgroundColor', 'OverlayType', 'GradientStartColor', 'Tone'] as $name) {
            $this->assertNotNull($colour->getChildren()->dataFieldByName($name), "Carousel Colour has $name");
        }
    }

    /**
     * Every field with a show/hide rule must render display-logic's data
     * attributes; without them it starts hidden and never appears (e.g. a
     * FieldGroup given its own rule: wrap it in a Wrapper instead).
     */
    public function testEveryShowHideRuleRendersItsAttributes(): void
    {
        $classes = [
            ...array_filter(
                ClassInfo::subclassesFor(BaseElement::class, false),
                fn (string $class) => str_starts_with($class, 'Antlion\\SilverwindBlocks\\'),
            ),
            Slide::class,
        ];
        $form = \SilverStripe\Forms\Form::create(null, 'Test', \SilverStripe\Forms\FieldList::create(), \SilverStripe\Forms\FieldList::create())->disableSecurityToken();

        $checked = 0;
        foreach ($classes as $class) {
            $walk = function ($fields) use (&$walk, &$checked, $class, $form) {
                foreach ($fields as $field) {
                    if ($field->hasMethod('getDisplayLogicCriteria') && $field->getDisplayLogicCriteria()) {
                        $field->setForm($form);
                        $this->assertStringContainsString(
                            'data-display-logic-eval',
                            (string) $field->FieldHolder(),
                            "$class: {$field->getName()} has a show/hide rule but renders without it, so it would never appear",
                        );
                        $checked++;
                    }
                    if ($field instanceof \SilverStripe\Forms\CompositeField) {
                        $walk($field->getChildren());
                    }
                }
            };
            $walk($class::create()->getCMSFields());
        }
        $this->assertGreaterThan(10, $checked, 'Sanity check: show/hide fields were found');
    }
}
