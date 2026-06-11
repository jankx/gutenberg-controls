import { Button, ButtonGroup, Tooltip, BaseControl } from '@wordpress/components';
import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { desktop, tablet, mobile } from '@wordpress/icons';

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

    if (!responsive) {
        return children({ value, onChange });
    }

    const currentValue = value[activeDevice] !== undefined ? value[activeDevice] : {};
    const childOnChange = useCallback((newVal) => {
        onChange({
            ...value,
            [activeDevice]: newVal,
        });
    }, [value, onChange, activeDevice]);

    return (
        <div className="jankx-responsive-wrapper">
            {label && (
                <BaseControl.VisualLabel>
                    {label}
                </BaseControl.VisualLabel>
            )}
            <ButtonGroup className="jankx-responsive-devices">
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
            </ButtonGroup>
            <div className="jankx-responsive-content">
                {children({ value: currentValue, onChange: childOnChange })}
            </div>
        </div>
    );
}
