<?php

namespace Jankx\Gutenberg\Blocks;

use Jankx\Gutenberg\Controls\AbstractControl;
use Jankx\Gutenberg\Registry\ControlRegistry;

/**
 * Abstract base class for blocks with custom controls
 *
 * Provides automatic control registration, attribute schema generation,
 * and frontend CSS generation for blocks using Jankx controls.
 *
 * @package Jankx\Gutenberg\Blocks
 * @since 1.0.0
 */
abstract class AbstractBlockWithControls
{
    /**
     * Block name
     *
     * @var string
     */
    protected $name;

    /**
     * Block title
     *
     * @var string
     */
    protected $title;

    /**
     * Block description
     *
     * @var string
     */
    protected $description;

    /**
     * Block category
     *
     * @var string
     */
    protected $category = 'jankx';

    /**
     * Block icon
     *
     * @var string|array
     */
    protected $icon = 'block-default';

    /**
     * Registered controls for this block
     *
     * @var AbstractControl[]
     */
    protected $controls = [];

    /**
     * Block supports
     *
     * @var array
     */
    protected $supports = [
        'anchor' => true,
        'align' => ['wide', 'full'],
        'html' => false,
        'multiple' => true,
        'reusable' => true,
        'lock' => false,
        'spacing' => [
            'padding' => true,
            'margin' => true,
        ],
    ];

    /**
     * Parent blocks (if any)
     *
     * @var array
     */
    protected $parent = [];

    /**
     * Allowed inner blocks
     *
     * @var array
     */
    protected $allowedBlocks = [];

    /**
     * Block script handle
     *
     * @var string
     */
    protected $scriptHandle;

