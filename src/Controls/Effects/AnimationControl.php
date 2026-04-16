<?php

namespace Jankx\Gutenberg\Controls\Effects;

use Jankx\Gutenberg\Controls\AbstractControl;

/**
 * Animation Control - Entrance and scroll-triggered animations
 *
 * High-UX component for adding visual effects:
 * - 30+ entrance animations (fade, slide, zoom, flip, bounce)
 * - Scroll-triggered reveal effects
 * - Infinite loop animations (pulse, bounce, shake)
 * - Hover-triggered animations
 * - Staggered animations for child elements
 * - Duration, delay, and easing controls
 *
 * @package Jankx\Gutenberg\Controls\Effects
 */
class AnimationControl extends AbstractControl
{
    /**
     * Control category
     */
    protected string $category = 'effects';

    /**
     * Control attributes with defaults
     */
    protected array $attributes = [
        // Entrance animations
        'entrance' => ['type' => 'string', 'default' => ''],
        'entranceDuration' => ['type' => 'number', 'default' => 1000],
        'entranceDelay' => ['type' => 'number', 'default' => 0],
        'entranceEasing' => ['type' => 'string', 'default' => 'ease-out'],

        // Scroll trigger
        'scrollTrigger' => ['type' => 'boolean', 'default' => false],
        'scrollOffset' => ['type' => 'number', 'default' => 100],
        'scrollOnce' => ['type' => 'boolean', 'default' => true],

        // Infinite animations
        'infinite' => ['type' => 'string', 'default' => ''],
        'infiniteDuration' => ['type' => 'number', 'default' => 2000],

        // Hover animations
        'hover' => ['type' => 'string', 'default' => ''],
        'hoverDuration' => ['type' => 'number', 'default' => 300],

        // Stagger
        'staggerChildren' => ['type' => 'boolean', 'default' => false],
        'staggerDelay' => ['type' => 'number', 'default' => 100],

        // Advanced
        'willChange' => ['type' => 'boolean', 'default' => true],
        'hardwareAccel' => ['type' => 'boolean', 'default' => true],
    ];

    /**
     * Available entrance animations
     */
    protected array $animations = [
        // Fade animations
        'fadeIn' => 'Fade In',
        'fadeInUp' => 'Fade In Up',
        'fadeInDown' => 'Fade In Down',
        'fadeInLeft' => 'Fade In Left',
        'fadeInRight' => 'Fade In Right',

        // Slide animations
        'slideInUp' => 'Slide In Up',
        'slideInDown' => 'Slide In Down',
        'slideInLeft' => 'Slide In Left',
        'slideInRight' => 'Slide In Right',

        // Zoom animations
        'zoomIn' => 'Zoom In',
        'zoomInUp' => 'Zoom In Up',
        'zoomInDown' => 'Zoom In Down',
        'zoomOut' => 'Zoom Out',

        // Flip animations
        'flipInX' => 'Flip In X',
        'flipInY' => 'Flip In Y',

        // Rotate animations
        'rotateIn' => 'Rotate In',
        'rotateInUpLeft' => 'Rotate Up Left',
        'rotateInDownRight' => 'Rotate Down Right',

        // Bounce animations
        'bounceIn' => 'Bounce In',
        'bounceInUp' => 'Bounce In Up',
        'bounceInDown' => 'Bounce In Down',
        'bounceInLeft' => 'Bounce In Left',
        'bounceInRight' => 'Bounce In Right',
    ];

    /**
     * Available infinite animations
     */
    protected array $infiniteAnimations = [
        'pulse' => 'Pulse',
        'bounce' => 'Bounce',
        'shake' => 'Shake',
        'tada' => 'Tada',
        'swing' => 'Swing',
        'wobble' => 'Wobble',
        'flash' => 'Flash',
        'rubberBand' => 'Rubber Band',
        'jello' => 'Jello',
        'heartBeat' => 'Heartbeat',
        'flip' => 'Flip',
    ];

    /**
     * Available hover animations
     */
    protected array $hoverAnimations = [
        'pulse' => 'Pulse',
        'bounce' => 'Bounce',
        'shake' => 'Shake',
        'grow' => 'Grow',
        'shrink' => 'Shrink',
        'rotate' => 'Rotate',
        'swing' => 'Swing',
    ];

