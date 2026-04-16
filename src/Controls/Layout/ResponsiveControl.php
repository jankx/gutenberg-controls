<?php

namespace Jankx\Gutenberg\Controls\Layout;

use Jankx\Gutenberg\Controls\AbstractControl;

/**
 * Responsive Control - Device visibility and responsive breakpoints
 *
 * High-UX component for managing responsive behavior:
 * - Device visibility toggles (desktop/tablet/mobile)
 * - Breakpoint overrides
 * - Responsive order/flex direction
 * - Column span adjustments per device
 * - Hide on specific devices
 *
 * @package Jankx\Gutenberg\Controls\Layout
 */
class ResponsiveControl extends AbstractControl
{
    /**
     * Control category
     */
    protected string $category = 'layout';

    /**
     * Supported devices
     */
    protected array $devices = ['desktop', 'tablet', 'mobile'];

    /**
     * Default breakpoints
     */
    protected array $breakpoints = [
        'desktop' => ['min' => 1025, 'max' => null],
        'tablet'  => ['min' => 768, 'max' => 1024],
        'mobile'  => ['min' => null, 'max' => 767],
    ];

    /**
     * Control attributes with defaults
     */
    protected array $attributes = [
        // Visibility on devices
        'hideOnDesktop' => ['type' => 'boolean', 'default' => false],
        'hideOnTablet'  => ['type' => 'boolean', 'default' => false],
        'hideOnMobile'  => ['type' => 'boolean', 'default' => false],

        // Column span per device (for grid systems)
        'colSpanDesktop' => ['type' => 'number', 'default' => 12],
        'colSpanTablet'  => ['type' => 'number', 'default' => 12],
        'colSpanMobile'  => ['type' => 'number', 'default' => 12],

        // Order per device
        'orderDesktop' => ['type' => 'number', 'default' => 0],
        'orderTablet'  => ['type' => 'number', 'default' => null],
        'orderMobile'  => ['type' => 'number', 'default' => null],

        // Flex direction per device
        'flexDirectionDesktop' => ['type' => 'string', 'default' => 'row'],
        'flexDirectionTablet'  => ['type' => 'string', 'default' => null],
        'flexDirectionMobile'  => ['type' => 'string', 'default' => null],

        // Alignment per device
        'alignItemsDesktop' => ['type' => 'string', 'default' => 'stretch'],
        'alignItemsTablet'  => ['type' => 'string', 'default' => null],
        'alignItemsMobile'  => ['type' => 'string', 'default' => null],

        'justifyContentDesktop' => ['type' => 'string', 'default' => 'flex-start'],
        'justifyContentTablet'  => ['type' => 'string', 'default' => null],
        'justifyContentMobile'  => ['type' => 'string', 'default' => null],

        // Text alignment per device
        'textAlignDesktop' => ['type' => 'string', 'default' => 'left'],
        'textAlignTablet'  => ['type' => 'string', 'default' => null],
        'textAlignMobile'  => ['type' => 'string', 'default' => null],

        // Stack behavior
        'stackVertically'     => ['type' => 'boolean', 'default' => false],
        'stackAtBreakpoint'   => ['type' => 'string', 'default' => 'mobile'],
        'reverseStackOrder'   => ['type' => 'boolean', 'default' => false],

        // Spacing overrides per device
        'paddingMobile'   => ['type' => 'object', 'default' => []],
        'marginMobile'    => ['type' => 'object', 'default' => []],
        'paddingTablet'   => ['type' => 'object', 'default' => []],
        'marginTablet'    => ['type' => 'object', 'default' => []],

        // Font size overrides
        'fontSizeDesktop' => ['type' => 'string', 'default' => null],
        'fontSizeTablet'  => ['type' => 'string', 'default' => null],
        'fontSizeMobile'  => ['type' => 'string', 'default' => null],
    ];

    /**
     * Get control type identifier
     */
    public function getType(): string
    {
        return 'jankx/responsive';
    }

    /**
     * Render editor component placeholder
     */
    public function renderEditor($value, callable $onChange): string
    {
        return '<ResponsiveInspector />';
    }

