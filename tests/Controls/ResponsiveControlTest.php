<?php

namespace Jankx\Gutenberg\Tests\Controls;

use Jankx\Gutenberg\Controls\Layout\ResponsiveControl;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Test case for ResponsiveControl
 *
 * @package Jankx\Gutenberg\Tests\Controls
 * @covers \Jankx\Gutenberg\Controls\Layout\ResponsiveControl
 */
class ResponsiveControlTest extends TestCase
{
    /**
     * @var ResponsiveControl
     */
    private $control;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // Mock WordPress functions
        Monkey\Functions\when('__')->returnArg();

        $this->control = new ResponsiveControl([
            'name' => 'responsive',
            'label' => __('Responsive Settings', 'jankx'),
        ]);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /** @test */
    public function it_has_correct_type()
    {
        $this->assertEquals('jankx/responsive', $this->control->getType());
    }

    /** @test */
    public function it_has_layout_category()
    {
        $reflection = new \ReflectionClass($this->control);
        $property = $reflection->getProperty('category');
        $property->setAccessible(true);

        $this->assertEquals('layout', $property->getValue($this->control));
    }

    /** @test */
    public function it_has_device_visibility_attributes()
    {
        $reflection = new \ReflectionClass($this->control);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attributes = $property->getValue($this->control);

        $this->assertArrayHasKey('hideOnDesktop', $attributes);
        $this->assertArrayHasKey('hideOnTablet', $attributes);
        $this->assertArrayHasKey('hideOnMobile', $attributes);
    }

    /** @test */
    public function it_has_column_span_attributes()
    {
        $reflection = new \ReflectionClass($this->control);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attributes = $property->getValue($this->control);

        $this->assertArrayHasKey('colSpanDesktop', $attributes);
        $this->assertArrayHasKey('colSpanTablet', $attributes);
        $this->assertArrayHasKey('colSpanMobile', $attributes);

        $this->assertEquals(12, $attributes['colSpanDesktop']['default']);
    }

    /** @test */
    public function it_has_order_attributes()
    {
        $reflection = new \ReflectionClass($this->control);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attributes = $property->getValue($this->control);

        $this->assertArrayHasKey('orderDesktop', $attributes);
        $this->assertArrayHasKey('orderTablet', $attributes);
        $this->assertArrayHasKey('orderMobile', $attributes);
    }

    /** @test */
    public function it_has_flex_direction_attributes()
    {
        $reflection = new \ReflectionClass($this->control);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attributes = $property->getValue($this->control);

        $this->assertArrayHasKey('flexDirectionDesktop', $attributes);
        $this->assertArrayHasKey('flexDirectionTablet', $attributes);
        $this->assertArrayHasKey('flexDirectionMobile', $attributes);
    }

    /** @test */
    public function it_has_text_align_attributes()
    {
        $reflection = new \ReflectionClass($this->control);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attributes = $property->getValue($this->control);

        $this->assertArrayHasKey('textAlignDesktop', $attributes);
        $this->assertArrayHasKey('textAlignTablet', $attributes);
        $this->assertArrayHasKey('textAlignMobile', $attributes);
    }

    /** @test */
    public function it_has_stack_behavior_attributes()
    {
        $reflection = new \ReflectionClass($this->control);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attributes = $property->getValue($this->control);

        $this->assertArrayHasKey('stackVertically', $attributes);
        $this->assertArrayHasKey('stackAtBreakpoint', $attributes);
        $this->assertArrayHasKey('reverseStackOrder', $attributes);
    }

    /** @test */
    public function it_generates_visibility_css_for_desktop_hide()
    {
        $value = ['hideOnDesktop' => true];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('.jankx-block { display: none !important; }', $css);
    }

    /** @test */
    public function it_generates_visibility_css_for_tablet_hide()
    {
        $value = ['hideOnTablet' => true];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('@media (max-width: 1024px)', $css);
        $this->assertStringContainsString('display: none !important', $css);
    }

    /** @test */
    public function it_generates_visibility_css_for_mobile_hide()
    {
        $value = ['hideOnMobile' => true];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('@media (max-width: 767px)', $css);
        $this->assertStringContainsString('display: none !important', $css);
    }

