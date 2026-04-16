# Jankx as "Flatsome for Gutenberg"

## Vision
Transform Jankx into a Flatsome-style theme builder powered entirely by Gutenberg blocks, with a comprehensive set of custom controls that match or exceed Flatsome's UX Builder capabilities.

## Core Concepts from Flatsome to Port

### 1. Layout System (The Grid)
| Flatsome Feature | Gutenberg Equivalent | Control Needed |
|-----------------|---------------------|----------------|
| Section/Row/Column | Group/Columns/Column | `SectionControl`, `RowControl`, `ColumnControl` |
| Parallax Sections | Cover block enhancement | `ParallaxControl` |
| Sticky Sections | Position sticky | `StickyControl` |
| Full Width & Height | Layout support | `DimensionControl` |
| Dividers | Separator enhancement | `DividerControl` |

### 2. Elements (30+ Flatsome Elements → Blocks)
| Flatsome Element | Block Implementation | Controls Required |
|-----------------|---------------------|-------------------|
| UX Slider | Media Slider block | `SliderControl`, `TransitionControl` |
| UX Banner | Advanced Cover block | `OverlayControl`, `HoverEffectControl` |
| UX Image | Enhanced Image block | `ImageMaskControl`, `HoverZoomControl` |
| UX Video | Video block enhancement | `VideoBackgroundControl`, `LightboxControl` |
| UX Gallery | Gallery block enhancement | `MasonryControl`, `JustifiedControl` |
| Product Grid | Query Loop variation | `ProductLayoutControl`, `QuickViewControl` |
| Countdown Timer | Countdown block | `TimerControl`, `ExpiryActionControl` |
| Animated Text | Heading enhancement | `TextAnimationControl`, `SplitTextControl` |
| Before/After Slider | Comparison block | `ComparisonControl`, `HandleControl` |
| Hotspots | Image Hotspots block | `HotspotControl`, `TooltipControl` |
| Tabs | Tabs block | `TabControl`, `AccordionModeControl` |
| Accordion | Details/Summary enhancement | `AccordionControl` |
| Testimonials | Testimonial carousel | `CarouselControl`, `RatingControl` |
| Team Members | Profile block | `SocialLinksControl`, `LayoutVariationControl` |
| Pricing Table | Pricing block | `PricingStyleControl`, `FeaturedToggleControl` |
| Google Maps | Map block | `MapStyleControl`, `MarkerControl` |
| Contact Form | Form block | `FormFieldControl`, `ValidationControl` |
| Search | Search block | `SearchStyleControl`, `AjaxControl` |
| Share Buttons | Share block | `PlatformControl`, `CountControl` |
| Scroll to Top | Button enhancement | `ScrollBehaviorControl` |
| Breadcrumbs | Breadcrumb block | `BreadcrumbStyleControl`, `HierarchyControl` |

### 3. Design Controls (Flatsome Panel → Inspector Controls)

#### Background Controls
- Color, Gradient, Image, Video backgrounds
- Pattern overlays
- Blend modes
- Multiple background layers

#### Typography Controls
- Google Fonts + Custom Fonts
- Fluid typography (clamp)
- Text shadows
- Letter spacing & line height
- Text transforms
- Font variants (weight, style)

#### Spacing Controls
- Margin/Padding with visual drag handles
- Responsive breakpoints
- Container queries support
- Negative margins

#### Border & Shadow
- Border radius (individual corners)
- Border styles & animations
- Multiple box shadows
- Inner shadows

#### Effects & Animations
- Entrance animations (fade, slide, zoom, flip)
- Hover effects (lift, glow, shake)
- Scroll animations (reveal on scroll)
- Mouse parallax
- Infinite animations (pulse, float)

#### Transform Controls
- 2D/3D transforms
- Hover transforms
- Scroll transforms
- Perspective

### 4. WooCommerce Integration
| Feature | Block | Controls |
|---------|-------|----------|
| Product Grid Variations | Query Loop | `ProductCardControl`, `BadgeControl` |
| Quick View | Modal block | `QuickViewTriggerControl` |
| Wishlist | Button block | `WishlistIntegrationControl` |
| Compare | Table block | `CompareFieldControl` |
| Off-canvas Cart | Sidebar block | `CartTriggerControl`, `MiniCartControl` |
| Catalog Mode | Layout toggle | `CatalogModeControl` |
| Custom Product Tabs | Tabs block | `ProductTabControl` |
| Size Guide | Modal block | `SizeGuideControl` |
| Delivery Time | Text block | `DeliveryEstimateControl` |
| Trust Badges | Image block | `TrustBadgeControl` |