    /**
     * Generate CSS for responsive settings
     */
    public function generateCss($value, string $selector): string
    {
        $css = '';

        // Generate visibility CSS
        $css .= $this->generateVisibilityCss($value, $selector);

        // Generate column span CSS
        $css .= $this->generateColSpanCss($value, $selector);

        // Generate order CSS
        $css .= $this->generateOrderCss($value, $selector);

        // Generate flex direction CSS
        $css .= $this->generateFlexDirectionCss($value, $selector);

        // Generate alignment CSS
        $css .= $this->generateAlignmentCss($value, $selector);

        // Generate text alignment CSS
        $css .= $this->generateTextAlignCss($value, $selector);

        // Generate spacing overrides CSS
        $css .= $this->generateSpacingOverrideCss($value, $selector);

        // Generate font size overrides CSS
        $css .= $this->generateFontSizeCss($value, $selector);

        // Generate stacking behavior CSS
        $css .= $this->generateStackCss($value, $selector);

        return $css;
    }

    /**
     * Generate visibility CSS
     */
    protected function generateVisibilityCss(array $value, string $selector): string
    {
        $css = '';

        if (!empty($value['hideOnDesktop'])) {
            $css .= sprintf("%s { display: none !important; }\n", $selector);
        }

        if (!empty($value['hideOnTablet'])) {
            $css .= sprintf("@media (max-width: 1024px) { %s { display: none !important; } }\n", $selector);
        }

        if (!empty($value['hideOnMobile'])) {
            $css .= sprintf("@media (max-width: 767px) { %s { display: none !important; } }\n", $selector);
        }

        return $css;
    }

    /**
     * Generate column span CSS (grid system)
     */
    protected function generateColSpanCss(array $value, string $selector): string
    {
        $css = '';

        // Desktop (default)
        $desktopSpan = $value['colSpanDesktop'] ?? 12;
        $css .= sprintf(
            "%s { grid-column: span %d / span %d; }\n",
            $selector,
            $desktopSpan,
            $desktopSpan
        );

        // Tablet
        $tabletSpan = $value['colSpanTablet'] ?? null;
        if ($tabletSpan !== null && $tabletSpan !== $desktopSpan) {
            $css .= sprintf(
                "@media (max-width: 1024px) { %s { grid-column: span %d / span %d; } }\n",
                $selector,
                $tabletSpan,
                $tabletSpan
            );
        }

        // Mobile
        $mobileSpan = $value['colSpanMobile'] ?? null;
        if ($mobileSpan !== null && $mobileSpan !== ($tabletSpan ?? $desktopSpan)) {
            $css .= sprintf(
                "@media (max-width: 767px) { %s { grid-column: span %d / span %d; } }\n",
                $selector,
                $mobileSpan,
                $mobileSpan
            );
        }

        return $css;
    }

    /**
     * Generate order CSS
     */
    protected function generateOrderCss(array $value, string $selector): string
    {
        $css = '';

        // Desktop
        $desktopOrder = $value['orderDesktop'] ?? 0;
        if ($desktopOrder !== 0) {
            $css .= sprintf("%s { order: %d; }\n", $selector, $desktopOrder);
        }

        // Tablet
        $tabletOrder = $value['orderTablet'] ?? null;
        if ($tabletOrder !== null) {
            $css .= sprintf(
                "@media (max-width: 1024px) { %s { order: %d; } }\n",
                $selector,
                $tabletOrder
            );
        }

        // Mobile
        $mobileOrder = $value['orderMobile'] ?? null;
        if ($mobileOrder !== null && $mobileOrder !== $tabletOrder) {
            $css .= sprintf(
                "@media (max-width: 767px) { %s { order: %d; } }\n",
                $selector,
                $mobileOrder
            );
        }

        return $css;
    }

    /**
     * Generate flex direction CSS
     */
    protected function generateFlexDirectionCss(array $value, string $selector): string
    {
        $css = '';

        // Desktop
        $desktopDir = $value['flexDirectionDesktop'] ?? 'row';

        // Tablet
        $tabletDir = $value['flexDirectionTablet'] ?? null;
        if ($tabletDir !== null && $tabletDir !== $desktopDir) {
            $css .= sprintf(
                "@media (max-width: 1024px) { %s { flex-direction: %s; } }\n",
                $selector,
                $tabletDir
            );
        }

        // Mobile
        $mobileDir = $value['flexDirectionMobile'] ?? null;
        if ($mobileDir !== null && $mobileDir !== ($tabletDir ?? $desktopDir)) {
            $css .= sprintf(
                "@media (max-width: 767px) { %s { flex-direction: %s; } }\n",
                $selector,
                $mobileDir
            );
        }

        return $css;
    }

