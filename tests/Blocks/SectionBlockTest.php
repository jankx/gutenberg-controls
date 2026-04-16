<?php

namespace Jankx\Gutenberg\Tests\Blocks;

use Jankx\Gutenberg\Blocks\SectionBlock;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Test case for SectionBlock
 *
 * @package Jankx\Gutenberg\Tests\Blocks
 * @covers \Jankx\Gutenberg\Blocks\SectionBlock
 */
class SectionBlockTest extends TestCase
{
    /**
     * @var SectionBlock
     */
    private $block;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // Mock WordPress functions
        Monkey\Functions\when('has_block')->justReturn(true);
        Monkey\Functions\when('esc_attr')->returnArg();
        Monkey\Functions\when('esc_html')->returnArg();
        Monkey\Functions\when('__')->returnArg();

        // Mock wp_enqueue_* functions
        Monkey\Functions\when('wp_enqueue_script')->justReturn();
        Monkey\Functions\when('wp_enqueue_style')->justReturn();
        Monkey\Functions\when('plugins_url')->returnArg(0);

        $this->block = new SectionBlock();
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /** @test */
    public function it_has_correct_block_name()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getBlockName');
        $method->setAccessible(true);

        $this->assertEquals('jankx/section', $method->invoke($this->block));
    }

    /** @test */
    public function it_has_correct_block_title()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getBlockTitle');
        $method->setAccessible(true);

        $title = $method->invoke($this->block);
        $this->assertStringContainsString('Section', $title);
    }

    /** @test */
    public function it_registers_layout_control()
    {
        $reflection = new \ReflectionClass($this->block);
        $property = $reflection->getProperty('controls');
        $property->setAccessible(true);
        $controls = $property->getValue($this->block);

        $this->assertArrayHasKey('layout', $controls);
    }

    /** @test */
    public function it_registers_animation_control()
    {
        $reflection = new \ReflectionClass($this->block);
        $property = $reflection->getProperty('controls');
        $property->setAccessible(true);
        $controls = $property->getValue($this->block);

        $this->assertArrayHasKey('animation', $controls);
    }

    /** @test */
    public function it_has_jankx_category()
    {
        $reflection = new \ReflectionClass($this->block);
        $property = $reflection->getProperty('category');
        $property->setAccessible(true);

        $this->assertEquals('jankx-layout', $property->getValue($this->block));
    }

    /** @test */
    public function it_allows_specific_inner_blocks()
    {
        $reflection = new \ReflectionClass($this->block);
        $property = $reflection->getProperty('allowedBlocks');
        $property->setAccessible(true);
        $allowed = $property->getValue($this->block);

        $this->assertContains('core/heading', $allowed);
        $this->assertContains('core/paragraph', $allowed);
        $this->assertContains('core/buttons', $allowed);
        $this->assertContains('jankx/row', $allowed);
    }

    /** @test */
    public function it_generates_section_classes_for_full_width()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionClasses');
        $method->setAccessible(true);

        $layout = ['width' => 'full'];
        $classes = $method->invoke($this->block, $layout);

        $this->assertContains('jankx-section', $classes);
        $this->assertContains('jankx-section--full-width', $classes);
    }

    /** @test */
    public function it_generates_section_classes_for_full_height()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionClasses');
        $method->setAccessible(true);

        $layout = ['height' => 'full'];
        $classes = $method->invoke($this->block, $layout);

        $this->assertContains('jankx-section--full-height', $classes);
    }

    /** @test */
    public function it_generates_section_classes_for_parallax()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionClasses');
        $method->setAccessible(true);

        $layout = ['parallax' => true];
        $classes = $method->invoke($this->block, $layout);

        $this->assertContains('jankx-section--parallax', $classes);
    }

    /** @test */
    public function it_generates_section_classes_for_sticky()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionClasses');
        $method->setAccessible(true);

        $layout = ['sticky' => true];
        $classes = $method->invoke($this->block, $layout);

        $this->assertContains('jankx-section--sticky', $classes);
    }

    /** @test */
    public function it_generates_section_styles_with_background_color()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionStyles');
        $method->setAccessible(true);

        $layout = [
            'backgroundType' => 'color',
            'backgroundColor' => '#ff5722',
        ];
        $styles = $method->invoke($this->block, $layout);

        $this->assertStringContainsString('background-color: #ff5722', $styles);
    }

    /** @test */
    public function it_generates_section_styles_with_gradient()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionStyles');
        $method->setAccessible(true);

        $layout = [
            'backgroundType' => 'gradient',
            'backgroundColor' => 'linear-gradient(90deg, red, blue)',
        ];
        $styles = $method->invoke($this->block, $layout);

        $this->assertStringContainsString('linear-gradient', $styles);
    }

    /** @test */
    public function it_generates_section_styles_with_min_height()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionStyles');
        $method->setAccessible(true);

        $layout = ['minHeight' => '600px'];
        $styles = $method->invoke($this->block, $layout);

        $this->assertStringContainsString('min-height: 600px', $styles);
    }

    /** @test */
    public function it_generates_section_styles_with_parallax_speed()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionStyles');
        $method->setAccessible(true);

        $layout = [
            'parallax' => true,
            'parallaxSpeed' => 0.7,
        ];
        $styles = $method->invoke($this->block, $layout);

        $this->assertStringContainsString('--jankx-parallax-speed: 0.7', $styles);
    }

    /** @test */
    public function it_generates_section_styles_with_sticky_offset()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionStyles');
        $method->setAccessible(true);

        $layout = [
            'sticky' => true,
            'stickyOffset' => 50,
        ];
        $styles = $method->invoke($this->block, $layout);

        $this->assertStringContainsString('--jankx-sticky-offset: 50px', $styles);
    }

    /** @test */
    public function it_returns_default_container_class()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getContainerClass');
        $method->setAccessible(true);

        $class = $method->invoke($this->block, 'default');

        $this->assertStringContainsString('jankx-section__container', $class);
        $this->assertStringContainsString('jankx-container--default', $class);
    }

    /** @test */
    public function it_returns_wide_container_class()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getContainerClass');
        $method->setAccessible(true);

        $class = $method->invoke($this->block, 'wide');

        $this->assertStringContainsString('jankx-container--wide', $class);
    }

    /** @test */
    public function it_returns_narrow_container_class()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getContainerClass');
        $method->setAccessible(true);

        $class = $method->invoke($this->block, 'narrow');

        $this->assertStringContainsString('jankx-container--narrow', $class);
    }

    /** @test */
    public function it_generates_data_attributes_for_animation()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionDataAttributes');
        $method->setAccessible(true);

        $animation = [
            'entrance' => 'fadeInUp',
            'scrollTrigger' => true,
            'scrollOffset' => 150,
        ];
        $attrs = $method->invoke($this->block, $animation);

        $this->assertStringContainsString('data-animate="true"', $attrs);
        $this->assertStringContainsString('data-animation="fadeInUp"', $attrs);
        $this->assertStringContainsString('data-scroll-trigger="true"', $attrs);
        $this->assertStringContainsString('data-scroll-offset="150"', $attrs);
    }

    /** @test */
    public function it_generates_data_attributes_for_infinite_animation()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getSectionDataAttributes');
        $method->setAccessible(true);

        $animation = [
            'infinite' => 'pulse',
        ];
        $attrs = $method->invoke($this->block, $animation);

        $this->assertStringContainsString('data-infinite-animation="pulse"', $attrs);
    }

    /** @test */
    public function it_renders_divider_svg()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('renderDivider');
        $method->setAccessible(true);

        $html = $method->invoke($this->block, 'curve', '#ffffff', 'top');

        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('jankx-section__divider', $html);
        $this->assertStringContainsString('jankx-section__divider--top', $html);
        $this->assertStringContainsString('jankx-section__divider--curve', $html);
    }

    /** @test */
    public function it_returns_empty_string_for_no_divider()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('renderDivider');
        $method->setAccessible(true);

        $html = $method->invoke($this->block, '', '#ffffff', 'top');

        $this->assertEquals('', $html);
    }

    /** @test */
    public function it_supports_multiple_divider_types()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('renderDivider');
        $method->setAccessible(true);

        $types = ['curve', 'triangle', 'slant', 'arrow', 'clouds'];

        foreach ($types as $type) {
            $html = $method->invoke($this->block, $type, '#fff', 'top');
            $this->assertStringContainsString("jankx-section__divider--{$type}", $html);
        }
    }

    /** @test */
    public function it_renders_background_overlay()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('renderBackgroundOverlay');
        $method->setAccessible(true);

        $html = $method->invoke($this->block, 'rgba(0,0,0,0.5)');

        $this->assertStringContainsString('jankx-section__overlay', $html);
        $this->assertStringContainsString('rgba(0,0,0,0.5)', $html);
    }

    /** @test */
    public function it_wraps_content_in_section_tag()
    {
        $attributes = [
            'jankxControls' => [
                'layout' => ['width' => 'default'],
            ],
        ];

        $html = $this->block->render($attributes, '<p>Content</p>', null);

        $this->assertStringContainsString('<section', $html);
        $this->assertStringContainsString('</section>', $html);
        $this->assertStringContainsString('<p>Content</p>', $html);
    }

    /** @test */
    public function it_includes_container_div()
    {
        $attributes = [
            'jankxControls' => [
                'layout' => ['containerWidth' => 'default'],
            ],
        ];

        $html = $this->block->render($attributes, '<p>Content</p>', null);

        $this->assertStringContainsString('jankx-section__container', $html);
    }
}
