/**
 * Jankx Gutenberg Controls - Built Version
 * Simplified build for testing
 */

// Export components for use by blocks bridge
window.jankxGutenbergControls = {
    // Controls
    ColorControl: window.wp.components.ColorPalette || function() { return null; },
    TypographyControl: function() { return null; },
    VisualSpacingControl: function() { return null; },
    ResponsiveControl: function() { return null; },
    ShadowControl: function() { return null; },
    IconPickerControl: function() { return null; },
    AnimationControl: function() { return null; },

    // Components
    PresetPanel: function() { return null; },
    CustomPresetManager: function() { return null; },
    TemplateLibrary: function() { return null; },

    // Hooks
    useLivePreview: function() { return null; },
    withLivePreview: function(Component) { return Component; },
};

console.log('Jankx Gutenberg Controls loaded (test build)');
