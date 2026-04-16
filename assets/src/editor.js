/**
 * Jankx Gutenberg Controls - Editor Entry Point
 *
 * Main entry for the block editor integration.
 * Registers custom inspector controls and filters.
 */

import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls, BlockControls } from '@wordpress/block-editor';
import { PanelBody, TabPanel, ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';
import { __ } from '@wordpress/i18n';
import { useState, useCallback, useRef } from '@wordpress/element';
import { undo, redo, cloudUpload, mediaDocument } from '@wordpress/icons';

// Import custom controls
import PresetPanel from './inspector/PresetPanel';
import VisualSpacingControl from './controls/VisualSpacingControl';
import IconPickerControl from './controls/IconPickerControl';
import ResponsiveControl from './controls/ResponsiveControl';
import ColorControl from './controls/ColorControl';
import TypographyControl from './controls/TypographyControl';

// Import new components
import useLivePreview, { withLivePreview } from './components/LivePreview';
import { CustomPresetManager } from './components/CustomPresetManager';
import { TemplateLibrary, TemplateExportButton } from './components/TemplateLibrary';

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
         * Enable live preview for real-time updates
         */
        useLivePreview(props.clientId, jankxControls);

        // State for custom presets and template library
        const [customPresets, setCustomPresets] = useState([]);
        const [isTemplateLibraryOpen, setIsTemplateLibraryOpen] = useState(false);

        // WordPress undo/redo dispatch
        const { undo: undoAction, redo: redoAction } = useDispatch(editorStore);
        const { createSuccessNotice } = useDispatch(noticesStore);

        // History tracking for presets
        const historyRef = useRef([]);
        const historyIndexRef = useRef(-1);

        /**
         * Save current state to history
         */
        const saveToHistory = useCallback((controls) => {
            // Remove any future history if we're not at the end
            if (historyIndexRef.current < historyRef.current.length - 1) {
                historyRef.current = historyRef.current.slice(0, historyIndexRef.current + 1);
            }

            historyRef.current.push(JSON.stringify(controls));
            historyIndexRef.current++;

            // Limit history to 50 states
            if (historyRef.current.length > 50) {
                historyRef.current.shift();
                historyIndexRef.current--;
            }
        }, []);

        /**
         * Update a specific control value with live preview
         */
        const updateControl = useCallback((controlName, value) => {
            const newControls = {
                ...jankxControls,
                [controlName]: value,
            };

            // Save to history before updating
            saveToHistory(jankxControls);

            setAttributes({
                jankxControls: newControls,
            });
        }, [jankxControls, setAttributes, saveToHistory]);

        /**
         * Undo preset application
         */
        const undoPreset = useCallback(() => {
            if (historyIndexRef.current > 0) {
                historyIndexRef.current--;
                const previousState = JSON.parse(historyRef.current[historyIndexRef.current]);

                setAttributes({
                    jankxControls: previousState,
                });

                createSuccessNotice(__('Reverted to previous state', 'jankx'), {
                    type: 'snackbar',
                });

                // Also trigger WordPress undo
                undoAction();
            }
        }, [setAttributes, undoAction, createSuccessNotice]);

        /**
         * Redo preset application
         */
        const redoPreset = useCallback(() => {
            if (historyIndexRef.current < historyRef.current.length - 1) {
                historyIndexRef.current++;
                const nextState = JSON.parse(historyRef.current[historyIndexRef.current]);

                setAttributes({
                    jankxControls: nextState,
                });

                createSuccessNotice(__('Restored next state', 'jankx'), {
                    type: 'snackbar',
                });

                // Also trigger WordPress redo
                redoAction();
            }
        }, [setAttributes, redoAction, createSuccessNotice]);

        /**
         * Apply a preset with undo support
         */
        const applyPreset = useCallback((preset, presetName = 'Preset') => {
            // Save current state before applying
            saveToHistory(jankxControls);

            const newControls = {
                ...jankxControls,
                ...(preset.controls || preset),
            };

            setAttributes({
                jankxControls: newControls,
            });

            createSuccessNotice(
                sprintf(__('Applied %s', 'jankx'), presetName),
                {
                    type: 'snackbar',
                    actions: [
                        {
                            label: __('Undo', 'jankx'),
                            onClick: undoPreset,
                        },
                    ],
                }
            );
        }, [jankxControls, setAttributes, saveToHistory, undoPreset, createSuccessNotice]);

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
                    return (
                        <ColorControl
                            key={controlName}
                            label={controlConfig.label}
                            value={value}
                            onChange={(newValue) => updateControl(controlName, newValue)}
                            allowSolid={controlConfig.allowSolid !== false}
                            allowGradient={controlConfig.allowGradient !== false}
                            allowDuotone={controlConfig.allowDuotone !== false}
                            allowAlpha={controlConfig.allowAlpha !== false}
                            allowTheme={controlConfig.allowTheme !== false}
                        />
                    );

                case 'jankx/typography':
                    return (
                        <TypographyControl
                            key={controlName}
                            label={controlConfig.label}
                            value={value}
                            onChange={(newValue) => updateControl(controlName, newValue)}
                            allowFluid={controlConfig.allowFluid !== false}
                            allowResponsive={controlConfig.allowResponsive !== false}
                        />
                    );

                case 'jankx/border':
                case 'jankx/shadow':
                    // Placeholder for future implementation
                    return (
                        <div key={controlName} className="jankx-control-wrapper">
                            <span className="jankx-control-label">{controlConfig.label}</span>
                            <span className="jankx-control-hint">{__('Configure in Style tab', 'jankx')}</span>
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

                {/* Block Toolbar - Template Library */}
                {isSelected && (
                    <BlockControls group="other">
                        <ToolbarGroup>
                            <TemplateExportButton clientId={props.clientId} />
                            <ToolbarButton
                                icon={cloudUpload}
                                label={__('Import Template', 'jankx')}
                                onClick={() => setIsTemplateLibraryOpen(true)}
                            />
                        </ToolbarGroup>
                    </BlockControls>
                )}

                {/* Template Library Modal */}
                {isTemplateLibraryOpen && (
                    <TemplateLibrary
                        clientId={props.clientId}
                        isOpen={isTemplateLibraryOpen}
                        onClose={() => setIsTemplateLibraryOpen(false)}
                        mode="import"
                    />
                )}

                {/* Inspector Controls */}
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
                                            <>
                                                {/* Built-in Presets */}
                                                <PresetPanel
                                                    presets={presets}
                                                    categories={categories}
                                                    currentValues={jankxControls}
                                                    onApplyPreset={(preset) =>
                                                        applyPreset(preset, preset.title)
                                                    }
                                                />

                                                {/* Custom User Presets */}
                                                <CustomPresetManager
                                                    currentControls={jankxControls}
                                                    onApplyPreset={(controls) =>
                                                        applyPreset({ controls }, __('Custom Preset', 'jankx'))
                                                    }
                                                    onPresetsChange={setCustomPresets}
                                                />

                                                {/* Preset Undo/Redo */}
                                                <PanelBody
                                                    title={__('History', 'jankx')}
                                                    initialOpen={false}
                                                >
                                                    <div className="jankx-history-controls">
                                                        <ToolbarGroup>
                                                            <ToolbarButton
                                                                icon={undo}
                                                                label={__('Undo Preset', 'jankx')}
                                                                onClick={undoPreset}
                                                                disabled={historyIndexRef.current <= 0}
                                                            />
                                                            <ToolbarButton
                                                                icon={redo}
                                                                label={__('Redo Preset', 'jankx')}
                                                                onClick={redoPreset}
                                                                disabled={
                                                                    historyIndexRef.current >=
                                                                    historyRef.current.length - 1
                                                                }
                                                            />
                                                        </ToolbarGroup>
                                                        <p className="jankx-history-hint">
                                                            {__('Undo/Redo preset applications', 'jankx')}
                                                        </p>
                                                    </div>
                                                </PanelBody>
                                            </>
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
/**
 * Export all components for external use
 */
export {
    PresetPanel,
    VisualSpacingControl,
    IconPickerControl,
    ResponsiveControl,
    ColorControl,
    TypographyControl,
    useLivePreview,
    withLivePreview,
    CustomPresetManager,
    TemplateLibrary,
    TemplateExportButton,
};
