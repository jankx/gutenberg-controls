<?php

namespace Jankx\Gutenberg\Blocks;

use Jankx\Gutenberg\Controls\Layout\SectionControl;
use Jankx\Gutenberg\Controls\Layout\ResponsiveControl;
use Jankx\Gutenberg\Controls\Effects\AnimationControl;

/**
 * Section Block - Flatsome-style section builder for Gutenberg
 *
 * A flexible section container with advanced layout controls,
 * background options, animations, and divider shapes.
 *
 * @package Jankx\Gutenberg\Blocks
 * @since 1.0.0
 */
class SectionBlock extends AbstractBlockWithControls
{
    protected $category = 'jankx-layout';

    protected $icon = [
        'foreground' => '#ff5722',
        'src' => 'layout',
    ];

    protected array $controls = [
        'layout' => SectionControl::class,
        'responsive' => ResponsiveControl::class,
        'animation' => AnimationControl::class,
    ];

    protected $supports = [
        'anchor' => true,
        'align' => ['wide', 'full'],
        'html' => false,
        'layout' => [
            'allowInheriting' => true,
            'allowSwitching' => false,
            'default' => [
                'type' => 'constrained',
            ],
        ],
        'spacing' => [
            'padding' => true,
            'margin' => true,
            'blockGap' => true,
        ],
        'color' => [
            'background' => false, // We handle this via SectionControl
            'text' => false,
        ],
    ];

    protected $allowedBlocks = [
        'core/heading',
        'core/paragraph',
        'core/image',
        'core/buttons',
        'core/columns',
        'core/group',
        'jankx/row',
        'jankx/banner',
    ];

    /**
     * Get block name
     *
     * @return string
     */
    protected function getBlockName(): string
    {
        return 'jankx/section';
    }

    /**
     * Get block title
     *
     * @return string
     */
    protected function getBlockTitle(): string
    {
        return __('Jankx Section', 'jankx');
    }

    /**
     * Register controls
     *
     * @return void
     */
    protected function registerControls(): void
    {
        // Section layout control (width, height, background, padding)
        $this->addControl(new SectionControl([
            'name' => 'layout',
            'label' => __('Section Layout', 'jankx'),
            'responsive' => true,
        ]));

        // Animation control (entrance effects, scroll triggers)
        $this->addControl(new AnimationControl([
            'name' => 'animation',
            'label' => __('Animations', 'jankx'),
        ]));
    }

    /**
     * Render block content
     *
     * @param array $attributes
     * @param string $content
     * @param WP_Block $block
     * @param array $jankxControls
     * @return string
     */
    protected function renderBlockContent(
        array $attributes,
        string $content,
        $block,
        array $jankxControls
    ): string {
        $layout = $jankxControls['layout'] ?? [];
        $animation = $jankxControls['animation'] ?? [];

        // Build section wrapper
        $sectionClasses = $this->getSectionClasses($layout);
        $sectionStyles = $this->getSectionStyles($layout);
        $sectionAttr = $this->getSectionDataAttributes($animation);

        // Container for content width control
        $containerWidth = $layout['containerWidth'] ?? 'default';
        $containerClass = $this->getContainerClass($containerWidth);

        // Build dividers
        $topDivider = $this->renderDivider($layout['dividerTop'] ?? '', $layout['dividerColor'] ?? '#ffffff', 'top');
        $bottomDivider = $this->renderDivider($layout['dividerBottom'] ?? '', $layout['dividerColor'] ?? '#ffffff', 'bottom');

        // Build section markup
        $html = '';

        // Top divider
        if ($topDivider) {
            $html .= $topDivider;
        }

        // Section wrapper
        $html .= sprintf(
            '<section class="%s" style="%s" %s>',
            esc_attr(implode(' ', $sectionClasses)),
            esc_attr($sectionStyles),
            $sectionAttr
        );

        // Background overlay
        if (!empty($layout['backgroundOverlay'])) {
            $html .= $this->renderBackgroundOverlay($layout['backgroundOverlay']);
        }

        // Container
        $html .= sprintf('<div class="%s">', esc_attr($containerClass));

        // Content
        $html .= $content;

        // Close container
        $html .= '</div>';

        // Close section
        $html .= '</section>';

        // Bottom divider
        if ($bottomDivider) {
            $html .= $bottomDivider;
        }

        return $html;
    }

