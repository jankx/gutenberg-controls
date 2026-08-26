/**
 * Jankx Gutenberg Controls - Editor Entry Point
 *
 * Main entry for the block editor integration.
 * Registers custom inspector controls and filters.
 */

import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls, BlockControls, FontSizePicker } from '@wordpress/block-editor';
import { PanelBody, TabPanel, ToolbarGroup, ToolbarButton, Tooltip, BaseControl, Button } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
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
        <div style={{ marginBottom: 12, display: 'flex', gap: 2 }}>
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
        </div>
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
 * JankxBlockToolbar - Toolbar button for template library
 */
const JankxBlockToolbar = ({ clientId, onOpenTemplateLibrary }) => (
    <BlockControls>
        <ToolbarGroup>
            <Tooltip text={__('Template Library', 'jankx')}>
                <ToolbarButton
                    icon={file}
                    label={__('Template Library', 'jankx')}
                    onClick={onOpenTemplateLibrary}
                />
            </Tooltip>
        </ToolbarGroup>
    </BlockControls>
);

/**
 * JankxInspectorControls - Full inspector panel for Jankx blocks
 */
const JankxInspectorControls = ({
    blockConfig,
    presets,
    categories,
    jankxControls,
    applyPreset,
    undoPreset,
    redoPreset,
    renderControl,
    historyIndexRef,
    historyRef,
    setCustomPresets,
}) => {
    const controls = blockConfig.controls || {};
    const hasPresets = presets && presets.length > 0;

    return (
        <InspectorControls>
            {hasPresets && (
                <PresetPanel
                    presets={presets}
                    categories={categories || []}
                    currentValues={jankxControls}
                    onApplyPreset={(preset) => applyPreset(preset, preset.title || 'Preset')}
                />
            )}

            {Object.keys(controls).length > 0 && (
                <PanelBody title={__('Design Controls', 'jankx')} initialOpen={true}>
                    <div style={{ display: 'flex', gap: 4, marginBottom: 8 }}>
                        <Tooltip text={__('Undo', 'jankx')}>
                            <Button
                                icon={undo}
                                size="small"
                                variant="tertiary"
                                disabled={historyIndexRef.current <= 0}
                                onClick={undoPreset}
                            />
                        </Tooltip>
                        <Tooltip text={__('Redo', 'jankx')}>
                            <Button
                                icon={redo}
                                size="small"
                                variant="tertiary"
                                disabled={historyIndexRef.current >= historyRef.current.length - 1}
                                onClick={redoPreset}
                            />
                        </Tooltip>
                    </div>
                    {Object.entries(controls).map(([controlName, controlConfig]) =>
                        renderControl(controlName, controlConfig)
                    )}
                </PanelBody>
            )}
        </InspectorControls>
    );
};

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
}

const withJankxControls = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        // === DEBUG LOGGING FOR REACT ERROR #130 ===
        if (window.jankxControlDebugHasRun !== true) {
            console.log('🛠 [Gutenberg Controls Editor] Checking imports for React #130:');
            const componentsToCheck = { JankxBlockToolbar, JankxInspectorControls, TemplateLibrary, PresetPanel, VisualSpacingControl, ResponsiveWrapper };
            console.log(componentsToCheck);
            Object.entries(componentsToCheck).forEach(([name, c]) => {
                if (c === undefined) console.error(`🚨 FATAL: Component "${name}" is undefined in editor.js!`);
            });
            window.jankxControlDebugHasRun = true;
        }
        // ==========================================

        const { attributes, setAttributes, isSelected, name } = props;

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

        // WordPress undo/redo dispatch — use core store (available in both post editor and site editor)
        const coreStore = 'core';
        let undoAction, redoAction, createSuccessNotice;
        try {
            ({ undo: undoAction, redo: redoAction } = useDispatch(coreStore));
        } catch (e) {
            undoAction = () => {};
            redoAction = () => {};
        }
        try {
            const noticesDispatch = useDispatch('core/notices');
            createSuccessNotice = noticesDispatch.createSuccessNotice;
        } catch (e) {
            createSuccessNotice = () => {};
        }

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
        }, [setAttributes]);

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
        }, [setAttributes]);

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

            const renderContent = (overrideValue, overrideOnChange, wrapperIsLinked, wrapperSetIsLinked) => {
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
                                isLinked={wrapperIsLinked}
                                setIsLinked={wrapperSetIsLinked}
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
                                isLinked={wrapperIsLinked}
                                setIsLinked={wrapperSetIsLinked}
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
                        {({ value: deviceValue, onChange: deviceOnChange, isLinked: wrapperIsLinked, setIsLinked: wrapperSetIsLinked }) =>
                            renderContent(deviceValue, deviceOnChange, wrapperIsLinked, wrapperSetIsLinked)
                        }
                    </ResponsiveWrapper>
                );
            }

            return renderContent(undefined, undefined, undefined, undefined);
        }, [jankxControls, updateControl]);

        // If this is a non-Jankx block, just return the responsive font size controls
        if (Object.keys(blockConfig).length === 0) {
            return (
                <>
                    <BlockEdit {...props} />
                    <NonJankxResponsiveFontSize props={props} />
                </>
            );
        }

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
