<?php

namespace Jankx\Gutenberg\Tests\Controls;

use Jankx\Gutenberg\Controls\Effects\AnimationControl;
use PHPUnit\Framework\TestCase;
use Brain\Monkey;

/**
 * Test case for AnimationControl
 *
 * @package Jankx\Gutenberg\Tests\Controls
 * @covers \Jankx\Gutenberg\Controls\Effects\AnimationControl
 */
class AnimationControlTest extends TestCase
{
    /**
     * @var AnimationControl
     */
    private $control;

    protected function setUp(): void
    {
        parent::setUp();
        Monkey\setUp();

        $this->control = new AnimationControl([
            'name' => 'animation',
            'label' => __('Animation', 'jankx'),
        ]);
    }

    protected function tearDown(): void
    {
        Monkey\tearDown();
        parent::tearDown();
    }

    /** @test */
    public function it_has_correct_type()
    {
        $this->assertEquals('jankx/animation', $this->control->getType());
    }

    /** @test */
    public function it_has_effects_category()
    {
        $reflection = new \ReflectionClass($this->control);
        $property = $reflection->getProperty('category');
        $property->setAccessible(true);

        $this->assertEquals('effects', $property->getValue($this->control));
    }

    /** @test */
    public function it_has_entrance_animations_list()
    {
        $animations = $this->control->getAnimations();

        $this->assertIsArray($animations);
        $this->assertArrayHasKey('fadeIn', $animations);
        $this->assertArrayHasKey('fadeInUp', $animations);
        $this->assertArrayHasKey('zoomIn', $animations);
        $this->assertArrayHasKey('bounceIn', $animations);
    }

    /** @test */
    public function it_has_infinite_animations_list()
    {
        $animations = $this->control->getInfiniteAnimations();

        $this->assertIsArray($animations);
        $this->assertArrayHasKey('pulse', $animations);
        $this->assertArrayHasKey('bounce', $animations);
        $this->assertArrayHasKey('shake', $animations);
    }

