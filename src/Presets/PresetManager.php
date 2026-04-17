<?php

namespace Jankx\Gutenberg\Controls\Presets;

/**
 * Preset Manager - One-click design templates (Flatsome-style)
 *
 * Provides pre-designed section configurations that users can
 * apply with a single click. Includes categories like Hero,
* Testimonials, Features, CTAs, etc.
 *
 * @package Jankx\Gutenberg\Presets
 * @since 1.0.0
 */
class PresetManager
{
    /**
     * Singleton instance
     *
     * @var self
     */
    private static $instance;

    /**
     * Registered presets
     *
     * @var array
     */
    private $presets = [];

    /**
     * Preset categories
     *
     * @var array
     */
    private $categories = [
        'hero' => ['title' => 'Hero Sections', 'icon' => 'cover-image'],
        'features' => ['title' => 'Features', 'icon' => 'star-filled'],
        'testimonials' => ['title' => 'Testimonials', 'icon' => 'format-quote'],
        'cta' => ['title' => 'Call to Action', 'icon' => 'megaphone'],
        'content' => ['title' => 'Content', 'icon' => 'text'],
        'team' => ['title' => 'Team', 'icon' => 'groups'],
        'pricing' => ['title' => 'Pricing', 'icon' => 'money-alt'],
        'logos' => ['title' => 'Logo Showcase', 'icon' => 'images-alt2'],
        'contact' => ['title' => 'Contact', 'icon' => 'email'],
        'footer' => ['title' => 'Footer', 'icon' => 'layout'],
    ];

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
     * Constructor - register default presets
     */
    private function __construct()
    {
        $this->registerDefaultPresets();
    }

    /**
     * Register a preset
     *
     * @param string $id
     * @param array $config
     * @return self
     */
    public function register(string $id, array $config): self
    {
        $this->presets[$id] = wp_parse_args($config, [
            'id' => $id,
            'title' => '',
            'description' => '',
            'category' => 'content',
            'thumbnail' => '',
            'controls' => [],
            'innerBlocks' => [],
            'tags' => [],
        ]);

        return $this;
    }

    /**
     * Register a preset for a specific block
     *
     * @param string $blockName
     * @param array $config
     * @return self
     */
    public function registerPresetForBlock(string $blockName, array $config): self
    {
        $id = $config['id'] ?? uniqid('preset-');
        $config['block'] = $blockName;

        return $this->register($id, $config);
    }

    /**
     * Get preset by ID
     *
     * @param string $id
     * @return array|null
     */
    public function get(string $id): ?array
    {
        return $this->presets[$id] ?? null;
    }

    /**
     * Get all presets
     *
     * @return array
     */
    public function all(): array
    {
        return $this->presets;
    }

    /**
     * Get presets by category
     *
     * @param string $category
     * @return array
     */
    public function getByCategory(string $category): array
    {
        return array_filter($this->presets, function($preset) use ($category) {
            return $preset['category'] === $category;
        });
    }

    /**
     * Get categories
     *
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * Apply preset to block
     *
     * @param string $presetId
     * @param array $currentAttributes
     * @return array
     */
    public function apply(string $presetId, array $currentAttributes = []): array
    {
        $preset = $this->get($presetId);

        if (!$preset) {
            return $currentAttributes;
        }

        // Merge preset controls with current attributes
        $attributes = $currentAttributes;
        $attributes['jankxControls'] = array_merge(
            $currentAttributes['jankxControls'] ?? [],
            $preset['controls'] ?? []
        );

        return $attributes;
    }

