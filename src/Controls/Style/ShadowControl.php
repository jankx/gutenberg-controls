<?php

namespace Jankx\Gutenberg\Controls\Style;

use Jankx\Gutenberg\Controls\AbstractControl;

/**
 * Shadow Control - Box shadows and text shadows
 *
 * High-UX component for shadows:
 * - Preset shadow styles (material, flat, floating)
 * - Custom shadow builder (x, y, blur, spread, color)
 * - Inset shadows
 * - Multiple shadows
 * - Responsive shadow intensity
 *
 * @package Jankx\Gutenberg\Controls\Style
 */
class ShadowControl extends AbstractControl
{
    /**
     * Control category
     */
    protected string $category = 'style';

    /**
     * Control attributes with defaults
     */
    protected array $attributes = [
        'shadowType' => ['type' => 'string', 'default' => 'none'],
        'customShadow' => ['type' => 'object', 'default' => []],
        'shadowColor' => ['type' => 'string', 'default' => 'rgba(0,0,0,0.15)'],
        'shadowOpacity' => ['type' => 'number', 'default' => 15],

        // Individual values
        'shadowX' => ['type' => 'number', 'default' => 0],
        'shadowY' => ['type' => 'number', 'default' => 4],
        'shadowBlur' => ['type' => 'number', 'default' => 6],
        'shadowSpread' => ['type' => 'number', 'default' => 0],
        'shadowInset' => ['type' => 'boolean', 'default' => false],

        // Multiple shadows
        'shadows' => ['type' => 'array', 'default' => []],

        // Hover state
        'hoverShadow' => ['type' => 'string', 'default' => ''],
        'hoverTransition' => ['type' => 'number', 'default' => 300],
    ];

    /**
     * Preset shadow styles
     */
    protected array $presets = [];

    /**
     * Initialize control
     */
    public function __construct(array $config = [])
    {
        parent::__construct($config);

        // Initialize presets with translated labels
        $this->presets = [
            'none' => [
                'label' => __('None', 'jankx'),
                'value' => 'none',
            ],
            'xs' => [
                'label' => __('Extra Small', 'jankx'),
                'value' => '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
            ],
            'sm' => [
                'label' => __('Small', 'jankx'),
                'value' => '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
            ],
            'md' => [
                'label' => __('Medium', 'jankx'),
                'value' => '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
            ],
            'lg' => [
                'label' => __('Large', 'jankx'),
                'value' => '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
            ],
            'xl' => [
                'label' => __('Extra Large', 'jankx'),
                'value' => '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
            ],
            '2xl' => [
                'label' => __('2XL', 'jankx'),
                'value' => '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
            ],
            'inner' => [
                'label' => __('Inner', 'jankx'),
                'value' => 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
            ],
            'floating' => [
                'label' => __('Floating', 'jankx'),
                'value' => '0 20px 40px -10px rgba(0, 0, 0, 0.3)',
            ],
            'colored' => [
                'label' => __('Colored Glow', 'jankx'),
                'value' => '0 0 20px 5px rgba(99, 102, 241, 0.4)',
            ],
        ];
    }

    /**
     * Get control type identifier
     */
    public function getType(): string
    {
        return 'jankx/shadow';
    }

    /**
     * Render editor component placeholder
     */
    public function renderEditor($value, callable $onChange): string
    {
        return '<ShadowInspector />';
    }

    /**
     * Generate CSS for shadows
     */
    public function generateCss($value, string $selector): string
    {
        $css = '';

        // Main shadow
        $mainShadow = $this->generateMainShadowCss($value, $selector);
        $css .= $mainShadow;

        // Multiple shadows
        $multiShadow = $this->generateMultiShadowCss($value, $selector);
        $css .= $multiShadow;

        // Hover shadow
        $hoverShadow = $this->generateHoverShadowCss($value, $selector);
        $css .= $hoverShadow;

        return $css;
    }

    /**
     * Generate main shadow CSS
     */
    protected function generateMainShadowCss(array $value, string $selector): string
    {
        $shadowType = $value['shadowType'] ?? 'none';

        if ($shadowType === 'none') {
            return '';
        }

        // Preset shadow
        if (isset($this->presets[$shadowType])) {
            $shadowValue = $this->presets[$shadowType]['value'];
            if ($shadowValue !== 'none') {
                return sprintf("%s { box-shadow: %s; }\n", $selector, $shadowValue);
            }
            return '';
        }

        // Custom shadow
        if ($shadowType === 'custom') {
            $shadow = $this->buildCustomShadow($value);
            if ($shadow) {
                return sprintf("%s { box-shadow: %s; }\n", $selector, $shadow);
            }
        }

        return '';
    }

