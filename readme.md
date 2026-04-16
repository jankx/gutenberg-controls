# Jankx Gutenberg Controls

**Extension layer for WordPress Gutenberg** — adds missing responsive, layout, and animation controls while maintaining 100% compatibility with WordPress core.

> **Philosophy: Extend, Don't Replace**
> 
> We enhance Gutenberg's native capabilities without breaking existing blocks or forking core functionality.

## What Makes This Different?

### WordPress Core → Jankx Extension Layer

```
WordPress Gutenberg (Core)
├─ Basic padding/margin controls (static values)
├─ No responsive visibility per device
├─ No column grid system
├─ No scroll animations
└─ Basic background options

        ↓ Jankx Wraps & Enhances ↓

Jankx Gutenberg Controls (Extension)
├─ 🎨 Visual Spacing Control (drag handles, live preview)
├─ 📱 Responsive Control (hide/show per device, breakpoints)
├─ 📐 Grid System (1/2/3/4/6/12 columns, responsive)
├─ ✨ Animation Control (scroll triggers, 30+ effects)
├─ 🎭 Section Control (Flatsome-style with dividers/parallax)
├─ 🎨 Icon Picker (favorites, recent, categories)
└─ ⚡ Preset System (1-click design templates)
```

**Result**: Same WordPress blocks, supercharged with Flatsome UX Builder capabilities.

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

## Architecture

```
┌─────────────────────────────────────────────────────────┐
│  Your Block (jankx/hero)                                │
├─────────────────────────────────────────────────────────┤
│  AbstractBlockWithControls                              │
│  ├─ Auto-enqueues assets                                │
│  ├─ Generates CSS from control values                   │
│  └─ Renders with jankxControls attributes               │
├─────────────────────────────────────────────────────────┤
│  Jankx Controls (Extensions)                            │
│  ├─ VisualSpacingControl                                │
│  ├─ ResponsiveControl                                    │
│  ├─ IconPickerControl                                   │
│  └─ AnimationControl                                    │
├─────────────────────────────────────────────────────────┤
│  WordPress Core (Native)                                │
│  ├─ @wordpress/components                              │
│  ├─ @wordpress/block-editor                            │
│  └─ @wordpress/compose                                   │
└─────────────────────────────────────────────────────────┘
```

### Key Principles

1. **Zero Breaking Changes**: Existing blocks work unchanged
2. **Progressive Enhancement**: Opt-in via `jankxControls` attribute
3. **CSS-Only Output**: No JavaScript required for frontend
4. **Native UI Patterns**: Uses WordPress component design system

## Available Controls

### Layout Controls

| Control | Type | Description | WordPress Equivalent |
|---------|------|-------------|---------------------|
| `SectionControl` | Layout | Flatsome-style sections with dividers, parallax, sticky | ❌ None |
| `ResponsiveControl` | Layout | Device visibility, columns, order, flex per breakpoint | ❌ None |
| `VisualSpacingControl` | Layout | Drag-handle margin/padding with live preview | ⚠️ Basic (static) |
| `RowControl` | Layout | Grid row with gap, alignment | ⚠️ Group block (limited) |

### Style Controls

| Control | Type | Description | WordPress Equivalent |
|---------|------|-------------|---------------------|
| `ColorControl` | Style | Color + gradient + duotone | ✅ Enhanced |
| `TypographyControl` | Style | Font family, fluid typography | ✅ Enhanced |
| `BorderControl` | Style | Border radius, style, color | ✅ Enhanced |
| `ShadowControl` | Style | Box shadows with presets | ❌ None |

### Advanced Controls

| Control | Type | Description | WordPress Equivalent |
|---------|------|-------------|---------------------|
| `AnimationControl` | Effects | Scroll-triggered animations | ❌ None |
| `IconPickerControl` | UI | Icon library with favorites | ❌ None |
| `PresetManager` | System | 1-click design templates | ❌ None |

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

## How It Works

### Control Rendering Flow

```php
// 1. Block registration
$controls = [
    'layout'     => SectionControl::class,      // Native WP doesn't have
    'responsive' => ResponsiveControl::class,   // Native WP doesn't have  
    'animation'  => AnimationControl::class,   // Native WP doesn't have
];

// 2. Editor: React components render in InspectorControls
// Uses @wordpress/components as base, adds Jankx UX layer

// 3. Frontend: PHP generates CSS from saved attributes
// Output: WordPress-compatible CSS with responsive breakpoints
```

### CSS Output Example

```css
/* WordPress generates base styles */
.wp-block-jankx-section {
    background: #fff;
}

/* Jankx adds responsive enhancement */
@media (max-width: 1024px) {
    .wp-block-jankx-section { padding: 20px; }
}
@media (max-width: 767px) {
    .wp-block-jankx-section { 
        display: none; /* responsive hide */
        flex-direction: column; /* auto-stack */
    }
}
```

## Gap Analysis: WordPress vs Jankx

| Feature | WordPress Core | Jankx Extension | Impact |
|---------|---------------|-----------------|---------|
| **Responsive Visibility** | ❌ Not available | ✅ Hide/show per device | Critical for mobile design |
| **Visual Spacing** | ⚠️ Static input fields | ✅ Drag handles, live preview | 10x faster workflow |
| **Column Grid** | ⚠️ Column block (fixed) | ✅ 1/2/3/4/6/12 responsive cols | Flexible layouts |
| **Scroll Animations** | ❌ Not available | ✅ 30+ entrance effects | Engaging UX |
| **Section Dividers** | ❌ Not available | ✅ SVG shapes, parallax | Professional designs |
| **Icon Picker** | ❌ Not available | ✅ Library with favorites | Consistent branding |
| **Design Presets** | ⚠️ Patterns (static) | ✅ 1-click customizable | Rapid prototyping |
| **Sticky Sections** | ❌ Not available | ✅ Sticky with offset | Modern navigation |

**Total Enhancement**: 8 major features WordPress doesn't have natively.

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
