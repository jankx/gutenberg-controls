<?php

namespace Jankx\Gutenberg\Controls\Layout;

use Jankx\Gutenberg\Controls\AbstractControl;

/**
 * Section control - Flatsome-style section builder
 *
 * Provides advanced section controls including:
 * - Full width/height options
 * - Background (color, image, video, gradient)
 * - Parallax and scroll effects
 * - Dividers (top/bottom shapes)
 * - Container width control
 * - Min-height settings
 *
 * @package Jankx\Gutenberg\Controls\Layout
 * @since 1.0.0
 */
class SectionControl extends AbstractControl
{
    protected $category = 'layout';

    protected $attributes = [
        'width' => ['type' => 'string', 'default' => 'full'],
        'height' => ['type' => 'string', 'default' => 'auto'],
        'minHeight' => ['type' => 'string', 'default' => ''],
        'backgroundType' => ['type' => 'string', 'default' => 'color'],
        'backgroundColor' => ['type' => 'string', 'default' => ''],
        'backgroundImage' => ['type' => 'object', 'default' => null],
        'backgroundOverlay' => ['type' => 'string', 'default' => ''],
        'parallax' => ['type' => 'boolean', 'default' => false],
        'parallaxSpeed' => ['type' => 'number', 'default' => 0.5],
        'dividerTop' => ['type' => 'string', 'default' => ''],
        'dividerBottom' => ['type' => 'string', 'default' => ''],
        'dividerColor' => ['type' => 'string', 'default' => '#ffffff'],
        'containerWidth' => ['type' => 'string', 'default' => 'default'],
        'padding' => ['type' => 'object', 'default' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']],
        'margin' => ['type' => 'object', 'default' => ['top' => '', 'right' => '', 'bottom' => '', 'left' => '']],
        'scrollEffect' => ['type' => 'string', 'default' => ''],
        'sticky' => ['type' => 'boolean', 'default' => false],
        'stickyOffset' => ['type' => 'number', 'default' => 0],
        'borderRadius' => ['type' => 'object', 'default' => ['topLeft' => '', 'topRight' => '', 'bottomLeft' => '', 'bottomRight' => '']],
        'boxShadow' => ['type' => 'object', 'default' => null],
    ];

    public function getType(): string
    {
        return 'jankx/section';
    }

    protected function getDefaultValue(): array
    {
        return [
            'width' => 'full',
            'height' => 'auto',
            'containerWidth' => 'default',
            'backgroundType' => 'color',
        ];
    }

    public function renderEditor($value, callable $onChange): string
    {
        // React component placeholder - actual implementation in JS
        return '<SectionInspector value={"' . json_encode($value) . '"} onChange={onChange} />';
    }

    public function generateCss($value, string $selector): string
    {
        $css = [];

        // Width handling
        if (($value['width'] ?? '') === 'full') {
            $css[] = 'width: 100vw';
            $css[] = 'position: relative';
            $css[] = 'left: 50%';
            $css[] = 'transform: translateX(-50%)';
        }

        // Height
        if (!empty($value['minHeight'])) {
            $css[] = "min-height: {$value['minHeight']}";
        }
        if (($value['height'] ?? '') === 'full') {
            $css[] = 'min-height: 100vh';
        }

        // Background
        switch ($value['backgroundType'] ?? 'color') {
            case 'color':
                if (!empty($value['backgroundColor'])) {
                    $css[] = "background-color: {$value['backgroundColor']}";
                }
                break;
            case 'image':
                if (!empty($value['backgroundImage']['url'])) {
                    $css[] = "background-image: url({$value['backgroundImage']['url']})";
                    $css[] = 'background-size: cover';
                    $css[] = 'background-position: center';
                    $css[] = 'background-repeat: no-repeat';
                }
                break;
            case 'gradient':
                if (!empty($value['backgroundColor'])) {
                    $css[] = "background: {$value['backgroundColor']}";
                }
                break;
        }

        // Overlay
        if (!empty($value['backgroundOverlay'])) {
            $css[] = 'position: relative';
            // Overlay applied via pseudo-element in frontend
        }

        // Padding
        if (!empty($value['padding'])) {
            $padding = $this->generateSpacingCss($value['padding']);
            if ($padding) {
                $css[] = "padding: {$padding}";
            }
        }

        // Margin
        if (!empty($value['margin'])) {
            $margin = $this->generateSpacingCss($value['margin']);
            if ($margin) {
                $css[] = "margin: {$margin}";
            }
        }

        // Border radius
        if (!empty($value['borderRadius'])) {
            $radius = $this->generateBorderRadiusCss($value['borderRadius']);
            if ($radius) {
                $css[] = "border-radius: {$radius}";
            }
        }

        // Box shadow
        if (!empty($value['boxShadow'])) {
            $shadow = $this->generateBoxShadowCss($value['boxShadow']);
            if ($shadow) {
                $css[] = "box-shadow: {$shadow}";
            }
        }

        return implode('; ', $css) . ';';
    }

    /**
     * Generate spacing CSS shorthand
     */
    protected function generateSpacingCss(array $spacing): string
    {
        $values = [
            $spacing['top'] ?? '0',
            $spacing['right'] ?? '0',
            $spacing['bottom'] ?? '0',
            $spacing['left'] ?? '0',
        ];

        // Remove empty values and normalize
        $values = array_map(function($v) {
            return $v ?: '0';
        }, $values);

        // Collapse if all same
        if (count(array_unique($values)) === 1) {
            return $values[0];
        }

        return implode(' ', $values);
    }

    /**
     * Generate border radius CSS
     */
    protected function generateBorderRadiusCss(array $radius): string
    {
        $values = [
            $radius['topLeft'] ?? '0',
            $radius['topRight'] ?? '0',
            $radius['bottomRight'] ?? '0',
            $radius['bottomLeft'] ?? '0',
        ];

        if (count(array_unique($values)) === 1) {
            return $values[0];
        }

        return implode(' ', $values);
    }

    /**
     * Generate box shadow CSS
     */
    protected function generateBoxShadowCss(array $shadow): string
    {
        $x = $shadow['x'] ?? 0;
        $y = $shadow['y'] ?? 0;
        $blur = $shadow['blur'] ?? 0;
        $spread = $shadow['spread'] ?? 0;
        $color = $shadow['color'] ?? 'rgba(0,0,0,0.1)';
        $inset = !empty($shadow['inset']) ? 'inset ' : '';

        return "{$inset}{$x}px {$y}px {$blur}px {$spread}px {$color}";
    }

    public function enqueueAssets(): void
    {
        wp_enqueue_script(
            'jankx-section-control',
            $this->getAssetUrl('assets/build/section-control.js'),
            ['wp-blocks', 'wp-components', 'wp-compose'],
            '1.0.0',
            true
        );

        wp_enqueue_style(
            'jankx-section-control',
            $this->getAssetUrl('assets/build/section-control.css'),
            [],
            '1.0.0'
        );
    }
}
