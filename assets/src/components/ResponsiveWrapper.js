import { Button, Tooltip, BaseControl } from '@wordpress/components';
import { useState, useCallback, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { desktop, tablet, mobile, link, linkOff } from '@wordpress/icons';

const DEFAULT_DEVICES = [
    { name: 'ultrawide', icon: desktop, label: __('Ultrawide', 'jankx') },
    { name: 'desktop', icon: desktop, label: __('Desktop', 'jankx') },
    { name: 'tablet', icon: tablet, label: __('Tablet', 'jankx') },
    { name: 'mobile', icon: mobile, label: __('Mobile', 'jankx') },
];

export default function ResponsiveWrapper({
    children,
    value = {},
    onChange,
    responsive = false,
    label = '',
    devices = DEFAULT_DEVICES,
}) {
    const [activeDevice, setActiveDevice] = useState('desktop');
    const [isLinked, setIsLinked] = useState(true);

    if (!responsive) {
        return children({ value, onChange, isLinked, setIsLinked });
    }

    const currentValue = value[activeDevice] !== undefined ? value[activeDevice] : {};

    const childOnChange = useCallback((newVal) => {
        if (isLinked) {
            const linkedValues = {};
            devices.forEach((device) => {
                linkedValues[device.name] = newVal;
            });
            onChange({
                ...value,
                ...linkedValues,
            });
            return;
        }
        onChange({
            ...value,
            [activeDevice]: newVal,
        });
    }, [value, onChange, activeDevice, isLinked, devices]);

    return (
        <div className="jankx-responsive-wrapper">
            {label && (
                <div className="jankx-responsive-header">
                    <BaseControl.VisualLabel>
                        {label}
                    </BaseControl.VisualLabel>
                    <Tooltip text={isLinked ? __('Unlink values', 'jankx') : __('Link values', 'jankx')}>
                        <Button
                            icon={isLinked ? link : linkOff}
                            isPressed={isLinked}
                            onClick={() => setIsLinked((current) => !current)}
                            label={isLinked ? __('Unlink values', 'jankx') : __('Link values', 'jankx')}
                            size="small"
                        />
                    </Tooltip>
                </div>
            )}
            <div style={{display:'flex',gap:'2px'}} className="jankx-responsive-devices">
                {devices.map((device) => (
                    <Tooltip key={device.name} text={device.label}>
                        <Button
                            icon={device.icon}
                            variant={activeDevice === device.name ? 'primary' : 'secondary'}
                            onClick={() => setActiveDevice(device.name)}
                            size="small"
                            className="jankx-device-btn"
                        />
                    </Tooltip>
                ))}
            </div>
            <div className="jankx-responsive-content">
                {children({ value: currentValue, onChange: childOnChange, isLinked, setIsLinked })}
            </div>
        </div>
    );
}
