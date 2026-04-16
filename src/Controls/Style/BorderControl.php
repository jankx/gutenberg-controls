<?php

namespace Jankx\Gutenberg\Controls\Style;

use Jankx\Gutenberg\Controls\AbstractControl;

/**
 * Border Control - Border styling and radius
 *
 * High-UX component for borders:
 * - Individual border sides (width, style, color)
 * - Border radius with individual corners
 * - Preset border styles
 * - Responsive border options
 *
 * @package Jankx\Gutenberg\Controls\Style
 */
class BorderControl extends AbstractControl
{
    /**
     * Control category
     */
    protected string $category = 'style';

    /**
     * Control attributes with defaults
     */
    protected array $attributes = [
        // Border width (individual sides)
        'borderTopWidth' => ['type' => 'string', 'default' => ''],
        'borderRightWidth' => ['type' => 'string', 'default' => ''],
        'borderBottomWidth' => ['type' => 'string', 'default' => ''],
        'borderLeftWidth' => ['type' => 'string', 'default' => ''],
        'borderWidth' => ['type' => 'string', 'default' => ''], // shorthand

        // Border style
        'borderTopStyle' => ['type' => 'string', 'default' => 'solid'],
        'borderRightStyle' => ['type' => 'string', 'default' => 'solid'],
        'borderBottomStyle' => ['type' => 'string', 'default' => 'solid'],
        'borderLeftStyle' => ['type' => 'string', 'default' => 'solid'],
        'borderStyle' => ['type' => 'string', 'default' => 'solid'], // shorthand

        // Border color
        'borderTopColor' => ['type' => 'string', 'default' => ''],
        'borderRightColor' => ['type' => 'string', 'default' => ''],
        'borderBottomColor' => ['type' => 'string', 'default' => ''],
        'borderLeftColor' => ['type' => 'string', 'default' => ''],
        'borderColor' => ['type' => 'string', 'default' => ''], // shorthand

        // Border radius (individual corners)
        'borderTopLeftRadius' => ['type' => 'string', 'default' => ''],
        'borderTopRightRadius' => ['type' => 'string', 'default' => ''],
        'borderBottomLeftRadius' => ['type' => 'string', 'default' => ''],
        'borderBottomRightRadius' => ['type' => 'string', 'default' => ''],
        'borderRadius' => ['type' => 'string', 'default' => ''], // shorthand

        // Responsive
        'borderRadiusTablet' => ['type' => 'string', 'default' => ''],
        'borderRadiusMobile' => ['type' => 'string', 'default' => ''],

        // Linked toggle
        'linkedWidth' => ['type' => 'boolean', 'default' => true],
        'linkedRadius' => ['type' => 'boolean', 'default' => true],
    ];

    /**
     * Get control type identifier
     */
    public function getType(): string
    {
        return 'jankx/border';
    }

    /**
     * Render editor component placeholder
     */
    public function renderEditor($value, callable $onChange): string
    {
        return '<BorderInspector />';
    }

    /**
     * Generate CSS for border settings
     */
    public function generateCss($value, string $selector): string
    {
        $css = '';

        // Border width CSS
        $css .= $this->generateBorderWidthCss($value, $selector);

        // Border style CSS
        $css .= $this->generateBorderStyleCss($value, $selector);

        // Border color CSS
        $css .= $this->generateBorderColorCss($value, $selector);

        // Border radius CSS
        $css .= $this->generateBorderRadiusCss($value, $selector);

        // Responsive radius
        $css .= $this->generateResponsiveRadiusCss($value, $selector);

        return $css;
    }

    /**
     * Generate border width CSS
     */
    protected function generateBorderWidthCss(array $value, string $selector): string
    {
        $css = '';

        // Check for shorthand first
        $shorthand = $value['borderWidth'] ?? '';
        if ($shorthand) {
            return sprintf("%s { border-width: %s; }\n", $selector, $shorthand);
        }

        // Individual sides
        $sides = [
            'top' => $value['borderTopWidth'] ?? '',
            'right' => $value['borderRightWidth'] ?? '',
            'bottom' => $value['borderBottomWidth'] ?? '',
            'left' => $value['borderLeftWidth'] ?? '',
        ];

        $hasBorder = false;
        foreach ($sides as $side => $width) {
            if ($width && $width !== '0' && $width !== '0px') {
                $css .= sprintf("    border-%s-width: %s;\n", $side, $width);
                $hasBorder = true;
            }
        }

        if ($hasBorder) {
            $css = sprintf("%s {\n%s}\n", $selector, $css);
        }

        return $css;
    }

