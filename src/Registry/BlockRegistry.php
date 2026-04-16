<?php

namespace Jankx\Gutenberg\Registry;

use Jankx\Gutenberg\Blocks\AbstractBlockWithControls;

/**
 * Central registry for all Jankx blocks with custom controls
 *
 * Handles block registration, asset management, and coordination
 * between PHP control definitions and React editor components.
 *
 * @package Jankx\Gutenberg\Registry
 * @since 1.0.0
 */
class BlockRegistry
{
    /**
     * Singleton instance
     *
     * @var self
     */
    private static $instance;

    /**
     * Registered blocks
     *
     * @var AbstractBlockWithControls[]
     */
    private $blocks = [];

    /**
     * Asset handles for enqueuing
     *
     * @var array
     */
    private $editorAssets = [];

    /**
     * @var array
     */
    private $frontendAssets = [];

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Private constructor
     */
    private function __construct()
    {
        $this->init();
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    protected function init(): void
    {
        // Register blocks on init
        add_action('init', [$this, 'registerBlocks'], 20);

        // Enqueue editor assets
        add_action('enqueue_block_editor_assets', [$this, 'enqueueEditorAssets']);

        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);

        // Filter block registration
        add_filter('register_block_type_args', [$this, 'filterBlockArgs'], 10, 2);
    }

    /**
     * Register a block
     *
     * @param AbstractBlockWithControls $block
     * @return self
     */
    public function register(AbstractBlockWithControls $block): self
    {
        $this->blocks[$block->getBlockName()] = $block;
        return $this;
    }

    /**
     * Get registered block
     *
     * @param string $name
     * @return AbstractBlockWithControls|null
     */
    public function get(string $name): ?AbstractBlockWithControls
    {
        return $this->blocks[$name] ?? null;
    }

    /**
     * Get all registered blocks
     *
     * @return array
     */
    public function all(): array
    {
        return $this->blocks;
    }

    /**
     * Register all blocks with WordPress
     *
     * @return void
     */
    public function registerBlocks(): void
    {
        foreach ($this->blocks as $block) {
            $block->register();
        }
    }

    /**
     * Enqueue shared editor assets
     *
     * @return void
     */
    public function enqueueEditorAssets(): void
    {
        // Core editor script
        wp_enqueue_script(
            'jankx-block-controls-editor',
            plugins_url('assets/dist/editor.js', __DIR__),
            [
                'wp-blocks',
                'wp-block-editor',
                'wp-components',
                'wp-compose',
                'wp-hooks',
                'wp-i18n',
                'wp-element',
                'wp-data',
            ],
            '1.0.0',
            true
        );

        // Editor styles
        wp_enqueue_style(
            'jankx-block-controls-editor',
            plugins_url('assets/dist/editor.css', __DIR__),
            ['wp-edit-blocks'],
            '1.0.0'
        );

        // Localize block configurations
        wp_localize_script(
            'jankx-block-controls-editor',
            'jankxBlocks',
            $this->getBlockConfigurations()
        );

        // Enqueue individual control assets
        foreach ($this->blocks as $block) {
            $block->enqueueEditorAssets();
        }
    }

    /**
     * Enqueue shared frontend assets
     *
     * @return void
     */
    public function enqueueFrontendAssets(): void
    {
        // Frontend animation engine
        wp_enqueue_script(
            'jankx-frontend',
            plugins_url('assets/dist/frontend.js', __DIR__),
            [],
            '1.0.0',
            true
        );

        // Frontend styles
        wp_enqueue_style(
            'jankx-frontend',
            plugins_url('assets/dist/frontend.css', __DIR__),
            [],
            '1.0.0'
        );

        // Enqueue individual block frontend assets
        foreach ($this->blocks as $block) {
            $block->enqueueFrontendAssets();
        }
    }

    /**
     * Filter block type args
     *
     * @param array $args
     * @param string $block_type
     * @return array
     */
    public function filterBlockArgs(array $args, string $block_type): array
    {
        $block = $this->get($block_type);

        if ($block) {
            // Add Jankx-specific attributes
            if (!isset($args['attributes'])) {
                $args['attributes'] = [];
            }

            $args['attributes']['jankxControls'] = [
                'type' => 'object',
                'default' => [],
            ];
        }

        return $args;
    }

    /**
     * Get configurations for all blocks
     *
     * @return array
     */
    protected function getBlockConfigurations(): array
    {
        $configs = [];

        foreach ($this->blocks as $block) {
            $configs[] = $block->getBlockConfig();
        }

        return [
            'blocks' => $configs,
            'categories' => $this->getBlockCategories(),
            'controls' => $this->getAllControlSchemas(),
        ];
    }

    /**
     * Get custom block categories
     *
     * @return array
     */
    protected function getBlockCategories(): array
    {
        return [
            [
                'slug' => 'jankx',
                'title' => __('Jankx Elements', 'jankx'),
                'icon' => 'smiley',
            ],
            [
                'slug' => 'jankx-layout',
                'title' => __('Jankx Layout', 'jankx'),
                'icon' => 'layout',
            ],
            [
                'slug' => 'jankx-media',
                'title' => __('Jankx Media', 'jankx'),
                'icon' => 'format-image',
            ],
            [
                'slug' => 'jankx-ecommerce',
                'title' => __('Jankx Ecommerce', 'jankx'),
                'icon' => 'cart',
            ],
        ];
    }

    /**
     * Get all control schemas
     *
     * @return array
     */
    protected function getAllControlSchemas(): array
    {
        $schemas = [];

        foreach ($this->blocks as $block) {
            $blockConfig = $block->getBlockConfig();
            $schemas[$block->getBlockName()] = $blockConfig['controls'] ?? [];
        }

        return $schemas;
    }

    /**
     * Add block category
     *
     * @param array $categories
     * @return array
     */
    public static function addBlockCategories(array $categories): array
    {
        return array_merge(
            $categories,
            [
                [
                    'slug' => 'jankx',
                    'title' => __('Jankx Elements', 'jankx'),
                    'icon' => null,
                ],
                [
                    'slug' => 'jankx-layout',
                    'title' => __('Jankx Layout', 'jankx'),
                    'icon' => null,
                ],
            ]
        );
    }
}