    /**
     * Block style handle
     *
     * @var string
     */
    protected $styleHandle;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->name = $this->getBlockName();
        $this->title = $this->getBlockTitle();
        $this->registerControls();
    }

    /**
     * Get block name
     *
     * @return string
     */
    abstract protected function getBlockName(): string;

    /**
     * Get block title
     *
     * @return string
     */
    abstract protected function getBlockTitle(): string;

    /**
     * Register controls for this block
     *
     * Override in child class to add controls
     *
     * @return void
     */
    protected function registerControls(): void
    {
        // Override in child class
    }

    /**
     * Add a control to this block
     *
     * @param AbstractControl $control
     * @return $this
     */
    protected function addControl(AbstractControl $control): self
    {
        $this->controls[$control->getName()] = $control;
        return $this;
    }

    /**
     * Register the block with WordPress
     *
     * @return void
     */
    public function register(): void
    {
        // Register block type
        register_block_type($this->name, [
            'attributes' => $this->getAttributesSchema(),
            'render_callback' => [$this, 'render'],
            'supports' => $this->supports,
            'parent' => $this->parent ?: null,
            'allowed_blocks' => $this->allowedBlocks ?: null,
        ]);

        // Enqueue editor assets
        add_action('enqueue_block_editor_assets', [$this, 'enqueueEditorAssets']);

        // Enqueue frontend assets
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);

        // Generate dynamic CSS
        add_action('wp_head', [$this, 'outputDynamicCss'], 100);
    }

    /**
     * Get attributes schema from controls
     *
     * @return array
     */
    protected function getAttributesSchema(): array
    {
        $attributes = [];

        // Standard block attributes
        $attributes['jankxControls'] = [
            'type' => 'object',
            'default' => [],
        ];

        // Each control contributes its schema
        foreach ($this->controls as $name => $control) {
            $attributes['jankxControls'][$name] = $control->getSchema();
        }

        return $attributes;
    }

    /**
     * Render callback for the block
     *
     * @param array $attributes
     * @param string $content
     * @param WP_Block $block
     * @return string
     */
    public function render(array $attributes, string $content, $block): string
    {
        $jankxControls = $attributes['jankxControls'] ?? [];

        // Generate wrapper classes
        $classes = $this->getWrapperClasses($attributes, $jankxControls);

        // Generate inline styles
        $styles = $this->generateStyles($jankxControls);

        // Build wrapper attributes
        $wrapperAttr = [
            'class' => implode(' ', $classes),
        ];

        if ($styles) {
            $wrapperAttr['style'] = $styles;
        }

        // Render block content
        $blockContent = $this->renderBlockContent($attributes, $content, $block, $jankxControls);

        // Wrap content if needed
        if ($this->shouldWrapContent()) {
            $attrString = $this->buildAttributesString($wrapperAttr);
            return "<div {$attrString}>{$blockContent}</div>";
        }

        return $blockContent;
    }

    /**
     * Render inner block content
     *
     * @param array $attributes
     * @param string $content
     * @param WP_Block $block
     * @param array $jankxControls
     * @return string
     */
    abstract protected function renderBlockContent(
        array $attributes,
        string $content,
        $block,
        array $jankxControls
    ): string;

    /**
     * Get wrapper CSS classes
     *
     * @param array $attributes
     * @param array $jankxControls
     * @return array
     */
    protected function getWrapperClasses(array $attributes, array $jankxControls): array
    {
        $classes = [
            'wp-block-' . str_replace('/', '-', $this->name),
            'jankx-block',
        ];

        // Add animation classes
        if (!empty($jankxControls['animation']['entrance'])) {
            $classes[] = 'jankx-animate-on-scroll';
            $classes[] = 'jankx-animate-' . $jankxControls['animation']['entrance'];
        }

        if (!empty($jankxControls['animation']['infinite'])) {
            $classes[] = 'jankx-animate-infinite';
            $classes[] = 'jankx-animate-' . $jankxControls['animation']['infinite'];
        }

        // Add alignment class
        if (!empty($attributes['align'])) {
            $classes[] = 'align' . $attributes['align'];
        }

        return $classes;
    }

    /**
     * Generate CSS styles from controls
     *
     * @param array $jankxControls
     * @return string
     */
    protected function generateStyles(array $jankxControls): string
    {
        $styles = [];

        foreach ($this->controls as $name => $control) {
            if (isset($jankxControls[$name])) {
                $controlStyles = $control->generateCss(
                    $jankxControls[$name],
                    '.' . $this->getBlockCssClass()
                );

                if ($controlStyles) {
                    $styles[] = $controlStyles;
                }
            }
        }

        return implode('; ', $styles);
    }

    /**
     * Get block CSS class for selectors
     *
     * @return string
     */
    protected function getBlockCssClass(): string
    {
        return 'wp-block-' . str_replace('/', '-', $this->name);
    }

    /**
     * Build HTML attributes string
     *
     * @param array $attributes
     * @return string
     */
    protected function buildAttributesString(array $attributes): string
    {
        $parts = [];

        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
                $parts[] = "data-{$key}='" . esc_attr($value) . "'";
            } else {
                $parts[] = "{$key}=\"" . esc_attr($value) . "\"";
            }
        }

        return implode(' ', $parts);
    }

    /**
     * Check if content should be wrapped
     *
     * @return bool
     */
    protected function shouldWrapContent(): bool
    {
        return true;
    }

    /**
     * Enqueue editor assets
     *
     * @return void
     */
    public function enqueueEditorAssets(): void
    {
        if ($this->scriptHandle) {
            wp_enqueue_script($this->scriptHandle);
        }

        // Enqueue control assets
        foreach ($this->controls as $control) {
            $control->enqueueAssets();
        }

        // Localize control schemas for editor
        wp_localize_script(
            $this->scriptHandle ?? 'wp-blocks',
            'jankxBlockControls_' . str_replace('/', '_', $this->name),
            $this->getControlSchemasForEditor()
        );
    }

    /**
     * Enqueue frontend assets
     *
     * @return void
     */
    public function enqueueFrontendAssets(): void
    {
        if ($this->styleHandle) {
            wp_enqueue_style($this->styleHandle);
        }
    }

    /**
     * Output dynamic CSS in head
     *
     * @return void
     */
    public function outputDynamicCss(): void
    {
        // Only output if this block is present on current page
        if (!$this->isBlockPresent()) {
            return;
        }

        $css = $this->generateDynamicCss();

        if ($css) {
            echo "<style id=\"jankx-block-{$this->name}\">{$css}</style>";
        }
    }

    /**
     * Check if block is present on current page
     *
     * @return bool
     */
    protected function isBlockPresent(): bool
    {
        // Check if block is in current post content
        // This is a simplified check - in production, use has_block()
        return has_block($this->name);
    }

    /**
     * Generate dynamic CSS for all block instances
     *
     * @return string
     */
    protected function generateDynamicCss(): string
    {
        $css = '';

        // Generate responsive CSS for each control
        foreach ($this->controls as $name => $control) {
            if (method_exists($control, 'getResponsiveCss')) {
                // This will be populated from actual block instances
            }
        }

        return $css;
    }

    /**
     * Get control schemas for editor JavaScript
     *
     * @return array
     */
    protected function getControlSchemasForEditor(): array
    {
        $schemas = [];

        foreach ($this->controls as $name => $control) {
            $schemas[$name] = [
                'type' => $control->getType(),
                'label' => $control->getLabel(),
                'schema' => $control->getSchema(),
            ];
        }

        return $schemas;
    }

    /**
     * Get block configuration for JavaScript registration
     *
     * @return array
     */
    public function getBlockConfig(): array
    {
        return [
            'name' => $this->name,
            'title' => $this->title,
            'description' => $this->description,
            'category' => $this->category,
            'icon' => $this->icon,
            'supports' => $this->supports,
            'controls' => $this->getControlSchemasForEditor(),
        ];
    }
}