    /**
     * Generate border style CSS
     */
    protected function generateBorderStyleCss(array $value, string $selector): string
    {
        $css = '';

        // Check for shorthand first
        $shorthand = $value['borderStyle'] ?? '';
        if ($shorthand && $shorthand !== 'none') {
            return sprintf("%s { border-style: %s; }\n", $selector, $shorthand);
        }

        // Individual sides (only if different from solid)
        $sides = [
            'top' => $value['borderTopStyle'] ?? 'solid',
            'right' => $value['borderRightStyle'] ?? 'solid',
            'bottom' => $value['borderBottomStyle'] ?? 'solid',
            'left' => $value['borderLeftStyle'] ?? 'solid',
        ];

        foreach ($sides as $side => $style) {
            if ($style !== 'solid') {
                $css .= sprintf("    border-%s-style: %s;\n", $side, $style);
            }
        }

        if ($css) {
            $css = sprintf("%s {\n%s}\n", $selector, $css);
        }

        return $css;
    }

    /**
     * Generate border color CSS
     */
    protected function generateBorderColorCss(array $value, string $selector): string
    {
        $css = '';

        // Check for shorthand first
        $shorthand = $value['borderColor'] ?? '';
        if ($shorthand) {
            return sprintf("%s { border-color: %s; }\n", $selector, $shorthand);
        }

        // Individual sides
        $sides = [
            'top' => $value['borderTopColor'] ?? '',
            'right' => $value['borderRightColor'] ?? '',
            'bottom' => $value['borderBottomColor'] ?? '',
            'left' => $value['borderLeftColor'] ?? '',
        ];

        foreach ($sides as $side => $color) {
            if ($color) {
                $css .= sprintf("    border-%s-color: %s;\n", $side, $color);
            }
        }

        if ($css) {
            $css = sprintf("%s {\n%s}\n", $selector, $css);
        }

        return $css;
    }

    /**
     * Generate border radius CSS
     */
    protected function generateBorderRadiusCss(array $value, string $selector): string
    {
        $css = '';

        // Check for shorthand first
        $shorthand = $value['borderRadius'] ?? '';
        if ($shorthand) {
            return sprintf("%s { border-radius: %s; }\n", $selector, $shorthand);
        }

        // Check linked mode - all corners same
        $linked = $value['linkedRadius'] ?? true;
        if ($linked) {
            $corner = $value['borderTopLeftRadius'] ?? '';
            if ($corner) {
                return sprintf("%s { border-radius: %s; }\n", $selector, $corner);
            }
            return '';
        }

        // Individual corners
        $corners = [
            'top-left' => $value['borderTopLeftRadius'] ?? '',
            'top-right' => $value['borderTopRightRadius'] ?? '',
            'bottom-right' => $value['borderBottomRightRadius'] ?? '',
            'bottom-left' => $value['borderBottomLeftRadius'] ?? '',
        ];

        // Try to create shorthand
        $values = array_values($corners);
        $shorthand = $this->generateShorthand($values);

        if ($shorthand) {
            return sprintf("%s { border-radius: %s; }\n", $selector, $shorthand);
        }

        // Individual declarations
        foreach ($corners as $corner => $radius) {
            if ($radius) {
                $css .= sprintf("    border-%s-radius: %s;\n", $corner, $radius);
            }
        }

        if ($css) {
            $css = sprintf("%s {\n%s}\n", $selector, $css);
        }

        return $css;
    }

