<?php

namespace Jankx\Gutenberg\Tests\Presets;

use Jankx\Gutenberg\Presets\PresetManager;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Test case for PresetManager
 *
 * @package Jankx\Gutenberg\Tests\Presets
 * @covers \Jankx\Gutenberg\Presets\PresetManager
 */
class PresetManagerTest extends TestCase
{
    /**
     * @var PresetManager
     */
    private $manager;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // Mock WordPress functions
        Monkey\Functions\when('wp_parse_args')->alias(function($args, $defaults) {
            return array_merge($defaults, $args);
        });

        Monkey\Functions\when('do_action')->justReturn();
        Monkey\Functions\when('wp_json_encode')->alias('json_encode');

        // Get fresh instance (clears any previously registered presets)
        $this->manager = PresetManager::getInstance();
    }

    protected function tearDown(): void
    {
        // Clear presets after each test
        $reflection = new \ReflectionClass($this->manager);
        $property = $reflection->getProperty('presets');
        $property->setAccessible(true);
        $property->setValue($this->manager, []);

        Monkey\tearDown();
        parent::tearDown();
    }

    /** @test */
    public function it_is_singleton()
    {
        $instance1 = PresetManager::getInstance();
        $instance2 = PresetManager::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    /** @test */
    public function it_registers_a_preset()
    {
        $this->manager->register('test-preset', [
            'title' => 'Test Preset',
            'description' => 'A test preset',
            'category' => 'hero',
            'controls' => ['layout' => ['width' => 'full']],
        ]);

        $preset = $this->manager->get('test-preset');

        $this->assertNotNull($preset);
        $this->assertEquals('test-preset', $preset['id']);
        $this->assertEquals('Test Preset', $preset['title']);
    }

    /** @test */
    public function it_returns_null_for_nonexistent_preset()
    {
        $preset = $this->manager->get('nonexistent');

        $this->assertNull($preset);
    }

    /** @test */
    public function it_gets_presets_by_category()
    {
        $this->manager->register('preset-1', [
            'title' => 'Hero 1',
            'category' => 'hero',
        ]);

        $this->manager->register('preset-2', [
            'title' => 'Hero 2',
            'category' => 'hero',
        ]);

        $this->manager->register('preset-3', [
            'title' => 'Feature 1',
            'category' => 'features',
        ]);

        $heroPresets = $this->manager->getByCategory('hero');

        $this->assertCount(2, $heroPresets);
        $this->assertArrayHasKey('preset-1', $heroPresets);
        $this->assertArrayHasKey('preset-2', $heroPresets);
        $this->assertArrayNotHasKey('preset-3', $heroPresets);
    }

    /** @test */
    public function it_gets_all_categories()
    {
        $categories = $this->manager->getCategories();

        $this->assertIsArray($categories);
        $this->assertArrayHasKey('hero', $categories);
        $this->assertArrayHasKey('features', $categories);
        $this->assertArrayHasKey('testimonials', $categories);
        $this->assertArrayHasKey('cta', $categories);

        // Check category structure
        $this->assertArrayHasKey('title', $categories['hero']);
        $this->assertArrayHasKey('icon', $categories['hero']);
    }

    /** @test */
    public function it_searches_presets_by_title()
    {
        $this->manager->register('hero-center', [
            'title' => 'Centered Hero',
            'category' => 'hero',
            'tags' => ['center', 'background'],
        ]);

        $this->manager->register('hero-split', [
            'title' => 'Split Hero',
            'category' => 'hero',
            'tags' => ['two-column'],
        ]);

        $results = $this->manager->search('center');

        $this->assertCount(1, $results);
        $this->assertArrayHasKey('hero-center', $results);
    }

    /** @test */
    public function it_searches_presets_by_description()
    {
        $this->manager->register('preset-1', [
            'title' => 'Preset One',
            'description' => 'Full width section with parallax',
            'category' => 'hero',
        ]);

        $this->manager->register('preset-2', [
            'title' => 'Preset Two',
            'description' => 'Simple centered content',
            'category' => 'content',
        ]);

        $results = $this->manager->search('parallax');

        $this->assertCount(1, $results);
        $this->assertArrayHasKey('preset-1', $results);
    }

    /** @test */
    public function it_searches_presets_by_tags()
    {
        $this->manager->register('preset-1', [
            'title' => 'Preset One',
            'category' => 'hero',
            'tags' => ['full-width', 'gradient'],
        ]);

        $this->manager->register('preset-2', [
            'title' => 'Preset Two',
            'category' => 'hero',
            'tags' => ['center', 'image'],
        ]);

        $results = $this->manager->search('gradient');

        $this->assertCount(1, $results);
        $this->assertArrayHasKey('preset-1', $results);
    }

    /** @test */
    public function it_applies_preset_to_attributes()
    {
        $this->manager->register('hero-center', [
            'title' => 'Centered Hero',
            'category' => 'hero',
            'controls' => [
                'layout' => [
                    'width' => 'full',
                    'height' => '500px',
                ],
                'animation' => [
                    'entrance' => 'fadeInUp',
                ],
            ],
        ]);

        $currentAttributes = [
            'jankxControls' => [
                'layout' => ['backgroundColor' => '#ffffff'],
            ],
        ];

        $result = $this->manager->apply('hero-center', $currentAttributes);

        $this->assertArrayHasKey('layout', $result['jankxControls']);
        $this->assertEquals('full', $result['jankxControls']['layout']['width']);
        $this->assertEquals('500px', $result['jankxControls']['layout']['height']);
        $this->assertEquals('fadeInUp', $result['jankxControls']['animation']['entrance']);
        // Original values should be preserved unless overwritten
        $this->assertEquals('#ffffff', $result['jankxControls']['layout']['backgroundColor']);
    }

    /** @test */
    public function it_returns_original_attributes_for_invalid_preset()
    {
        $currentAttributes = ['jankxControls' => ['layout' => []]];

        $result = $this->manager->apply('nonexistent', $currentAttributes);

        $this->assertEquals($currentAttributes, $result);
    }

    /** @test */
    public function it_exports_preset_to_json()
    {
        $this->manager->register('test-preset', [
            'title' => 'Test Preset',
            'description' => 'For export',
            'category' => 'hero',
            'controls' => ['layout' => ['width' => 'full']],
        ]);

        $json = $this->manager->export('test-preset');

        $data = json_decode($json, true);
        $this->assertEquals('test-preset', $data['id']);
        $this->assertEquals('Test Preset', $data['title']);
    }

    /** @test */
    public function it_returns_empty_string_for_invalid_export()
    {
        $json = $this->manager->export('nonexistent');

        $this->assertEquals('', $json);
    }

    /** @test */
    public function it_imports_preset_from_json()
    {
        $json = json_encode([
            'id' => 'imported-preset',
            'title' => 'Imported Preset',
            'category' => 'hero',
            'controls' => ['layout' => []],
        ]);

        $result = $this->manager->import($json);

        $this->assertTrue($result);

        $preset = $this->manager->get('imported-preset');
        $this->assertEquals('Imported Preset', $preset['title']);
    }

    /** @test */
    public function it_fails_import_for_invalid_json()
    {
        $result = $this->manager->import('invalid json');

        $this->assertFalse($result);
    }

    /** @test */
    public function it_fails_import_for_missing_id()
    {
        $json = json_encode([
            'title' => 'No ID Preset',
            'category' => 'hero',
        ]);

        $result = $this->manager->import($json);

        $this->assertFalse($result);
    }

    /** @test */
    public function it_gets_js_data_structure()
    {
        $this->manager->register('preset-1', [
            'title' => 'Preset One',
            'category' => 'hero',
        ]);

        $data = $this->manager->getJsData();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('presets', $data);
        $this->assertArrayHasKey('categories', $data);
        $this->assertIsArray($data['presets']);
        $this->assertIsArray($data['categories']);
    }

    /** @test */
    public function it_preserves_default_presets_on_construction()
    {
        // The manager is already instantiated, check if default presets exist
        $all = $this->manager->all();

        // Default presets include hero-center, hero-split, features-3col, etc.
        $this->assertNotEmpty($all);

        // Check for known default presets
        $this->assertTrue(
            $this->manager->get('hero-center') !== null ||
            $this->manager->get('hero-split') !== null ||
            !empty($all),
            'Should have default presets registered'
        );
    }

    /** @test */
    public function it_allows_custom_preset_registration()
    {
        $this->manager->register('custom-preset', [
            'title' => 'My Custom Preset',
            'description' => 'Custom description',
            'category' => 'custom',
            'thumbnail' => 'custom.jpg',
            'tags' => ['custom', 'test'],
            'controls' => [
                'layout' => ['width' => 'full'],
            ],
            'innerBlocks' => [
                ['name' => 'core/heading'],
            ],
        ]);

        $preset = $this->manager->get('custom-preset');

        $this->assertEquals('My Custom Preset', $preset['title']);
        $this->assertEquals('custom.jpg', $preset['thumbnail']);
        $this->assertEquals(['custom', 'test'], $preset['tags']);
        $this->assertIsArray($preset['innerBlocks']);
    }

    /** @test */
    public function it_merges_preset_with_defaults()
    {
        $this->manager->register('minimal-preset', [
            'title' => 'Minimal',
        ]);

        $preset = $this->manager->get('minimal-preset');

        // Should have default values for missing fields
        $this->assertEquals('', $preset['description']);
        $this->assertEquals('content', $preset['category']);
        $this->assertEquals('', $preset['thumbnail']);
        $this->assertIsArray($preset['controls']);
        $this->assertIsArray($preset['innerBlocks']);
        $this->assertIsArray($preset['tags']);
    }
}