    /**
     * Generate alignment CSS
     */
    protected function generateAlignmentCss(array $value, string $selector): string
    {
        $css = '';

        // Desktop
        $desktopAlign = $value['alignItemsDesktop'] ?? 'stretch';
        $desktopJustify = $value['justifyContentDesktop'] ?? 'flex-start';

        // Tablet
        $tabletAlign = $value['alignItemsTablet'] ?? null;
        $tabletJustify = $value['justifyContentTablet'] ?? null;

        if ($tabletAlign !== null || $tabletJustify !== null) {
            $tabletAlignCss = $tabletAlign ?? $desktopAlign;
            $tabletJustifyCss = $tabletJustify ?? $desktopJustify;
            $css .= sprintf(
                "@media (max-width: 1024px) { %s { align-items: %s; justify-content: %s; } }\n",
                $selector,
                $tabletAlignCss,
                $tabletJustifyCss
            );
        }

        // Mobile
        $mobileAlign = $value['alignItemsMobile'] ?? null;
        $mobileJustify = $value['justifyContentMobile'] ?? null;

        if (($mobileAlign !== null && $mobileAlign !== ($tabletAlign ?? $desktopAlign)) ||
            ($mobileJustify !== null && $mobileJustify !== ($tabletJustify ?? $desktopJustify))) {
            $mobileAlignCss = $mobileAlign ?? ($tabletAlign ?? $desktopAlign);
            $mobileJustifyCss = $mobileJustify ?? ($tabletJustify ?? $desktopJustify);
            $css .= sprintf(
                "@media (max-width: 767px) { %s { align-items: %s; justify-content: %s; } }\n",
                $selector,
                $mobileAlignCss,
                $mobileJustifyCss
            );
        }

        return $css;
    }

    /**
     * Generate text alignment CSS
     */
    protected function generateTextAlignCss(array $value, string $selector): string
    {
        $css = '';

        // Desktop
        $desktopAlign = $value['textAlignDesktop'] ?? 'left';

        // Tablet
        $tabletAlign = $value['textAlignTablet'] ?? null;
        if ($tabletAlign !== null && $tabletAlign !== $desktopAlign) {
            $css .= sprintf(
                "@media (max-width: 1024px) { %s { text-align: %s; } }\n",
                $selector,
                $tabletAlign
            );
        }

        // Mobile
        $mobileAlign = $value['textAlignMobile'] ?? null;
        if ($mobileAlign !== null && $mobileAlign !== ($tabletAlign ?? $desktopAlign)) {
            $css .= sprintf(
                "@media (max-width: 767px) { %s { text-align: %s; } }\n",
                $selector,
                $mobileAlign
            );
        }

        return $css;
    }