### 5. Global Design System
- Color palette presets (like Flatsome's presets)
- Typography scale
- Spacing scale
- Button styles (primary, secondary, outline, link)
- Form styles
- Card styles
- Shadow presets

## Control Architecture

```php
// Control hierarchy
AbstractControl
├── LayoutControls
│   ├── SectionControl
│   ├── RowControl (12-column grid)
│   ├── ColumnControl
│   └── ContainerControl
├── MediaControls
│   ├── ImageControl (with masks, filters)
│   ├── VideoControl (background, lightbox)
│   ├── GalleryControl (masonry, justified, slider)
│   └── SliderControl (UX Slider replacement)
├── ContentControls
│   ├── TextControl (with animations)
│   ├── HeadingControl
│   ├── ButtonControl (with icons, effects)
│   ├── IconControl (icon libraries)
│   └── DividerControl
├── EcommerceControls
│   ├── ProductGridControl
│   ├── ProductCardControl
│   ├── AddToCartControl
│   ├── PriceControl
│   └── StockControl
├── EffectControls
│   ├── AnimationControl (entrance)
│   ├── HoverEffectControl
│   ├── ScrollEffectControl
│   ├── ParallaxControl
│   └── TransformControl
├── AdvancedControls
│   ├── ConditionControl (display conditions)
│   ├── CustomCssControl
│   ├── VisibilityControl (responsive)
│   └── InteractionControl
└── WooControls (Flatsome-like WooCommerce)
    ├── CatalogControl
    ├── ProductLayoutControl
    ├── ShopFilterControl
    └── CheckoutStyleControl
```

## File Structure

```
jankx/gutenberg-controls/
├── src/
│   ├── Controls/
│   │   ├── AbstractControl.php
│   │   ├── Layout/
│   │   │   ├── SectionControl.php
│   │   │   ├── RowControl.php
│   │   │   └── ColumnControl.php
│   │   ├── Media/
│   │   │   ├── ImageControl.php
│   │   │   ├── GalleryControl.php
│   │   │   └── VideoControl.php
│   │   ├── Effects/
│   │   │   ├── AnimationControl.php
│   │   │   ├── HoverEffectControl.php
│   │   │   └── ScrollEffectControl.php
│   │   └── Ecommerce/
│   │       ├── ProductGridControl.php
│   │       └── ProductCardControl.php
│   ├── Blocks/
│   │   ├── SectionBlock.php
│   │   ├── RowBlock.php
│   │   ├── BannerBlock.php (UX Banner equivalent)
│   │   ├── SliderBlock.php (UX Slider equivalent)
│   │   ├── GalleryBlock.php
│   │   ├── ProductGridBlock.php
│   │   ├── CountdownBlock.php
│   │   ├── HotspotsBlock.php
│   │   ├── ComparisonBlock.php
│   │   └── AnimatedTextBlock.php
│   ├── Hooks/
│   │   ├── EditorAssets.php
│   │   ├── FrontendAssets.php
│   │   └── BlockFilters.php
│   └── Utils/
│       ├── AnimationEngine.php
│       ├── ResponsiveHelper.php
│       └── CssGenerator.php
├── assets/
│   ├── src/
│   │   ├── controls/ (React components)
│   │   ├── blocks/ (Block implementations)
│   │   └── frontend/ (Animation engine, effects)
│   └── dist/
└── docs/
```

## Implementation Phases

### Phase 1: Foundation (2 weeks)
- [ ] Core control framework
- [ ] Section/Row/Column layout system
- [ ] Basic animation engine
- [ ] Editor sidebar panel (Flatsome-style)

### Phase 2: Essential Elements (3 weeks)
- [ ] Banner block (UX Banner equivalent)
- [ ] Slider block (UX Slider equivalent)
- [ ] Gallery block with layouts
- [ ] Image with effects
- [ ] Video block

### Phase 3: WooCommerce (2 weeks)
- [ ] Product grid variations
- [ ] Product card styles
- [ ] Quick view modal
- [ ] Off-canvas cart
- [ ] Catalog mode

### Phase 4: Advanced Effects (2 weeks)
- [ ] Scroll animations library
- [ ] Mouse parallax
- [ ] Advanced hover effects
- [ ] Text animations
- [ ] Hotspots

### Phase 5: Polish & Integration (1 week)
- [ ] Preset system (like Flatsome's presets)
- [ ] Import/Export layouts
- [ ] Template library
- [ ] Global design system sync

## Success Metrics

- **Control Count**: 50+ custom controls (Flatsome has 30+)
- **Block Variations**: 100+ pre-designed patterns
- **Performance**: <100ms additional editor load time
- **UX**: 1-click element insertion (like Flatsome UX Builder)
- **Flexibility**: Every Flatsome feature has Gutenberg equivalent

## Competitive Advantage

| Flatsome UX Builder | Jankx Gutenberg |
|--------------------|-----------------|
| Proprietary builder | Native WordPress blocks |
| Shortcode-based | Modern block markup |
| Locked to theme | Works with any block theme |
| No FSE support | Full Site Editing ready |
| Limited dynamic data | Block bindings API |
| Single license | Open source |
