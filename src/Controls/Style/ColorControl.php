<?php

namespace Jankx\Gutenberg\Controls\Style;

use Jankx\Gutenberg\Controls\AbstractControl;

/**
 * Color Control - Advanced color picker with gradients and duotone
 *
 * High-UX component for color selection:
 * - Solid color picker with alpha channel
 * - Linear and radial gradients
 * - Duotone filters (WP 6.4+)
 * - Theme color integration
 * - Color presets and favorites
 * - WCAG contrast checking
 *
 * @package Jankx\Gutenberg\Controls\Style
 */
class ColorControl extends AbstractControl
{
    /**
     * Control category
     */
    protected string $category = 'style';

    /**
     * Control attributes with defaults
     */
    protected array $attributes = [
        'colorType' => ['type' => 'string', 'default' => 'solid'],
        'solidColor' => ['type' => 'string', 'default' => ''],
        'solidOpacity' => ['type' => 'number', 'default' => 100],

        // Gradient
        'gradientType' => ['type' => 'string', 'default' => 'linear'],
        'gradientAngle' => ['type' => 'number', 'default' => 90],
        'gradientColors' => ['type' => 'array', 'default' => []],

        // Duotone
        'duotoneShadows' => ['type' => 'string', 'default' => ''],
        'duotoneHighlights' => ['type' => 'string', 'default' => ''],

        // Theme integration
        'useThemeColor' => ['type' => 'boolean', 'default' => false],
        'themeColorKey' => ['type' => 'string', 'default' => 'primary'],

        // CSS variables
        'cssVar' => ['type' => 'string', 'default' => ''],
    ];

    /**
     * Supported features
     */
    protected array $supports = [
        'solid' => true,
        'gradient' => true,
        'duotone' => true,
        'alpha' => true,
    ];

    /**
     * Get control type identifier
     */
    public function getType(): string
    {
        return 'jankx/color';
    }

    /**
     * Render editor component placeholder
     */
    public function renderEditor($value, callable $onChange): string
    {
        return '<ColorInspector />';
    }

    /**
     * Generate CSS for color settings
     */
    public function generateCss($value, string $selector): string
    {
        $css = '';
        $colorType = $value['colorType'] ?? 'solid';

        switch ($colorType) {
            case 'solid':
                $css .= $this->generateSolidCss($value, $selector);
                break;
            case 'gradient':
                $css .= $this->generateGradientCss($value, $selector);
                break;
            case 'duotone':
                $css .= $this->generateDuotoneCss($value, $selector);
                break;
        }

        // CSS variable support
        if (!empty($value['cssVar'])) {
            $cssVarValue = $this->getCssValue($value);
            if ($cssVarValue) {
                $css .= sprintf("%s { %s: %s; }\n", $selector, $value['cssVar'], $cssVarValue);
            }
        }

        return $css;
    }

    /**
     * Generate solid color CSS
     */
    protected function generateSolidCss(array $value, string $selector): string
    {
        $color = $value['solidColor'] ?? '';
        if (empty($color)) {
            return '';
        }

        // Handle theme color
        if (!empty($value['useThemeColor']) && !empty($value['themeColorKey'])) {
            return sprintf("%s { color: var(--jankx-%s-color); }\n", $selector, $value['themeColorKey']);
        }

        // Handle opacity
        $opacity = ($value['solidOpacity'] ?? 100) / 100;
        if ($opacity < 1) {
            $color = $this->addAlphaToColor($color, $opacity);
        }

        return sprintf("%s { color: %s; }\n", $selector, $color);
    }

    /**
     * Generate gradient CSS
     */
    protected function generateGradientCss(array $value, string $selector): string
    {
        $gradient = $this->buildGradientString($value);
        if (empty($gradient)) {
            return '';
        }

        return sprintf("%s { background: %s; }\n", $selector, $gradient);
    }

    /**
     * Build gradient string from value
     */
    protected function buildGradientString(array $value): string
    {
        $type = $value['gradientType'] ?? 'linear';
        $colors = $value['gradientColors'] ?? [];

        if (empty($colors) || count($colors) < 2) {
            return '';
        }

        $stops = [];
        foreach ($colors as $stop) {
            $color = $stop['color'] ?? '';
            $position = $stop['position'] ?? 0;
            if ($color) {
                $stops[] = sprintf('%s %s%%', $color, $position);
            }
        }

        if (empty($stops)) {
            return '';
        }

        if ($type === 'linear') {
            $angle = ($value['gradientAngle'] ?? 90) . 'deg';
            return sprintf('linear-gradient(%s, %s)', $angle, implode(', ', $stops));
        }

        return sprintf('radial-gradient(circle, %s)', implode(', ', $stops));
    }

    /**
     * Generate duotone CSS filter
     */
    protected function generateDuotoneCss(array $value, string $selector): string
    {
        $shadows = $value['duotoneShadows'] ?? '';
        $highlights = $value['duotoneHighlights'] ?? '';

        if (empty($shadows) || empty($highlights)) {
            return '';
        }

        // Duotone using CSS filter
        return sprintf(
            "%s { filter: grayscale(100%%) sepia(100%%) hue-rotate(%s); }\n",
            $selector,
            $this->calculateHueRotate($shadows, $highlights)
        );
    }