    /**
     * Generate spacing override CSS
     */
    protected function generateSpacingOverrideCss(array $value, string $selector): string
    {
        $css = '';

        // Tablet spacing
        if (!empty($value['paddingTablet'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['paddingTablet'],
                $selector,
                'padding',
                1024
            );
        }
        if (!empty($value['marginTablet'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['marginTablet'],
                $selector,
                'margin',
                1024
            );
        }

        // Mobile spacing
        if (!empty($value['paddingMobile'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['paddingMobile'],
                $selector,
                'padding',
                767
            );
        }
        if (!empty($value['marginMobile'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['marginMobile'],
                $selector,
                'margin',
                767
            );
        }

        return $css;
    }

    /**
     * Generate responsive spacing CSS
     */
    protected function generateResponsiveSpacing(
        array $spacing,
        string $selector,
        string $property,
        int $breakpoint
    ): string {
        $sides = ['top', 'right', 'bottom', 'left'];
        $values = [];

        foreach ($sides as $side) {
            $values[$side] = $spacing[$side] ?? '0';
        }

        // Check if all values are the same (shorthand possible)
        $uniqueValues = array_unique(array_values($values));
        $isShorthand = count($uniqueValues) === 1;

        if ($isShorthand && $uniqueValues[0] !== '0') {
            return sprintf(
                "@media (max-width: %dpx) { %s { %s: %s; } }\n",
                $breakpoint,
                $selector,
                $property,
                $uniqueValues[0]
            );
        }

        // Generate individual properties
        $css = sprintf("@media (max-width: %dpx) { %s {\n", $breakpoint, $selector);
        foreach ($values as $side => $val) {
            if ($val !== '0') {
                $css .= sprintf("    %s-%s: %s;\n", $property, $side, $val);
            }
        }
        $css .= "}\n}\n";

        return $css;
    }

    /**
     * Generate font size CSS
     */
    protected function generateFontSizeCss(array $value, string $selector): string
    {
        $css = '';

        // Tablet
        if (!empty($value['fontSizeTablet'])) {
            $css .= sprintf(
                "@media (max-width: 1024px) { %s { font-size: %s; } }\n",
                $selector,
                $value['fontSizeTablet']
            );
        }

        // Mobile
        if (!empty($value['fontSizeMobile'])) {
            $css .= sprintf(
                "@media (max-width: 767px) { %s { font-size: %s; } }\n",
                $selector,
                $value['fontSizeMobile']
            );
        }

        return $css;
    }

    /**
     * Generate stacking CSS (flex direction column at breakpoints)
     */
    protected function generateStackCss(array $value, string $selector): string
    {
        $css = '';

        if (empty($value['stackVertically'])) {
            return $css;
        }

        $breakpoint = $value['stackAtBreakpoint'] ?? 'mobile';
        $maxWidth = $breakpoint === 'tablet' ? 1024 : 767;

        // Flex direction column
        $css .= sprintf(
            "@media (max-width: %dpx) { %s { flex-direction: column; } }\n",
            $maxWidth,
            $selector
        );

        // Reverse order if enabled
        if (!empty($value['reverseStackOrder'])) {
            $css .= sprintf(
                "@media (max-width: %dpx) { %s { flex-direction: column-reverse; } }\n",
                $maxWidth,
                $selector
            );
        }

        return $css;
    }

    /**
     * Get breakpoints configuration
     */
    public function getBreakpoints(): array
    {
        return $this->breakpoints;
    }

    /**
     * Set custom breakpoints
     */
    public function setBreakpoints(array $breakpoints): self
    {
        $this->breakpoints = array_merge($this->breakpoints, $breakpoints);
        return $this;
    }

    /**
     * Get available flex direction options
     */
    public function getFlexDirectionOptions(): array
    {
        return [
            'row'            => __('Row', 'jankx'),
            'row-reverse'    => __('Row Reverse', 'jankx'),
            'column'         => __('Column', 'jankx'),
            'column-reverse' => __('Column Reverse', 'jankx'),
        ];
    }

    /**
     * Get available alignment options
     */
    public function getAlignItemsOptions(): array
    {
        return [
            'stretch'     => __('Stretch', 'jankx'),
            'flex-start'  => __('Start', 'jankx'),
            'flex-end'    => __('End', 'jankx'),
            'center'      => __('Center', 'jankx'),
            'baseline'    => __('Baseline', 'jankx'),
        ];
    }

    /**
     * Get available justify content options
     */
    public function getJustifyContentOptions(): array
    {
        return [
            'flex-start'    => __('Start', 'jankx'),
            'flex-end'      => __('End', 'jankx'),
            'center'        => __('Center', 'jankx'),
            'space-between' => __('Space Between', 'jankx'),
            'space-around'  => __('Space Around', 'jankx'),
            'space-evenly'  => __('Space Evenly', 'jankx'),
        ];
    }

    /**
     * Get available text align options
     */
    public function getTextAlignOptions(): array
    {
        return [
            'left'    => __('Left', 'jankx'),
            'center'  => __('Center', 'jankx'),
            'right'   => __('Right', 'jankx'),
            'justify' => __('Justify', 'jankx'),
        ];
    }
}
