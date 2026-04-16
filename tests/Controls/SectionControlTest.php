<?php

namespace Jankx\Gutenberg\Tests\Controls;

use Jankx\Gutenberg\Controls\Layout\SectionControl;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Test case for SectionControl
 *
 * @package Jankx\Gutenberg\Tests\Controls
 * @covers \Jankx\Gutenberg\Controls\Layout\SectionControl
 */
class SectionControlTest extends TestCase
{
    /**
     * @var SectionControl
     */
    private $control;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        $this->control = new SectionControl([
            'name' => 'layout',
            'label' => __('Section Layout', 'jankx'),
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
        $this->assertEquals('jankx/section', $this->control->getType());
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
    public function it_has_default_values_in_schema()
    {
        $schema = $this->control->getSchema();

        $this->assertArrayHasKey('default', $schema);
        $default = $schema['default'];

        $this->assertEquals('full', $default['width']);
        $this->assertEquals('auto', $default['height']);
        $this->assertEquals('default', $default['containerWidth']);
        $this->assertEquals('color', $default['backgroundType']);
    }

    /** @test */
    public function it_generates_css_for_full_width_section()
    {
        $value = [
            'width' => 'full',
            'backgroundType' => 'color',
            'backgroundColor' => '#f5f5f5',
        ];

        $css = $this->control->generateCss($value, '.jankx-section');

        $this->assertStringContainsString('100vw', $css);
        $this->assertStringContainsString('#f5f5f5', $css);
    }

    /** @test */
    public function it_generates_css_for_background_image()
    {
        $value = [
            'backgroundType' => 'image',
            'backgroundImage' => ['url' => 'https://example.com/bg.jpg'],
        ];

        $css = $this->control->generateCss($value, '.jankx-section');

        $this->assertStringContainsString('background-image', $css);
        $this->assertStringContainsString('https://example.com/bg.jpg', $css);
        $this->assertStringContainsString('cover', $css);
        $this->assertStringContainsString('no-repeat', $css);
    }

    /** @test */
    public function it_generates_css_for_min_height()
    {
        $value = [
            'minHeight' => '500px',
        ];

        $css = $this->control->generateCss($value, '.jankx-section');

        $this->assertStringContainsString('min-height: 500px', $css);
    }

    /** @test */
    public function it_generates_css_for_full_height()
    {
        $value = [
            'height' => 'full',
        ];

        $css = $this->control->generateCss($value, '.jankx-section');

        $this->assertStringContainsString('min-height: 100vh', $css);
    }

    /** @test */
    public function it_generates_css_for_padding()
    {
        $value = [
            'padding' => [
                'top' => '60px',
                'right' => '20px',
                'bottom' => '60px',
                'left' => '20px',
            ],
        ];

        $css = $this->control->generateCss($value, '.jankx-section');

        $this->assertStringContainsString('padding:', $css);
        $this->assertStringContainsString('60px', $css);
    }

    /** @test */
    public function it_generates_css_for_border_radius()
    {
        $value = [
            'borderRadius' => [
                'topLeft' => '10px',
                'topRight' => '10px',
                'bottomLeft' => '0',
                'bottomRight' => '0',
            ],
        ];

        $css = $this->control->generateCss($value, '.jankx-section');

        $this->assertStringContainsString('border-radius:', $css);
        $this->assertStringContainsString('10px', $css);
    }

    /** @test */
    public function it_generates_css_for_box_shadow()
    {
        $value = [
            'boxShadow' => [
                'x' => 0,
                'y' => 4,
                'blur' => 6,
                'spread' => 0,
                'color' => 'rgba(0,0,0,0.1)',
            ],
        ];

        $css = $this->control->generateCss($value, '.jankx-section');

        $this->assertStringContainsString('box-shadow:', $css);
        $this->assertStringContainsString('0px 4px 6px', $css);
    }

    /** @test */
    public function it_returns_empty_css_for_empty_values()
    {
        $value = [];

        $css = $this->control->generateCss($value, '.jankx-section');

        $this->assertIsString($css);
    }

    /** @test */
    public function it_collapse_padding_when_all_same()
    {
        $reflection = new \ReflectionClass($this->control);
        $method = $reflection->getMethod('generateSpacingCss');
        $method->setAccessible(true);

        $spacing = [
            'top' => '20px',
            'right' => '20px',
            'bottom' => '20px',
            'left' => '20px',
        ];

        $result = $method->invoke($this->control, $spacing);

        // Should collapse to single value
        $this->assertEquals('20px', $result);
    }

    /** @test */
    public function it_expands_padding_when_different()
    {
        $reflection = new \ReflectionClass($this->control);
        $method = $reflection->getMethod('generateSpacingCss');
        $method->setAccessible(true);

        $spacing = [
            'top' => '20px',
            'right' => '10px',
            'bottom' => '20px',
            'left' => '10px',
        ];

        $result = $method->invoke($this->control, $spacing);

        // Should return 4 values
        $this->assertStringContainsString('20px', $result);
        $this->assertStringContainsString('10px', $result);
    }

    /** @test */
    public function it_handles_empty_padding_values()
    {
        $reflection = new \ReflectionClass($this->control);
        $method = $reflection->getMethod('generateSpacingCss');
        $method->setAccessible(true);

        $spacing = [
            'top' => '',
            'right' => '',
            'bottom' => '',
            'left' => '',
        ];

        $result = $method->invoke($this->control, $spacing);

        // Should return 0 for empty values
        $this->assertStringContainsString('0', $result);
    }

    /** @test */
    public function it_parses_box_shadow_to_css()
    {
        $reflection = new \ReflectionClass($this->control);
        $method = $reflection->getMethod('generateBoxShadowCss');
        $method->setAccessible(true);

        $shadow = [
            'x' => 2,
            'y' => 4,
            'blur' => 8,
            'spread' => 1,
            'color' => 'rgba(0,0,0,0.2)',
            'inset' => true,
        ];

        $result = $method->invoke($this->control, $shadow);

        $this->assertStringContainsString('inset', $result);
        $this->assertStringContainsString('2px', $result);
        $this->assertStringContainsString('rgba(0,0,0,0.2)', $result);
    }

    /** @test */
    public function it_renders_editor_placeholder()
    {
        $render = $this->control->renderEditor(['width' => 'full'], function() {});

        $this->assertStringContainsString('SectionInspector', $render);
    }

    /** @test */
    public function it_responsive_mode_is_available()
    {
        $control = new SectionControl([
            'name' => 'layout',
            'responsive' => true,
        ]);

        $reflection = new \ReflectionClass($control);
        $property = $reflection->getProperty('responsive');
        $property->setAccessible(true);

        $this->assertTrue($property->getValue($control));
    }
}
