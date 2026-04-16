<?php

namespace Jankx\Gutenberg\Controls\Style;

use Jankx\Gutenberg\Controls\AbstractControl;

/**
 * Typography Control - Font styling and text formatting
 *
 * High-UX component for typography:
 * - Font family selection (Google Fonts, system fonts)
 * - Fluid/responsive font sizes (clamp)
 * - Font weight and style
 * - Line height and letter spacing
 * - Text transform and decoration
 * - Text shadow
 *
 * @package Jankx\Gutenberg\Controls\Style
 */
class TypographyControl extends AbstractControl
{
    /**
     * Control category
     */
    protected string $category = 'style';

    /**
     * Control attributes with defaults
     */
    protected array $attributes = [
        // Font family
        'fontFamily' => ['type' => 'string', 'default' => ''],
        'useThemeFont' => ['type' => 'boolean', 'default' => true],
        'fontCategory' => ['type' => 'string', 'default' => 'sans-serif'],

        // Font size
        'fontSize' => ['type' => 'string', 'default' => ''],
        'fontSizeTablet' => ['type' => 'string', 'default' => ''],
        'fontSizeMobile' => ['type' => 'string', 'default' => ''],
        'fluidTypography' => ['type' => 'boolean', 'default' => false],
        'fluidMin' => ['type' => 'string', 'default' => '16px'],
        'fluidMax' => ['type' => 'string', 'default' => '24px'],

        // Font weight & style
        'fontWeight' => ['type' => 'string', 'default' => '400'],
        'fontStyle' => ['type' => 'string', 'default' => 'normal'],

        // Spacing
        'lineHeight' => ['type' => 'number', 'default' => 1.6],
        'letterSpacing' => ['type' => 'string', 'default' => '0'],

        // Transform & decoration
        'textTransform' => ['type' => 'string', 'default' => 'none'],
        'textDecoration' => ['type' => 'string', 'default' => 'none'],

        // Shadow
        'textShadow' => ['type' => 'object', 'default' => []],

        // Alignment
        'textAlign' => ['type' => 'string', 'default' => 'left'],
        'textAlignTablet' => ['type' => 'string', 'default' => ''],
        'textAlignMobile' => ['type' => 'string', 'default' => ''],
    ];

    /**
     * Get control type identifier
     */
    public function getType(): string
    {
        return 'jankx/typography';
    }

    /**
     * Render editor component placeholder
     */
    public function renderEditor($value, callable $onChange): string
    {
        return '<TypographyInspector />';
    }

    /**
     * Generate CSS for typography settings
     */
    public function generateCss($value, string $selector): string
    {
        $css = '';
        $declarations = [];

        // Font family
        $fontFamily = $this->getFontFamilyValue($value);
        if ($fontFamily) {
            $declarations[] = sprintf('font-family: %s', $fontFamily);
        }

        // Font size (with fluid support)
        $fontSize = $this->getFontSizeValue($value);
        if ($fontSize) {
            $declarations[] = sprintf('font-size: %s', $fontSize);
        }

        // Font weight
        $fontWeight = $value['fontWeight'] ?? '';
        if ($fontWeight && $fontWeight !== '400') {
            $declarations[] = sprintf('font-weight: %s', $fontWeight);
        }

        // Font style
        $fontStyle = $value['fontStyle'] ?? 'normal';
        if ($fontStyle !== 'normal') {
            $declarations[] = sprintf('font-style: %s', $fontStyle);
        }

        // Line height
        $lineHeight = $value['lineHeight'] ?? null;
        if ($lineHeight !== null && $lineHeight !== 1.6) {
            $declarations[] = sprintf('line-height: %s', is_numeric($lineHeight) ? $lineHeight : $lineHeight);
        }

        // Letter spacing
        $letterSpacing = $value['letterSpacing'] ?? '';
        if ($letterSpacing && $letterSpacing !== '0') {
            $declarations[] = sprintf('letter-spacing: %s', $letterSpacing);
        }

        // Text transform
        $textTransform = $value['textTransform'] ?? 'none';
        if ($textTransform !== 'none') {
            $declarations[] = sprintf('text-transform: %s', $textTransform);
        }

        // Text decoration
        $textDecoration = $value['textDecoration'] ?? 'none';
        if ($textDecoration !== 'none') {
            $declarations[] = sprintf('text-decoration: %s', $textDecoration);
        }

        // Text shadow
        $textShadow = $this->getTextShadowValue($value);
        if ($textShadow) {
            $declarations[] = sprintf('text-shadow: %s', $textShadow);
        }

        // Build CSS block
        if (!empty($declarations)) {
            $css .= sprintf("%s {\n    %s;\n}\n", $selector, implode(";\n    ", $declarations));
        }

        // Responsive font sizes
        $css .= $this->generateResponsiveFontSizeCss($value, $selector);

        // Responsive text alignment
        $css .= $this->generateResponsiveTextAlignCss($value, $selector);

        return $css;
    }

