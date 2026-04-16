# Jankx Gutenberg Controls

Advanced Gutenberg block controls and Higher-Order Components (HOCs) for the Jankx Framework. Provides custom inspector controls, block bindings, and Full Site Editing (FSE) utilities.

## Features

- **Custom Inspector Controls**: Color, Typography, Spacing, Border, and Shadow controls
- **Higher-Order Components (HOCs)**: Reusable block enhancement wrappers
- **Block Bindings API**: Connect blocks to dynamic data sources (WP 6.5+)
- **Full Site Editing Support**: Template parts, patterns, and global styles integration
- **Native WordPress Compatibility**: Built on `@wordpress/components` and `@wordpress/compose`

## Installation

```bash
composer require jankx/gutenberg-controls
```

## Quick Start

```php
use Jankx\Gutenberg\Controls\ColorControl;
use Jankx\Gutenberg\Controls\TypographyControl;

// Register custom controls for your block
add_filter('jankx/gutenberg/register-controls', function($controls) {
    $controls[] = new ColorControl([
        'name' => 'customBackground',
        'label' => __('Background Color'),
        'supports' => ['gradient', 'duotone'],
    ]);
    return $controls;
});
```

## Documentation

### Available Controls

| Control | Description | Supports |
|---------|-------------|----------|
| `ColorControl` | Advanced color picker | Gradient, Duotone, Transparency |
| `TypographyControl` | Font and text styling | Font Library, Fluid Typography |
| `SpacingControl` | Margin and padding | Responsive breakpoints |
| `BorderControl` | Border styling | Radius, Width, Style |
| `ShadowControl` | Box shadows | Presets, Custom values |
| `AnimationControl` | Entrance & scroll animations | 30+ effects, parallax |
| `SectionControl` | Flatsome-style sections | Dividers, backgrounds, sticky |

### Creating Blocks with Custom Controls

Extend `AbstractBlockWithControls` to create blocks with Jankx controls:

```php
use Jankx\Gutenberg\Blocks\AbstractBlockWithControls;
use Jankx\Gutenberg\Controls\Layout\SectionControl;
use Jankx\Gutenberg\Controls\Effects\AnimationControl;
use Jankx\Gutenberg\Registry\BlockRegistry;

class HeroBlock extends AbstractBlockWithControls
{
    protected function getBlockName(): string
    {
        return 'jankx/hero';
    }

    protected function getBlockTitle(): string
    {
        return __('Hero Section', 'jankx');
    }

    protected function registerControls(): void
    {
        $this->addControl(new SectionControl([
            'name' => 'layout',
            'label' => __('Layout', 'jankx'),
        ]));

        $this->addControl(new AnimationControl([
            'name' => 'animation',
            'label' => __('Animation', 'jankx'),
        ]));
    }

    protected function renderBlockContent(
        array $attributes,
        string $content,
        $block,
        array $jankxControls
    ): string {
        // Custom rendering logic
        return '<div class="hero">' . $content . '</div>';
    }
}

// Register the block
add_action('init', function() {
    $registry = BlockRegistry::getInstance();
    $registry->register(new HeroBlock());
});
```

### Block Bindings (WP 6.5+)

```php
use Jankx\Gutenberg\Bindings\PostMetaBinding;

// Register custom binding source
add_action('init', function() {
    PostMetaBinding::register('jankx/post-meta', [
        'label' => __('Post Meta Field'),
        'get_value' => function($args) {
            return get_post_meta(get_the_ID(), $args['key'], true);
        },
    ]);
});
```

### Full Site Editing Integration

```php
// Register template parts
add_action('jankx/gutenberg/register-template-parts', function($registry) {
    $registry->register('jankx/header', [
        'title' => __('Jankx Header'),
        'area' => 'header',
    ]);
});
```

## Development

### Requirements

- Node.js 16+
- WordPress 6.0+
- PHP 7.4+

### Build

```bash
npm install
npm run build
```

### Testing

```bash
npm test
```

## Architecture

This package follows the WordPress Gutenberg component patterns:

- **Inspector Controls**: Sidebar panel components for block settings
- **Higher-Order Components**: Wrapper functions that add functionality to blocks
- **Block Bindings**: Data connection layer for dynamic content
- **Slot/Fill Pattern**: Extensibility via WordPress SlotFill API

## Compatibility

| WordPress Version | Support Status |
|-------------------|----------------|
| 6.5+ | Full support (Block Bindings API) |
| 6.4+ | Font Library, Style revisions |
| 6.0+ | Core controls and FSE basics |

## Contributing

See [CONTRIBUTING.md](../../CONTRIBUTING.md) for guidelines.

## License

MIT License - see [LICENSE](../../LICENSE) for details.

---

**Note**: This package is designed to integrate seamlessly with the Jankx Theme Framework. For standalone usage, ensure WordPress block editor scripts are properly enqueued.