    /**
     * Generate responsive border radius CSS
     */
    protected function generateResponsiveRadiusCss(array $value, string $selector): string
    {
        $css = '';

        $tabletRadius = $value['borderRadiusTablet'] ?? '';
        if ($tabletRadius) {
            $css .= sprintf(
                "@media (max-width: 1024px) { %s { border-radius: %s; } }\n",
                $selector,
                $tabletRadius
            );
        }

        $mobileRadius = $value['borderRadiusMobile'] ?? '';
        if ($mobileRadius) {
            $css .= sprintf(
                "@media (max-width: 767px) { %s { border-radius: %s; } }\n",
                $selector,
                $mobileRadius
            );
        }

        return $css;
    }

    /**
     * Generate shorthand from 4 values
     */
    protected function generateShorthand(array $values): ?string
    {
        // Remove empty values
        $filtered = array_filter($values);
        if (empty($filtered)) {
            return null;
        }

        // All same
        if (count(array_unique($filtered)) === 1) {
            return $filtered[0];
        }

        // Check for top-bottom same, left-right same
        if (count($values) === 4) {
            if ($values[0] === $values[2] && $values[1] === $values[3]) {
                return sprintf('%s %s', $values[0], $values[1]);
            }
        }

        // Return all 4 values
        return implode(' ', array_map(function($v) { return $v ?: '0'; }, $values));
    }

    /**
     * Get border style options
     */
    public function getBorderStyleOptions(): array
    {
        return [
            'none' => __('None', 'jankx'),
            'solid' => __('Solid', 'jankx'),
            'dashed' => __('Dashed', 'jankx'),
            'dotted' => __('Dotted', 'jankx'),
            'double' => __('Double', 'jankx'),
            'groove' => __('Groove', 'jankx'),
            'ridge' => __('Ridge', 'jankx'),
            'inset' => __('Inset', 'jankx'),
            'outset' => __('Outset', 'jankx'),
        ];
    }

    /**
     * Get border width presets
     */
    public function getBorderWidthPresets(): array
    {
        return [
            ['value' => '0', 'label' => __('None', 'jankx')],
            ['value' => '1px', 'label' => __('Hairline', 'jankx')],
            ['value' => '2px', 'label' => __('Thin', 'jankx')],
            ['value' => '4px', 'label' => __('Medium', 'jankx')],
            ['value' => '6px', 'label' => __('Thick', 'jankx')],
            ['value' => '8px', 'label' => __('Extra Thick', 'jankx')],
        ];
    }

    /**
     * Get border radius presets
     */
    public function getBorderRadiusPresets(): array
    {
        return [
            ['value' => '0', 'label' => __('None', 'jankx')],
            ['value' => '2px', 'label' => __('XS', 'jankx')],
            ['value' => '4px', 'label' => __('Small', 'jankx')],
            ['value' => '8px', 'label' => __('Medium', 'jankx')],
            ['value' => '12px', 'label' => __('Large', 'jankx')],
            ['value' => '16px', 'label' => __('XL', 'jankx')],
            ['value' => '24px', 'label' => __('2XL', 'jankx')],
            ['value' => '9999px', 'label' => __('Full', 'jankx')],
        ];
    }

    /**
     * Get border preset styles
     */
    public function getBorderPresets(): array
    {
        return [
            'none' => [
                'label' => __('No Border', 'jankx'),
                'value' => ['borderWidth' => '0', 'borderStyle' => 'none'],
            ],
            'solid-thin' => [
                'label' => __('Solid Thin', 'jankx'),
                'value' => ['borderWidth' => '1px', 'borderStyle' => 'solid'],
            ],
            'solid-medium' => [
                'label' => __('Solid Medium', 'jankx'),
                'value' => ['borderWidth' => '2px', 'borderStyle' => 'solid'],
            ],
            'dashed' => [
                'label' => __('Dashed', 'jankx'),
                'value' => ['borderWidth' => '2px', 'borderStyle' => 'dashed'],
            ],
            'dotted' => [
                'label' => __('Dotted', 'jankx'),
                'value' => ['borderWidth' => '2px', 'borderStyle' => 'dotted'],
            ],
            'double' => [
                'label' => __('Double', 'jankx'),
                'value' => ['borderWidth' => '4px', 'borderStyle' => 'double'],
            ],
        ];
    }
}