    /**
     * Get section CSS classes
     *
     * @param array $layout
     * @return array
     */
    protected function getSectionClasses(array $layout): array
    {
        $classes = [
            'jankx-section',
        ];

        // Width modifiers
        if (($layout['width'] ?? '') === 'full') {
            $classes[] = 'jankx-section--full-width';
        }

        // Height modifiers
        if (($layout['height'] ?? '') === 'full') {
            $classes[] = 'jankx-section--full-height';
        }

        // Background type
        if (!empty($layout['backgroundType'])) {
            $classes[] = 'jankx-section--bg-' . $layout['backgroundType'];
        }

        // Parallax
        if (!empty($layout['parallax'])) {
            $classes[] = 'jankx-section--parallax';
        }

        // Sticky
        if (!empty($layout['sticky'])) {
            $classes[] = 'jankx-section--sticky';
        }

        // Scroll effects
        if (!empty($layout['scrollEffect'])) {
            $classes[] = 'jankx-section--effect-' . $layout['scrollEffect'];
        }

        return $classes;
    }

    /**
     * Get section inline styles
     *
     * @param array $layout
     * @return string
     */
    protected function getSectionStyles(array $layout): string
    {
        $styles = [];

        // Min height
        if (!empty($layout['minHeight'])) {
            $styles[] = "min-height: {$layout['minHeight']}";
        }

        // Background styles handled via CSS class or inline
        switch ($layout['backgroundType'] ?? 'color') {
            case 'color':
                if (!empty($layout['backgroundColor'])) {
                    $styles[] = "background-color: {$layout['backgroundColor']}";
                }
                break;

            case 'image':
                if (!empty($layout['backgroundImage']['url'])) {
                    $styles[] = "background-image: url({$layout['backgroundImage']['url']})";
                    $styles[] = 'background-size: cover';
                    $styles[] = 'background-position: center';
                    $styles[] = 'background-repeat: no-repeat';
                }
                break;

            case 'gradient':
                if (!empty($layout['backgroundColor'])) {
                    $styles[] = "background: {$layout['backgroundColor']}";
                }
                break;

            case 'video':
                // Video background handled via JS
                break;
        }

        // Parallax speed
        if (!empty($layout['parallax']) && !empty($layout['parallaxSpeed'])) {
            $styles[] = "--jankx-parallax-speed: {$layout['parallaxSpeed']}";
        }

        // Sticky offset
        if (!empty($layout['sticky']) && !empty($layout['stickyOffset'])) {
            $styles[] = "--jankx-sticky-offset: {$layout['stickyOffset']}px";
        }

        return implode('; ', $styles);
    }

    /**
     * Get data attributes for JavaScript
     *
     * @param array $animation
     * @return string
     */
    protected function getSectionDataAttributes(array $animation): string
    {
        $attrs = [];

        if (!empty($animation['entrance'])) {
            $attrs[] = 'data-animate="true"';
            $attrs[] = 'data-animation="' . esc_attr($animation['entrance']) . '"';

            if (!empty($animation['scrollTrigger'])) {
                $attrs[] = 'data-scroll-trigger="true"';
                $attrs[] = 'data-scroll-offset="' . intval($animation['scrollOffset'] ?? 100) . '"';
            }
        }

        if (!empty($animation['infinite'])) {
            $attrs[] = 'data-infinite-animation="' . esc_attr($animation['infinite']) . '"';
        }

        return implode(' ', $attrs);
    }

    /**
     * Get container class based on width setting
     *
     * @param string $width
     * @return string
     */
    protected function getContainerClass(string $width): string
    {
        $classes = [
            'jankx-section__container',
        ];

        switch ($width) {
            case 'full':
                $classes[] = 'jankx-container--full';
                break;
            case 'wide':
                $classes[] = 'jankx-container--wide';
                break;
            case 'narrow':
                $classes[] = 'jankx-container--narrow';
                break;
            default:
                $classes[] = 'jankx-container--default';
                break;
        }

        return implode(' ', $classes);
    }

