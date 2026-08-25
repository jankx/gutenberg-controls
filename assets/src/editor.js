/**
 * Jankx Gutenberg Controls - Editor Entry Point
 *
 * Main entry for the block editor integration.
 * Registers custom inspector controls and filters.
 */

import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls, BlockControls, FontSizePicker } from '@wordpress/block-editor';
import { PanelBody, TabPanel, ToolbarGroup, ToolbarButton, Tooltip, BaseControl, Button, ButtonGroup } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';
import { __, sprintf } from '@wordpress/i18n';
import { useState, useCallback, useRef, useMemo, useEffect } from '@wordpress/element';
import { undo, redo, cloudUpload, desktop, tablet, mobile, file } from '@wordpress/icons';

// Import custom controls
import PresetPanel from './inspector/PresetPanel';
import VisualSpacingControl from './controls/VisualSpacingControl';
import IconPickerControl from './controls/IconPickerControl';
import ResponsiveControl from './controls/ResponsiveControl';
import ColorControl from './controls/ColorControl';
import TypographyControl from './controls/TypographyControl';

// Import new components
import ResponsiveWrapper from './components/ResponsiveWrapper';
import useLivePreview, { withLivePreview } from './components/LivePreview';
import { CustomPresetManager } from './components/CustomPresetManager';
import { TemplateLibrary, TemplateExportButton } from './components/TemplateLibrary';

// Import styles
import './styles/editor.scss';

/**
 * Responsive Font Size device buttons + FontSizePicker
 */
const FONT_SIZE_DEVICES = [
    { name: 'ultrawide', label: __('Ultrawide', 'jankx'), icon: desktop },
    { name: 'desktop', label: __('Desktop', 'jankx'), icon: desktop },
    { name: 'tablet', label: __('Tablet', 'jankx'), icon: tablet },
    { name: 'mobile', label: __('Mobile', 'jankx'), icon: mobile },
];

const ResponsiveFontSizeContent = ({ device, onDeviceChange, value, onChange }) => (
    <div className="jankx-responsive-font-size">
        <ButtonGroup style={{ marginBottom: 12, display: 'flex', gap: 2 }}>
            {FONT_SIZE_DEVICES.map((d) => (
                <Tooltip key={d.name} text={d.label}>
                    <Button
                        icon={d.icon}
                        variant={device === d.name ? 'primary' : 'secondary'}
                        onClick={() => onDeviceChange(d.name)}
                        size="small"
                    />
                </Tooltip>
            ))}
        </ButtonGroup>
        <BaseControl
            label={sprintf(__('Font Size (%s)', 'jankx'), FONT_SIZE_DEVICES.find(d => d.name === device)?.label || device)}
        >
            <FontSizePicker
                value={value}
                onChange={onChange}
                withReset={false}
                __nextHasNoMarginBottom
            />
        </BaseControl>
    </div>
);

/**
 * Stable empty object default to avoid new reference every render
 */
const EMPTY_OBJECT = {};

/**
 * Responsive Font Size controls for non-Jankx blocks
 */
const NonJankxResponsiveFontSize = ({ props }) => {
    const { attributes, setAttributes, isSelected } = props;
    const responsiveFs = attributes.jankxResponsiveFontSize || EMPTY_OBJECT;
    const [fsDevice, setFsDevice] = useState('desktop');

    const updateResponsiveFontSize = (device, value) => {
        setAttributes({
            jankxResponsiveFontSize: {
                ...responsiveFs,
                [device]: value || undefined,
            },
        });
    };

    if (!isSelected) return null;

    return (
        <InspectorControls>
            <PanelBody
                title={__('Responsive Font Size', 'jankx')}
                initialOpen={false}
            >
                <ResponsiveFontSizeContent
                    device={fsDevice}
                    onDeviceChange={setFsDevice}
                    value={responsiveFs[fsDevice] || attributes.fontSize || undefined}
                    onChange={(val) => updateResponsiveFontSize(fsDevice, val)}
                />
            </PanelBody>
        </InspectorControls>
    );
};

/**
 * Block toolbar controls for Jankx blocks
 */
const JankxBlockToolbar = ({ clientId, onOpenTemplateLibrary }) => (
    <BlockControls group="other">
        <ToolbarGroup>
            <TemplateExportButton clientId={clientId} />
            <ToolbarButton
                icon={cloudUpload}
                label={__('Import Template', 'jankx')}
                onClick={onOpenTemplateLibrary}
            />
        </ToolbarGroup>
    </BlockControls>
);

/**
 * Inspector controls panel for Jankx blocks
 */
