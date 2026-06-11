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
    protected array $devices = ['ultrawide', 'desktop', 'tablet', 'mobile'];

    /**
     * Default breakpoints
     */
    protected array $breakpoints = [
        'ultrawide' => ['min' => 1600, 'max' => null],
        'desktop'   => ['min' => 1025, 'max' => 1599],
        'tablet'    => ['min' => 768, 'max' => 1024],
        'mobile'    => ['min' => null, 'max' => 767],
    ];

    /**
     * Control attributes with defaults
     */
    protected array $attributes = [
        // Visibility on devices
        'hideOnUltrawide' => ['type' => 'boolean', 'default' => false],
        'hideOnDesktop'   => ['type' => 'boolean', 'default' => false],
        'hideOnTablet'    => ['type' => 'boolean', 'default' => false],
        'hideOnMobile'    => ['type' => 'boolean', 'default' => false],

        // Column span per device (for grid systems)
        'colSpanUltrawide' => ['type' => 'number', 'default' => 12],
        'colSpanDesktop'   => ['type' => 'number', 'default' => 12],
        'colSpanTablet'    => ['type' => 'number', 'default' => 12],
        'colSpanMobile'    => ['type' => 'number', 'default' => 12],

        // Order per device
        'orderUltrawide' => ['type' => 'number', 'default' => null],
        'orderDesktop'   => ['type' => 'number', 'default' => 0],
        'orderTablet'    => ['type' => 'number', 'default' => null],
        'orderMobile'    => ['type' => 'number', 'default' => null],

        // Flex direction per device
        'flexDirectionUltrawide' => ['type' => 'string', 'default' => null],
        'flexDirectionDesktop'   => ['type' => 'string', 'default' => 'row'],
        'flexDirectionTablet'    => ['type' => 'string', 'default' => null],
        'flexDirectionMobile'    => ['type' => 'string', 'default' => null],

        // Alignment per device
        'alignItemsUltrawide' => ['type' => 'string', 'default' => null],
        'alignItemsDesktop'   => ['type' => 'string', 'default' => 'stretch'],
        'alignItemsTablet'    => ['type' => 'string', 'default' => null],
        'alignItemsMobile'    => ['type' => 'string', 'default' => null],

        'justifyContentUltrawide' => ['type' => 'string', 'default' => null],
        'justifyContentDesktop'   => ['type' => 'string', 'default' => 'flex-start'],
        'justifyContentTablet'    => ['type' => 'string', 'default' => null],
        'justifyContentMobile'    => ['type' => 'string', 'default' => null],

        // Text alignment per device
        'textAlignUltrawide' => ['type' => 'string', 'default' => null],
        'textAlignDesktop'   => ['type' => 'string', 'default' => 'left'],
        'textAlignTablet'    => ['type' => 'string', 'default' => null],
        'textAlignMobile'    => ['type' => 'string', 'default' => null],

        // Stack behavior
        'stackVertically'     => ['type' => 'boolean', 'default' => false],
        'stackAtBreakpoint'   => ['type' => 'string', 'default' => 'mobile'],
        'reverseStackOrder'   => ['type' => 'boolean', 'default' => false],

        // Spacing overrides per device
        'paddingUltrawide' => ['type' => 'object', 'default' => []],
        'marginUltrawide'  => ['type' => 'object', 'default' => []],
        'paddingDesktop'   => ['type' => 'object', 'default' => []],
        'marginDesktop'    => ['type' => 'object', 'default' => []],
        'paddingTablet'    => ['type' => 'object', 'default' => []],
        'marginTablet'     => ['type' => 'object', 'default' => []],
        'paddingMobile'    => ['type' => 'object', 'default' => []],
        'marginMobile'     => ['type' => 'object', 'default' => []],

        // Font size overrides
        'fontSizeUltrawide' => ['type' => 'string', 'default' => null],
        'fontSizeDesktop'   => ['type' => 'string', 'default' => null],
        'fontSizeTablet'    => ['type' => 'string', 'default' => null],
        'fontSizeMobile'    => ['type' => 'string', 'default' => null],
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
        // Apply breakpoint filter so theme options can override
        $this->breakpoints = apply_filters('jankx_responsive_breakpoints', $this->breakpoints);

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

        if (!empty($value['hideOnUltrawide'])) {
            $mq = $this->getMediaQuery('ultrawide');
            $css .= sprintf("%s { %s { display: none !important; } }\n", $mq, $selector);
        }

        if (!empty($value['hideOnDesktop'])) {
            $css .= sprintf("%s { display: none !important; }\n", $selector);
        }

        if (!empty($value['hideOnTablet'])) {
            $mq = $this->getMediaQuery('tablet');
            $css .= sprintf("%s { %s { display: none !important; } }\n", $mq, $selector);
        }

        if (!empty($value['hideOnMobile'])) {
            $mq = $this->getMediaQuery('mobile');
            $css .= sprintf("%s { %s { display: none !important; } }\n", $mq, $selector);
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

        // Ultrawide
        $ultrawideSpan = $value['colSpanUltrawide'] ?? null;
        if ($ultrawideSpan !== null && $ultrawideSpan !== $desktopSpan) {
            $mq = $this->getMediaQuery('ultrawide');
            $css .= sprintf(
                "%s { %s { grid-column: span %d / span %d; } }\n",
                $mq,
                $selector,
                $ultrawideSpan,
                $ultrawideSpan
            );
        }

        // Tablet
        $tabletSpan = $value['colSpanTablet'] ?? null;
        if ($tabletSpan !== null && $tabletSpan !== $desktopSpan) {
            $mq = $this->getMediaQuery('tablet');
            $css .= sprintf(
                "%s { %s { grid-column: span %d / span %d; } }\n",
                $mq,
                $selector,
                $tabletSpan,
                $tabletSpan
            );
        }

        // Mobile
        $mobileSpan = $value['colSpanMobile'] ?? null;
        if ($mobileSpan !== null && $mobileSpan !== ($tabletSpan ?? $desktopSpan)) {
            $mq = $this->getMediaQuery('mobile');
            $css .= sprintf(
                "%s { %s { grid-column: span %d / span %d; } }\n",
                $mq,
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

        // Ultrawide
        $ultrawideOrder = $value['orderUltrawide'] ?? null;
        if ($ultrawideOrder !== null && $ultrawideOrder !== $desktopOrder) {
            $mq = $this->getMediaQuery('ultrawide');
            $css .= sprintf(
                "%s { %s { order: %d; } }\n",
                $mq,
                $selector,
                $ultrawideOrder
            );
        }

        // Tablet
        $tabletOrder = $value['orderTablet'] ?? null;
        if ($tabletOrder !== null) {
            $mq = $this->getMediaQuery('tablet');
            $css .= sprintf(
                "%s { %s { order: %d; } }\n",
                $mq,
                $selector,
                $tabletOrder
            );
        }

        // Mobile
        $mobileOrder = $value['orderMobile'] ?? null;
        if ($mobileOrder !== null && $mobileOrder !== $tabletOrder) {
            $mq = $this->getMediaQuery('mobile');
            $css .= sprintf(
                "%s { %s { order: %d; } }\n",
                $mq,
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

        // Ultrawide
        $ultrawideDir = $value['flexDirectionUltrawide'] ?? null;
        if ($ultrawideDir !== null && $ultrawideDir !== $desktopDir) {
            $mq = $this->getMediaQuery('ultrawide');
            $css .= sprintf(
                "%s { %s { flex-direction: %s; } }\n",
                $mq,
                $selector,
                $ultrawideDir
            );
        }

        // Tablet
        $tabletDir = $value['flexDirectionTablet'] ?? null;
        if ($tabletDir !== null && $tabletDir !== $desktopDir) {
            $mq = $this->getMediaQuery('tablet');
            $css .= sprintf(
                "%s { %s { flex-direction: %s; } }\n",
                $mq,
                $selector,
                $tabletDir
            );
        }

        // Mobile
        $mobileDir = $value['flexDirectionMobile'] ?? null;
        if ($mobileDir !== null && $mobileDir !== ($tabletDir ?? $desktopDir)) {
            $mq = $this->getMediaQuery('mobile');
            $css .= sprintf(
                "%s { %s { flex-direction: %s; } }\n",
                $mq,
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

        // Ultrawide
        $ultrawideAlign = $value['alignItemsUltrawide'] ?? null;
        $ultrawideJustify = $value['justifyContentUltrawide'] ?? null;

        if ($ultrawideAlign !== null || $ultrawideJustify !== null) {
            $ultrawideAlignCss = $ultrawideAlign ?? $desktopAlign;
            $ultrawideJustifyCss = $ultrawideJustify ?? $desktopJustify;
            $mq = $this->getMediaQuery('ultrawide');
            $css .= sprintf(
                "%s { %s { align-items: %s; justify-content: %s; } }\n",
                $mq,
                $selector,
                $ultrawideAlignCss,
                $ultrawideJustifyCss
            );
        }

        // Tablet
        $tabletAlign = $value['alignItemsTablet'] ?? null;
        $tabletJustify = $value['justifyContentTablet'] ?? null;

        if ($tabletAlign !== null || $tabletJustify !== null) {
            $tabletAlignCss = $tabletAlign ?? $desktopAlign;
            $tabletJustifyCss = $tabletJustify ?? $desktopJustify;
            $mq = $this->getMediaQuery('tablet');
            $css .= sprintf(
                "%s { %s { align-items: %s; justify-content: %s; } }\n",
                $mq,
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
            $mq = $this->getMediaQuery('mobile');
            $css .= sprintf(
                "%s { %s { align-items: %s; justify-content: %s; } }\n",
                $mq,
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

        // Ultrawide
        $ultrawideAlign = $value['textAlignUltrawide'] ?? null;
        if ($ultrawideAlign !== null && $ultrawideAlign !== $desktopAlign) {
            $mq = $this->getMediaQuery('ultrawide');
            $css .= sprintf(
                "%s { %s { text-align: %s; } }\n",
                $mq,
                $selector,
                $ultrawideAlign
            );
        }

        // Tablet
        $tabletAlign = $value['textAlignTablet'] ?? null;
        if ($tabletAlign !== null && $tabletAlign !== $desktopAlign) {
            $mq = $this->getMediaQuery('tablet');
            $css .= sprintf(
                "%s { %s { text-align: %s; } }\n",
                $mq,
                $selector,
                $tabletAlign
            );
        }

        // Mobile
        $mobileAlign = $value['textAlignMobile'] ?? null;
        if ($mobileAlign !== null && $mobileAlign !== ($tabletAlign ?? $desktopAlign)) {
            $mq = $this->getMediaQuery('mobile');
            $css .= sprintf(
                "%s { %s { text-align: %s; } }\n",
                $mq,
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

        // Ultrawide spacing
        if (!empty($value['paddingUltrawide'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['paddingUltrawide'],
                $selector,
                'padding',
                'ultrawide'
            );
        }
        if (!empty($value['marginUltrawide'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['marginUltrawide'],
                $selector,
                'margin',
                'ultrawide'
            );
        }

        // Desktop spacing (base - no media query)
        if (!empty($value['paddingDesktop'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['paddingDesktop'],
                $selector,
                'padding',
                'desktop'
            );
        }
        if (!empty($value['marginDesktop'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['marginDesktop'],
                $selector,
                'margin',
                'desktop'
            );
        }

        // Tablet spacing
        if (!empty($value['paddingTablet'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['paddingTablet'],
                $selector,
                'padding',
                'tablet'
            );
        }
        if (!empty($value['marginTablet'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['marginTablet'],
                $selector,
                'margin',
                'tablet'
            );
        }

        // Mobile spacing
        if (!empty($value['paddingMobile'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['paddingMobile'],
                $selector,
                'padding',
                'mobile'
            );
        }
        if (!empty($value['marginMobile'])) {
            $css .= $this->generateResponsiveSpacing(
                $value['marginMobile'],
                $selector,
                'margin',
                'mobile'
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
        string $device
    ): string {
        $sides = ['top', 'right', 'bottom', 'left'];
        $values = [];

        foreach ($sides as $side) {
            $values[$side] = $spacing[$side] ?? '0';
        }

        // Check if all values are the same (shorthand possible)
        $uniqueValues = array_unique(array_values($values));
        $isShorthand = count($uniqueValues) === 1;

        // Desktop base - no media query
        if ($device === 'desktop') {
            if ($isShorthand && $uniqueValues[0] !== '0') {
                return sprintf(
                    "%s { %s: %s; }\n",
                    $selector,
                    $property,
                    $uniqueValues[0]
                );
            }

            $css = sprintf("%s {\n", $selector);
            foreach ($values as $side => $val) {
                if ($val !== '0') {
                    $css .= sprintf("    %s-%s: %s;\n", $property, $side, $val);
                }
            }
            $css .= "}\n";
            return $css;
        }

        // Other devices - wrap in media query
        $mq = $this->getMediaQuery($device);

        if ($isShorthand && $uniqueValues[0] !== '0') {
            return sprintf(
                "%s { %s { %s: %s; } }\n",
                $mq,
                $selector,
                $property,
                $uniqueValues[0]
            );
        }

        $css = sprintf("%s { %s {\n", $mq, $selector);
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

        // Ultrawide
        if (!empty($value['fontSizeUltrawide'])) {
            $mq = $this->getMediaQuery('ultrawide');
            $css .= sprintf(
                "%s { %s { font-size: %s; } }\n",
                $mq,
                $selector,
                $value['fontSizeUltrawide']
            );
        }

        // Tablet
        if (!empty($value['fontSizeTablet'])) {
            $mq = $this->getMediaQuery('tablet');
            $css .= sprintf(
                "%s { %s { font-size: %s; } }\n",
                $mq,
                $selector,
                $value['fontSizeTablet']
            );
        }

        // Mobile
        if (!empty($value['fontSizeMobile'])) {
            $mq = $this->getMediaQuery('mobile');
            $css .= sprintf(
                "%s { %s { font-size: %s; } }\n",
                $mq,
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

        // Use dynamic media query from breakpoint config
        $mediaQuery = $this->getMediaQuery($breakpoint);

        // Flex direction column
        $css .= sprintf(
            "%s { %s { flex-direction: column; } }\n",
            $mediaQuery,
            $selector
        );

        // Reverse order if enabled
        if (!empty($value['reverseStackOrder'])) {
            $css .= sprintf(
                "%s { %s { flex-direction: column-reverse; } }\n",
                $mediaQuery,
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
     * Generate media query string from breakpoint config
     */
    protected function getMediaQuery(string $device): string
    {
        $bp = $this->breakpoints[$device] ?? null;
        if (!$bp) {
            return '';
        }

        if ($bp['min'] !== null && $bp['max'] !== null) {
            return sprintf('@media (min-width: %dpx) and (max-width: %dpx)', $bp['min'], $bp['max']);
        }
        if ($bp['min'] !== null) {
            return sprintf('@media (min-width: %dpx)', $bp['min']);
        }
        if ($bp['max'] !== null) {
            return sprintf('@media (max-width: %dpx)', $bp['max']);
        }

        return '';
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
