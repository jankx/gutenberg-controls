/**
 * Responsive Control - Device visibility and breakpoint management
 *
 * High-UX component for managing responsive behavior:
 * - Device visibility toggles with visual icons
 * - Live preview of responsive state
 * - Quick presets for common patterns
 * - Per-device settings for columns, order, alignment
 * - Visual breakpoint indicator
 */

import { useState, useCallback } from '@wordpress/element';
import {
    BaseControl,
    Button,
    ButtonGroup,
    ToggleControl,
    __experimentalGrid as Grid,
    __experimentalHStack as HStack,
    __experimentalVStack as VStack,
    Tooltip,
    Icon,
    __experimentalNumberControl as NumberControl,
    SelectControl,
    PanelRow,
    __experimentalDivider as Divider,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
    desktop,
    tablet,
    mobile,
    layout,
    order as orderIcon,
    textAlignLeft,
    textAlignCenter,
    textAlignRight,
    textAlignJustify,
    arrowRight,
    arrowDown,
    arrowUp,
    arrowLeft,
    stackUp,
} from '@wordpress/icons';

const DEVICES = [
    { name: 'desktop', icon: desktop, label: __('Desktop', 'jankx'), width: '100%' },
    { name: 'tablet', icon: tablet, label: __('Tablet', 'jankx'), width: '768px' },
    { name: 'mobile', icon: mobile, label: __('Mobile', 'jankx'), width: '360px' },
];

const FLEX_DIRECTIONS = [
    { value: 'row', label: __('Row', 'jankx'), icon: arrowRight },
    { value: 'row-reverse', label: __('Row Reverse', 'jankx'), icon: arrowLeft },
    { value: 'column', label: __('Column', 'jankx'), icon: arrowDown },
    { value: 'column-reverse', label: __('Col Reverse', 'jankx'), icon: arrowUp },
];

const ALIGN_ITEMS = [
    { value: 'flex-start', label: __('Start', 'jankx') },
    { value: 'flex-end', label: __('End', 'jankx') },
    { value: 'center', label: __('Center', 'jankx') },
    { value: 'stretch', label: __('Stretch', 'jankx') },
    { value: 'baseline', label: __('Baseline', 'jankx') },
];

const JUSTIFY_CONTENT = [
    { value: 'flex-start', label: __('Start', 'jankx') },
    { value: 'flex-end', label: __('End', 'jankx') },
    { value: 'center', label: __('Center', 'jankx') },
    { value: 'space-between', label: __('Space Between', 'jankx') },
    { value: 'space-around', label: __('Space Around', 'jankx') },
    { value: 'space-evenly', label: __('Space Evenly', 'jankx') },
];

const TEXT_ALIGNMENTS = [
    { value: 'left', label: __('Left', 'jankx'), icon: textAlignLeft },
    { value: 'center', label: __('Center', 'jankx'), icon: textAlignCenter },
    { value: 'right', label: __('Right', 'jankx'), icon: textAlignRight },
    { value: 'justify', label: __('Justify', 'jankx'), icon: textAlignJustify },
];

const STACK_PRESETS = [
    { value: 'none', label: __('Never', 'jankx'), icon: layout },
    { value: 'tablet', label: __('Tablet', 'jankx'), icon: tablet },
    { value: 'mobile', label: __('Mobile', 'jankx'), icon: mobile },
];

