<?php

namespace Jankx\Gutenberg\Tests\Controls;

use Jankx\Gutenberg\Controls\Style\BorderControl;
use Brain\Monkey;

/**
 * Test Border Control functionality
 *
 * @package Jankx\Gutenberg\Tests\Controls
 */
class BorderControlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var BorderControl
     */
    protected $control;

    /**
     * Setup test
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\Functions\stubTranslationFunctions();
        $this->control = new BorderControl(['name' => 'border']);
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
        $this->assertEquals('jankx/border', $this->control->getType());
    }

    /**
     * Test control category
     */
    public function testGetCategory()
    {
        $this->assertEquals('style', $this->control->getCategory());
    }

    /**
     * Test border width shorthand CSS
     */
    public function testBorderWidthShorthand()
    {
        $value = [
            'borderWidth' => '2px',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('border-width: 2px', $css);
    }

    /**
     * Test individual border widths
     */
    public function testIndividualBorderWidths()
    {
        $value = [
            'borderTopWidth' => '1px',
            'borderBottomWidth' => '2px',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('border-top-width: 1px', $css);
        $this->assertStringContainsString('border-bottom-width: 2px', $css);
    }

    /**
     * Test border color CSS
     */
    public function testBorderColorCss()
    {
        $value = [
            'borderColor' => '#333333',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('border-color: #333333', $css);
    }

    /**
     * Test individual border colors
     */
    public function testIndividualBorderColors()
    {
        $value = [
            'borderTopColor' => '#ff0000',
            'borderBottomColor' => '#00ff00',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('border-top-color: #ff0000', $css);
        $this->assertStringContainsString('border-bottom-color: #00ff00', $css);
    }

    /**
     * Test border radius shorthand
     */
    public function testBorderRadiusShorthand()
    {
        $value = [
            'borderRadius' => '8px',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('border-radius: 8px', $css);
    }

    /**
     * Test linked radius mode
     */
    public function testLinkedRadiusMode()
    {
        $value = [
            'linkedRadius' => true,
            'borderTopLeftRadius' => '12px',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('border-radius: 12px', $css);
    }

    /**
     * Test unlinked radius with all corners
     */
    public function testUnlinkedRadiusAllCorners()
    {
        $value = [
            'linkedRadius' => false,
            'borderTopLeftRadius' => '4px',
            'borderTopRightRadius' => '8px',
            'borderBottomRightRadius' => '12px',
            'borderBottomLeftRadius' => '16px',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('border-radius: 4px 8px 12px 16px', $css);
    }

    /**
     * Test responsive border radius
     */
    public function testResponsiveBorderRadius()
    {
        $value = [
            'borderRadius' => '16px',
            'borderRadiusTablet' => '12px',
            'borderRadiusMobile' => '8px',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('border-radius: 16px', $css);
        $this->assertStringContainsString('@media (max-width: 1024px)', $css);
        $this->assertStringContainsString('border-radius: 12px', $css);
        $this->assertStringContainsString('@media (max-width: 767px)', $css);
        $this->assertStringContainsString('border-radius: 8px', $css);
    }

    /**
     * Test border style options
     */
    public function testGetBorderStyleOptions()
    {
        $options = $this->control->getBorderStyleOptions();

        $this->assertArrayHasKey('solid', $options);
        $this->assertArrayHasKey('dashed', $options);
        $this->assertArrayHasKey('dotted', $options);
        $this->assertArrayHasKey('double', $options);
    }

    /**
     * Test border presets
     */
    public function testGetBorderPresets()
    {
        $presets = $this->control->getBorderPresets();

        $this->assertArrayHasKey('none', $presets);
        $this->assertArrayHasKey('solid-thin', $presets);
        $this->assertArrayHasKey('solid-medium', $presets);
        $this->assertArrayHasKey('dashed', $presets);
    }

    /**
     * Test border width presets
     */
    public function testGetBorderWidthPresets()
    {
        $presets = $this->control->getBorderWidthPresets();

        $this->assertIsArray($presets);
        $this->assertNotEmpty($presets);

        $values = array_column($presets, 'value');
        $this->assertContains('0', $values);
        $this->assertContains('1px', $values);
        $this->assertContains('4px', $values);
    }

    /**
     * Test border radius presets
     */
    public function testGetBorderRadiusPresets()
    {
        $presets = $this->control->getBorderRadiusPresets();

        $this->assertIsArray($presets);
        $this->assertNotEmpty($presets);

        $values = array_column($presets, 'value');
        $this->assertContains('0', $values);
        $this->assertContains('4px', $values);
        $this->assertContains('9999px', $values); // Full
    }

    /**
     * Test empty border returns empty CSS
     */
    public function testEmptyBorderReturnsEmptyCss()
    {
        $value = [];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertEmpty($css);
    }

    /**
     * Test zero border width not included
     */
    public function testZeroBorderWidthNotIncluded()
    {
        $value = [
            'borderWidth' => '0',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertEmpty($css);
    }

    /**
     * Test complete schema
     */
    public function testCompleteSchema()
    {
        $schema = $this->control->getSchema();
        $expected = [
            'borderWidth',
            'borderTopWidth',
            'borderRightWidth',
            'borderBottomWidth',
            'borderLeftWidth',
            'borderStyle',
            'borderColor',
            'borderRadius',
            'borderRadiusTablet',
            'borderRadiusMobile',
            'linkedWidth',
            'linkedRadius',
        ];

        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $schema, "Schema missing: $key");
        }
    }
}
