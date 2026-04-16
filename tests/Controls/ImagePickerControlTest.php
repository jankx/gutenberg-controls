<?php

namespace Jankx\Gutenberg\Tests\Controls;

use Jankx\Gutenberg\Controls\Media\ImagePickerControl;
use Brain\Monkey;

/**
 * Test Image Picker Control functionality
 *
 * @package Jankx\Gutenberg\Tests\Controls
 */
class ImagePickerControlTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ImagePickerControl
     */
    protected $control;

    /**
     * Setup test
     */
    protected function setUp(): void
    {
        parent::setUp();
        Monkey\Functions\stubTranslationFunctions();
        Monkey\Functions\when('wp_get_additional_image_sizes')->justReturn([
            'custom-size' => ['width' => 400, 'height' => 300, 'crop' => true],
        ]);
        $this->control = new ImagePickerControl(['name' => 'image']);
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
        $this->assertEquals('jankx/image', $this->control->getType());
    }

    /**
     * Test control category
     */
    public function testGetCategory()
    {
        $this->assertEquals('media', $this->control->getCategory());
    }

    /**
     * Test object fit CSS generation
     */
    public function testObjectFitCss()
    {
        $value = [
            'objectFit' => 'contain',
            'focalPoint' => ['x' => 0.5, 'y' => 0.5],
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('object-fit: contain', $css);
    }

    /**
     * Test default object fit not included
     */
    public function testDefaultObjectFitNotIncluded()
    {
        $value = [
            'objectFit' => 'cover',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        // Should not include object-fit: cover since it's the default
        $this->assertStringNotContainsString('object-fit', $css);
    }

    /**
     * Test focal point CSS
     */
    public function testFocalPointCss()
    {
        $value = [
            'focalPoint' => ['x' => 0.25, 'y' => 0.75],
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('object-position: 25% 75%', $css);
    }

    /**
     * Test center focal point not included
     */
    public function testCenterFocalPointNotIncluded()
    {
        $value = [
            'focalPoint' => ['x' => 0.5, 'y' => 0.5],
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertEmpty($css);
    }

    /**
     * Test overlay CSS generation
     */
    public function testOverlayCss()
    {
        $value = [
            'overlayColor' => '#000000',
            'overlayOpacity' => 50,
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertStringContainsString('::before', $css);
        $this->assertStringContainsString('position: absolute', $css);
        $this->assertStringContainsString('rgba(0, 0, 0, 0.5)', $css);
    }

    /**
     * Test no overlay returns no CSS
     */
    public function testNoOverlayReturnsNoCss()
    {
        $value = [
            'overlayColor' => '',
        ];

        $css = $this->control->generateCss($value, '.test-element');

        $this->assertEmpty($css);
    }

    /**
     * Test image attributes generation
     */
    public function testImageAttributes()
    {
        $value = [
            'alt' => 'Test image',
            'lazyLoad' => true,
            'srcset' => 'image-400.jpg 400w, image-800.jpg 800w',
        ];

        $attrs = $this->control->getImageAttributes($value);

        $this->assertArrayHasKey('alt', $attrs);
        $this->assertEquals('Test image', $attrs['alt']);
        $this->assertArrayHasKey('loading', $attrs);
        $this->assertEquals('lazy', $attrs['loading']);
        $this->assertArrayHasKey('srcset', $attrs);
        $this->assertArrayHasKey('sizes', $attrs);
    }

    /**
     * Test lazy loading disabled
     */
    public function testLazyLoadingDisabled()
    {
        $value = [
            'lazyLoad' => false,
        ];

        $attrs = $this->control->getImageAttributes($value);

        $this->assertArrayNotHasKey('loading', $attrs);
    }

    /**
     * Test object fit options
     */
    public function testGetObjectFitOptions()
    {
        $options = $this->control->getObjectFitOptions();

        $this->assertArrayHasKey('cover', $options);
        $this->assertArrayHasKey('contain', $options);
        $this->assertArrayHasKey('fill', $options);
        $this->assertArrayHasKey('none', $options);
        $this->assertArrayHasKey('scale-down', $options);
    }

    /**
     * Test link options
     */
    public function testGetLinkOptions()
    {
        $options = $this->control->getLinkOptions();

        $this->assertArrayHasKey('none', $options);
        $this->assertArrayHasKey('media', $options);
        $this->assertArrayHasKey('custom', $options);
    }

    /**
     * Test image size options
     */
    public function testGetImageSizeOptions()
    {
        $options = $this->control->getImageSizeOptions();

        $this->assertArrayHasKey('thumbnail', $options);
        $this->assertArrayHasKey('medium', $options);
        $this->assertArrayHasKey('large', $options);
        $this->assertArrayHasKey('full', $options);
        // Custom size from mock
        $this->assertArrayHasKey('custom-size', $options);
    }

    /**
     * Test sanitize URL
     */
    public function testSanitizeUrl()
    {
        $value = [
            'url' => 'https://example.com/image.jpg',
        ];

        Monkey\Functions\when('esc_url_raw')
            ->returnArg();

        $sanitized = $this->control->sanitize($value);

        $this->assertArrayHasKey('url', $sanitized);
    }

    /**
     * Test sanitize focal point bounds
     */
    public function testSanitizeFocalPointBounds()
    {
        $value = [
            'focalPoint' => ['x' => 1.5, 'y' => -0.5],
        ];

        $sanitized = $this->control->sanitize($value);

        $this->assertEquals(1, $sanitized['focalPoint']['x']);
        $this->assertEquals(0, $sanitized['focalPoint']['y']);
    }

    /**
     * Test default attributes
     */
    public function testDefaultAttributes()
    {
        $schema = $this->control->getSchema();

        $this->assertArrayHasKey('id', $schema);
        $this->assertArrayHasKey('url', $schema);
        $this->assertArrayHasKey('alt', $schema);
        $this->assertArrayHasKey('size', $schema);
        $this->assertEquals('large', $schema['size']['default']);
        $this->assertArrayHasKey('objectFit', $schema);
        $this->assertEquals('cover', $schema['objectFit']['default']);
        $this->assertArrayHasKey('lazyLoad', $schema);
        $this->assertTrue($schema['lazyLoad']['default']);
    }

    /**
     * Test complete schema
     */
    public function testCompleteSchema()
    {
        $schema = $this->control->getSchema();
        $expected = [
            'id',
            'url',
            'alt',
            'size',
            'focalPoint',
            'objectFit',
            'lazyLoad',
            'lightbox',
            'linkTo',
            'overlayColor',
            'overlayOpacity',
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