    /**
     * CSS easing functions
     */
    protected array $easingFunctions = [
        'linear' => 'Linear',
        'ease' => 'Ease',
        'ease-in' => 'Ease In',
        'ease-out' => 'Ease Out',
        'ease-in-out' => 'Ease In Out',
        'cubic-bezier(0.4, 0, 0.2, 1)' => 'Material',
        'cubic-bezier(0.68, -0.55, 0.265, 1.55)' => 'Bouncy',
    ];

    /**
     * Get control type identifier
     */
    public function getType(): string
    {
        return 'jankx/animation';
    }

    /**
     * Render editor component placeholder
     */
    public function renderEditor($value, callable $onChange): string
    {
        return '<AnimationInspector />';
    }

    /**
     * Generate CSS for animations
     */
    public function generateCss($value, string $selector): string
    {
        $css = '';

        // Generate entrance animation CSS
        $css .= $this->generateEntranceCss($value, $selector);

        // Generate infinite animation CSS
        $css .= $this->generateInfiniteCss($value, $selector);

        // Generate hover animation CSS
        $css .= $this->generateHoverCss($value, $selector);

        // Generate scroll trigger CSS
        $css .= $this->generateScrollTriggerCss($value, $selector);

        // Add performance hints
        $css .= $this->generatePerformanceCss($value, $selector);

        return $css;
    }

    /**
     * Generate entrance animation CSS
     */
    protected function generateEntranceCss(array $value, string $selector): string
    {
        $entrance = $value['entrance'] ?? '';
        if (empty($entrance)) {
            return '';
        }

        $duration = ($value['entranceDuration'] ?? 1000) . 'ms';
        $delay = ($value['entranceDelay'] ?? 0) . 'ms';
        $easing = $value['entranceEasing'] ?? 'ease-out';

        // Add initial hidden state for scroll-triggered animations
        $scrollTrigger = $value['scrollTrigger'] ?? false;
        $initialState = '';
        if ($scrollTrigger) {
            $initialState = sprintf(
                "%s { opacity: 0; visibility: hidden; will-change: opacity, transform; }\n",
                $selector
            );
        }

        // Active state (animated)
        $activeClass = $scrollTrigger ? '.jankx-animated' : '';
        $activeSelector = $selector . $activeClass;

        $animationCss = sprintf(
            "%s { animation: %s %s %s %s forwards; }\n",
            $activeSelector,
            $entrance,
            $duration,
            $easing,
            $delay
        );

        return $initialState . $animationCss;
    }

    /**
     * Generate infinite animation CSS
     */
    protected function generateInfiniteCss(array $value, string $selector): string
    {
        $infinite = $value['infinite'] ?? '';
        if (empty($infinite)) {
            return '';
        }

        $duration = ($value['infiniteDuration'] ?? 2000) . 'ms';

        return sprintf(
            "%s { animation: %s %s infinite; }\n",
            $selector,
            $infinite,
            $duration
        );
    }

    /**
     * Generate hover animation CSS
     */
    protected function generateHoverCss(array $value, string $selector): string
    {
        $hover = $value['hover'] ?? '';
        if (empty($hover)) {
            return '';
        }

        $duration = ($value['hoverDuration'] ?? 300) . 'ms';

        // Special handling for scale animations
        if ($hover === 'grow') {
            return sprintf(
                "%s:hover { transform: scale(1.05); transition: transform %s ease; }\n",
                $selector,
                $duration
            );
        }

        if ($hover === 'shrink') {
            return sprintf(
                "%s:hover { transform: scale(0.95); transition: transform %s ease; }\n",
                $selector,
                $duration
            );
        }

        if ($hover === 'rotate') {
            return sprintf(
                "%s:hover { transform: rotate(5deg); transition: transform %s ease; }\n",
                $selector,
                $duration
            );
        }

        // Standard animation
        return sprintf(
            "%s:hover { animation: %s %s; }\n",
            $selector,
            $hover,
            $duration
        );
    }

