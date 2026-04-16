<?php

namespace Jankx\Gutenberg\Tests\Registry;

use Jankx\Gutenberg\Registry\BlockRegistry;
use Jankx\Gutenberg\Blocks\AbstractBlockWithControls;
use Jankx\Gutenberg\Blocks\SectionBlock;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Test case for BlockRegistry
 *
 * @package Jankx\Gutenberg\Tests\Registry
 * @covers \Jankx\Gutenberg\Registry\BlockRegistry
 */
class BlockRegistryTest extends TestCase
{
    /**
     * @var BlockRegistry
     */
    private $registry;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        // Get fresh singleton instance
        $this->registry = BlockRegistry::getInstance();

        // Clear existing blocks
        $reflection = new \ReflectionClass($this->registry);
        $property = $reflection->getProperty('blocks');
        $property->setAccessible(true);
        $property->setValue($this->registry, []);
    }

    protected function tearDown(): void
    {
        // Cleanup after tests
        $reflection = new \ReflectionClass($this->registry);
        $property = $reflection->getProperty('blocks');
        $property->setAccessible(true);
        $property->setValue($this->registry, []);

        Monkey\tearDown();
        parent::tearDown();
    }

    /** @test */
    public function it_is_singleton()
    {
        $instance1 = BlockRegistry::getInstance();
        $instance2 = BlockRegistry::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    /** @test */
    public function it_registers_a_block()
    {
        $block = new SectionBlock();

        $this->registry->register($block);

        $registered = $this->registry->get('jankx/section');
        $this->assertSame($block, $registered);
    }

    /** @test */
    public function it_returns_null_for_unregistered_block()
    {
        $result = $this->registry->get('nonexistent/block');

        $this->assertNull($result);
    }

    /** @test */
    public function it_returns_all_registered_blocks()
    {
        $block1 = new SectionBlock();
        // Create a second block via mock
        $block2 = $this->createMockBlock('jankx/hero');

        $this->registry->register($block1);
        $this->registry->register($block2);

        $all = $this->registry->all();

        $this->assertCount(2, $all);
        $this->assertArrayHasKey('jankx/section', $all);
        $this->assertArrayHasKey('jankx/hero', $all);
    }

    /** @test */
    public function it_returns_empty_array_when_no_blocks_registered()
    {
        $all = $this->registry->all();

        $this->assertIsArray($all);
        $this->assertEmpty($all);
    }

    /** @test */
    public function it_returns_block_configurations_for_js()
    {
        $block = new SectionBlock();
        $this->registry->register($block);

        $reflection = new \ReflectionClass($this->registry);
        $method = $reflection->getMethod('getBlockConfigurations');
        $method->setAccessible(true);

        $config = $method->invoke($this->registry);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('blocks', $config);
        $this->assertArrayHasKey('categories', $config);
        $this->assertArrayHasKey('controls', $config);
    }

    /** @test */
    public function it_returns_custom_categories()
    {
        $reflection = new \ReflectionClass($this->registry);
        $method = $reflection->getMethod('getBlockCategories');
        $method->setAccessible(true);

        $categories = $method->invoke($this->registry);

        $this->assertIsArray($categories);

        $slugs = array_column($categories, 'slug');
        $this->assertContains('jankx', $slugs);
        $this->assertContains('jankx-layout', $slugs);
        $this->assertContains('jankx-media', $slugs);
        $this->assertContains('jankx-ecommerce', $slugs);
    }

    /** @test */
    public function it_returns_control_schemas_for_all_blocks()
    {
        $block = new SectionBlock();
        $this->registry->register($block);

        $reflection = new \ReflectionClass($this->registry);
        $method = $reflection->getMethod('getAllControlSchemas');
        $method->setAccessible(true);

        $schemas = $method->invoke($this->registry);

        $this->assertIsArray($schemas);
        $this->assertArrayHasKey('jankx/section', $schemas);
    }

    /** @test */
    public function it_registers_block_categories_statically()
    {
        $categories = [
            ['slug' => 'existing', 'title' => 'Existing'],
        ];

        $result = BlockRegistry::addBlockCategories($categories);

        $this->assertIsArray($result);
        $this->assertCount(3, $result); // original + 2 jankx categories

        $slugs = array_column($result, 'slug');
        $this->assertContains('jankx', $slugs);
        $this->assertContains('jankx-layout', $slugs);
    }

    /** @test */
    public function it_maintains_fluent_interface()
    {
        $block = new SectionBlock();

        $result = $this->registry->register($block);

        $this->assertSame($this->registry, $result);
    }

    /** @test */
    public function it_overwrites_existing_block_registration()
    {
        $block1 = new SectionBlock();
        $block2 = new SectionBlock(); // Another instance

        $this->registry->register($block1);
        $this->registry->register($block2);

        $registered = $this->registry->get('jankx/section');
        $this->assertSame($block2, $registered);
    }

    /** @test */
    public function it_initializes_hooks_in_constructor()
    {
        // Verify hooks are registered in init()
        $this->assertTrue(has_action('init'));
        $this->assertTrue(has_action('enqueue_block_editor_assets'));
        $this->assertTrue(has_action('wp_enqueue_scripts'));
    }

    /**
     * Helper to create a mock block
     */
    private function createMockBlock(string $name): AbstractBlockWithControls
    {
        return new class($name) extends AbstractBlockWithControls {
            private $mockName;

            public function __construct(string $name)
            {
                $this->mockName = $name;
                parent::__construct();
            }

            protected function getBlockName(): string
            {
                return $this->mockName;
            }

            protected function getBlockTitle(): string
            {
                return 'Mock Block';
            }

            protected function renderBlockContent(
                array $attributes,
                string $content,
                $block,
                array $jankxControls
            ): string {
                return '<div>Mock</div>';
            }
        };
    }
}
