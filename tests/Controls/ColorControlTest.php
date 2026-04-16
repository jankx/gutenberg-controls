<?php

namespace Jankx\Gutenberg\Tests\Controls;

use Jankx\Gutenberg\Controls\Style\ColorControl;
use Brain\Monkey;

/**
 * Test Color Control functionality
 *
 * @package Jankx\Gutenberg\Tests\Controls
 */
class ColorControlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ColorControl
     */
    protected $control;

    /**
     * Setup test
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\Functions\stubTranslationFunctions();
        $this->control = new ColorControl(['name' => 'color']);
    }

    /**
     * Tear down
     */
    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /**
     * Test control type
     */
    public function testGetType()
    {
        $this->assertEquals('jankx/color', $this->control->getType());
    }

    /**
     * Test control category
     */
    public function testGetCategory()
    {
        $this->assertEquals('style', $this->control->getCategory());
    }

    /**
     * Test default attributes
     */
    public function testDefaultAttributes()
    {
        $schema = $this->control->getSchema();

        $this->assertArrayHasKey('colorType', $schema);
        $this->assertEquals('solid', $schema['colorType']['default']);

        $this->assertArrayHasKey('solidColor', $schema);
        $this->assertArrayHasKey('solidOpacity', $schema);
        $this->assertEquals(100, $schema['solidOpacity']['default']);

        $this->assertArrayHasKey('gradientType', $schema);
        $this->assertEquals('linear', $schema['gradientType']['default']);

        $this->assertArrayHasKey('gradientColors', $schema);
        $this->assertIsArray($schema['gradientColors']['default']);
    }

    /**
     * Test solid color CSS generation
     */
    public function testSolidColorCssGeneration()
    {
        $value = [
            'colorType' => 'solid',
            'solidColor' => '#ff5722',
            'solidOpacity' => 100,
            'useThemeColor' => false,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('.test-element', $css);
        $this->assertStringContainsString('color: #ff5722', $css);
    }

    /**
     * Test solid color with opacity
     */
    public function testSolidColorWithOpacity()
    {
        $value = [
            'colorType' => 'solid',
            'solidColor' => '#ff5722',
            'solidOpacity' => 50,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('rgba(255, 87, 34, 0.5)', $css);
    }

    /**
     * Test theme color CSS
     */
    public function testThemeColorCss()
    {
        $value = [
            'colorType' => 'solid',
            'useThemeColor' => true,
            'themeColorKey' => 'primary',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('var(--jankx-primary-color)', $css);
    }

    /**
     * Test gradient CSS generation
     */
    public function testGradientCssGeneration()
    {
        $value = [
            'colorType' => 'gradient',
            'gradientType' => 'linear',
            'gradientAngle' => 90,
            'gradientColors' => [
                ['color' => '#ff5722', 'position' => 0],
                ['color' => '#009688', 'position' => 100],
            ],
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('background:', $css);
        $this->assertStringContainsString('linear-gradient(90deg', $css);
        $this->assertStringContainsString('#ff5722 0%', $css);
        $this->assertStringContainsString('#009688 100%', $css);
    }

    /**
     * Test radial gradient
     */
    public function testRadialGradient()
    {
        $value = [
            'colorType' => 'gradient',
            'gradientType' => 'radial',
            'gradientColors' => [
                ['color' => '#ffffff', 'position' => 0],
                ['color' => '#000000', 'position' => 100],
            ],
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('radial-gradient', $css);
    }

    /**
     * Test CSS variable output
     */
    public function testCssVariableOutput()
    {
        $value = [
            'colorType' => 'solid',
            'solidColor' => '#333333',
            'cssVar' => '--custom-color',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('--custom-color:', $css);
    }

    /**
     * Test empty color returns empty CSS
     */
    public function testEmptyColorReturnsEmptyCss()
    {
        $value = [
            'colorType' => 'solid',
            'solidColor' => '',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertEmpty($css);
    }

    /**
     * Test color type options
     */
    public function testGetColorTypeOptions()
    {
        $options = $this->control->getColorTypeOptions();

        $this->assertArrayHasKey('solid', $options);
        $this->assertArrayHasKey('gradient', $options);
        $this->assertArrayHasKey('duotone', $options);
    }

    /**
     * Test preset colors
     */
    public function testGetPresetColors()
    {
        $colors = $this->control->getPresetColors();

        $this->assertIsArray($colors);
        $this->assertNotEmpty($colors);

        $first = $colors[0];
        $this->assertArrayHasKey('color', $first);
        $this->assertArrayHasKey('slug', $first);
        $this->assertArrayHasKey('name', $first);
    }

    /**
     * Test hex to RGB conversion
     */
    public function testHexToRgbConversion()
    {
        $value = [
            'colorType' => 'solid',
            'solidColor' => '#fff',
            'solidOpacity' => 50,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('rgba(255, 255, 255, 0.5)', $css);
    }

    /**
     * Test contrast ratio calculation
     */
    public function testContrastRatio()
    {
        $ratio = $this->control->getContrastRatio('#ffffff', '#000000');

        $this->assertGreaterThan(20, $ratio); // Should be ~21:1
    }

    /**
     * Test color validation
     */
    public function testValidColorDetection()
    {
        $validColors = [
            '#fff',
            '#ffffff',
            'rgb(255, 0, 0)',
            'rgba(255, 0, 0, 0.5)',
            'hsl(0, 100%, 50%)',
            'transparent',
        ];

        foreach ($validColors as $color) {
            $this->assertTrue($this->isValidColor($color), "Color $color should be valid");
        }
    }

    /**
     * Helper to validate color
     */
    protected function isValidColor(string $color): bool
    {
        if (empty($color)) {
            return false;
        }
        if (preg_match('/^#([a-fA-F0-9]{3}){1,2}$/', $color)) {
            return true;
        }
        if (preg_match('/^rgba?\(/', $color)) {
            return true;
        }
        if (in_array(strtolower($color), ['transparent', 'currentcolor', 'inherit'])) {
            return true;
        }
        return false;
    }

    /**
     * Test gradient type options
     */
    public function testGetGradientTypeOptions()
    {
        $options = $this->control->getGradientTypeOptions();

        $this->assertArrayHasKey('linear', $options);
        $this->assertArrayHasKey('radial', $options);
    }

    /**
     * Test schema contains all expected keys
     */
    public function testCompleteSchema()
    {
        $schema = $this->control->getSchema();
        $expected = [
            'colorType',
            'solidColor',
            'solidOpacity',
            'gradientType',
            'gradientAngle',
            'gradientColors',
            'useThemeColor',
            'themeColorKey',
            'cssVar',
        ];

        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $schema, "Schema missing key: $key");
        }
    }
}
