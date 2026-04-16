<?php

namespace Jankx\Gutenberg\Tests\Controls;

use Jankx\Gutenberg\Controls\AbstractControl;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Test case for AbstractControl base class
 *
 * @package Jankx\Gutenberg\Tests\Controls
 * @covers \Jankx\Gutenberg\Controls\AbstractControl
 */
class AbstractControlTest extends TestCase
{
    /**
     * @var AbstractControl
     */
    private $control;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // Create concrete implementation for testing
        $this->control = new class extends AbstractControl {
            protected $supportedBlocks = ['test/block'];

            public function getType(): string
            {
                return 'jankx/test';
            }

            public function renderEditor($value, callable $onChange): string
            {
                return '<div class="test-control"></div>';
            }

            public function generateCss($value, string $selector): string
            {
                return sprintf('%s { color: %s; }', $selector, $value);
            }
        };
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /** @test */
    public function it_has_default_name_based_on_class()
    {
        // The test control class is anonymous, so name will be empty string
        // from basename of the class name
        $this->assertNotEmpty($this->control->getName());
    }

    /** @test */
    public function it_returns_configured_name()
    {
        $control = new class(['name' => 'custom-control']) extends AbstractControl {
            public function getType(): string { return 'jankx/test'; }
            public function renderEditor($value, callable $onChange): string { return ''; }
            public function generateCss($value, string $selector): string { return ''; }
        };

        $this->assertEquals('custom-control', $control->getName());
    }

    /** @test */
    public function it_returns_configured_label()
    {
        $control = new class(['label' => 'Custom Label']) extends AbstractControl {
            public function getType(): string { return 'jankx/test'; }
            public function renderEditor($value, callable $onChange): string { return ''; }
            public function generateCss($value, string $selector): string { return ''; }
        };

        $this->assertEquals('Custom Label', $control->getLabel());
    }

    /** @test */
    public function it_generates_default_label_from_name()
    {
        $control = new class(['name' => 'background_color']) extends AbstractControl {
            public function getType(): string { return 'jankx/test'; }
            public function renderEditor($value, callable $onChange): string { return ''; }
            public function generateCss($value, string $selector): string { return ''; }
        };

        // Should convert snake_case to Title Case
        $this->assertStringContainsString('Background', $control->getLabel());
    }

    /** @test */
    public function it_returns_schema_for_block_registration()
    {
        $schema = $this->control->getSchema();

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('type', $schema);
        $this->assertArrayHasKey('default', $schema);
        $this->assertEquals('object', $schema['type']);
    }

    /** @test */
    public function it_stores_default_value_in_schema()
    {
        $control = new class(['default' => ['color' => '#ff0000']]) extends AbstractControl {
            public function getType(): string { return 'jankx/test'; }
            public function renderEditor($value, callable $onChange): string { return ''; }
            public function generateCss($value, string $selector): string { return ''; }
        };

        $schema = $control->getSchema();
        $this->assertEquals(['color' => '#ff0000'], $schema['default']);
    }

    /** @test */
    public function it_supports_check_returns_true_for_supported_blocks()
    {
        $this->assertTrue($this->control->supports('test/block'));
    }

    /** @test */
    public function it_supports_check_returns_false_for_unsupported_blocks()
    {
        $this->assertFalse($this->control->supports('other/block'));
    }

    /** @test */
    public function it_returns_supported_blocks_list()
    {
        $blocks = $this->control->getSupportedBlocks();
        $this->assertIsArray($blocks);
        $this->assertContains('test/block', $blocks);
    }

    /** @test */
    public function it_sanitizes_value_by_default()
    {
        $value = ['color' => '#ff0000', 'size' => 'large'];
        $sanitized = $this->control->sanitize($value);

        $this->assertEquals($value, $sanitized);
    }

    /** @test */
    public function it_validates_value_returns_true_by_default()
    {
        $result = $this->control->validate(['color' => '#ff0000']);
        $this->assertTrue($result);
    }

    /** @test */
    public function it_has_default_breakpoints()
    {
        $expected = [
            'desktop' => '',
            'tablet' => '@media (max-width: 1024px)',
            'mobile' => '@media (max-width: 768px)',
        ];

        // Access protected property via reflection
        $reflection = new \ReflectionClass($this->control);
        $property = $reflection->getProperty('breakpoints');
        $property->setAccessible(true);

        $this->assertEquals($expected, $property->getValue($this->control));
    }

    /** @test */
    public function it_merges_attributes_from_config()
    {
        $control = new class([
            'attributes' => ['customAttr' => ['type' => 'string', 'default' => 'test']]
        ]) extends AbstractControl {
            protected $attributes = ['existingAttr' => ['type' => 'boolean']];

            public function getType(): string { return 'jankx/test'; }
            public function renderEditor($value, callable $onChange): string { return ''; }
            public function generateCss($value, string $selector): string { return ''; }
        };

        $reflection = new \ReflectionClass($control);
        $property = $reflection->getProperty('attributes');
        $property->setAccessible(true);
        $attributes = $property->getValue($control);

        $this->assertArrayHasKey('existingAttr', $attributes);
        $this->assertArrayHasKey('customAttr', $attributes);
    }

    /** @test */
    public function it_returns_dependencies_for_wordpress_components()
    {
        $deps = $this->control->getDependencies();

        $this->assertIsArray($deps);
        $this->assertContains('wp-components', $deps);
        $this->assertContains('wp-compose', $deps);
        $this->assertContains('wp-hooks', $deps);
    }

    /** @test */
    public function it_should_render_returns_true_by_default()
    {
        $this->assertTrue($this->control->shouldRender());
    }

    /** @test */
    public function it_should_render_returns_true_with_context()
    {
        $this->assertTrue($this->control->shouldRender(['postType' => 'page']));
    }

    /** @test */
    public function it_generates_css_with_selector()
    {
        $css = $this->control->generateCss('#ff0000', '.my-block');

        $this->assertStringContainsString('.my-block', $css);
        $this->assertStringContainsString('#ff0000', $css);
    }
}