    /**
     * Generate scroll trigger CSS
     */
    protected function generateScrollTriggerCss(array $value, string $selector): string
    {
        $scrollTrigger = $value['scrollTrigger'] ?? false;
        if (!$scrollTrigger) {
            return '';
        }

        // CSS class for JavaScript targeting
        return sprintf(
            "%s { --jankx-scroll-offset: %dpx; }\n",
            $selector,
            $value['scrollOffset'] ?? 100
        );
    }

    /**
     * Generate performance optimization CSS
     */
    protected function generatePerformanceCss(array $value, string $selector): string
    {
        $willChange = $value['willChange'] ?? true;
        if (!$willChange) {
            return '';
        }

        // Check if any animation is enabled
        $hasAnimation = !empty($value['entrance'])
            || !empty($value['infinite'])
            || !empty($value['hover']);

        if (!$hasAnimation) {
            return '';
        }

        return sprintf(
            "%s { will-change: transform, opacity; }\n",
            $selector
        );
    }

    /**
     * Get JavaScript configuration for animations
     */
    public function getJsConfig(array $value): array
    {
        $config = [
            'enabled' => false,
        ];

        // Entrance animation config
        $entrance = $value['entrance'] ?? '';
        if (!empty($entrance)) {
            $config['enabled'] = true;
            $config['type'] = 'entrance';
            $config['animation'] = $entrance;
            $config['duration'] = $value['entranceDuration'] ?? 1000;
            $config['delay'] = $value['entranceDelay'] ?? 0;
            $config['easing'] = $value['entranceEasing'] ?? 'ease-out';
            $config['scrollTrigger'] = $value['scrollTrigger'] ?? false;
            $config['scrollOffset'] = $value['scrollOffset'] ?? 100;
            $config['scrollOnce'] = $value['scrollOnce'] ?? true;
        }

        // Infinite animation config
        $infinite = $value['infinite'] ?? '';
        if (!empty($infinite)) {
            $config['enabled'] = true;
            $config['infinite'] = [
                'animation' => $infinite,
                'duration' => $value['infiniteDuration'] ?? 2000,
            ];
        }

        // Hover animation config
        $hover = $value['hover'] ?? '';
        if (!empty($hover)) {
            $config['enabled'] = true;
            $config['hover'] = [
                'animation' => $hover,
                'duration' => $value['hoverDuration'] ?? 300,
            ];
        }

        // Stagger config
        $stagger = $value['staggerChildren'] ?? false;
        if ($stagger) {
            $config['stagger'] = [
                'enabled' => true,
                'delay' => $value['staggerDelay'] ?? 100,
            ];
        }

        return $config;
    }

    /**
     * Get all entrance animations
     */
    public function getAnimations(): array
    {
        return $this->animations;
    }

    /**
     * Get all infinite animations
     */
    public function getInfiniteAnimations(): array
    {
        return $this->infiniteAnimations;
    }

    /**
     * Get all hover animations
     */
    public function getHoverAnimations(): array
    {
        return $this->hoverAnimations;
    }

    /**
     * Get all easing functions
     */
    public function getEasingFunctions(): array
    {
        return $this->easingFunctions;
    }

    /**
     * Get CSS keyframes for all animations
     */
    public function getKeyframesCss(): string
    {
        $css = '';

        // Entrance animations
        $css .= $this->getFadeInKeyframes();
        $css .= $this->getSlideInKeyframes();
        $css .= $this->getZoomInKeyframes();
        $css .= $this->getFlipInKeyframes();
        $css .= $this->getRotateInKeyframes();
        $css .= $this->getBounceInKeyframes();

        // Infinite animations
        $css .= $this->getPulseKeyframes();
        $css .= $this->getShakeKeyframes();
        $css .= $this->getBounceKeyframes();
        $css .= $this->getTadaKeyframes();
        $css .= $this->getFlashKeyframes();
        $css .= $this->getRubberBandKeyframes();
        $css .= $this->getHeartbeatKeyframes();
        $css .= $this->getFlipKeyframes();

        // Scroll reveal helper
        $css .= ".jankx-animate-on-scroll { opacity: 0; }\n";
        $css .= ".jankx-animate-on-scroll.jankx-animated { opacity: 1; }\n";

        return $css;
    }