    /**
     * Render background overlay
     *
     * @param string $overlay
     * @return string
     */
    protected function renderBackgroundOverlay(string $overlay): string
    {
        return sprintf(
            '<div class="jankx-section__overlay" style="background-color: %s;"></div>',
            esc_attr($overlay)
        );
    }

    /**
     * Render divider shape
     *
     * @param string $type
     * @param string $color
     * @param string $position
     * @return string
     */
    protected function renderDivider(string $type, string $color, string $position): string
    {
        if (empty($type)) {
            return '';
        }

        $shapes = [
            'curve' => $this->getCurveSvg($color, $position),
            'triangle' => $this->getTriangleSvg($color, $position),
            'slant' => $this->getSlantSvg($color, $position),
            'arrow' => $this->getArrowSvg($color, $position),
            'clouds' => $this->getCloudsSvg($color, $position),
        ];

        if (!isset($shapes[$type])) {
            return '';
        }

        $class = "jankx-section__divider jankx-section__divider--{$position} jankx-section__divider--{$type}";

        return sprintf(
            '<div class="%s" aria-hidden="true">%s</div>',
            esc_attr($class),
            $shapes[$type]
        );
    }

    /**
     * Get curve SVG divider
     */
    protected function getCurveSvg(string $color, string $position): string
    {
        $flip = $position === 'bottom' ? ' transform="scale(1, -1)"' : '';

        return <<<SVG
<svg viewBox="0 0 1200 120" preserveAspectRatio="none"{$flip}>
    <path d="M0,0 C600,120 600,120 1200,0 L1200,120 L0,120 Z" fill="{$color}"/>
</svg>
SVG;
    }

    /**
     * Get triangle SVG divider
     */
    protected function getTriangleSvg(string $color, string $position): string
    {
        $flip = $position === 'bottom' ? ' transform="scale(1, -1)"' : '';

        return <<<SVG
<svg viewBox="0 0 1200 100" preserveAspectRatio="none"{$flip}>
    <polygon points="0,0 600,100 1200,0" fill="{$color}"/>
</svg>
SVG;
    }

    /**
     * Get slant SVG divider
     */
    protected function getSlantSvg(string $color, string $position): string
    {
        $flip = $position === 'bottom' ? ' transform="scale(1, -1)"' : '';

        return <<<SVG
<svg viewBox="0 0 1200 100" preserveAspectRatio="none"{$flip}>
    <polygon points="0,0 1200,100 1200,0" fill="{$color}"/>
</svg>
SVG;
    }

    /**
     * Get arrow SVG divider
     */
    protected function getArrowSvg(string $color, string $position): string
    {
        $flip = $position === 'bottom' ? ' transform="scale(1, -1)"' : '';

        return <<<SVG
<svg viewBox="0 0 1200 120" preserveAspectRatio="none"{$flip}>
    <path d="M0,0 L600,120 L1200,0 L1200,120 L0,120 Z" fill="{$color}"/>
</svg>
SVG;
    }

    /**
     * Get clouds SVG divider
     */
    protected function getCloudsSvg(string $color, string $position): string
    {
        $flip = $position === 'bottom' ? ' transform="scale(1, -1)"' : '';

        return <<<SVG
<svg viewBox="0 0 1200 120" preserveAspectRatio="none"{$flip}>
    <path d="M0,60 Q300,120 600,60 T1200,60 L1200,120 L0,120 Z" fill="{$color}"/>
</svg>
SVG;
    }

    /**
     * Enqueue frontend assets
     *
     * @return void
     */
    public function enqueueFrontendAssets(): void
    {
        parent::enqueueFrontendAssets();

        // Parallax script
        wp_enqueue_script(
            'jankx-parallax',
            plugins_url('assets/dist/parallax.js', __DIR__),
            [],
            '1.0.0',
            true
        );

        // Sticky section script
        wp_enqueue_script(
            'jankx-sticky',
            plugins_url('assets/dist/sticky.js', __DIR__),
            [],
            '1.0.0',
            true
        );
    }
}
