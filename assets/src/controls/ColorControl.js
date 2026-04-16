/**
 * Color Control Component
 *
 * High-UX color picker with:
 * - Solid color (hex, alpha)
 * - Gradient builder (linear/radial)
 * - Duotone filter
 * - Theme color integration
 * - Live preview
 */

import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
    BaseControl,
    Button,
    ButtonGroup,
    ColorPicker,
    RangeControl,
    __experimentalHStack as HStack,
    __experimentalVStack as VStack,
    __experimentalSpacer as Spacer,
    __experimentalUnitControl as UnitControl,
    Dropdown,
} from '@wordpress/components';
import {
    color as colorIcon,
    gradient as gradientIcon,
    filter as filterIcon,
    check,
} from '@wordpress/icons';

const COLOR_TYPE_SOLID = 'solid';
const COLOR_TYPE_GRADIENT = 'gradient';
const COLOR_TYPE_DUOTONE = 'duotone';

const PRESET_COLORS = [
    { color: '#ff5722', slug: 'primary' },
    { color: '#009688', slug: 'secondary' },
    { color: '#333333', slug: 'dark' },
    { color: '#ffffff', slug: 'light' },
    { color: '#f44336', slug: 'red' },
    { color: '#2196f3', slug: 'blue' },
    { color: '#4caf50', slug: 'green' },
    { color: '#ff9800', slug: 'orange' },
    { color: '#9c27b0', slug: 'purple' },
    { color: '#00bcd4', slug: 'cyan' },
];

const THEME_COLORS = [
    { slug: 'primary', name: __('Primary', 'jankx') },
    { slug: 'secondary', name: __('Secondary', 'jankx') },
    { slug: 'dark', name: __('Dark', 'jankx') },
    { slug: 'light', name: __('Light', 'jankx') },
];