    /**
     * Register default presets (Flatsome-style)
     *
     * @return void
     */
    protected function registerDefaultPresets(): void
    {
        // HERO PRESETS
        $this->register('hero-center', [
            'title' => __('Centered Hero', 'jankx'),
            'description' => __('Full-width hero with centered content', 'jankx'),
            'category' => 'hero',
            'thumbnail' => 'hero-center.jpg',
            'tags' => ['full-width', 'background-image', 'center'],
            'controls' => [
                'layout' => [
                    'width' => 'full',
                    'height' => 'full',
                    'minHeight' => '600px',
                    'backgroundType' => 'image',
                    'backgroundOverlay' => 'rgba(0,0,0,0.4)',
                    'containerWidth' => 'narrow',
                    'padding' => ['top' => '100px', 'right' => '20px', 'bottom' => '100px', 'left' => '20px'],
                ],
                'animation' => [
                    'entrance' => 'fadeInUp',
                    'entranceDuration' => 1000,
                    'scrollTrigger' => true,
                ],
            ],
            'innerBlocks' => [
                ['name' => 'core/heading', 'attributes' => ['level' => 1, 'align' => 'center', 'textColor' => 'white']],
                ['name' => 'core/paragraph', 'attributes' => ['align' => 'center', 'textColor' => 'white']],
                ['name' => 'core/buttons', 'attributes' => ['layout' => ['type' => 'flex', 'justifyContent' => 'center']]],
            ],
        ]);

        $this->register('hero-split', [
            'title' => __('Split Hero', 'jankx'),
            'description' => __('Two-column hero with image and content', 'jankx'),
            'category' => 'hero',
            'thumbnail' => 'hero-split.jpg',
            'tags' => ['two-column', 'image', 'content'],
            'controls' => [
                'layout' => [
                    'width' => 'full',
                    'height' => 'auto',
                    'backgroundType' => 'color',
                    'backgroundColor' => '#f8f9fa',
                    'containerWidth' => 'wide',
                    'padding' => ['top' => '80px', 'right' => '20px', 'bottom' => '80px', 'left' => '20px'],
                ],
                'animation' => [
                    'entrance' => 'fadeIn',
                    'entranceDuration' => 800,
                ],
            ],
            'innerBlocks' => [
                ['name' => 'core/columns', 'innerBlocks' => [
                    ['name' => 'core/column', 'innerBlocks' => [
                        ['name' => 'core/heading'],
                        ['name' => 'core/paragraph'],
                        ['name' => 'core/button'],
                    ]],
                    ['name' => 'core/column', 'innerBlocks' => [
                        ['name' => 'core/image'],
                    ]],
                ]],
            ],
        ]);

        $this->register('hero-gradient', [
            'title' => __('Gradient Hero', 'jankx'),
            'description' => __('Hero with gradient background', 'jankx'),
            'category' => 'hero',
            'thumbnail' => 'hero-gradient.jpg',
            'tags' => ['gradient', 'modern', 'colorful'],
            'controls' => [
                'layout' => [
                    'width' => 'full',
                    'height' => 'auto',
                    'minHeight' => '500px',
                    'backgroundType' => 'gradient',
                    'backgroundColor' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    'containerWidth' => 'default',
                    'padding' => ['top' => '120px', 'right' => '20px', 'bottom' => '120px', 'left' => '20px'],
                ],
                'animation' => [
                    'entrance' => 'zoomIn',
                    'entranceDuration' => 1000,
                    'scrollTrigger' => true,
                ],
            ],
            'innerBlocks' => [
                ['name' => 'core/heading', 'attributes' => ['textColor' => 'white']],
                ['name' => 'core/paragraph', 'attributes' => ['textColor' => 'white']],
            ],
        ]);

        // FEATURES PRESETS
        $this->register('features-3col', [
            'title' => __('3 Column Features', 'jankx'),
            'description' => __('Three feature boxes with icons', 'jankx'),
            'category' => 'features',
            'thumbnail' => 'features-3col.jpg',
            'tags' => ['3-column', 'icons', 'services'],
            'controls' => [
                'layout' => [
                    'width' => 'default',
                    'backgroundType' => 'color',
                    'backgroundColor' => '#ffffff',
                    'padding' => ['top' => '80px', 'right' => '20px', 'bottom' => '80px', 'left' => '20px'],
                ],
                'animation' => [
                    'entrance' => 'fadeInUp',
                    'staggerChildren' => true,
                    'staggerDelay' => 150,
                ],
            ],
        ]);

        $this->register('features-large', [
            'title' => __('Large Feature Section', 'jankx'),
            'description' => __('Full-width feature with large image', 'jankx'),
            'category' => 'features',
            'thumbnail' => 'features-large.jpg',
            'tags' => ['image', 'full-width', 'showcase'],
            'controls' => [
                'layout' => [
                    'width' => 'full',
                    'dividerTop' => 'curve',
                    'dividerBottom' => 'curve',
                    'dividerColor' => '#ffffff',
                    'backgroundType' => 'color',
                    'backgroundColor' => '#f8f9fa',
                    'padding' => ['top' => '100px', 'right' => '20px', 'bottom' => '100px', 'left' => '20px'],
                ],
            ],
        ]);

        // TESTIMONIAL PRESETS
        $this->register('testimonial-slider', [
            'title' => __('Testimonial Slider', 'jankx'),
            'description' => __('Carousel of customer testimonials', 'jankx'),
            'category' => 'testimonials',
            'thumbnail' => 'testimonial-slider.jpg',
            'tags' => ['slider', 'carousel', 'reviews'],
            'controls' => [
                'layout' => [
                    'width' => 'full',
                    'backgroundType' => 'color',
                    'backgroundColor' => '#f5f5f5',
                    'padding' => ['top' => '80px', 'bottom' => '80px'],
                ],
            ],
        ]);

        $this->register('testimonial-grid', [
            'title' => __('Testimonial Grid', 'jankx'),
            'description' => __('Grid of testimonial cards', 'jankx'),
            'category' => 'testimonials',
            'thumbnail' => 'testimonial-grid.jpg',
            'tags' => ['grid', 'cards', 'reviews'],
            'controls' => [
                'layout' => [
                    'width' => 'default',
                    'backgroundType' => 'color',
                    'backgroundColor' => '#ffffff',
                    'padding' => ['top' => '60px', 'bottom' => '60px'],
                ],
                'animation' => [
                    'entrance' => 'fadeIn',
                    'staggerChildren' => true,
                ],
            ],
        ]);

        // CTA PRESETS
        $this->register('cta-simple', [
            'title' => __('Simple CTA', 'jankx'),
            'description' => __('Clean call-to-action with button', 'jankx'),
            'category' => 'cta',
            'thumbnail' => 'cta-simple.jpg',
            'tags' => ['button', 'conversion', 'clean'],
            'controls' => [
                'layout' => [
                    'width' => 'default',
                    'backgroundType' => 'color',
                    'backgroundColor' => '#ff5722',
                    'containerWidth' => 'narrow',
                    'padding' => ['top' => '60px', 'bottom' => '60px'],
                ],
                'animation' => [
                    'entrance' => 'bounceIn',
                    'scrollTrigger' => true,
                ],
            ],
        ]);

        $this->register('cta-parallax', [
            'title' => __('Parallax CTA', 'jankx'),
            'description' => __('Full-width parallax background CTA', 'jankx'),
            'category' => 'cta',
            'thumbnail' => 'cta-parallax.jpg',
            'tags' => ['parallax', 'full-width', 'impactful'],
            'controls' => [
                'layout' => [
                    'width' => 'full',
                    'height' => 'auto',
                    'minHeight' => '400px',
                    'backgroundType' => 'image',
                    'parallax' => true,
                    'parallaxSpeed' => 0.5,
                    'backgroundOverlay' => 'rgba(0,0,0,0.5)',
                    'containerWidth' => 'default',
                    'padding' => ['top' => '100px', 'bottom' => '100px'],
                ],
            ],
        ]);

        // CONTENT PRESETS
        $this->register('content-centered', [
            'title' => __('Centered Content', 'jankx'),
            'description' => __('Center-aligned text content block', 'jankx'),
            'category' => 'content',
            'thumbnail' => 'content-centered.jpg',
            'tags' => ['text', 'center', 'article'],
            'controls' => [
                'layout' => [
                    'width' => 'default',
                    'containerWidth' => 'narrow',
                    'padding' => ['top' => '40px', 'bottom' => '40px'],
                ],
            ],
        ]);

        // Allow external presets
        do_action('jankx/gutenberg/register_presets', $this);
    }