    /**
     * Get fade in keyframes
     */
    protected function getFadeInKeyframes(): string
    {
        return "
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeInDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }
@keyframes fadeInLeft { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
@keyframes fadeInRight { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
";
    }

    /**
     * Get slide in keyframes
     */
    protected function getSlideInKeyframes(): string
    {
        return "
@keyframes slideInUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
@keyframes slideInDown { from { transform: translateY(-100%); } to { transform: translateY(0); } }
@keyframes slideInLeft { from { transform: translateX(-100%); } to { transform: translateX(0); } }
@keyframes slideInRight { from { transform: translateX(100%); } to { transform: translateX(0); } }
";
    }

    /**
     * Get zoom in keyframes
     */
    protected function getZoomInKeyframes(): string
    {
        return "
@keyframes zoomIn { from { opacity: 0; transform: scale3d(0.3, 0.3, 0.3); } to { opacity: 1; } }
@keyframes zoomInUp { from { opacity: 0; transform: scale3d(0.3, 0.3, 0.3) translateY(50px); } to { opacity: 1; transform: scale3d(1, 1, 1) translateY(0); } }
@keyframes zoomInDown { from { opacity: 0; transform: scale3d(0.3, 0.3, 0.3) translateY(-50px); } to { opacity: 1; transform: scale3d(1, 1, 1) translateY(0); } }
@keyframes zoomOut { from { opacity: 1; } to { opacity: 0; transform: scale3d(0.3, 0.3, 0.3); } }
";
    }

    /**
     * Get flip in keyframes
     */
    protected function getFlipInKeyframes(): string
    {
        return "
@keyframes flipInX { from { transform: perspective(400px) rotateX(90deg); opacity: 0; } to { transform: perspective(400px) rotateX(0); opacity: 1; } }
@keyframes flipInY { from { transform: perspective(400px) rotateY(90deg); opacity: 0; } to { transform: perspective(400px) rotateY(0); opacity: 1; } }
";
    }

    /**
     * Get rotate in keyframes
     */
    protected function getRotateInKeyframes(): string
    {
        return "
@keyframes rotateIn { from { transform: rotate(-200deg); opacity: 0; } to { transform: rotate(0); opacity: 1; } }
@keyframes rotateInUpLeft { from { transform: rotate(45deg); opacity: 0; } to { transform: rotate(0); opacity: 1; } }
@keyframes rotateInDownRight { from { transform: rotate(-45deg); opacity: 0; } to { transform: rotate(0); opacity: 1; } }
";
    }

    /**
     * Get bounce in keyframes
     */
    protected function getBounceInKeyframes(): string
    {
        return "
@keyframes bounceIn { from, 20%, 40%, 60%, 80%, to { animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1); } from { opacity: 0; transform: scale3d(0.3, 0.3, 0.3); } 20% { transform: scale3d(1.1, 1.1, 1.1); } 40% { transform: scale3d(0.9, 0.9, 0.9); } 60% { opacity: 1; transform: scale3d(1.03, 1.03, 1.03); } 80% { transform: scale3d(0.97, 0.97, 0.97); } to { opacity: 1; transform: scale3d(1, 1, 1); } }
@keyframes bounceInUp { from, 60%, 75%, 90%, to { animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1); } from { opacity: 0; transform: translateY(3000px); } 60% { opacity: 1; transform: translateY(-20px); } 75% { transform: translateY(10px); } 90% { transform: translateY(-5px); } to { transform: translateY(0); } }
@keyframes bounceInDown { from, 60%, 75%, 90%, to { animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1); } from { opacity: 0; transform: translateY(-3000px); } 60% { opacity: 1; transform: translateY(25px); } 75% { transform: translateY(-10px); } 90% { transform: translateY(5px); } to { transform: translateY(0); } }
@keyframes bounceInLeft { from, 60%, 75%, 90%, to { animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1); } from { opacity: 0; transform: translateX(-3000px); } 60% { opacity: 1; transform: translateX(25px); } 75% { transform: translateX(-10px); } 90% { transform: translateX(5px); } to { transform: translateX(0); } }
@keyframes bounceInRight { from, 60%, 75%, 90%, to { animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1); } from { opacity: 0; transform: translateX(3000px); } 60% { opacity: 1; transform: translateX(-25px); } 75% { transform: translateX(10px); } 90% { transform: translateX(-5px); } to { transform: translateX(0); } }
";
    }

    /**
     * Get pulse keyframes
     */
    protected function getPulseKeyframes(): string
    {
        return "@keyframes pulse { from { transform: scale3d(1, 1, 1); } 50% { transform: scale3d(1.05, 1.05, 1.05); } to { transform: scale3d(1, 1, 1); } }\n";
    }

    /**
     * Get shake keyframes
     */
    protected function getShakeKeyframes(): string
    {
        return "@keyframes shake { from, to { transform: translateX(0); } 10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); } 20%, 40%, 60%, 80% { transform: translateX(10px); } }\n";
    }

    /**
     * Get bounce keyframes
     */
    protected function getBounceKeyframes(): string
    {
        return "@keyframes bounce { from, 20%, 53%, 80%, to { animation-timing-function: cubic-bezier(0.215, 0.61, 0.355, 1); transform: translateY(0); } 40%, 43% { animation-timing-function: cubic-bezier(0.755, 0.05, 0.855, 0.06); transform: translateY(-30px); } 70% { animation-timing-function: cubic-bezier(0.755, 0.05, 0.855, 0.06); transform: translateY(-15px); } 90% { transform: translateY(-4px); } }\n";
    }

    /**
     * Get tada keyframes
     */
    protected function getTadaKeyframes(): string
    {
        return "@keyframes tada { from { transform: scale3d(1, 1, 1); } 10%, 20% { transform: scale3d(0.9, 0.9, 0.9) rotate3d(0, 0, 1, -3deg); } 30%, 50%, 70%, 90% { transform: scale3d(1.1, 1.1, 1.1) rotate3d(0, 0, 1, 3deg); } 40%, 60%, 80% { transform: scale3d(1.1, 1.1, 1.1) rotate3d(0, 0, 1, -3deg); } to { transform: scale3d(1, 1, 1); } }\n";
    }

    /**
     * Get flash keyframes
     */
    protected function getFlashKeyframes(): string
    {
        return "@keyframes flash { from, 50%, to { opacity: 1; } 25%, 75% { opacity: 0; } }\n";
    }

    /**
     * Get rubber band keyframes
     */
    protected function getRubberBandKeyframes(): string
    {
        return "@keyframes rubberBand { from { transform: scale3d(1, 1, 1); } 30% { transform: scale3d(1.25, 0.75, 1); } 40% { transform: scale3d(0.75, 1.25, 1); } 50% { transform: scale3d(1.15, 0.85, 1); } 65% { transform: scale3d(0.95, 1.05, 1); } 75% { transform: scale3d(1.05, 0.95, 1); } to { transform: scale3d(1, 1, 1); } }\n";
    }

    /**
     * Get heartbeat keyframes
     */
    protected function getHeartbeatKeyframes(): string
    {
        return "@keyframes heartBeat { 0% { transform: scale(1); } 14% { transform: scale(1.3); } 28% { transform: scale(1); } 42% { transform: scale(1.3); } 70% { transform: scale(1); } }\n";
    }

    /**
     * Get flip keyframes
     */
    protected function getFlipKeyframes(): string
    {
        return "@keyframes flip { from { transform: perspective(400px) scaleX(1) translateZ(0) rotateY(-1turn); animation-timing-function: ease-out; } 40% { transform: perspective(400px) scaleX(1) translateZ(150px) rotateY(-190deg); animation-timing-function: ease-out; } 50% { transform: perspective(400px) scaleX(1) translateZ(150px) rotateY(-170deg); animation-timing-function: ease-in; } 80% { transform: perspective(400px) scale3d(0.95, 0.95, 0.95) translateZ(0) rotateY(0deg); animation-timing-function: ease-in; } to { transform: perspective(400px) scaleX(1) translateZ(0) rotateY(0deg); animation-timing-function: ease-in; } }\n";
    }

    /**
     * Get animation CSS format string
     */
    protected function getAnimationCss(string $animation, int $duration, int $delay, string $easing): string
    {
        return sprintf('%s %dms %s %dms forwards', $animation, $duration, $easing, $delay);
    }
}