export const ColorControl = ({
    label,
    value = {},
    onChange,
    allowSolid = true,
    allowGradient = true,
    allowDuotone = true,
    allowAlpha = true,
    allowTheme = true,
    presetColors = PRESET_COLORS,
}) => {
    const [activeTab, setActiveTab] = useState(value.colorType || COLOR_TYPE_SOLID);

    const colorType = value.colorType || COLOR_TYPE_SOLID;
    const solidColor = value.solidColor || '';
    const solidOpacity = value.solidOpacity ?? 100;
    const gradientType = value.gradientType || 'linear';
    const gradientAngle = value.gradientAngle || 90;
    const gradientColors = value.gradientColors || [
        { color: '#ff5722', position: 0 },
        { color: '#009688', position: 100 },
    ];
    const useThemeColor = value.useThemeColor || false;
    const themeColorKey = value.themeColorKey || 'primary';

    const updateValue = useCallback(
        (newValues) => {
            onChange({
                ...value,
                ...newValues,
            });
        },
        [value, onChange]
    );

    const handleTabChange = (tab) => {
        setActiveTab(tab);
        updateValue({ colorType: tab });
    };

    const handleColorChange = (newColor) => {
        updateValue({
            colorType: COLOR_TYPE_SOLID,
            solidColor: newColor,
            useThemeColor: false,
        });
    };

    const handleOpacityChange = (newOpacity) => {
        updateValue({ solidOpacity: Math.max(0, Math.min(100, newOpacity)) });
    };

    const handleThemeColorChange = (slug) => {
        updateValue({
            colorType: COLOR_TYPE_SOLID,
            useThemeColor: true,
            themeColorKey: slug,
        });
    };

    const handleGradientTypeChange = (type) => {
        updateValue({ gradientType: type });
    };

    const handleGradientAngleChange = (angle) => {
        updateValue({ gradientAngle: angle });
    };

    const handleGradientColorChange = (index, newColor) => {
        const newColors = [...gradientColors];
        newColors[index] = { ...newColors[index], color: newColor };
        updateValue({ gradientColors: newColors });
    };

    const handleGradientPositionChange = (index, position) => {
        const newColors = [...gradientColors];
        newColors[index] = { ...newColors[index], position };
        updateValue({ gradientColors: newColors });
    };

    const addGradientStop = () => {
        const newColors = [...gradientColors];
        const lastPosition = newColors[newColors.length - 1]?.position || 0;
        newColors.push({
            color: '#666666',
            position: Math.min(100, lastPosition + 25),
        });
        updateValue({ gradientColors: newColors });
    };

    const removeGradientStop = (index) => {
        if (gradientColors.length <= 2) return; // Minimum 2 stops
        const newColors = gradientColors.filter((_, i) => i !== index);
        updateValue({ gradientColors: newColors });
    };

    // Generate preview style
    const getPreviewStyle = () => {
        switch (colorType) {
            case COLOR_TYPE_SOLID:
                if (useThemeColor) {
                    return { background: `var(--jankx-${themeColorKey}-color)` };
                }
                if (allowAlpha && solidOpacity < 100) {
                    const alpha = solidOpacity / 100;
                    return { background: hexToRgba(solidColor, alpha) };
                }
                return { background: solidColor };

            case COLOR_TYPE_GRADIENT:
                const stops = gradientColors
                    .map((stop) => `${stop.color} ${stop.position}%`)
                    .join(', ');
                if (gradientType === 'linear') {
                    return {
                        background: `linear-gradient(${gradientAngle}deg, ${stops})`,
                    };
                }
                return { background: `radial-gradient(circle, ${stops})` };

            case COLOR_TYPE_DUOTONE:
                return {
                    background: '#333',
                    filter: 'grayscale(100%) sepia(100%) hue-rotate(180deg)',
                };

            default:
                return {};
        }
    };

    // Helper: hex to rgba
    const hexToRgba = (hex, alpha) => {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    };

    return (
        <BaseControl label={label} className="jankx-color-control">
            {/* Preview Box */}
            <div className="jankx-color-preview" style={getPreviewStyle()}>
                <span className="jankx-color-preview-label">
                    {colorType === COLOR_TYPE_SOLID && useThemeColor &&
                        __('Theme Color', 'jankx')}
                    {colorType === COLOR_TYPE_SOLID && !useThemeColor &&
                        solidColor}
                    {colorType === COLOR_TYPE_GRADIENT && __('Gradient', 'jankx')}
                    {colorType === COLOR_TYPE_DUOTONE && __('Duotone', 'jankx')}
                </span>
            </div>

            {/* Type Tabs */}
            {(allowSolid || allowGradient || allowDuotone) && (
                <ButtonGroup className="jankx-color-tabs">
                    {allowSolid && (
                        <Button
                            variant={colorType === COLOR_TYPE_SOLID ? 'primary' : 'secondary'}
                            icon={colorIcon}
                            onClick={() => handleTabChange(COLOR_TYPE_SOLID)}
                            size="small"
                        >
                            {__('Solid', 'jankx')}
                        </Button>
                    )}
                    {allowGradient && (
                        <Button
                            variant={colorType === COLOR_TYPE_GRADIENT ? 'primary' : 'secondary'}
                            icon={gradientIcon}
                            onClick={() => handleTabChange(COLOR_TYPE_GRADIENT)}
                            size="small"
                        >
                            {__('Gradient', 'jankx')}
                        </Button>
                    )}
                    {allowDuotone && (
                        <Button
                            variant={colorType === COLOR_TYPE_DUOTONE ? 'primary' : 'secondary'}
                            icon={filterIcon}
                            onClick={() => handleTabChange(COLOR_TYPE_DUOTONE)}
                            size="small"
                        >
                            {__('Duotone', 'jankx')}
                        </Button>
                    )}
                </ButtonGroup>
            )}

            {/* Solid Color Panel */}
            {colorType === COLOR_TYPE_SOLID && (
                <VStack spacing={3} className="jankx-color-panel">
                    {/* Theme Colors */}
                    {allowTheme && (
                        <>
                            <div className="jankx-color-section-title">
                                {__('Theme Colors', 'jankx')}
                            </div>
                            <div className="jankx-theme-colors">
                                {THEME_COLORS.map((themeColor) => (
                                    <Button
                                        key={themeColor.slug}
                                        className={`jankx-theme-color-btn ${
                                            useThemeColor && themeColorKey === themeColor.slug
                                                ? 'is-active'
                                                : ''
                                        }`}
                                        onClick={() => handleThemeColorChange(themeColor.slug)}
                                        style={{
                                            background: `var(--jankx-${themeColor.slug}-color)`,
                                        }}
                                        label={themeColor.name}
                                    >
                                        {useThemeColor && themeColorKey === themeColor.slug && (
                                            <span className="jankx-color-check">✓</span>
                                        )}
                                    </Button>
                                ))}
                            </div>
                        </>
                    )}

                    {/* Custom Color */}
                    <div className="jankx-color-section-title">
                        {__('Custom Color', 'jankx')}
                    </div>
                    <div className="jankx-color-picker-wrapper">
                        <ColorPicker
                            color={solidColor}
                            onChange={handleColorChange}
                            enableAlpha={allowAlpha}
                        />
                    </div>

                    {/* Opacity Slider */}
                    {allowAlpha && (
                        <RangeControl
                            label={__('Opacity', 'jankx')}
                            value={solidOpacity}
                            onChange={handleOpacityChange}
                            min={0}
                            max={100}
                            suffix="%"
                        />
                    )}

                    {/* Preset Colors */}
                    <div className="jankx-color-section-title">
                        {__('Presets', 'jankx')}
                    </div>
                    <div className="jankx-preset-colors">
                        {presetColors.map((preset) => (
                            <Button
                                key={preset.slug}
                                className="jankx-preset-color-btn"
                                onClick={() => handleColorChange(preset.color)}
                                style={{ background: preset.color }}
                                label={preset.slug}
                            />
                        ))}
                    </div>
                </VStack>
            )}

            {/* Gradient Panel */}
            {colorType === COLOR_TYPE_GRADIENT && (
                <VStack spacing={3} className="jankx-color-panel">
                    {/* Gradient Type */}
                    <ButtonGroup className="jankx-gradient-type">
                        <Button
                            variant={gradientType === 'linear' ? 'primary' : 'secondary'}
                            onClick={() => handleGradientTypeChange('linear')}
                            size="small"
                        >
                            {__('Linear', 'jankx')}
                        </Button>
                        <Button
                            variant={gradientType === 'radial' ? 'primary' : 'secondary'}
                            onClick={() => handleGradientTypeChange('radial')}
                            size="small"
                        >
                            {__('Radial', 'jankx')}
                        </Button>
                    </ButtonGroup>

                    {/* Angle (linear only) */}
                    {gradientType === 'linear' && (
                        <RangeControl
                            label={__('Angle', 'jankx')}
                            value={gradientAngle}
                            onChange={handleGradientAngleChange}
                            min={0}
                            max={360}
                            suffix="°"
                        />
                    )}

                    {/* Gradient Stops */}
                    <div className="jankx-color-section-title">
                        {__('Gradient Stops', 'jankx')}
                    </div>
                    <VStack spacing={2}>
                        {gradientColors.map((stop, index) => (
                            <HStack key={index} alignment="center" spacing={2}>
                                <Dropdown
                                    renderToggle={({ isOpen, onToggle }) => (
                                        <Button
                                            onClick={onToggle}
                                            aria-expanded={isOpen}
                                            style={{
                                                background: stop.color,
                                                width: 40,
                                                height: 40,
                                                borderRadius: 4,
                                            }}
                                        />
                                    )}
                                    renderContent={() => (
                                        <div className="jankx-color-popover">
                                            <ColorPicker
                                                color={stop.color}
                                                onChange={(color) =>
                                                    handleGradientColorChange(index, color)
                                                }
                                            />
                                        </div>
                                    )}
                                />
                                <RangeControl
                                    value={stop.position}
                                    onChange={(pos) =>
                                        handleGradientPositionChange(index, pos)
                                    }
                                    min={0}
                                    max={100}
                                    suffix="%"
                                    style={{ flex: 1 }}
                                />
                                {gradientColors.length > 2 && (
                                    <Button
                                        icon="trash"
                                        onClick={() => removeGradientStop(index)}
                                        isDestructive
                                        size="small"
                                    />
                                )}
                            </HStack>
                        ))}
                    </VStack>

                    <Button
                        variant="secondary"
                        onClick={addGradientStop}
                        disabled={gradientColors.length >= 5}
                        size="small"
                    >
                        {__('Add Stop', 'jankx')}
                    </Button>
                </VStack>
            )}

            {/* Duotone Panel */}
            {colorType === COLOR_TYPE_DUOTONE && (
                <VStack spacing={3} className="jankx-color-panel">
                    <div className="jankx-color-section-title">
                        {__('Duotone Filter', 'jankx')}
                    </div>
                    <p className="jankx-color-hint">
                        {__('Apply two-color filter to images. Requires WordPress 6.4+', 'jankx')}
                    </p>
                </VStack>
            )}
        </BaseControl>
    );
};

export default ColorControl;