    /**
     * Get font family value
     */
    protected function getFontFamilyValue(array $value): ?string
    {
        // Use theme font
        if (!empty($value['useThemeFont'])) {
            return 'var(--jankx-font-family)';
        }

        $fontFamily = $value['fontFamily'] ?? '';
        if (empty($fontFamily)) {
            return null;
        }

        // System font stacks
        $systemFonts = [
            'system-ui' => 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'serif' => 'Georgia, "Times New Roman", Times, serif',
            'monospace' => '"SF Mono", Monaco, "Cascadia Code", "Roboto Mono", Consolas, monospace',
        ];

        if (isset($systemFonts[$fontFamily])) {
            return $systemFonts[$fontFamily];
        }

        // Google Font or custom
        return sprintf('"%s", sans-serif', $fontFamily);
    }

    /**
     * Get font size value with fluid support
     */
    protected function getFontSizeValue(array $value): ?string
    {
        // Fluid typography
        if (!empty($value['fluidTypography'])) {
            $min = $value['fluidMin'] ?? '16px';
            $max = $value['fluidMax'] ?? '24px';
            return sprintf('clamp(%s, 2vw + 1rem, %s)', $min, $max);
        }

        // Fixed size
        $fontSize = $value['fontSize'] ?? '';
        return $fontSize ?: null;
    }

    /**
     * Get text shadow value
     */
    protected function getTextShadowValue(array $value): ?string
    {
        $shadow = $value['textShadow'] ?? [];
        if (empty($shadow)) {
            return null;
        }

        $x = $shadow['x'] ?? 0;
        $y = $shadow['y'] ?? 0;
        $blur = $shadow['blur'] ?? 0;
        $color = $shadow['color'] ?? 'transparent';

        return sprintf('%spx %spx %spx %s', $x, $y, $blur, $color);
    }

    /**
     * Generate responsive font size CSS
     */
    protected function generateResponsiveFontSizeCss(array $value, string $selector): string
    {
        $css = '';

        // Tablet font size
        $tabletSize = $value['fontSizeTablet'] ?? '';
        if ($tabletSize && empty($value['fluidTypography'])) {
            $css .= sprintf("@media (max-width: 1024px) { %s { font-size: %s; } }\n", $selector, $tabletSize);
        }

        // Mobile font size
        $mobileSize = $value['fontSizeMobile'] ?? '';
        if ($mobileSize && empty($value['fluidTypography'])) {
            $css .= sprintf("@media (max-width: 767px) { %s { font-size: %s; } }\n", $selector, $mobileSize);
        }

        return $css;
    }

    /**
     * Generate responsive text align CSS
     */
    protected function generateResponsiveTextAlignCss(array $value, string $selector): string
    {
        $css = '';

        $desktopAlign = $value['textAlign'] ?? 'left';

        // Tablet alignment
        $tabletAlign = $value['textAlignTablet'] ?? '';
        if ($tabletAlign && $tabletAlign !== $desktopAlign) {
            $css .= sprintf("@media (max-width: 1024px) { %s { text-align: %s; } }\n", $selector, $tabletAlign);
        }

        // Mobile alignment
        $mobileAlign = $value['textAlignMobile'] ?? '';
        if ($mobileAlign && $mobileAlign !== ($tabletAlign ?: $desktopAlign)) {
            $css .= sprintf("@media (max-width: 767px) { %s { text-align: %s; } }\n", $selector, $mobileAlign);
        }

        return $css;
    }

