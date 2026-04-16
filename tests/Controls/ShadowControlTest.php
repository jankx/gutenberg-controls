<?php

namespace Jankx\Gutenberg\Tests\Controls;

use Jankx\Gutenberg\Controls\Style\ShadowControl;
use Brain\Monkey;

/**
 * Test Shadow Control functionality
 *
 * @package Jankx\Gutenberg\Tests\Controls
 */
class ShadowControlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ShadowControl
     */
    protected $control;

    /**
     * Setup test
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\Functions\stubTranslationFunctions();
        $this->control = new ShadowControl(['name' => 'shadow']);
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
        $this->assertEquals('jankx/shadow', $this->control->getType());
    }

    /**
     * Test control category
     */
    public function testGetCategory()
    {
        $this->assertEquals('style', $this->control->getCategory());
    }

    /**
     * Test preset shadow CSS
     */
    public function testPresetShadowCss()
    {
        $value = [
            'shadowType' => 'md',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('box-shadow:', $css);
        $this->assertStringContainsString('0 4px 6px', $css);
    }

    /**
     * Test none shadow type
     */
    public function testNoneShadowType()
    {
        $value = [
            'shadowType' => 'none',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertEmpty($css);
    }

    /**
     * Test custom shadow CSS
     */
    public function testCustomShadowCss()
    {
        $value = [
            'shadowType' => 'custom',
            'shadowX' => 0,
            'shadowY' => 4,
            'shadowBlur' => 6,
            'shadowSpread' => 2,
            'shadowColor' => '#000000',
            'shadowOpacity' => 20,
            'shadowInset' => false,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('box-shadow:', $css);
        $this->assertStringContainsString('0px 4px 6px 2px', $css);
    }

    /**
     * Test custom shadow with inset
     */
    public function testCustomShadowInset()
    {
        $value = [
            'shadowType' => 'custom',
            'shadowX' => 0,
            'shadowY' => 2,
            'shadowBlur' => 4,
            'shadowSpread' => 0,
            'shadowInset' => true,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('inset', $css);
    }

    /**
     * Test hover shadow CSS
     */
    public function testHoverShadowCss()
    {
        $value = [
            'shadowType' => 'sm',
            'hoverShadow' => 'lg',
            'hoverTransition' => 300,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('transition: box-shadow', $css);
        $this->assertStringContainsString('300ms', $css);
        $this->assertStringContainsString('.test-element:hover', $css);
        $this->assertStringContainsString('0 10px 15px', $css); // lg shadow
    }

    /**
     * Test multiple shadows
     */
    public function testMultipleShadows()
    {
        $value = [
            'shadows' => [
                [
                    'enabled' => true,
                    'x' => 0,
                    'y' => 2,
                    'blur' => 4,
                    'spread' => 0,
                    'color' => 'rgba(0,0,0,0.1)',
                ],
                [
                    'enabled' => true,
                    'x' => 0,
                    'y' => 4,
                    'blur' => 8,
                    'spread' => 0,
                    'color' => 'rgba(0,0,0,0.05)',
                ],
            ],
        ];

        $css = $this->control->generateCss($value, '.test-element');

        // Multiple shadows separated by comma
        $this->assertStringContainsString('rgba(0,0,0,0.1)', $css);
        $this->assertStringContainsString('rgba(0,0,0,0.05)', $css);
    }

    /**
     * Test disabled shadow not included
     */
    public function testDisabledShadowNotIncluded()
    {
        $value = [
            'shadows' => [
                [
                    'enabled' => false,
                    'x' => 0,
                    'y' => 2,
                    'blur' => 4,
                ],
            ],
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertEmpty($css);
    }

    /**
     * Test shadow presets available
     */
    public function testShadowPresetsAvailable()
    {
        $presets = $this->control->getPresets();

        $this->assertArrayHasKey('none', $presets);
        $this->assertArrayHasKey('xs', $presets);
        $this->assertArrayHasKey('sm', $presets);
        $this->assertArrayHasKey('md', $presets);
        $this->assertArrayHasKey('lg', $presets);
        $this->assertArrayHasKey('xl', $presets);
        $this->assertArrayHasKey('2xl', $presets);
        $this->assertArrayHasKey('inner', $presets);
        $this->assertArrayHasKey('floating', $presets);
        $this->assertArrayHasKey('colored', $presets);
    }

    /**
     * Test preset options
     */
    public function testGetPresetOptions()
    {
        $options = $this->control->getPresetOptions();

        $this->assertArrayHasKey('none', $options);
        $this->assertArrayHasKey('md', $options);
        $this->assertIsString($options['md']);
    }

    /**
     * Test transition presets
     */
    public function testGetTransitionPresets()
    {
        $presets = $this->control->getTransitionPresets();

        $this->assertArrayHasKey(150, $presets);
        $this->assertArrayHasKey(300, $presets);
        $this->assertArrayHasKey(500, $presets);
    }

    /**
     * Test intensity options
     */
    public function testGetIntensityOptions()
    {
        $options = $this->control->getIntensityOptions();

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);
    }

    /**
     * Test hex to rgba conversion
     */
    public function testHexToRgbaConversion()
    {
        $value = [
            'shadowType' => 'custom',
            'shadowColor' => '#ffffff',
            'shadowOpacity' => 50,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('rgba(255, 255, 255, 0.5)', $css);
    }

    /**
     * Test complete schema
     */
    public function testCompleteSchema()
    {
        $schema = $this->control->getSchema();
        $expected = [
            'shadowType',
            'customShadow',
            'shadowColor',
            'shadowOpacity',
            'shadowX',
            'shadowY',
            'shadowBlur',
            'shadowSpread',
            'shadowInset',
            'shadows',
            'hoverShadow',
            'hoverTransition',
        ];

        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $schema, "Schema missing: $key");
        }
    }

    /**
     * Test empty value returns no CSS
     */
    public function testEmptyValueReturnsNoCss()
    {
        $value = [];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertEmpty($css);
    }
}
