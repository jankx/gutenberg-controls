/**
 * Jankx Gutenberg Controls - Editor Entry Point
 *
 * Main entry for the block editor integration.
 * Registers custom inspector controls and filters.
 */

import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TabPanel } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

// Import custom controls
import PresetPanel from './inspector/PresetPanel';
import VisualSpacingControl from './controls/VisualSpacingControl';
import IconPickerControl from './controls/IconPickerControl';
import ResponsiveControl from './controls/ResponsiveControl';

// Import styles
import './styles/editor.scss';

/**
 * Add Jankx controls inspector panel to supported blocks
 */
const withJankxControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        const { name, attributes, setAttributes, isSelected } = props;

        // Only add to Jankx blocks
        if (!name.startsWith('jankx/')) {
            return <BlockEdit {...props} />;
        }

        // Get block configuration
        const blockConfig = window.jankxBlocks?.controls?.[name] || {};
        const presets = window.jankxBlocks?.presets || [];
        const categories = window.jankxBlocks?.categories || [];

        // Get current jankx controls values
        const jankxControls = attributes.jankxControls || {};

        /**
         * Update a specific control value
         */
        const updateControl = (controlName, value) => {
            setAttributes({
                jankxControls: {
                    ...jankxControls,
                    [controlName]: value,
                },
            });
        };

        /**
         * Apply a preset
         */
        const applyPreset = (preset) => {
            const newControls = {
                ...jankxControls,
                ...preset.controls,
            };

            setAttributes({
                jankxControls: newControls,
            });

            // Show notification (in production, use wp.notice)
            // console.log(`Applied preset: ${preset.title}`);
        };

        /**
         * Render control based on type
         */
        const renderControl = (controlName, controlConfig) => {
            const value = jankxControls[controlName] || {};

            switch (controlConfig.type) {
                case 'jankx/spacing':
                case 'jankx/visual-spacing':
                    return (
                        <VisualSpacingControl
                            key={controlName}
                            label={controlConfig.label}
                            value={value}
                            onChange={(newValue) => updateControl(controlName, newValue)}
                            allowResponsive={true}
                        />
                    );

                case 'jankx/icon':
                case 'jankx/icon-picker':
                    return (
                        <IconPickerControl
                            key={controlName}
                            label={controlConfig.label}
                            value={value}
                            onChange={(newValue) => updateControl(controlName, newValue)}
                            allowColor={true}
                            allowSize={true}
                        />
                    );

                case 'jankx/responsive':
                    return (
                        <ResponsiveControl
                            key={controlName}
                            label={controlConfig.label}
                            value={value}
                            onChange={(newValue) => updateControl(controlName, newValue)}
                        />
                    );

                case 'jankx/color':
                case 'jankx/typography':
                case 'jankx/border':
                case 'jankx/shadow':
                    // Use WordPress core components with Jankx enhancements
                    return (
                        <div key={controlName} className="jankx-control-wrapper">
                            {/* WordPress component wrapped with Jankx UX */}
                            <span className="jankx-control-label">{controlConfig.label}</span>
                        </div>
                    );

                case 'jankx/row':
                case 'jankx/image':
                    // Complex controls with dedicated inspectors
                    return (
                        <div key={controlName} className="jankx-control-wrapper jankx-complex-control">
                            <span className="jankx-control-label">{controlConfig.label}</span>
                            <span className="jankx-control-hint">{__('Configure in block toolbar', 'jankx')}</span>
                        </div>
                    );

                default:
                    return null;
            }
        };

        return (
            <>
                <BlockEdit {...props} />

                {isSelected && (
                    <InspectorControls group="styles">
                        <TabPanel
                            className="jankx-inspector-tabs"
                            activeClass="is-active"
                            tabs={[
                                {
                                    name: 'presets',
                                    title: __('Presets', 'jankx'),
                                },
                                {
                                    name: 'layout',
                                    title: __('Layout', 'jankx'),
                                },
                                {
                                    name: 'style',
                                    title: __('Style', 'jankx'),
                                },
                                {
                                    name: 'effects',
                                    title: __('Effects', 'jankx'),
                                },
                            ]}
                        >
                            {(tab) => {
                                switch (tab.name) {
                                    case 'presets':
                                        return (
                                            <PresetPanel
                                                presets={presets}
                                                categories={categories}
                                                currentValues={jankxControls}
                                                onApplyPreset={applyPreset}
                                            />
                                        );

                                    case 'layout':
                                        return (
                                            <PanelBody
                                                title={__('Layout Settings', 'jankx')}
                                                initialOpen={true}
                                            >
                                                {Object.entries(blockConfig).map(
                                                    ([name, config]) =>
                                                        config.category === 'layout' &&
                                                        renderControl(name, config)
                                                )}

                                                {Object.keys(blockConfig).filter(
                                                    key => blockConfig[key].category === 'layout'
                                                ).length === 0 && (
                                                    <p className="jankx-no-controls">
                                                        {__('No layout controls available for this block.', 'jankx')}
                                                    </p>
                                                )}
                                            </PanelBody>
                                        );

                                    case 'style':
                                        return (
                                            <PanelBody
                                                title={__('Style Settings', 'jankx')}
                                                initialOpen={true}
                                            >
                                                {Object.entries(blockConfig).map(
                                                    ([name, config]) =>
                                                        config.category === 'style' &&
                                                        renderControl(name, config)
                                                )}

                                                {Object.keys(blockConfig).filter(
                                                    key => blockConfig[key].category === 'style'
                                                ).length === 0 && (
                                                    <p className="jankx-no-controls">
                                                        {__('No style controls available for this block.', 'jankx')}
                                                    </p>
                                                )}
                                            </PanelBody>
                                        );

                                    case 'effects':
                                        return (
                                            <PanelBody
                                                title={__('Effects & Animations', 'jankx')}
                                                initialOpen={true}
                                            >
                                                {Object.entries(blockConfig).map(
                                                    ([name, config]) =>
                                                        config.category === 'effects' &&
                                                        renderControl(name, config)
                                                )}

                                                {Object.keys(blockConfig).filter(
                                                    key => blockConfig[key].category === 'effects'
                                                ).length === 0 && (
                                                    <p className="jankx-no-controls">
                                                        {__('No effect controls available for this block.', 'jankx')}
                                                    </p>
                                                )}
                                            </PanelBody>
                                        );

                                    default:
                                        return null;
                                }
                            }}
                        </TabPanel>
                    </InspectorControls>
                )}
            </>
        );
    };
}, 'withJankxControls');