    /** @test */
    public function it_generates_css_for_entrance_animation()
    {
        $value = [
            'entrance' => 'fadeInUp',
            'entranceDuration' => 1000,
            'entranceDelay' => 0,
            'entranceEasing' => 'ease-out',
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('fadeInUp', $css);
        $this->assertStringContainsString('1000ms', $css);
        $this->assertStringContainsString('ease-out', $css);
    }

    /** @test */
    public function it_adds_initial_hidden_state_for_scroll_trigger()
    {
        $value = [
            'entrance' => 'fadeIn',
            'scrollTrigger' => true,
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('opacity: 0', $css);
        $this->assertStringContainsString('visibility: hidden', $css);
    }

    /** @test */
    public function it_generates_css_for_infinite_animation()
    {
        $value = [
            'infinite' => 'pulse',
            'infiniteDuration' => 2000,
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('pulse', $css);
        $this->assertStringContainsString('2000ms', $css);
        $this->assertStringContainsString('infinite', $css);
    }

    /** @test */
    public function it_combines_entrance_and_infinite_animations()
    {
        $value = [
            'entrance' => 'fadeIn',
            'entranceDuration' => 800,
            'infinite' => 'pulse',
            'infiniteDuration' => 2000,
        ];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertStringContainsString('fadeIn', $css);
        $this->assertStringContainsString('pulse', $css);
        $this->assertStringContainsString('animation:', $css);
    }

    /** @test */
    public function it_returns_empty_css_for_no_animation()
    {
        $value = [];

        $css = $this->control->generateCss($value, '.jankx-block');

        $this->assertEquals('', $css);
    }

    /** @test */
    public function it_generates_js_config_for_entrance()
    {
        $value = [
            'entrance' => 'fadeInUp',
            'entranceDuration' => 1000,
            'entranceDelay' => 200,
            'entranceEasing' => 'ease-out',
            'scrollTrigger' => true,
            'scrollOffset' => 100,
            'scrollOnce' => true,
        ];

        $config = $this->control->getJsConfig($value);

        $this->assertTrue($config['enabled']);
        $this->assertEquals('entrance', $config['type']);
        $this->assertEquals('fadeInUp', $config['animation']);
        $this->assertEquals(1000, $config['duration']);
        $this->assertEquals(200, $config['delay']);
        $this->assertTrue($config['scrollTrigger']);
        $this->assertEquals(100, $config['scrollOffset']);
    }

    /** @test */
    public function it_generates_js_config_for_infinite()
    {
        $value = [
            'infinite' => 'pulse',
            'infiniteDuration' => 1500,
        ];

        $config = $this->control->getJsConfig($value);

        $this->assertArrayHasKey('infinite', $config);
        $this->assertEquals('pulse', $config['infinite']['animation']);
        $this->assertEquals(1500, $config['infinite']['duration']);
    }

    /** @test */
    public function it_generates_js_config_for_hover()
    {
        $value = [
            'hover' => 'bounce',
            'hoverDuration' => 300,
        ];

        $config = $this->control->getJsConfig($value);

        $this->assertArrayHasKey('hover', $config);
        $this->assertEquals('bounce', $config['hover']['animation']);
        $this->assertEquals(300, $config['hover']['duration']);
    }

    /** @test */
    public function it_generates_js_config_for_stagger()
    {
        $value = [
            'entrance' => 'fadeInUp',
            'staggerChildren' => true,
            'staggerDelay' => 100,
        ];

        $config = $this->control->getJsConfig($value);

        $this->assertArrayHasKey('stagger', $config);
        $this->assertTrue($config['stagger']['enabled']);
        $this->assertEquals(100, $config['stagger']['delay']);
    }

    /** @test */
    public function it_returns_disabled_config_for_empty_values()
    {
        $value = [];

        $config = $this->control->getJsConfig($value);

        $this->assertFalse($config['enabled']);
    }

    /** @test */
    public function it_gets_animation_css_format()
    {
        $reflection = new \ReflectionClass($this->control);
        $method = $reflection->getMethod('getAnimationCss');
        $method->setAccessible(true);

        $result = $method->invoke($this->control, 'fadeIn', 1000, 200, 'ease-out');

        $this->assertEquals('fadeIn 1000ms ease-out 200ms forwards', $result);
    }

    /** @test */
    public function it_has_keyframes_css()
    {
        $keyframes = $this->control->getKeyframesCss();

        $this->assertStringContainsString('@keyframes fadeIn', $keyframes);
        $this->assertStringContainsString('@keyframes fadeInUp', $keyframes);
        $this->assertStringContainsString('@keyframes zoomIn', $keyframes);
        $this->assertStringContainsString('@keyframes pulse', $keyframes);
        $this->assertStringContainsString('@keyframes bounce', $keyframes);
    }

    /** @test */
    public function it_has_scroll_reveal_classes_in_keyframes()
    {
        $keyframes = $this->control->getKeyframesCss();

        $this->assertStringContainsString('.jankx-animate-on-scroll', $keyframes);
        $this->assertStringContainsString('.jankx-animate-on-scroll.jankx-animated', $keyframes);
    }

    /** @test */
    public function it_renders_editor_placeholder()
    {
        $render = $this->control->renderEditor(['entrance' => 'fadeIn'], function() {});

        $this->assertStringContainsString('AnimationInspector', $render);
    }

    /** @test */
    public function it_has_all_expected_entrance_animations()
    {
        $animations = $this->control->getAnimations();

        $expectedAnimations = [
            'fadeIn', 'fadeInUp', 'fadeInDown', 'fadeInLeft', 'fadeInRight',
            'slideInUp', 'slideInDown', 'slideInLeft', 'slideInRight',
            'zoomIn', 'zoomInUp', 'zoomInDown',
            'flipInX', 'flipInY',
            'rotateIn', 'rotateInUpLeft', 'rotateInDownRight',
            'bounceIn', 'bounceInUp', 'bounceInDown',
        ];

        foreach ($expectedAnimations as $animation) {
            $this->assertArrayHasKey($animation, $animations, "Missing animation: {$animation}");
        }
    }
}
