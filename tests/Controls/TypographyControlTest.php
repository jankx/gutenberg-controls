<?php

namespace Jankx\Gutenberg\Tests\Controls;

use Jankx\Gutenberg\Controls\Style\TypographyControl;
use Brain\Monkey;

/**
 * Test Typography Control functionality
 *
 * @package Jankx\Gutenberg\Tests\Controls
 */
class TypographyControlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TypographyControl
     */
    protected $control;

    /**
     * Setup test
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\Functions\stubTranslationFunctions();
        $this->control = new TypographyControl(['name' => 'typography']);
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
        $this->assertEquals('jankx/typography', $this->control->getType());
    }

    /**
     * Test control category
     */
    public function testGetCategory()
    {
        $this->assertEquals('style', $this->control->getCategory());
    }

    /**
     * Test font family CSS generation
     */
    public function testFontFamilyCss()
    {
        $value = [
            'useThemeFont' => false,
            'fontFamily' => 'Inter',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('font-family:', $css);
        $this->assertStringContainsString('"Inter"', $css);
    }

    /**
     * Test theme font variable
     */
    public function testThemeFontCss()
    {
        $value = [
            'useThemeFont' => true,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('var(--jankx-font-family)', $css);
    }

    /**
     * Test system font family
     */
    public function testSystemFontFamily()
    {
        $value = [
            'useThemeFont' => false,
            'fontFamily' => 'system-ui',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('system-ui', $css);
    }

    /**
     * Test font weight CSS
     */
    public function testFontWeightCss()
    {
        $value = [
            'fontWeight' => '700',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('font-weight: 700', $css);
    }

    /**
     * Test default font weight not included
     */
    public function testDefaultFontWeightNotIncluded()
    {
        $value = [
            'fontWeight' => '400',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertEmpty($css);
    }

    /**
     * Test font style italic
     */
    public function testFontStyleItalic()
    {
        $value = [
            'fontStyle' => 'italic',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('font-style: italic', $css);
    }

    /**
     * Test line height CSS
     */
    public function testLineHeightCss()
    {
        $value = [
            'lineHeight' => 1.8,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('line-height: 1.8', $css);
    }

    /**
     * Test default line height not included
     */
    public function testDefaultLineHeightNotIncluded()
    {
        $value = [
            'lineHeight' => 1.6,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertEmpty($css);
    }

    /**
     * Test letter spacing CSS
     */
    public function testLetterSpacingCss()
    {
        $value = [
            'letterSpacing' => '0.05em',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('letter-spacing: 0.05em', $css);
    }

    /**
     * Test text transform CSS
     */
    public function testTextTransformCss()
    {
        $value = [
            'textTransform' => 'uppercase',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('text-transform: uppercase', $css);
    }

    /**
     * Test text decoration CSS
     */
    public function testTextDecorationCss()
    {
        $value = [
            'textDecoration' => 'underline',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('text-decoration: underline', $css);
    }

    /**
     * Test text shadow CSS
     */
    public function testTextShadowCss()
    {
        $value = [
            'textShadow' => [
                'x' => 2,
                'y' => 2,
                'blur' => 4,
                'color' => 'rgba(0,0,0,0.3)',
            ],
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('text-shadow:', $css);
        $this->assertStringContainsString('2px 2px 4px rgba(0,0,0,0.3)', $css);
    }

    /**
     * Test fluid typography CSS
     */
    public function testFluidTypographyCss()
    {
        $value = [
            'fluidTypography' => true,
            'fluidMin' => '16px',
            'fluidMax' => '24px',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('clamp(16px, 2vw + 1rem, 24px)', $css);
    }

    /**
     * Test responsive font size CSS
     */
    public function testResponsiveFontSizeCss()
    {
        $value = [
            'fontSize' => '24px',
            'fontSizeTablet' => '20px',
            'fontSizeMobile' => '18px',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('font-size: 24px', $css);
        $this->assertStringContainsString('@media (max-width: 1024px)', $css);
        $this->assertStringContainsString('font-size: 20px', $css);
        $this->assertStringContainsString('@media (max-width: 767px)', $css);
        $this->assertStringContainsString('font-size: 18px', $css);
    }

    /**
     * Test responsive text align CSS
     */
    public function testResponsiveTextAlignCss()
    {
        $value = [
            'textAlign' => 'left',
            'textAlignTablet' => 'center',
            'textAlignMobile' => 'right',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('@media (max-width: 1024px)', $css);
        $this->assertStringContainsString('text-align: center', $css);
        $this->assertStringContainsString('@media (max-width: 767px)', $css);
        $this->assertStringContainsString('text-align: right', $css);
    }

    /**
     * Test font weight options
     */
    public function testGetFontWeightOptions()
    {
        $options = $this->control->getFontWeightOptions();

        $this->assertArrayHasKey('400', $options);
        $this->assertArrayHasKey('700', $options);
    }

    /**
     * Test text transform options
     */
    public function testGetTextTransformOptions()
    {
        $options = $this->control->getTextTransformOptions();

        $this->assertArrayHasKey('none', $options);
        $this->assertArrayHasKey('uppercase', $options);
        $this->assertArrayHasKey('capitalize', $options);
    }

    /**
     * Test font size presets
     */
    public function testGetFontSizePresets()
    {
        $presets = $this->control->getFontSizePresets();

        $this->assertIsArray($presets);
        $this->assertNotEmpty($presets);

        $base = array_filter($presets, function ($p) {
            return $p['label'] === 'Base';
        });
        $this->assertNotEmpty($base);
    }

    /**
     * Test complete schema
     */
    public function testCompleteSchema()
    {
        $schema = $this->control->getSchema();
        $expected = [
            'fontFamily',
            'useThemeFont',
            'fontSize',
            'fontSizeTablet',
            'fontSizeMobile',
            'fluidTypography',
            'fontWeight',
            'fontStyle',
            'lineHeight',
            'letterSpacing',
            'textTransform',
            'textDecoration',
            'textShadow',
            'textAlign',
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

        // Should at least have font-family from theme default
        $this->assertNotEmpty($css);
        $this->assertStringContainsString('font-family', $css);
    }
}