    /** @test */
    public function it_generates_column_span_css()
    {
        $value = [
            'colSpanDesktop' => 6,
            'colSpanTablet' => 4,
            'colSpanMobile' => 12,
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        // Desktop
        $this->assertStringContainsString('grid-column: span 6 / span 6', $css);
        // Tablet
        $this->assertStringContainsString('@media (max-width: 1024px)', $css);
        $this->assertStringContainsString('grid-column: span 4 / span 4', $css);
        // Mobile
        $this->assertStringContainsString('@media (max-width: 767px)', $css);
        $this->assertStringContainsString('grid-column: span 12 / span 12', $css);
    }

    /** @test */
    public function it_skips_duplicate_column_span_values()
    {
        $value = [
            'colSpanDesktop' => 6,
            'colSpanTablet' => 6, // Same as desktop
            'colSpanMobile' => 6, // Same as tablet
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        // Should only have desktop CSS
        $this->assertStringContainsString('span 6', $css);
        // Should not have tablet or mobile specific rules
        $this->assertStringNotContainsString('grid-column: span 6 / span 6', $css);
    }

    /** @test */
    public function it_generates_order_css()
    {
        $value = [
            'orderDesktop' => 2,
            'orderTablet' => 1,
            'orderMobile' => 3,
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('order: 2', $css);
        $this->assertStringContainsString('@media (max-width: 1024px) { .jankx-block { order: 1', $css);
        $this->assertStringContainsString('@media (max-width: 767px) { .jankx-block { order: 3', $css);
    }

    /** @test */
    public function it_skips_order_zero_for_desktop()
    {
        $value = ['orderDesktop' => 0];

        $css = $this->control->generateCss($value, '.jankx-block');

        // Default order is 0, so no CSS needed
        $this->assertStringNotContainsString('order: 0', $css);
    }

    /** @test */
    public function it_generates_flex_direction_css_for_tablet()
    {
        $value = [
            'flexDirectionDesktop' => 'row',
            'flexDirectionTablet' => 'column',
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('@media (max-width: 1024px) { .jankx-block { flex-direction: column; } }', $css);
    }

    /** @test */
    public function it_generates_flex_direction_css_for_mobile()
    {
        $value = [
            'flexDirectionDesktop' => 'row',
            'flexDirectionTablet' => 'row',
            'flexDirectionMobile' => 'column',
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        // Tablet same as desktop, so no tablet rule
        $this->assertStringNotContainsString('1024px', $css);
        // Mobile different
        $this->assertStringContainsString('@media (max-width: 767px) { .jankx-block { flex-direction: column; } }', $css);
    }

    /** @test */
    public function it_generates_text_align_css()
    {
        $value = [
            'textAlignDesktop' => 'left',
            'textAlignTablet' => 'center',
            'textAlignMobile' => 'right',
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        // Desktop is default left, usually no CSS needed unless explicitly set
        $this->assertStringContainsString('@media (max-width: 1024px) { .jankx-block { text-align: center', $css);
        $this->assertStringContainsString('@media (max-width: 767px) { .jankx-block { text-align: right', $css);
    }

    /** @test */
    public function it_generates_stack_css()
    {
        $value = [
            'stackVertically' => true,
            'stackAtBreakpoint' => 'tablet',
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('@media (max-width: 1024px) { .jankx-block { flex-direction: column; } }', $css);
    }

    /** @test */
    public function it_generates_reverse_stack_css()
    {
        $value = [
            'stackVertically' => true,
            'stackAtBreakpoint' => 'mobile',
            'reverseStackOrder' => true,
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('@media (max-width: 767px) { .jankx-block { flex-direction: column-reverse; } }', $css);
    }

    /** @test */
    public function it_skips_stack_css_when_disabled()
    {
        $value = ['stackVertically' => false];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringNotContainsString('flex-direction: column', $css);
    }

    /** @test */
    public function it_generates_spacing_override_css()
    {
        $value = [
            'paddingTablet' => ['top' => '20px', 'right' => '20px', 'bottom' => '20px', 'left' => '20px'],
            'marginMobile' => ['top' => '10px', 'bottom' => '10px'],
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        // Tablet padding shorthand (all same)
        $this->assertStringContainsString('@media (max-width: 1024px) { .jankx-block { padding: 20px; } }', $css);

        // Mobile margin individual
        $this->assertStringContainsString('@media (max-width: 767px) { .jankx-block {', $css);
        $this->assertStringContainsString('margin-top: 10px;', $css);
        $this->assertStringContainsString('margin-bottom: 10px;', $css);
    }

    /** @test */
    public function it_generates_font_size_override_css()
    {
        $value = [
            'fontSizeTablet' => '14px',
            'fontSizeMobile' => '12px',
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('@media (max-width: 1024px) { .jankx-block { font-size: 14px; } }', $css);
        $this->assertStringContainsString('@media (max-width: 767px) { .jankx-block { font-size: 12px; } }', $css);
    }

    /** @test */
    public function it_skips_zero_spacing_values()
    {
        $value = [
            'paddingTablet' => ['top' => '0', 'right' => '0', 'bottom' => '0', 'left' => '0'],
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        // All zeros, should not generate padding CSS
        $this->assertStringNotContainsString('padding', $css);
    }

    /** @test */
    public function it_has_default_breakpoints()
    {
        $breakpoints = $this->control->getBreakpoints();

        $this->assertIsArray($breakpoints);
        $this->assertArrayHasKey('desktop', $breakpoints);
        $this->assertArrayHasKey('tablet', $breakpoints);
        $this->assertArrayHasKey('mobile', $breakpoints);

        $this->assertEquals(1025, $breakpoints['desktop']['min']);
        $this->assertEquals(768, $breakpoints['tablet']['min']);
        $this->assertEquals(767, $breakpoints['mobile']['max']);
    }

    /** @test */
    public function it_allows_custom_breakpoints()
    {
        $this->control->setBreakpoints(['tablet' => ['min' => 600, 'max' => 900]]);

        $breakpoints = $this->control->getBreakpoints();

        $this->assertEquals(600, $breakpoints['tablet']['min']);
        $this->assertEquals(900, $breakpoints['tablet']['max']);
    }

    /** @test */
    public function it_returns_flex_direction_options()
    {
        $options = $this->control->getFlexDirectionOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('row', $options);
        $this->assertArrayHasKey('column', $options);
        $this->assertArrayHasKey('row-reverse', $options);
        $this->assertArrayHasKey('column-reverse', $options);
    }

    /** @test */
    public function it_returns_align_items_options()
    {
        $options = $this->control->getAlignItemsOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('stretch', $options);
        $this->assertArrayHasKey('center', $options);
        $this->assertArrayHasKey('flex-start', $options);
        $this->assertArrayHasKey('flex-end', $options);
    }

    /** @test */
    public function it_returns_justify_content_options()
    {
        $options = $this->control->getJustifyContentOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('space-between', $options);
        $this->assertArrayHasKey('space-around', $options);
        $this->assertArrayHasKey('space-evenly', $options);
    }

    /** @test */
    public function it_returns_text_align_options()
    {
        $options = $this->control->getTextAlignOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('left', $options);
        $this->assertArrayHasKey('center', $options);
        $this->assertArrayHasKey('right', $options);
        $this->assertArrayHasKey('justify', $options);
    }

    /** @test */
    public function it_renders_editor_placeholder()
    {
        $render = $this->control->renderEditor([], function() {});

        $this->assertStringContainsString('ResponsiveInspector', $render);
    }

    /** @test */
    public function it_handles_empty_values_gracefully()
    {
        $css = $this->control->generateCss([], '.jankx-block');

        $this->assertIsString($css);
        // Should not throw errors
    }

    /** @test */
    public function it_allows_chaining_set_breakpoints()
    {
        $result = $this->control->setBreakpoints(['mobile' => ['max' => 600]]);

        $this->assertSame($this->control, $result);
    }

    /** @test */
    public function it_maintains_fluent_interface()
    {
        $result = $this->control->setBreakpoints(['tablet' => ['min' => 500]]);

        $this->assertInstanceOf(ResponsiveControl::class, $result);
    }
}
