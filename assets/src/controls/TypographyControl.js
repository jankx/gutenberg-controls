/**
 * Typography Control Component
 *
 * High-UX typography picker with:
 * - Font family selection (Google Fonts, system fonts)
 * - Fluid/responsive font sizes
 * - Font weight and style
 * - Line height and letter spacing
 * - Text effects (transform, decoration, shadow)
 * - Live preview
 */

import { useState, useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
    BaseControl,
    Button,
    ButtonGroup,
    ToggleControl,
    RangeControl,
    SelectControl,
    __experimentalHStack as HStack,
    __experimentalVStack as VStack,
    __experimentalUnitControl as UnitControl,
    __experimentalText as Text,
    TabPanel,
} from '@wordpress/components';

// Font family options
const FONT_OPTIONS = [
    { value: '', label: __('Theme Default', 'jankx') },
    { value: 'system-ui', label: __('System UI', 'jankx') },
    { value: 'serif', label: __('Serif', 'jankx') },
    { value: 'monospace', label: __('Monospace', 'jankx') },
    { value: 'Inter', label: __('Inter', 'jankx') },
    { value: 'Roboto', label: __('Roboto', 'jankx') },
    { value: 'Open Sans', label: __('Open Sans', 'jankx') },
    { value: 'Poppins', label: __('Poppins', 'jankx') },
    { value: 'Montserrat', label: __('Montserrat', 'jankx') },
    { value: 'Playfair Display', label: __('Playfair Display', 'jankx') },
    { value: 'Lato', label: __('Lato', 'jankx') },
    { value: 'Source Sans Pro', label: __('Source Sans Pro', 'jankx') },
];

// Font weight options
const FONT_WEIGHTS = [
    { value: '100', label: __('Thin (100)', 'jankx') },
    { value: '200', label: __('Extra Light (200)', 'jankx') },
    { value: '300', label: __('Light (300)', 'jankx') },
    { value: '400', label: __('Regular (400)', 'jankx') },
    { value: '500', label: __('Medium (500)', 'jankx') },
    { value: '600', label: __('Semi Bold (600)', 'jankx') },
    { value: '700', label: __('Bold (700)', 'jankx') },
    { value: '800', label: __('Extra Bold (800)', 'jankx') },
    { value: '900', label: __('Black (900)', 'jankx') },
];

// Text transform options
const TEXT_TRANSFORMS = [
    { value: 'none', label: __('None', 'jankx') },
    { value: 'capitalize', label: __('Capitalize', 'jankx') },
    { value: 'uppercase', label: __('Uppercase', 'jankx') },
    { value: 'lowercase', label: __('Lowercase', 'jankx') },
];

// Text decoration options
const TEXT_DECORATIONS = [
    { value: 'none', label: __('None', 'jankx') },
    { value: 'underline', label: __('Underline', 'jankx') },
    { value: 'line-through', label: __('Line Through', 'jankx') },
    { value: 'overline', label: __('Overline', 'jankx') },
];

// Line height presets
const LINE_HEIGHT_PRESETS = [
    { value: 1, label: __('None (1)', 'jankx') },
    { value: 1.2, label: __('Tight (1.2)', 'jankx') },
    { value: 1.4, label: __('Snug (1.4)', 'jankx') },
    { value: 1.6, label: __('Normal (1.6)', 'jankx') },
    { value: 1.8, label: __('Relaxed (1.8)', 'jankx') },
    { value: 2, label: __('Loose (2)', 'jankx') },
];

// Letter spacing presets
const LETTER_SPACING_PRESETS = [
    { value: '-0.05em', label: __('Tighter', 'jankx') },
    { value: '-0.025em', label: __('Tight', 'jankx') },
    { value: '0', label: __('Normal', 'jankx') },
    { value: '0.025em', label: __('Wide', 'jankx') },
    { value: '0.05em', label: __('Wider', 'jankx') },
    { value: '0.1em', label: __('Widest', 'jankx') },
];

// Font size presets
const FONT_SIZE_PRESETS = [
    { value: '12px', label: __('XS', 'jankx') },
    { value: '14px', label: __('Small', 'jankx') },
    { value: '16px', label: __('Base', 'jankx') },
    { value: '18px', label: __('LG', 'jankx') },
    { value: '20px', label: __('XL', 'jankx') },
    { value: '24px', label: __('2XL', 'jankx') },
    { value: '30px', label: __('3XL', 'jankx') },
    { value: '36px', label: __('4XL', 'jankx') },
    { value: '48px', label: __('5XL', 'jankx') },
    { value: '60px', label: __('6XL', 'jankx') },
    { value: '72px', label: __('7XL', 'jankx') },
];