    /**
     * Build custom shadow string
     */
    protected function buildCustomShadow(array $value): string
    {
        $x = ($value['shadowX'] ?? 0) . 'px';
        $y = ($value['shadowY'] ?? 4) . 'px';
        $blur = ($value['shadowBlur'] ?? 6) . 'px';
        $spread = ($value['shadowSpread'] ?? 0) . 'px';

        // Build color with opacity
        $color = $value['shadowColor'] ?? 'rgba(0,0,0,0.15)';
        $opacity = ($value['shadowOpacity'] ?? 15) / 100;

        // Convert to rgba if needed
        if (strpos($color, '#') === 0) {
            $color = $this->hexToRgba($color, $opacity);
        }

        $inset = ($value['shadowInset'] ?? false) ? 'inset ' : '';

        return sprintf('%s%s %s %s %s %s', $inset, $x, $y, $blur, $spread, $color);
    }

    /**
     * Generate multiple shadows CSS
     */
    protected function generateMultiShadowCss(array $value, string $selector): string
    {
        $shadows = $value['shadows'] ?? [];
        if (empty($shadows)) {
            return '';
        }

        $shadowStrings = [];
        foreach ($shadows as $shadow) {
            if (!empty($shadow['enabled'])) {
                $shadowStrings[] = $this->buildShadowFromArray($shadow);
            }
        }

        if (empty($shadowStrings)) {
            return '';
        }

        return sprintf("%s { box-shadow: %s; }\n", $selector, implode(', ', $shadowStrings));
    }

    /**
     * Build shadow from array values
     */
    protected function buildShadowFromArray(array $shadow): string
    {
        $x = ($shadow['x'] ?? 0) . 'px';
        $y = ($shadow['y'] ?? 4) . 'px';
        $blur = ($shadow['blur'] ?? 6) . 'px';
        $spread = ($shadow['spread'] ?? 0) . 'px';
        $color = $shadow['color'] ?? 'rgba(0,0,0,0.15)';
        $inset = !empty($shadow['inset']) ? 'inset ' : '';

        return sprintf('%s%s %s %s %s %s', $inset, $x, $y, $blur, $spread, $color);
    }

    /**
     * Generate hover shadow CSS
     */
    protected function generateHoverShadowCss(array $value, string $selector): string
    {
        $hoverType = $value['hoverShadow'] ?? '';
        if (empty($hoverType) || $hoverType === 'none') {
            return '';
        }

        $shadow = '';
        if (isset($this->presets[$hoverType])) {
            $shadow = $this->presets[$hoverType]['value'];
        }

        if (!$shadow || $shadow === 'none') {
            return '';
        }

        $transition = ($value['hoverTransition'] ?? 300) . 'ms';

        $css = sprintf(
            "%s { transition: box-shadow %s ease; }\n",
            $selector,
            $transition
        );
        $css .= sprintf(
            "%s:hover { box-shadow: %s; }\n",
            $selector,
            $shadow
        );

        return $css;
    }

    /**
     * Convert hex to rgba
     */
    protected function hexToRgba(string $hex, float $alpha): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $r = hexdec(str_repeat($hex[0], 2));
            $g = hexdec(str_repeat($hex[1], 2));
            $b = hexdec(str_repeat($hex[2], 2));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        return sprintf('rgba(%d, %d, %d, %s)', $r, $g, $b, $alpha);
    }

    /**
     * Get shadow presets
     */
    public function getPresets(): array
    {
        return $this->presets;
    }

    /**
     * Get preset labels for dropdown
     */
    public function getPresetOptions(): array
    {
        $options = [];
        foreach ($this->presets as $key => $preset) {
            $options[$key] = $preset['label'];
        }
        return $options;
    }

    /**
     * Get hover transition presets
     */
    public function getTransitionPresets(): array
    {
        return [
            150 => __('Fast (150ms)', 'jankx'),
            300 => __('Normal (300ms)', 'jankx'),
            500 => __('Slow (500ms)', 'jankx'),
        ];
    }

    /**
     * Get shadow intensity options
     */
    public function getIntensityOptions(): array
    {
        return [
            ['value' => 5, 'label' => __('Subtle', 'jankx')],
            ['value' => 15, 'label' => __('Light', 'jankx')],
            ['value' => 30, 'label' => __('Medium', 'jankx')],
            ['value' => 50, 'label' => __('Strong', 'jankx')],
            ['value' => 75, 'label' => __('Heavy', 'jankx')],
        ];
    }
}
