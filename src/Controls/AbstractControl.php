<?php

namespace Jankx\Gutenberg\Controls;

/**
 * Abstract base class for all Gutenberg controls
 *
 * Provides common functionality for control registration, attribute handling,
 * and rendering. All custom controls must extend this class.
 *
 * @package Jankx\Gutenberg\Controls
 * @since 1.0.0
 */
abstract class AbstractControl
{
    /**
     * Control name/identifier
     *
     * @var string
     */
    protected $name;

    /**
     * Control label
     *
     * @var string
     */
    protected $label;

    /**
     * Control description
     *
     * @var string
     */
    protected $description;

    /**
     * Control category
     *
     * @var string
     */
    protected $category = 'common';

    /**
     * Default control value
     *
     * @var mixed
     */
    protected $default;

    /**
     * Control attributes schema
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Whether control is responsive
     *
     * @var bool
     */
    protected $responsive = false;

    /**
     * Responsive breakpoints
     *
     * @var array
     */
    protected $breakpoints = [
        'desktop' => '',
        'tablet' => '@media (max-width: 1024px)',
        'mobile' => '@media (max-width: 768px)',
    ];

    /**
     * Constructor
     *
     * @param array $config Control configuration
     */
    public function __construct(array $config = [])
    {
        $this->name = $config['name'] ?? $this->getDefaultName();
        $this->label = $config['label'] ?? $this->getDefaultLabel();
        $this->description = $config['description'] ?? '';
        $this->default = $config['default'] ?? null;
        $this->responsive = $config['responsive'] ?? false;

        if (isset($config['attributes'])) {
            $this->attributes = array_merge($this->attributes, $config['attributes']);
        }
    }

    /**
     * Get control name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get control label
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Get control type
     *
     * @return string
     */
    abstract public function getType(): string;

    /**
     * Get control schema for block registration
     *
     * @return array
     */
    public function getSchema(): array
    {
        return [
            'type' => 'object',
            'default' => $this->default ?? $this->getDefaultValue(),
        ];
    }

    /**
     * Get default value
     *
     * @return mixed
     */
    protected function getDefaultValue()
    {
        return null;
    }

    /**
     * Get default name from class
     *
     * @return string
     */
    protected function getDefaultName(): string
    {
        $class = basename(str_replace('\\', '/', get_class($this)));
        return strtolower(str_replace('Control', '', $class));
    }

    /**
     * Get default label
     *
     * @return string
     */
    protected function getDefaultLabel(): string
    {
        $name = $this->getDefaultName();
        return ucwords(str_replace('_', ' ', $name));
    }

    /**
     * Render control in editor
     *
     * @param mixed $value Current value
     * @param callable $onChange Change handler
     * @return string React component markup
     */
    abstract public function renderEditor($value, callable $onChange): string;

    /**
     * Generate CSS from control value
     *
     * @param mixed $value Control value
     * @param string $selector CSS selector
     * @return string Generated CSS
     */
    abstract public function generateCss($value, string $selector): string;

    /**
     * Sanitize value for frontend
     *
     * @param mixed $value Raw value
     * @return mixed Sanitized value
     */
    public function sanitize($value)
    {
        return $value;
    }

    /**
     * Validate control value
     *
     * @param mixed $value
     * @return bool|WP_Error
     */
    public function validate($value)
    {
        return true;
    }

    /**
     * Get responsive CSS
     *
     * @param mixed $value Control value
     * @param string $selector CSS selector
     * @return array CSS by breakpoint
     */
    protected function getResponsiveCss($value, string $selector): array
    {
        if (!$this->responsive || !is_array($value)) {
            return ['desktop' => $this->generateCss($value, $selector)];
        }

        $css = [];
        foreach ($this->breakpoints as $device => $mediaQuery) {
            if (isset($value[$device])) {
                $deviceCss = $this->generateCss($value[$device], $selector);
                if ($mediaQuery) {
                    $css[$device] = "{$mediaQuery} { {$deviceCss} }";
                } else {
                    $css[$device] = $deviceCss;
                }
            }
        }

        return $css;
    }

    /**
     * Enqueue control assets
     *
     * @return void
     */
    public function enqueueAssets(): void
    {
        // Override in child classes
    }

    /**
     * Get control dependencies
     *
     * @return array WordPress component slugs
     */
    public function getDependencies(): array
    {
        return ['wp-components', 'wp-compose', 'wp-hooks'];
    }

    /**
     * Check if control should render
     *
     * @param array $blockContext Current block context
     * @return bool
     */
    public function shouldRender(array $blockContext = []): bool
    {
        return true;
    }
}