    /**
     * Add alpha channel to hex color
     */
    protected function addAlphaToColor(string $color, float $alpha): string
    {
        // Handle hex color
        if (strpos($color, '#') === 0) {
            $hex = ltrim($color, '#');
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

        // Handle rgb/rgba - return as-is with alpha
        if (strpos($color, 'rgb') === 0) {
            // Parse rgb/rgba and reconstruct with new alpha
            if (preg_match('/rgba?\((\d+),\s*(\d+),\s*(\d+)/', $color, $matches)) {
                return sprintf('rgba(%s, %s, %s, %s)', $matches[1], $matches[2], $matches[3], $alpha);
            }
        }

        return $color;
    }

    /**
     * Calculate hue rotate for duotone effect
     */
    protected function calculateHueRotate(string $shadows, string $highlights): string
    {
        // Simplified calculation - in production would use proper color math
        return '180deg';
    }

    /**
     * Get CSS value for variable assignment
     */
    protected function getCssValue(array $value): ?string
    {
        $colorType = $value['colorType'] ?? 'solid';

        switch ($colorType) {
            case 'solid':
                $color = $value['solidColor'] ?? '';
                if (!empty($value['useThemeColor']) && !empty($value['themeColorKey'])) {
                    return sprintf('var(--jankx-%s-color)', $value['themeColorKey']);
                }
                $opacity = ($value['solidOpacity'] ?? 100) / 100;
                return $opacity < 1 ? $this->addAlphaToColor($color, $opacity) : $color;
            case 'gradient':
                return $this->buildGradientString($value);
            default:
                return null;
        }
    }

    /**
     * Get color type options
     */
    public function getColorTypeOptions(): array
    {
        $options = [
            'solid' => __('Solid Color', 'jankx'),
        ];

        if ($this->supports['gradient']) {
            $options['gradient'] = __('Gradient', 'jankx');
        }

        if ($this->supports['duotone']) {
            $options['duotone'] = __('Duotone', 'jankx');
        }

        return $options;
    }

    /**
     * Get gradient type options
     */
    public function getGradientTypeOptions(): array
    {
        return [
            'linear' => __('Linear', 'jankx'),
            'radial' => __('Radial', 'jankx'),
        ];
    }

    /**
     * Get preset colors (theme colors)
     */
    public function getPresetColors(): array
    {
        return [
            ['color' => '#ff5722', 'slug' => 'primary', 'name' => __('Primary', 'jankx')],
            ['color' => '#009688', 'slug' => 'secondary', 'name' => __('Secondary', 'jankx')],
            ['color' => '#333333', 'slug' => 'dark', 'name' => __('Dark', 'jankx')],
            ['color' => '#ffffff', 'slug' => 'light', 'name' => __('Light', 'jankx')],
            ['color' => '#f5f5f5', 'slug' => 'gray-100', 'name' => __('Gray 100', 'jankx')],
            ['color' => '#e0e0e0', 'slug' => 'gray-200', 'name' => __('Gray 200', 'jankx')],
            ['color' => '#9e9e9e', 'slug' => 'gray-500', 'name' => __('Gray 500', 'jankx')],
            ['color' => '#616161', 'slug' => 'gray-700', 'name' => __('Gray 700', 'jankx')],
        ];
    }

    /**
     * Check if color is valid
     */
    protected function isValidColor(string $color): bool
    {
        if (empty($color)) {
            return false;
        }

        // Hex color
        if (preg_match('/^#([a-fA-F0-9]{3}){1,2}$/', $color)) {
            return true;
        }

        // RGB/RGBA
        if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+\s*)?\)$/', $color)) {
            return true;
        }

        // HSL/HSLA
        if (preg_match('/^hsla?\(\s*\d+\s*,\s*\d+%\s*,\s*\d+%\s*(,\s*[\d.]+\s*)?\)$/', $color)) {
            return true;
        }

        // CSS color names
        $cssColors = ['transparent', 'currentColor', 'inherit'];
        if (in_array(strtolower($color), $cssColors, true)) {
            return true;
        }

        return false;
    }

    /**
     * Get contrast ratio between two colors (WCAG)
     */
    public function getContrastRatio(string $color1, string $color2): float
    {
        $l1 = $this->getLuminance($color1);
        $l2 = $this->getLuminance($color2);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Get relative luminance of a color
     */
    protected function getLuminance(string $color): float
    {
        $rgb = $this->hexToRgb($color);
        if (!$rgb) {
            return 0;
        }

        $rsrgb = $rgb[0] / 255;
        $gsrgb = $rgb[1] / 255;
        $bsrgb = $rgb[2] / 255;

        $r = $rsrgb <= 0.03928 ? $rsrgb / 12.92 : pow(($rsrgb + 0.055) / 1.055, 2.4);
        $g = $gsrgb <= 0.03928 ? $gsrgb / 12.92 : pow(($gsrgb + 0.055) / 1.055, 2.4);
        $b = $bsrgb <= 0.03928 ? $bsrgb / 12.92 : pow(($bsrgb + 0.055) / 1.055, 2.4);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Convert hex to RGB array
     */
    protected function hexToRgb(string $color): ?array
    {
        $color = ltrim($color, '#');

        if (strlen($color) === 3) {
            $color = $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        if (strlen($color) !== 6) {
            return null;
        }

        return [
            hexdec(substr($color, 0, 2)),
            hexdec(substr($color, 2, 2)),
            hexdec(substr($color, 4, 2)),
        ];
    }
}