/**
 * Register the HOC filter
 */
addFilter(
    'editor.BlockEdit',
    'jankx/gutenberg-controls/with-controls',
    withJankxControls
);

/**
 * Add custom block attributes
 */
addFilter(
    'blocks.registerBlockType',
    'jankx/gutenberg-controls/add-attributes',
    (settings, name) => {
        if (!name.startsWith('jankx/')) {
            return settings;
        }

        return {
            ...settings,
            attributes: {
                ...settings.attributes,
                jankxControls: {
                    type: 'object',
                    default: {},
                },
            },
        };
    }
);

/**
 * Initialize when DOM is ready
 */
wp.domReady(() => {
    // Register custom block categories
    const categories = wp.blocks.getCategories();
    const jankxCategories = [
        { slug: 'jankx', title: __('Jankx Elements', 'jankx') },
        { slug: 'jankx-layout', title: __('Jankx Layout', 'jankx') },
        { slug: 'jankx-media', title: __('Jankx Media', 'jankx') },
        { slug: 'jankx-ecommerce', title: __('Jankx Ecommerce', 'jankx') },
    ];

    // Add categories if not already present
    jankxCategories.forEach((cat) => {
        if (!categories.find((c) => c.slug === cat.slug)) {
            wp.blocks.setCategories([...categories, cat]);
        }
    });

    // console.log('Jankx Gutenberg Controls initialized');
});

/**
 * Export components for external use
 */
export {
    PresetPanel,
    VisualSpacingControl,
    IconPickerControl,
    ResponsiveControl,
};
