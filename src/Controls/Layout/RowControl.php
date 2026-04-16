<?php

namespace Jankx\Gutenberg\Controls\Layout;

use Jankx\Gutenberg\Controls\AbstractControl;

/**
 * Row Control - Grid row with columns
 *
 * High-UX component for grid layouts:
 * - Column count (1-6)
 * - Gap control
 * - Responsive behavior
 * - Alignment options
 * - Masonry support
 *
 * @package Jankx\Gutenberg\Controls\Layout
 */
class RowControl extends AbstractControl
{
    /**
     * Control category
     */
    protected string $category = 'layout';

    /**
     * Control attributes with defaults
     */
    protected array $attributes = [
        // Columns
        'columns' => ['type' => 'number', 'default' => 2],
        'columnsTablet' => ['type' => 'number', 'default' => 0], // 0 = same as desktop
        'columnsMobile' => ['type' => 'number', 'default' => 1],

        // Gap
        'gap' => ['type' => 'string', 'default' => '30px'],
        'gapTablet' => ['type' => 'string', 'default' => ''],
        'gapMobile' => ['type' => 'string', 'default' => '20px'],

        // Row gap
        'rowGap' => ['type' => 'string', 'default' => ''],
        'rowGapTablet' => ['type' => 'string', 'default' => ''],
        'rowGapMobile' => ['type' => 'string', 'default' => ''],

        // Alignment
        'alignItems' => ['type' => 'string', 'default' => 'stretch'],
        'justifyContent' => ['type' => 'string', 'default' => 'flex-start'],

        // Direction
        'direction' => ['type' => 'string', 'default' => 'row'],
        'wrap' => ['type' => 'boolean', 'default' => true],

        // Masonry
        'masonry' => ['type' => 'boolean', 'default' => false],
        'masonryColumns' => ['type' => 'number', 'default' => 3],

        // Equal height
        'equalHeight' => ['type' => 'boolean', 'default' => false],
    ];

    /**
     * Get control type identifier
     */
    public function getType(): string
    {
        return 'jankx/row';
    }

    /**
     * Render editor component placeholder
     */
    public function renderEditor($value, callable $onChange): string
    {
        return '<RowInspector />';
    }

    /**
     * Generate CSS for row settings
     */
    public function generateCss($value, string $selector): string
    {
        $css = '';

        // Generate grid CSS
        $css .= $this->generateGridCss($value, $selector);

        // Generate flex fallback CSS
        $css .= $this->generateFlexCss($value, $selector);

        // Generate responsive CSS
        $css .= $this->generateResponsiveCss($value, $selector);

        return $css;
    }

    /**
     * Generate grid CSS
     */
    protected function generateGridCss(array $value, string $selector): string
    {
        $columns = $value['columns'] ?? 2;
        $gap = $value['gap'] ?? '30px';
        $rowGap = $value['rowGap'] ?? '';

        $css = sprintf("%s {\n", $selector);
        $css .= "    display: grid;\n";
        $css .= sprintf("    grid-template-columns: repeat(%d, 1fr);\n", $columns);
        $css .= sprintf("    gap: %s", $gap);

        if ($rowGap && $rowGap !== $gap) {
            $css .= sprintf(" %s", $rowGap);
        }

        $css .= ";\n}";

        return $css . "\n";
    }

    /**
     * Generate flex fallback CSS
     */
    protected function generateFlexCss(array $value, string $selector): string
    {
        $css = '';

        // Alignment
        $alignItems = $value['alignItems'] ?? 'stretch';
        if ($alignItems !== 'stretch') {
            $css .= sprintf("%s { align-items: %s; }\n", $selector, $alignItems);
        }

        $justifyContent = $value['justifyContent'] ?? 'flex-start';
        if ($justifyContent !== 'flex-start') {
            $css .= sprintf("%s { justify-content: %s; }\n", $selector, $justifyContent);
        }

        return $css;
    }

    /**
     * Generate responsive CSS
     */
    protected function generateResponsiveCss(array $value, string $selector): string
    {
        $css = '';
        $columns = $value['columns'] ?? 2;

        // Tablet
        $tabletColumns = $value['columnsTablet'] ?? 0;
        $tabletGap = $value['gapTablet'] ?? '';

        if ($tabletColumns > 0 || $tabletGap) {
            $css .= sprintf("@media (max-width: 1024px) {\n");
            $css .= sprintf("    %s {\n", $selector);

            if ($tabletColumns > 0 && $tabletColumns !== $columns) {
                $css .= sprintf("        grid-template-columns: repeat(%d, 1fr);\n", $tabletColumns);
            }

            if ($tabletGap) {
                $css .= sprintf("        gap: %s;\n", $tabletGap);
            }

            $css .= "    }\n}\n";
        }

        // Mobile
        $mobileColumns = $value['columnsMobile'] ?? 1;
        $mobileGap = $value['gapMobile'] ?? '';

        if ($mobileColumns !== ($tabletColumns ?: $columns) || $mobileGap) {
            $css .= sprintf("@media (max-width: 767px) {\n");
            $css .= sprintf("    %s {\n", $selector);

            if ($mobileColumns !== ($tabletColumns ?: $columns)) {
                $css .= sprintf("        grid-template-columns: repeat(%d, 1fr);\n", $mobileColumns);
            }

            if ($mobileGap) {
                $css .= sprintf("        gap: %s;\n", $mobileGap);
            }

            $css .= "    }\n}\n";
        }

        return $css;
    }

    /**
     * Get column options
     */
    public function getColumnOptions(): array
    {
        return [
            1 => __('1 Column', 'jankx'),
            2 => __('2 Columns', 'jankx'),
            3 => __('3 Columns', 'jankx'),
            4 => __('4 Columns', 'jankx'),
            5 => __('5 Columns', 'jankx'),
            6 => __('6 Columns', 'jankx'),
        ];
    }

    /**
     * Get gap presets
     */
    public function getGapPresets(): array
    {
        return [
            ['value' => '0', 'label' => __('None', 'jankx')],
            ['value' => '10px', 'label' => __('XS', 'jankx')],
            ['value' => '20px', 'label' => __('Small', 'jankx')],
            ['value' => '30px', 'label' => __('Medium', 'jankx')],
            ['value' => '40px', 'label' => __('Large', 'jankx')],
            ['value' => '60px', 'label' => __('XL', 'jankx')],
        ];
    }

    /**
     * Get alignment options
     */
    public function getAlignItemsOptions(): array
    {
        return [
            'stretch' => __('Stretch', 'jankx'),
            'flex-start' => __('Start', 'jankx'),
            'flex-end' => __('End', 'jankx'),
            'center' => __('Center', 'jankx'),
        ];
    }

    /**
     * Get justify content options
     */
    public function getJustifyContentOptions(): array
    {
        return [
            'flex-start' => __('Start', 'jankx'),
            'flex-end' => __('End', 'jankx'),
            'center' => __('Center', 'jankx'),
            'space-between' => __('Space Between', 'jankx'),
            'space-around' => __('Space Around', 'jankx'),
            'space-evenly' => __('Space Evenly', 'jankx'),
        ];
    }

    /**
     * Calculate column width percentage
     */
    public function getColumnWidth(int $columns): string
    {
        if ($columns <= 0) {
            $columns = 1;
        }

        $percentage = 100 / $columns;
        return sprintf('%s%%', round($percentage, 4));
    }
}