const ResponsiveControl = ({
    label,
    value = {},
    onChange,
    allowVisibility = true,
    allowColumns = true,
    allowOrder = true,
    allowFlex = true,
    allowTextAlign = true,
    allowStack = true,
}) => {
    const [activeDevice, setActiveDevice] = useState('desktop');
    const [showAdvanced, setShowAdvanced] = useState(false);

    const updateDeviceValue = useCallback((device, key, newValue) => {
        const deviceKey = `${key}${device.charAt(0).toUpperCase() + device.slice(1)}`;
        onChange({
            ...value,
            [deviceKey]: newValue,
        });
    }, [value, onChange]);

    const getDeviceValue = useCallback((device, key, defaultValue = null) => {
        const deviceKey = `${key}${device.charAt(0).toUpperCase() + device.slice(1)}`;
        return value[deviceKey] ?? defaultValue;
    }, [value]);

    const toggleVisibility = (device) => {
        const key = `hideOn${device.charAt(0).toUpperCase() + device.slice(1)}`;
        onChange({
            ...value,
            [key]: !value[key],
        });
    };

    const isHidden = (device) => {
        const key = `hideOn${device.charAt(0).toUpperCase() + device.slice(1)}`;
        return value[key] ?? false;
    };

    const applyStackPreset = (preset) => {
        onChange({
            ...value,
            stackVertically: preset !== 'none',
            stackAtBreakpoint: preset,
        });
    };

    const getActiveStackPreset = () => {
        if (!value.stackVertically) return 'none';
        return value.stackAtBreakpoint || 'mobile';
    };

    return (
        <BaseControl label={label} className="jankx-responsive-control">
            {/* Visual breakpoint indicator */}
            <div className="jankx-responsive-indicator">
                <Icon icon={DEVICES.find(d => d.name === activeDevice).icon} />
                <span>
                    {__('Editing for:', 'jankx')} <strong>{DEVICES.find(d => d.name === activeDevice).label}</strong>
                </span>
            </div>

            {/* Device selector tabs */}
            <ButtonGroup className="jankx-device-tabs">
                {DEVICES.map((device) => (
                    <Tooltip key={device.name} text={device.label}>
                        <Button
                            icon={device.icon}
                            isPrimary={activeDevice === device.name}
                            isSecondary={activeDevice !== device.name}
                            onClick={() => setActiveDevice(device.name)}
                            className={`jankx-device-tab ${isHidden(device.name) ? 'is-hidden' : ''}`}
                        >
                            {isHidden(device.name) && (
                                <span className="jankx-hidden-badge">{__('Hidden', 'jankx')}</span>
                            )}
                        </Button>
                    </Tooltip>
                ))}
            </ButtonGroup>

            {/* Visibility toggles */}
            {allowVisibility && (
                <PanelRow className="jankx-visibility-toggles">
                    <label className="jankx-control-label">
                        {__('Hide on devices', 'jankx')}
                    </label>
                    <HStack spacing={2}>
                        {DEVICES.map((device) => (
                            <Button
                                key={device.name}
                                icon={device.icon}
                                isPressed={isHidden(device.name)}
                                onClick={() => toggleVisibility(device.name)}
                                className={`jankx-visibility-btn ${isHidden(device.name) ? 'is-hidden' : ''}`}
                                label={isHidden(device.name)
                                    ? __('Hidden on', 'jankx') + ' ' + device.label
                                    : __('Visible on', 'jankx') + ' ' + device.label
                                }
                            />
                        ))}
                    </HStack>
                </PanelRow>
            )}

            <Divider className="jankx-divider" />

            {/* Quick stack preset */}
            {allowStack && (
                <PanelRow className="jankx-stack-preset">
                    <label className="jankx-control-label">
                        {__('Stack vertically at', 'jankx')}
                    </label>
                    <ButtonGroup className="jankx-stack-buttons">
                        {STACK_PRESETS.map((preset) => (
                            <Button
                                key={preset.value}
                                icon={preset.icon}
                                isPrimary={getActiveStackPreset() === preset.value}
                                isSecondary={getActiveStackPreset() !== preset.value}
                                onClick={() => applyStackPreset(preset.value)}
                            >
                                {preset.label}
                            </Button>
                        ))}
                    </ButtonGroup>
                    
                    {value.stackVertically && (
                        <ToggleControl
                            label={__('Reverse order when stacked', 'jankx')}
                            checked={value.reverseStackOrder || false}
                            onChange={(checked) => onChange({ ...value, reverseStackOrder: checked })}
                            className="jankx-reverse-stack"
                        />
                    )}
                </PanelRow>
            )}

            <Divider className="jankx-divider" />

            {/* Per-device settings */}
            <div className="jankx-device-settings">
                <label className="jankx-control-label">
                    {DEVICES.find(d => d.name === activeDevice).label} {__('Settings', 'jankx')}
                </label>

                <VStack spacing={3}>
                    {/* Column span */}
                    {allowColumns && (
                        <div className="jankx-setting-row">
                            <label className="jankx-setting-label">
                                <Icon icon={layout} size={16} />
                                {__('Columns', 'jankx')}
                            </label>
                            <ButtonGroup className="jankx-col-span">
                                {[1, 2, 3, 4, 6, 12].map((cols) => (
                                    <Button
                                        key={cols}
                                        isPrimary={getDeviceValue(activeDevice, 'colSpan', 12) === cols}
                                        isSecondary={getDeviceValue(activeDevice, 'colSpan', 12) !== cols}
                                        onClick={() => updateDeviceValue(activeDevice, 'colSpan', cols)}
                                        className="jankx-col-btn"
                                    >
                                        {cols === 12 ? __('Full', 'jankx') : `${12/cols}/${cols}`}
                                    </Button>
                                ))}
                            </ButtonGroup>
                        </div>
                    )}

                    {/* Order */}
                    {allowOrder && (
                        <div className="jankx-setting-row">
                            <label className="jankx-setting-label">
                                <Icon icon={orderIcon} size={16} />
                                {__('Order', 'jankx')}
                            </label>
                            <NumberControl
                                value={getDeviceValue(activeDevice, 'order', 0)}
                                onChange={(val) => updateDeviceValue(activeDevice, 'order', parseInt(val) || 0)}
                                min={-10}
                                max={10}
                                step={1}
                                className="jankx-order-input"
                            />
                        </div>
                    )}

                    {/* Flex direction */}
                    {allowFlex && (
                        <div className="jankx-setting-row">
                            <label className="jankx-setting-label">
                                {__('Direction', 'jankx')}
                            </label>
                            <ButtonGroup className="jankx-flex-direction">
                                {FLEX_DIRECTIONS.map((dir) => (
                                    <Tooltip key={dir.value} text={dir.label}>
                                        <Button
                                            icon={dir.icon}
                                            isPrimary={getDeviceValue(activeDevice, 'flexDirection', 'row') === dir.value}
                                            isSecondary={getDeviceValue(activeDevice, 'flexDirection', 'row') !== dir.value}
                                            onClick={() => updateDeviceValue(activeDevice, 'flexDirection', dir.value)}
                                            className="jankx-direction-btn"
                                        />
                                    </Tooltip>
                                ))}
                            </ButtonGroup>
                        </div>
                    )}

                    {/* Text alignment */}
                    {allowTextAlign && (
                        <div className="jankx-setting-row">
                            <label className="jankx-setting-label">
                                {__('Text Align', 'jankx')}
                            </label>
                            <ButtonGroup className="jankx-text-align">
                                {TEXT_ALIGNMENTS.map((align) => (
                                    <Tooltip key={align.value} text={align.label}>
                                        <Button
                                            icon={align.icon}
                                            isPrimary={getDeviceValue(activeDevice, 'textAlign', 'left') === align.value}
                                            isSecondary={getDeviceValue(activeDevice, 'textAlign', 'left') !== align.value}
                                            onClick={() => updateDeviceValue(activeDevice, 'textAlign', align.value)}
                                            className="jankx-align-btn"
                                        />
                                    </Tooltip>
                                ))}
                            </ButtonGroup>
                        </div>
                    )}

                    {/* Alignment and Justify (Advanced) */}
                    {showAdvanced && allowFlex && (
                        <>
                            <div className="jankx-setting-row">
                                <label className="jankx-setting-label">
                                    {__('Align Items', 'jankx')}
                                </label>
                                <SelectControl
                                    value={getDeviceValue(activeDevice, 'alignItems', 'stretch')}
                                    options={ALIGN_ITEMS}
                                    onChange={(val) => updateDeviceValue(activeDevice, 'alignItems', val)}
                                    className="jankx-align-select"
                                />
                            </div>

                            <div className="jankx-setting-row">
                                <label className="jankx-setting-label">
                                    {__('Justify Content', 'jankx')}
                                </label>
                                <SelectControl
                                    value={getDeviceValue(activeDevice, 'justifyContent', 'flex-start')}
                                    options={JUSTIFY_CONTENT}
                                    onChange={(val) => updateDeviceValue(activeDevice, 'justifyContent', val)}
                                    className="jankx-justify-select"
                                />
                            </div>
                        </>
                    )}
                </VStack>

                {/* Advanced toggle */}
                <Button
                    isLink
                    onClick={() => setShowAdvanced(!showAdvanced)}
                    className="jankx-advanced-toggle"
                >
                    {showAdvanced ? __('Hide advanced', 'jankx') : __('Show advanced', 'jankx')}
                </Button>
            </div>

            {/* Preview hint */}
            <div className="jankx-responsive-hint">
                <p>
                    {__('Tip: Click device icons above to preview how your content looks on different screen sizes.', 'jankx')}
                </p>
            </div>
        </BaseControl>
    );
};

export default ResponsiveControl;