    /**
     * Get font weight options
     */
    public function getFontWeightOptions(): array
    {
        return [
            '100' => __('Thin (100)', 'jankx'),
            '200' => __('Extra Light (200)', 'jankx'),
            '300' => __('Light (300)', 'jankx'),
            '400' => __('Regular (400)', 'jankx'),
            '500' => __('Medium (500)', 'jankx'),
            '600' => __('Semi Bold (600)', 'jankx'),
            '700' => __('Bold (700)', 'jankx'),
            '800' => __('Extra Bold (800)', 'jankx'),
            '900' => __('Black (900)', 'jankx'),
        ];
    }

    /**
     * Get font style options
     */
    public function getFontStyleOptions(): array
    {
        return [
            'normal' => __('Normal', 'jankx'),
            'italic' => __('Italic', 'jankx'),
        ];
    }

    /**
     * Get text transform options
     */
    public function getTextTransformOptions(): array
    {
        return [
            'none' => __('None', 'jankx'),
            'capitalize' => __('Capitalize', 'jankx'),
            'uppercase' => __('Uppercase', 'jankx'),
            'lowercase' => __('Lowercase', 'jankx'),
        ];
    }

    /**
     * Get text decoration options
     */
    public function getTextDecorationOptions(): array
    {
        return [
            'none' => __('None', 'jankx'),
            'underline' => __('Underline', 'jankx'),
            'line-through' => __('Line Through', 'jankx'),
            'overline' => __('Overline', 'jankx'),
        ];
    }

    /**
     * Get font family options
     */
    public function getFontFamilyOptions(): array
    {
        return [
            '' => __('Theme Default', 'jankx'),
            'system-ui' => __('System UI', 'jankx'),
            'serif' => __('Serif', 'jankx'),
            'monospace' => __('Monospace', 'jankx'),
            'Inter' => __('Inter', 'jankx'),
            'Roboto' => __('Roboto', 'jankx'),
            'Open Sans' => __('Open Sans', 'jankx'),
            'Poppins' => __('Poppins', 'jankx'),
            'Montserrat' => __('Montserrat', 'jankx'),
            'Playfair Display' => __('Playfair Display', 'jankx'),
        ];
    }

    /**
     * Get font size presets
     */
    public function getFontSizePresets(): array
    {
        return [
            ['value' => '12px', 'label' => __('XS', 'jankx')],
            ['value' => '14px', 'label' => __('Small', 'jankx')],
            ['value' => '16px', 'label' => __('Base', 'jankx')],
            ['value' => '18px', 'label' => __('LG', 'jankx')],
            ['value' => '20px', 'label' => __('XL', 'jankx')],
            ['value' => '24px', 'label' => __('2XL', 'jankx')],
            ['value' => '30px', 'label' => __('3XL', 'jankx')],
            ['value' => '36px', 'label' => __('4XL', 'jankx')],
            ['value' => '48px', 'label' => __('5XL', 'jankx')],
            ['value' => '60px', 'label' => __('6XL', 'jankx')],
            ['value' => '72px', 'label' => __('7XL', 'jankx')],
        ];
    }

    /**
     * Get line height presets
     */
    public function getLineHeightPresets(): array
    {
        return [
            ['value' => 1, 'label' => __('None (1)', 'jankx')],
            ['value' => 1.2, 'label' => __('Tight (1.2)', 'jankx')],
            ['value' => 1.4, 'label' => __('Snug (1.4)', 'jankx')],
            ['value' => 1.6, 'label' => __('Normal (1.6)', 'jankx')],
            ['value' => 1.8, 'label' => __('Relaxed (1.8)', 'jankx')],
            ['value' => 2, 'label' => __('Loose (2)', 'jankx')],
        ];
    }

    /**
     * Get letter spacing presets
     */
    public function getLetterSpacingPresets(): array
    {
        return [
            ['value' => '-0.05em', 'label' => __('Tighter', 'jankx')],
            ['value' => '-0.025em', 'label' => __('Tight', 'jankx')],
            ['value' => '0', 'label' => __('Normal', 'jankx')],
            ['value' => '0.025em', 'label' => __('Wide', 'jankx')],
            ['value' => '0.05em', 'label' => __('Wider', 'jankx')],
            ['value' => '0.1em', 'label' => __('Widest', 'jankx')],
        ];
    }
}