const JankxInspectorControls = ({
    blockConfig, presets, categories, jankxControls,
    applyPreset, undoPreset, redoPreset, renderControl,
    historyIndexRef, historyRef, setCustomPresets,
}) => (
    <InspectorControls group="styles">
        <TabPanel
            className="jankx-inspector-tabs"
            activeClass="is-active"
            tabs={[
                { name: 'presets', title: __('Presets', 'jankx') },
                { name: 'layout', title: __('Layout', 'jankx') },
                { name: 'style', title: __('Style', 'jankx') },
                { name: 'effects', title: __('Effects', 'jankx') },
            ]}
        >
            {(tab) => {
                switch (tab.name) {
                    case 'presets':
                        return (
                            <>
                                <PresetPanel
                                    presets={presets}
                                    categories={categories}
                                    currentValues={jankxControls}
                                    onApplyPreset={(preset) => applyPreset(preset, preset.title)}
                                />
                                <CustomPresetManager
                                    currentControls={jankxControls}
                                    onApplyPreset={(controls) =>
                                        applyPreset({ controls }, __('Custom Preset', 'jankx'))
                                    }
                                    onPresetsChange={setCustomPresets}
                                />
                                <PanelBody title={__('History', 'jankx')} initialOpen={false}>
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
                                                disabled={historyIndexRef.current >= historyRef.current.length - 1}
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
                            <PanelBody title={__('Layout Settings', 'jankx')} initialOpen={true}>
                                {Object.entries(blockConfig).map(
                                    ([name, config]) => config.category === 'layout' && renderControl(name, config)
                                )}
                                {Object.keys(blockConfig).filter(key => blockConfig[key].category === 'layout').length === 0 && (
                                    <p className="jankx-no-controls">
                                        {__('No layout controls available for this block.', 'jankx')}
                                    </p>
                                )}
                            </PanelBody>
                        );
                    case 'style':
                        return (
                            <PanelBody title={__('Style Settings', 'jankx')} initialOpen={true}>
                                {Object.entries(blockConfig).map(
                                    ([name, config]) => config.category === 'style' && renderControl(name, config)
                                )}
                                {Object.keys(blockConfig).filter(key => blockConfig[key].category === 'style').length === 0 && (
                                    <p className="jankx-no-controls">
                                        {__('No style controls available for this block.', 'jankx')}
                                    </p>
                                )}
                            </PanelBody>
                        );
                    case 'effects':
                        return (
                            <PanelBody title={__('Effects & Animations', 'jankx')} initialOpen={true}>
                                {Object.entries(blockConfig).map(
                                    ([name, config]) => config.category === 'effects' && renderControl(name, config)
                                )}
                                {Object.keys(blockConfig).filter(key => blockConfig[key].category === 'effects').length === 0 && (
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
);

/**
 * Add Jankx controls inspector panel to supported blocks
 */
const withJankxControls = createHigherOrderComponent((BlockEdit) => {
    const WrappedBlockEdit = (props) => {
        const { name, attributes, setAttributes, isSelected } = props;

        const blockType = wp.blocks?.getBlockType?.(name);
        const supportsFontSize = blockType?.supports?.typography?.fontSize;
        const isJankxBlock = name.startsWith('jankx/');

        // For non-Jankx blocks, only add responsive font-size if supported
        if (!isJankxBlock) {
            if (!supportsFontSize) {
                return <BlockEdit {...props} />;
            }

            return (
                <>
                    <BlockEdit {...props} />
                    <NonJankxResponsiveFontSize props={props} />
                </>
            );
        }

        // Get block configuration
        const blockConfig = window.jankxBlocks?.controls?.[name] || EMPTY_OBJECT;
        const presets = window.jankxBlocks?.presets || [];
        const categories = window.jankxBlocks?.categories || [];

        // Get current jankx controls values - use stable reference
        const jankxControls = useMemo(
            () => attributes.jankxControls || EMPTY_OBJECT,
            [attributes.jankxControls]
        );

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
            if (historyIndexRef.current < historyRef.current.length - 1) {
                historyRef.current = historyRef.current.slice(0, historyIndexRef.current + 1);
            }
            historyRef.current.push(JSON.stringify(controls));
            historyIndexRef.current++;
            if (historyRef.current.length > 50) {
                historyRef.current.shift();
                historyIndexRef.current--;
            }
        }, []);

        /**
         * Update a specific control value with live preview
         */
        const updateControl = useCallback((controlName, value) => {
            const newControls = { ...jankxControls, [controlName]: value };
            saveToHistory(jankxControls);
            setAttributes({ jankxControls: newControls });
        }, [jankxControls, setAttributes, saveToHistory]);

        /**
         * Undo preset application
         */
        const undoPreset = useCallback(() => {
            if (historyIndexRef.current > 0) {
                historyIndexRef.current--;
                const previousState = JSON.parse(historyRef.current[historyIndexRef.current]);
                setAttributes({ jankxControls: previousState });
                createSuccessNotice(__('Reverted to previous state', 'jankx'), { type: 'snackbar' });
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
                setAttributes({ jankxControls: nextState });
                createSuccessNotice(__('Restored next state', 'jankx'), { type: 'snackbar' });
                redoAction();
            }
        }, [setAttributes, redoAction, createSuccessNotice]);

        /**
         * Apply a preset with undo support
         */
        const applyPreset = useCallback((preset, presetName = 'Preset') => {
            saveToHistory(jankxControls);
            const newControls = { ...jankxControls, ...(preset.controls || preset) };
            setAttributes({ jankxControls: newControls });
            createSuccessNotice(
                sprintf(__('Applied %s', 'jankx'), presetName),
                {
                    type: 'snackbar',
                    actions: [{ label: __('Undo', 'jankx'), onClick: undoPreset }],
                }
            );
        }, [jankxControls, setAttributes, saveToHistory, undoPreset, createSuccessNotice]);

        /**
         * Render control based on type, optionally wrapped with ResponsiveWrapper
         */
        const renderControl = useCallback((controlName, controlConfig) => {
            const value = jankxControls[controlName] || {};

            const renderContent = (overrideValue, overrideOnChange) => {
                const currentValue = overrideValue !== undefined ? overrideValue : value;
                const handleChange = overrideOnChange || ((newValue) => updateControl(controlName, newValue));

                switch (controlConfig.type) {
                    case 'jankx/spacing':
                    case 'jankx/visual-spacing':
                        return (
                            <VisualSpacingControl
                                key={controlName}
                                label={controlConfig.label}
                                value={currentValue}
                                onChange={handleChange}
                                allowResponsive={controlConfig.responsive !== false}
                            />
                        );
                    case 'jankx/icon':
                    case 'jankx/icon-picker':
                        return (
                            <IconPickerControl
                                key={controlName}
                                label={controlConfig.label}
                                value={currentValue}
                                onChange={handleChange}
                                allowColor={true}
                                allowSize={true}
                            />
                        );
                    case 'jankx/responsive':
                        return (
                            <ResponsiveControl
                                key={controlName}
                                label={controlConfig.label}
                                value={currentValue}
                                onChange={handleChange}
                            />
                        );
                    case 'jankx/color':
                        return (
                            <ColorControl
                                key={controlName}
                                label={controlConfig.label}
                                value={currentValue}
                                onChange={handleChange}
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
                                value={currentValue}
                                onChange={handleChange}
                                allowFluid={controlConfig.allowFluid !== false}
                                allowResponsive={controlConfig.allowResponsive !== false}
                            />
                        );
                    case 'jankx/border':
                    case 'jankx/shadow':
                        return (
                            <div key={controlName} className="jankx-control-wrapper">
                                <span className="jankx-control-label">{controlConfig.label}</span>
                                <span className="jankx-control-hint">{__('Configure in Style tab', 'jankx')}</span>
                            </div>
                        );
                    case 'jankx/row':
                    case 'jankx/image':
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

            if (controlConfig.responsive) {
                return (
                    <ResponsiveWrapper
                        key={controlName}
                        label={controlConfig.label}
                        value={value}
                        onChange={(newValue) => updateControl(controlName, newValue)}
                        responsive={true}
                    >
                        {({ value: deviceValue, onChange: deviceOnChange }) =>
                            renderContent(deviceValue, deviceOnChange)
                        }
                    </ResponsiveWrapper>
                );
            }

            return renderContent();
        }, [jankxControls, updateControl]);

        return (
            <>
                <BlockEdit {...props} />

                {isSelected && (
                    <JankxBlockToolbar
                        clientId={props.clientId}
                        onOpenTemplateLibrary={() => setIsTemplateLibraryOpen(true)}
                    />
                )}

                {isTemplateLibraryOpen && (
                    <TemplateLibrary
                        clientId={props.clientId}
                        isOpen={isTemplateLibraryOpen}
                        onClose={() => setIsTemplateLibraryOpen(false)}
                        mode="import"
                    />
                )}

                {isSelected && (
                    <JankxInspectorControls
                        blockConfig={blockConfig}
                        presets={presets}
                        categories={categories}
                        jankxControls={jankxControls}
                        applyPreset={applyPreset}
                        undoPreset={undoPreset}
                        redoPreset={redoPreset}
                        renderControl={renderControl}
                        historyIndexRef={historyIndexRef}
                        historyRef={historyRef}
                        setCustomPresets={setCustomPresets}
                    />
                )}
            </>
        );
    };

    return WrappedBlockEdit;
}, 'withJankxControls');

/**
 * Register the HOC filter (single filter for all Jankx features)
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
        const supportsFontSize = settings?.supports?.typography?.fontSize;

        const newAttrs = {
            ...settings.attributes,
            jankxControls: {
                type: 'object',
                default: {},
            },
        };

        // Add responsiveFontSize attribute for blocks that support typography.fontSize
        if (supportsFontSize) {
            newAttrs.jankxResponsiveFontSize = {
                type: 'object',
                default: {},
            };
        }

        return {
            ...settings,
            attributes: newAttrs,
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