    /**
     * Search presets
     *
     * @param string $query
     * @return array
     */
    public function search(string $query): array
    {
        $query = strtolower($query);

        return array_filter($this->presets, function($preset) use ($query) {
            return (
                stripos($preset['title'], $query) !== false ||
                stripos($preset['description'], $query) !== false ||
                in_array($query, array_map('strtolower', $preset['tags'] ?? []))
            );
        });
    }

    /**
     * Export preset to JSON
     *
     * @param string $id
     * @return string
     */
    public function export(string $id): string
    {
        $preset = $this->get($id);

        if (!$preset) {
            return '';
        }

        return wp_json_encode($preset, JSON_PRETTY_PRINT);
    }

    /**
     * Import preset from JSON
     *
     * @param string $json
     * @return bool
     */
    public function import(string $json): bool
    {
        $data = json_decode($json, true);

        if (!$data || empty($data['id'])) {
            return false;
        }

        $this->register($data['id'], $data);
        return true;
    }

    /**
     * Get data for JavaScript
     *
     * @return array
     */
    public function getJsData(): array
    {
        return [
            'presets' => array_values($this->presets),
            'categories' => $this->categories,
        ];
    }

    /**
     * Get all presets for JavaScript integration
     *
     * @return array
     */
    public function getAllPresetsForJS(): array
    {
        return array_values($this->presets);
    }
}