export const TypographyControl = ({
    label,
    value = {},
    onChange,
    allowFluid = true,
    allowResponsive = true,
}) => {
    const [activeTab, setActiveTab] = useState('font');

    const fontFamily = value.fontFamily || '';
    const useThemeFont = value.useThemeFont ?? true;
    const fontSize = value.fontSize || '';
    const fontSizeTablet = value.fontSizeTablet || '';
    const fontSizeMobile = value.fontSizeMobile || '';
    const fluidTypography = value.fluidTypography || false;
    const fluidMin = value.fluidMin || '16px';
    const fluidMax = value.fluidMax || '24px';
    const fontWeight = value.fontWeight || '400';
    const fontStyle = value.fontStyle || 'normal';
    const lineHeight = value.lineHeight ?? 1.6;
    const letterSpacing = value.letterSpacing || '0';
    const textTransform = value.textTransform || 'none';
    const textDecoration = value.textDecoration || 'none';
    const textAlign = value.textAlign || 'left';
    const textAlignTablet = value.textAlignTablet || '';
    const textAlignMobile = value.textAlignMobile || '';

    const updateValue = useCallback(
        (newValues) => {
            onChange({
                ...value,
                ...newValues,
            });
        },
        [value, onChange]
    );

    // Generate preview style
    const getPreviewStyle = () => {
        const font = useThemeFont
            ? 'var(--jankx-font-family)'
            : fontFamily
            ? `"${fontFamily}", sans-serif`
            : 'inherit';

        let size = fontSize;
        if (fluidTypography) {
            size = `clamp(${fluidMin}, 2vw + 1rem, ${fluidMax})`;
        }

        return {
            fontFamily: font,
            fontSize: size || '16px',
            fontWeight,
            fontStyle,
            lineHeight,
            letterSpacing,
            textTransform,
            textDecoration,
            textAlign,
            padding: '16px',
            background: '#f8f9fa',
            borderRadius: '4px',
            border: '1px solid #e0e0e0',
        };
    };

    const tabs = [
        { name: 'font', title: __('Font', 'jankx') },
        { name: 'size', title: __('Size', 'jankx') },
        { name: 'style', title: __('Style', 'jankx') },
    ];

    if (allowResponsive) {
        tabs.push({ name: 'responsive', title: __('Responsive', 'jankx') });
    }

    return (
        <BaseControl label={label} className="jankx-typography-control">
            {/* Live Preview */}
            <div className="jankx-typo-preview" style={getPreviewStyle()}>
                <Text as="p" style={{ margin: 0 }}>
                    {__('The quick brown fox jumps over the lazy dog.', 'jankx')}
                </Text>
                <Text as="p" style={{ margin: '8px 0 0', fontSize: '0.8em', opacity: 0.6 }}>
                    {__('ABCDEFGHIJKLMNOPQRSTUVWXYZ 0123456789', 'jankx')}
                </Text>
            </div>

            <TabPanel
                className="jankx-typo-tabs"
                activeClass="is-active"
                tabs={tabs}
                onSelect={setActiveTab}
            >
                {(tab) => (
                    <>
                        {/* Font Tab */}
                        {tab.name === 'font' && (
                            <VStack spacing={3} className="jankx-typo-panel">
                                {/* Theme Font Toggle */}
                                <ToggleControl
                                    label={__('Use Theme Font', 'jankx')}
                                    checked={useThemeFont}
                                    onChange={(checked) =>
                                        updateValue({
                                            useThemeFont: checked,
                                            fontFamily: checked ? '' : 'Inter',
                                        })
                                    }
                                />

                                {/* Font Family */}
                                {!useThemeFont && (
                                    <SelectControl
                                        label={__('Font Family', 'jankx')}
                                        value={fontFamily}
                                        options={FONT_OPTIONS}
                                        onChange={(newFont) =>
                                            updateValue({ fontFamily: newFont })
                                        }
                                        __next40pxDefaultSize
                                    />
                                )}

                                {/* Font Weight */}
                                <SelectControl
                                    label={__('Font Weight', 'jankx')}
                                    value={fontWeight}
                                    options={FONT_WEIGHTS}
                                    onChange={(newWeight) =>
                                        updateValue({ fontWeight: newWeight })
                                    }
                                    __next40pxDefaultSize
                                />

                                {/* Font Style */}
                                <ButtonGroup className="jankx-typo-style-toggle">
                                    <Button
                                        variant={fontStyle === 'normal' ? 'primary' : 'secondary'}
                                        onClick={() => updateValue({ fontStyle: 'normal' })}
                                        size="small"
                                    >
                                        {__('Normal', 'jankx')}
                                    </Button>
                                    <Button
                                        variant={fontStyle === 'italic' ? 'primary' : 'secondary'}
                                        onClick={() => updateValue({ fontStyle: 'italic' })}
                                        size="small"
                                    >
                                        {__('Italic', 'jankx')}
                                    </Button>
                                </ButtonGroup>
                            </VStack>
                        )}

                        {/* Size Tab */}
                        {tab.name === 'size' && (
                            <VStack spacing={3} className="jankx-typo-panel">
                                {/* Fluid Typography Toggle */}
                                {allowFluid && (
                                    <ToggleControl
                                        label={__('Fluid Typography', 'jankx')}
                                        checked={fluidTypography}
                                        onChange={(checked) =>
                                            updateValue({ fluidTypography: checked })
                                        }
                                        help={
                                            fluidTypography
                                                ? __('Automatically scales between min and max', 'jankx')
                                                : __('Fixed font size on all devices', 'jankx')
                                        }
                                    />
                                )}

                                {fluidTypography ? (
                                    <>
                                        <UnitControl
                                            label={__('Minimum Size', 'jankx')}
                                            value={fluidMin}
                                            onChange={(newVal) =>
                                                updateValue({ fluidMin: newVal })
                                            }
                                            units={[{ value: 'px', label: 'px', default: 16 }]}
                                            __next40pxDefaultSize
                                        />
                                        <UnitControl
                                            label={__('Maximum Size', 'jankx')}
                                            value={fluidMax}
                                            onChange={(newVal) =>
                                                updateValue({ fluidMax: newVal })
                                            }
                                            units={[{ value: 'px', label: 'px', default: 24 }]}
                                            __next40pxDefaultSize
                                        />
                                    </>
                                ) : (
                                    <>
                                        <SelectControl
                                            label={__('Font Size', 'jankx')}
                                            value={fontSize}
                                            options={[
                                                { value: '', label: __('Custom', 'jankx') },
                                                ...FONT_SIZE_PRESETS,
                                            ]}
                                            onChange={(newSize) =>
                                                updateValue({ fontSize: newSize })
                                            }
                                            __next40pxDefaultSize
                                        />
                                        <UnitControl
                                            label={__('Custom Size', 'jankx')}
                                            value={fontSize}
                                            onChange={(newVal) =>
                                                updateValue({ fontSize: newVal })
                                            }
                                            units={[
                                                { value: 'px', label: 'px', default: 16 },
                                                { value: 'em', label: 'em', default: 1 },
                                                { value: 'rem', label: 'rem', default: 1 },
                                            ]}
                                            __next40pxDefaultSize
                                        />
                                    </>
                                )}

                                {/* Line Height */}
                                <SelectControl
                                    label={__('Line Height', 'jankx')}
                                    value={lineHeight}
                                    options={LINE_HEIGHT_PRESETS}
                                    onChange={(newVal) =>
                                        updateValue({ lineHeight: parseFloat(newVal) })
                                    }
                                    __next40pxDefaultSize
                                />

                                {/* Letter Spacing */}
                                <SelectControl
                                    label={__('Letter Spacing', 'jankx')}
                                    value={letterSpacing}
                                    options={LETTER_SPACING_PRESETS}
                                    onChange={(newVal) =>
                                        updateValue({ letterSpacing: newVal })
                                    }
                                    __next40pxDefaultSize
                                />
                            </VStack>
                        )}

                        {/* Style Tab */}
                        {tab.name === 'style' && (
                            <VStack spacing={3} className="jankx-typo-panel">
                                {/* Text Transform */}
                                <SelectControl
                                    label={__('Text Transform', 'jankx')}
                                    value={textTransform}
                                    options={TEXT_TRANSFORMS}
                                    onChange={(newVal) =>
                                        updateValue({ textTransform: newVal })
                                    }
                                    __next40pxDefaultSize
                                />

                                {/* Text Decoration */}
                                <SelectControl
                                    label={__('Text Decoration', 'jankx')}
                                    value={textDecoration}
                                    options={TEXT_DECORATIONS}
                                    onChange={(newVal) =>
                                        updateValue({ textDecoration: newVal })
                                    }
                                    __next40pxDefaultSize
                                />

                                {/* Text Shadow */}
                                <div className="jankx-typo-section-title">
                                    {__('Text Shadow', 'jankx')}
                                </div>
                                <HStack spacing={2}>
                                    <UnitControl
                                        label={__('X', 'jankx')}
                                        value={value.textShadow?.x || 0}
                                        onChange={(newVal) =>
                                            updateValue({
                                                textShadow: {
                                                    ...(value.textShadow || {}),
                                                    x: parseFloat(newVal) || 0,
                                                },
                                            })
                                        }
                                        units={[{ value: 'px', label: 'px', default: 0 }]}
                                    />
                                    <UnitControl
                                        label={__('Y', 'jankx')}
                                        value={value.textShadow?.y || 0}
                                        onChange={(newVal) =>
                                            updateValue({
                                                textShadow: {
                                                    ...(value.textShadow || {}),
                                                    y: parseFloat(newVal) || 0,
                                                },
                                            })
                                        }
                                        units={[{ value: 'px', label: 'px', default: 0 }]}
                                    />
                                    <UnitControl
                                        label={__('Blur', 'jankx')}
                                        value={value.textShadow?.blur || 0}
                                        onChange={(newVal) =>
                                            updateValue({
                                                textShadow: {
                                                    ...(value.textShadow || {}),
                                                    blur: parseFloat(newVal) || 0,
                                                },
                                            })
                                        }
                                        units={[{ value: 'px', label: 'px', default: 0 }]}
                                    />
                                </HStack>
                            </VStack>
                        )}

                        {/* Responsive Tab */}
                        {tab.name === 'responsive' && allowResponsive && (
                            <VStack spacing={3} className="jankx-typo-panel">
                                <div className="jankx-typo-section-title">
                                    {__('Text Align', 'jankx')}
                                </div>

                                {/* Desktop */}
                                <div className="jankx-typo-device-row">
                                    <span className="jankx-typo-device-label">
                                        {__('Desktop', 'jankx')}
                                    </span>
                                    <ButtonGroup>
                                        {['left', 'center', 'right', 'justify'].map((align) => (
                                            <Button
                                                key={align}
                                                variant={
                                                    textAlign === align ? 'primary' : 'secondary'
                                                }
                                                onClick={() =>
                                                    updateValue({ textAlign: align })
                                                }
                                                size="small"
                                                icon={`align-${align}`}
                                                label={align}
                                            />
                                        ))}
                                    </ButtonGroup>
                                </div>

                                {/* Tablet */}
                                <div className="jankx-typo-device-row">
                                    <span className="jankx-typo-device-label">
                                        {__('Tablet', 'jankx')}
                                    </span>
                                    <ButtonGroup>
                                        {['left', 'center', 'right', 'justify'].map((align) => (
                                            <Button
                                                key={align}
                                                variant={
                                                    textAlignTablet === align
                                                        ? 'primary'
                                                        : 'secondary'
                                                }
                                                onClick={() =>
                                                    updateValue({
                                                        textAlignTablet:
                                                            textAlignTablet === align
                                                                ? ''
                                                                : align,
                                                    })
                                                }
                                                size="small"
                                                icon={`align-${align}`}
                                                label={align}
                                            />
                                        ))}
                                    </ButtonGroup>
                                </div>

                                {/* Mobile */}
                                <div className="jankx-typo-device-row">
                                    <span className="jankx-typo-device-label">
                                        {__('Mobile', 'jankx')}
                                    </span>
                                    <ButtonGroup>
                                        {['left', 'center', 'right', 'justify'].map((align) => (
                                            <Button
                                                key={align}
                                                variant={
                                                    textAlignMobile === align
                                                        ? 'primary'
                                                        : 'secondary'
                                                }
                                                onClick={() =>
                                                    updateValue({
                                                        textAlignMobile:
                                                            textAlignMobile === align
                                                                ? ''
                                                                : align,
                                                    })
                                                }
                                                size="small"
                                                icon={`align-${align}`}
                                                label={align}
                                            />
                                        ))}
                                    </ButtonGroup>
                                </div>

                                {/* Responsive Font Size */}
                                {!fluidTypography && (
                                    <>
                                        <div className="jankx-typo-section-title">
                                            {__('Responsive Font Size', 'jankx')}
                                        </div>
                                        <UnitControl
                                            label={__('Tablet Size', 'jankx')}
                                            value={fontSizeTablet}
                                            onChange={(newVal) =>
                                                updateValue({ fontSizeTablet: newVal })
                                            }
                                            units={[
                                                { value: 'px', label: 'px', default: 16 },
                                                { value: 'em', label: 'em', default: 1 },
                                                { value: 'rem', label: 'rem', default: 1 },
                                            ]}
                                            __next40pxDefaultSize
                                        />
                                        <UnitControl
                                            label={__('Mobile Size', 'jankx')}
                                            value={fontSizeMobile}
                                            onChange={(newVal) =>
                                                updateValue({ fontSizeMobile: newVal })
                                            }
                                            units={[
                                                { value: 'px', label: 'px', default: 16 },
                                                { value: 'em', label: 'em', default: 1 },
                                                { value: 'rem', label: 'rem', default: 1 },
                                            ]}
                                            __next40pxDefaultSize
                                        />
                                    </>
                                )}
                            </VStack>
                        )}
                    </>
                )}
            </TabPanel>
        </BaseControl>
    );
};

export default TypographyControl;
