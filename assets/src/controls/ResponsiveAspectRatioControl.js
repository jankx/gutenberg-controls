/**
 * Responsive Aspect Ratio Control
 *
 * Per-device aspect-ratio editor for item/box layouts.
 * Stores a map of device -> ratio string (e.g. "16/9", "4/3", "1/1", "auto").
 *
 * Value shape:
 * {
 *   ultrawide: "16/9",
 *   desktop: "16/9",
 *   tablet: "4/3",
 *   mobile: "auto",
 * }
 */

import { useState, useCallback } from '@wordpress/element';
import {
    BaseControl,
    Button,
    Tooltip,
    Icon,
    TextControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
    desktop,
    tablet,
    mobile,
    link,
    linkOff,
} from '@wordpress/icons';

const DEFAULT_DEVICES = [
    { name: 'ultrawide', icon: desktop, label: __('Ultrawide', 'jankx') },
    { name: 'desktop', icon: desktop, label: __('Desktop', 'jankx') },
    { name: 'tablet', icon: tablet, label: __('Tablet', 'jankx') },
    { name: 'mobile', icon: mobile, label: __('Mobile', 'jankx') },
];

const RATIO_PRESETS = [
    { value: 'auto', label: __('Auto', 'jankx') },
    { value: '1/1', label: '1:1' },
    { value: '4/3', label: '4:3' },
    { value: '3/4', label: '3:4' },
    { value: '3/2', label: '3:2' },
    { value: '2/3', label: '2:3' },
    { value: '16/9', label: '16:9' },
    { value: '21/9', label: '21:9' },
];

const getDevices = () => {
    const bp = window.jankxBlocks?.breakpoints;
    if (bp) {
        return Object.keys(bp).map((name) => {
            const iconMap = { ultrawide: desktop, desktop, tablet, mobile };
            return {
                name,
                icon: iconMap[name] || desktop,
                label: __(name.charAt(0).toUpperCase() + name.slice(1), 'jankx'),
            };
        });
    }
    return DEFAULT_DEVICES;
};

const DEVICES = getDevices();

const ResponsiveAspectRatioControl = ({
    label = __('Aspect Ratio', 'jankx'),
    value = {},
    onChange,
    allowLink = true,
}) => {
    const [activeDevice, setActiveDevice] = useState('desktop');
    const [internalIsLinked, setInternalIsLinked] = useState(true);
    const [customValue, setCustomValue] = useState('');

    const isLinked = internalIsLinked;
    const setIsLinked = setInternalIsLinked;

    const getDeviceValue = useCallback((device) => {
        return value[device] || 'auto';
    }, [value]);

    const setDeviceValues = useCallback((devicesToSet, newValue) => {
        const next = { ...value };
        devicesToSet.forEach((device) => {
            next[device] = newValue;
        });
        onChange(next);
    }, [value, onChange]);

    const selectRatio = (ratio) => {
        setCustomValue('');
        if (isLinked) {
            setDeviceValues(DEVICES.map((d) => d.name), ratio);
        } else {
            setDeviceValues([activeDevice], ratio);
        }
    };

    const applyCustomRatio = (raw) => {
        const ratio = raw.trim().replace(/[:x×*]/g, '/');
        setCustomValue(raw);
        if (!ratio) return;
        if (isLinked) {
            setDeviceValues(DEVICES.map((d) => d.name), ratio);
        } else {
            setDeviceValues([activeDevice], ratio);
        }
    };

    return (
        <BaseControl label={label} className="jankx-responsive-ratio">
            {/* Device selector tabs */}
            <div style={{ display: 'flex', gap: '2px', marginBottom: '8px' }} className="jankx-device-tabs">
                {DEVICES.map((device) => (
                    <Tooltip key={device.name} text={device.label}>
                        <Button
                            icon={device.icon}
                            isPrimary={activeDevice === device.name}
                            isSecondary={activeDevice !== device.name}
                            onClick={() => setActiveDevice(device.name)}
                            size="small"
                        />
                    </Tooltip>
                ))}
                {allowLink && (
                    <div style={{ marginLeft: 'auto' }}>
                        <Tooltip text={isLinked ? __('Unlink device values', 'jankx') : __('Link device values', 'jankx')}>
                            <Button
                                icon={isLinked ? link : linkOff}
                                isPressed={isLinked}
                                onClick={() => setIsLinked((cur) => !cur)}
                                label={isLinked ? __('Unlink device values', 'jankx') : __('Link device values', 'jankx')}
                                size="small"
                            />
                        </Tooltip>
                    </div>
                )}
            </div>

            <div style={{ fontSize: '11px', color: '#757575', textTransform: 'uppercase', fontWeight: 500, marginBottom: '6px' }}>
                {DEVICES.find((d) => d.name === activeDevice)?.label} {__('Ratio', 'jankx')}
            </div>

            {/* Ratio preset buttons */}
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: '4px' }} className="jankx-ratio-presets">
                {RATIO_PRESETS.map((preset) => {
                    const isActive = getDeviceValue(activeDevice) === preset.value;
                    return (
                        <Button
                            key={preset.value}
                            isPrimary={isActive}
                            isSecondary={!isActive}
                            onClick={() => selectRatio(preset.value)}
                            size="small"
                            style={{ minWidth: '48px', height: '32px' }}
                        >
                            {preset.label}
                        </Button>
                    );
                })}
            </div>

            {/* Custom ratio */}
            <div style={{ marginTop: '8px' }}>
                <TextControl
                    placeholder={__('Custom', 'jankx') + ' (e.g. 16/9)'}
                    value={customValue}
                    onChange={applyCustomRatio}
                    help={__('Use format like 16/9, 4/3, 1/1 or "auto".', 'jankx')}
                    __nextHasNoMarginBottom
                />
            </div>
        </BaseControl>
    );
};

export default ResponsiveAspectRatioControl;
