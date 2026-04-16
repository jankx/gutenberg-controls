<?php

namespace Jankx\Gutenberg\Tests\Controls;

use Jankx\Gutenberg\Controls\Layout\RowControl;
use Brain\Monkey;

/**
 * Test Row Control functionality
 *
 * @package Jankx\Gutenberg\Tests\Controls
 */
class RowControlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var RowControl
     */
    protected $control;

    /**
     * Setup test
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\Functions\stubTranslationFunctions();
        $this->control = new RowControl(['name' => 'row']);
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
        $this->assertEquals('jankx/row', $this->control->getType());
    }

    /**
     * Test control category
     */
    public function testGetCategory()
    {
        $this->assertEquals('layout', $this->control->getCategory());
    }

    /**
     * Test basic grid CSS generation
     */
    public function testBasicGridCss()
    {
        $value = [
            'columns' => 3,
            'gap' => '30px',
        ];

        $css = $this->control->generateCss($value, '.test-row');

        $this->assertStringContainsString('display: grid', $css);
        $this->assertStringContainsString('grid-template-columns: repeat(3, 1fr)', $css);
        $this->assertStringContainsString('gap: 30px', $css);
    }

    /**
     * Test grid with custom row gap
     */
    public function testGridWithRowGap()
    {
        $value = [
            'columns' => 2,
            'gap' => '20px',
            'rowGap' => '40px',
        ];

        $css = $this->control->generateCss($value, '.test-row');

        $this->assertStringContainsString('gap: 20px 40px', $css);
    }

    /**
     * Test responsive columns CSS
     */
    public function testResponsiveColumnsCss()
    {
        $value = [
            'columns' => 4,
            'columnsTablet' => 2,
            'columnsMobile' => 1,
        ];

        $css = $this->control->generateCss($value, '.test-row');

        // Desktop: 4 columns
        $this->assertStringContainsString('repeat(4, 1fr)', $css);

        // Tablet: 2 columns
        $this->assertStringContainsString('@media (max-width: 1024px)', $css);
        $this->assertStringContainsString('repeat(2, 1fr)', $css);

        // Mobile: 1 column
        $this->assertStringContainsString('@media (max-width: 767px)', $css);
        $this->assertStringContainsString('repeat(1, 1fr)', $css);
    }

    /**
     * Test responsive gap CSS
     */
    public function testResponsiveGapCss()
    {
        $value = [
            'columns' => 3,
            'gap' => '40px',
            'gapTablet' => '30px',
            'gapMobile' => '20px',
        ];

        $css = $this->control->generateCss($value, '.test-row');

        $this->assertStringContainsString('gap: 40px', $css);
        $this->assertStringContainsString('@media (max-width: 1024px)', $css);
        $this->assertStringContainsString('gap: 30px', $css);
        $this->assertStringContainsString('@media (max-width: 767px)', $css);
        $this->assertStringContainsString('gap: 20px', $css);
    }

    /**
     * Test alignment CSS
     */
    public function testAlignmentCss()
    {
        $value = [
            'alignItems' => 'center',
            'justifyContent' => 'space-between',
        ];

        $css = $this->control->generateCss($value, '.test-row');

        $this->assertStringContainsString('align-items: center', $css);
        $this->assertStringContainsString('justify-content: space-between', $css);
    }

    /**
     * Test default alignment not included
     */
    public function testDefaultAlignmentNotIncluded()
    {
        $value = [
            'alignItems' => 'stretch',
            'justifyContent' => 'flex-start',
        ];

        $css = $this->control->generateCss($value, '.test-row');

        // Only grid CSS, no alignment
        $this->assertStringNotContainsString('align-items', $css);
        $this->assertStringNotContainsString('justify-content', $css);
    }

    /**
     * Test column options
     */
    public function testGetColumnOptions()
    {
        $options = $this->control->getColumnOptions();

        $this->assertArrayHasKey(1, $options);
        $this->assertArrayHasKey(2, $options);
        $this->assertArrayHasKey(3, $options);
        $this->assertArrayHasKey(4, $options);
        $this->assertArrayHasKey(5, $options);
        $this->assertArrayHasKey(6, $options);
    }

    /**
     * Test gap presets
     */
    public function testGetGapPresets()
    {
        $presets = $this->control->getGapPresets();

        $this->assertIsArray($presets);
        $this->assertNotEmpty($presets);

        $values = array_column($presets, 'value');
        $this->assertContains('0', $values);
        $this->assertContains('30px', $values);
    }

    /**
     * Test align items options
     */
    public function testGetAlignItemsOptions()
    {
        $options = $this->control->getAlignItemsOptions();

        $this->assertArrayHasKey('stretch', $options);
        $this->assertArrayHasKey('flex-start', $options);
        $this->assertArrayHasKey('flex-end', $options);
        $this->assertArrayHasKey('center', $options);
    }

    /**
     * Test justify content options
     */
    public function testGetJustifyContentOptions()
    {
        $options = $this->control->getJustifyContentOptions();

        $this->assertArrayHasKey('flex-start', $options);
        $this->assertArrayHasKey('flex-end', $options);
        $this->assertArrayHasKey('center', $options);
        $this->assertArrayHasKey('space-between', $options);
        $this->assertArrayHasKey('space-around', $options);
        $this->assertArrayHasKey('space-evenly', $options);
    }

    /**
     * Test column width calculation
     */
    public function testGetColumnWidth()
    {
        $width = $this->control->getColumnWidth(2);
        $this->assertEquals('50%', $width);

        $width = $this->control->getColumnWidth(3);
        $this->assertEquals('33.3333%', $width);

        $width = $this->control->getColumnWidth(4);
        $this->assertEquals('25%', $width);
    }

    /**
     * Test zero columns handled
     */
    public function testZeroColumnsHandled()
    {
        $width = $this->control->getColumnWidth(0);
        $this->assertEquals('100%', $width);
    }

    /**
     * Test default attributes
     */
    public function testDefaultAttributes()
    {
        $schema = $this->control->getSchema();

        $this->assertArrayHasKey('columns', $schema);
        $this->assertEquals(2, $schema['columns']['default']);

        $this->assertArrayHasKey('columnsMobile', $schema);
        $this->assertEquals(1, $schema['columnsMobile']['default']);

        $this->assertArrayHasKey('gap', $schema);
        $this->assertEquals('30px', $schema['gap']['default']);

        $this->assertArrayHasKey('alignItems', $schema);
        $this->assertEquals('stretch', $schema['alignItems']['default']);
    }

    /**
     * Test complete schema
     */
    public function testCompleteSchema()
    {
        $schema = $this->control->getSchema();
        $expected = [
            'columns',
            'columnsTablet',
            'columnsMobile',
            'gap',
            'gapTablet',
            'gapMobile',
            'rowGap',
            'alignItems',
            'justifyContent',
            'masonry',
            'equalHeight',
        ];

        foreach ($expected as $key) {
            $this->assertArrayHasKey($key, $schema, "Schema missing: $key");
        }
    }

    /**
     * Test empty value returns CSS with defaults
     */
    public function testEmptyValueReturnsDefaultCss()
    {
        $value = [];

        $css = $this->control->generateCss($value, '.test-row');

        // Should have default 2 columns with 30px gap
        $this->assertStringContainsString('grid-template-columns: repeat(2, 1fr)', $css);
        $this->assertStringContainsString('gap: 30px', $css);
    }
}
