<?php

namespace Jankx\Gutenberg\Controls\Media;

use Jankx\Gutenberg\Controls\AbstractControl;

/**
 * Image Picker Control - Media library with advanced features
 *
 * High-UX component for image selection:
 * - WordPress media library integration
 * - Focal point picker
 * - Image sizes selection
 * - Lazy loading toggle
 * - Lightbox integration
 * - Object fit options
 *
 * @package Jankx\Gutenberg\Controls\Media
 */
class ImagePickerControl extends AbstractControl
{
    /**
     * Control category
     */
    protected string $category = 'media';

    /**
     * Control attributes with defaults
     */
    protected array $attributes = [
        'id' => ['type' => 'number', 'default' => 0],
        'url' => ['type' => 'string', 'default' => ''],
        'alt' => ['type' => 'string', 'default' => ''],
        'title' => ['type' => 'string', 'default' => ''],
        'caption' => ['type' => 'string', 'default' => ''],

        // Image size
        'size' => ['type' => 'string', 'default' => 'large'],
        'sizes' => ['type' => 'object', 'default' => []],

        // Focal point
        'focalPoint' => ['type' => 'object', 'default' => ['x' => 0.5, 'y' => 0.5]],

        // Display options
        'objectFit' => ['type' => 'string', 'default' => 'cover'],
        'objectPosition' => ['type' => 'string', 'default' => 'center center'],

        // Behavior
        'lazyLoad' => ['type' => 'boolean', 'default' => true],
        'lightbox' => ['type' => 'boolean', 'default' => false],
        'linkTo' => ['type' => 'string', 'default' => 'none'], // none, media, custom
        'customLink' => ['type' => 'string', 'default' => ''],

        // Overlay
        'overlayColor' => ['type' => 'string', 'default' => ''],
        'overlayOpacity' => ['type' => 'number', 'default' => 50],

        // Responsive images
        'srcset' => ['type' => 'string', 'default' => ''],
        'responsive' => ['type' => 'boolean', 'default' => true],
    ];

    /**
     * Get control type identifier
     */
    public function getType(): string
    {
        return 'jankx/image';
    }

    /**
     * Render editor component placeholder
     */
    public function renderEditor($value, callable $onChange): string
    {
        return '<ImagePickerInspector />';
    }

    /**
     * Generate CSS for image display
     */
    public function generateCss($value, string $selector): string
    {
        $css = '';
        $declarations = [];

        // Object fit
        $objectFit = $value['objectFit'] ?? 'cover';
        if ($objectFit !== 'cover') {
            $declarations[] = sprintf('object-fit: %s', $objectFit);
        }

        // Object position with focal point
        $focalPoint = $value['focalPoint'] ?? ['x' => 0.5, 'y' => 0.5];
        $positionX = ($focalPoint['x'] ?? 0.5) * 100;
        $positionY = ($focalPoint['y'] ?? 0.5) * 100;

        if ($positionX !== 50 || $positionY !== 50) {
            $declarations[] = sprintf('object-position: %s%% %s%%', $positionX, $positionY);
        }

        // Overlay
        $overlay = $this->generateOverlayCss($value, $selector);
        $css .= $overlay;

        // Build CSS block
        if (!empty($declarations)) {
            $css .= sprintf("%s img {\n    %s;\n}\n", $selector, implode(";\n    ", $declarations));
        }

        return $css;
    }

    /**
     * Generate overlay CSS
     */
    protected function generateOverlayCss(array $value, string $selector): string
    {
        $overlayColor = $value['overlayColor'] ?? '';
        if (empty($overlayColor)) {
            return '';
        }

        $opacity = ($value['overlayOpacity'] ?? 50) / 100;
        $overlayRgba = $this->addAlphaToColor($overlayColor, $opacity);

        $css = sprintf(
            "%s::before {\n    content: '';\n    position: absolute;\n    top: 0;\n    left: 0;\n    right: 0;\n    bottom: 0;\n    background: %s;\n    pointer-events: none;\n}\n",
            $selector,
            $overlayRgba
        );

        $css .= sprintf("%s { position: relative; }\n", $selector);

        return $css;
    }

    /**
     * Get image HTML attributes
     */
    public function getImageAttributes(array $value): array
    {
        $attrs = [];

        // Alt text
        $attrs['alt'] = $value['alt'] ?? '';

        // Lazy loading
        if (!empty($value['lazyLoad'])) {
            $attrs['loading'] = 'lazy';
        }

        // Srcset for responsive images
        if (!empty($value['srcset'])) {
            $attrs['srcset'] = $value['srcset'];
        }

        // Sizes
        $attrs['sizes'] = '(max-width: 768px) 100vw, (max-width: 1024px) 50vw, 33vw';

        return $attrs;
    }

    /**
     * Get available image sizes
     */
    public function getImageSizeOptions(): array
    {
        $sizes = [
            'thumbnail' => __('Thumbnail', 'jankx'),
            'medium' => __('Medium', 'jankx'),
            'large' => __('Large', 'jankx'),
            'full' => __('Full Size', 'jankx'),
        ];

        // Add custom sizes if available
        $customSizes = wp_get_additional_image_sizes();
        foreach ($customSizes as $name => $size) {
            $sizes[$name] = sprintf(
                '%s (%dx%d)',
                ucfirst(str_replace('-', ' ', $name)),
                $size['width'],
                $size['height']
            );
        }

        return $sizes;
    }

    /**
     * Get object fit options
     */
    public function getObjectFitOptions(): array
    {
        return [
            'cover' => __('Cover', 'jankx'),
            'contain' => __('Contain', 'jankx'),
            'fill' => __('Fill', 'jankx'),
            'none' => __('None', 'jankx'),
            'scale-down' => __('Scale Down', 'jankx'),
        ];
    }

    /**
     * Get link options
     */
    public function getLinkOptions(): array
    {
        return [
            'none' => __('None', 'jankx'),
            'media' => __('Media File', 'jankx'),
            'attachment' => __('Attachment Page', 'jankx'),
            'custom' => __('Custom URL', 'jankx'),
        ];
    }

    /**
     * Add alpha to hex color
     */
    protected function addAlphaToColor(string $color, float $alpha): string
    {
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

        return $color;
    }

    /**
     * Sanitize value
     */
    public function sanitize($value)
    {
        $sanitized = parent::sanitize($value);

        // Ensure focal point is within bounds
        if (isset($sanitized['focalPoint'])) {
            $sanitized['focalPoint']['x'] = max(0, min(1, $sanitized['focalPoint']['x'] ?? 0.5));
            $sanitized['focalPoint']['y'] = max(0, min(1, $sanitized['focalPoint']['y'] ?? 0.5));
        }

        // Sanitize URL
        if (!empty($sanitized['url'])) {
            $sanitized['url'] = esc_url_raw($sanitized['url']);
        }

        return $sanitized;
    }
}
