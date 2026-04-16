/**
 * Live Preview Component
 *
 * Provides real-time CSS updates when inspector controls change.
 * Uses CSS custom properties for instant feedback without re-render.
 */

import { useEffect, useRef, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';

/**
 * Generate CSS custom properties from control values
 */
const generateCSSVariables = (controlValues, blockId) => {
    const variables = {};
    const prefix = `--jankx-${blockId}`;

    Object.entries(controlValues || {}).forEach(([controlName, value]) => {
        if (!value || typeof value !== 'object') return;

        // Spacing controls
        if (value.padding !== undefined) {
            variables[`${prefix}-${controlName}-padding`] = value.padding;
            variables[`${prefix}-${controlName}-padding-top`] = value.paddingTop || value.padding;
            variables[`${prefix}-${controlName}-padding-right`] = value.paddingRight || value.padding;
            variables[`${prefix}-${controlName}-padding-bottom`] = value.paddingBottom || value.padding;
            variables[`${prefix}-${controlName}-padding-left`] = value.paddingLeft || value.padding;
        }

        if (value.margin !== undefined) {
            variables[`${prefix}-${controlName}-margin`] = value.margin;
            variables[`${prefix}-${controlName}-margin-top`] = value.marginTop || value.margin;
            variables[`${prefix}-${controlName}-margin-right`] = value.marginRight || value.margin;
            variables[`${prefix}-${controlName}-margin-bottom`] = value.marginBottom || value.margin;
            variables[`${prefix}-${controlName}-margin-left`] = value.marginLeft || value.margin;
        }

        // Color controls
        if (value.solidColor) {
            variables[`${prefix}-${controlName}-color`] = value.solidColor;
        }
        if (value.gradientColors && value.gradientColors.length > 0) {
            const gradient = generateGradient(value);
            variables[`${prefix}-${controlName}-gradient`] = gradient;
        }

        // Typography
        if (value.fontSize) {
            variables[`${prefix}-${controlName}-font-size`] = value.fontSize;
        }
        if (value.fontWeight && value.fontWeight !== '400') {
            variables[`${prefix}-${controlName}-font-weight`] = value.fontWeight;
        }
        if (value.lineHeight && value.lineHeight !== 1.6) {
            variables[`${prefix}-${controlName}-line-height`] = value.lineHeight;
        }

        // Border
        if (value.borderRadius) {
            variables[`${prefix}-${controlName}-border-radius`] = value.borderRadius;
        }
        if (value.borderWidth) {
            variables[`${prefix}-${controlName}-border-width`] = value.borderWidth;
        }
        if (value.borderColor) {
            variables[`${prefix}-${controlName}-border-color`] = value.borderColor;
        }

        // Shadow
        if (value.shadowType && value.shadowType !== 'none') {
            const shadow = getShadowValue(value.shadowType, value);
            variables[`${prefix}-${controlName}-shadow`] = shadow;
        }

        // Responsive visibility
        if (value.hideOnDesktop !== undefined) {
            variables[`${prefix}-${controlName}-hide-desktop`] = value.hideOnDesktop ? 'none' : 'block';
        }
        if (value.hideOnTablet !== undefined) {
            variables[`${prefix}-${controlName}-hide-tablet`] = value.hideOnTablet ? 'none' : 'block';
        }
        if (value.hideOnMobile !== undefined) {
            variables[`${prefix}-${controlName}-hide-mobile`] = value.hideOnMobile ? 'none' : 'block';
        }
    });

    return variables;
};

/**
 * Generate gradient string
 */
const generateGradient = (value) => {
    if (!value.gradientColors || value.gradientColors.length < 2) return '';

    const type = value.gradientType || 'linear';
    const angle = value.gradientAngle || 90;
    const stops = value.gradientColors
        .map(stop => `${stop.color} ${stop.position}%`)
        .join(', ');

    return type === 'linear'
        ? `linear-gradient(${angle}deg, ${stops})`
        : `radial-gradient(circle, ${stops})`;
};

/**
 * Get shadow value from preset or custom
 */
const getShadowValue = (shadowType, value) => {
    const presets = {
        xs: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
        sm: '0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)',
        md: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
        lg: '0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
        xl: '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)',
        '2xl': '0 25px 50px -12px rgba(0, 0, 0, 0.25)',
        inner: 'inset 0 2px 4px 0 rgba(0, 0, 0, 0.06)',
        floating: '0 20px 40px -10px rgba(0, 0, 0, 0.3)',
        colored: '0 0 20px 5px rgba(99, 102, 241, 0.4)',
    };

    if (shadowType === 'custom' && value.shadowX !== undefined) {
        const x = value.shadowX || 0;
        const y = value.shadowY || 4;
        const blur = value.shadowBlur || 6;
        const spread = value.shadowSpread || 0;
        const color = value.shadowColor || 'rgba(0,0,0,0.15)';
        const inset = value.shadowInset ? 'inset ' : '';
        return `${inset}${x}px ${y}px ${blur}px ${spread}px ${color}`;
    }

    return presets[shadowType] || '';
};

/**
 * Apply CSS variables to block element
 */
const applyCSSVariables = (blockId, variables) => {
    const blockElement = document.querySelector(`[data-block="${blockId}"]`);
    if (!blockElement) return;

    Object.entries(variables).forEach(([property, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            blockElement.style.setProperty(property, value);
        } else {
            blockElement.style.removeProperty(property);
        }
    });
};

/**
 * Live Preview Hook
 * Use in block edit component for real-time updates
 */
export const useLivePreview = (clientId, jankxControls) => {
    const prevControlsRef = useRef(null);
    const isFirstRender = useRef(true);

    // Get block element
    const { getBlockElement } = useSelect((select) => {
        const store = select(blockEditorStore);
        return {
            getBlockElement: () => document.querySelector(`[data-block="${clientId}"]`),
        };
    }, [clientId]);

    useEffect(() => {
        // Skip first render to avoid applying default styles
        if (isFirstRender.current) {
            isFirstRender.current = false;
            prevControlsRef.current = JSON.stringify(jankxControls);
            return;
        }

        // Only update if controls actually changed
        const currentControls = JSON.stringify(jankxControls);
        if (currentControls === prevControlsRef.current) {
            return;
        }
        prevControlsRef.current = currentControls;

        // Generate and apply CSS variables
        const variables = generateCSSVariables(jankxControls, clientId);
        applyCSSVariables(clientId, variables);

        // Also update preview styles in iframe if FSE
        const iframe = document.querySelector('iframe[name="editor-canvas"]');
        if (iframe && iframe.contentDocument) {
            const iframeBlock = iframe.contentDocument.querySelector(`[data-block="${clientId}"]`);
            if (iframeBlock) {
                Object.entries(variables).forEach(([property, value]) => {
                    if (value !== undefined && value !== null) {
                        iframeBlock.style.setProperty(property, value);
                    }
                });
            }
        }
    }, [jankxControls, clientId]);

    // Cleanup on unmount
    useEffect(() => {
        return () => {
            const blockElement = document.querySelector(`[data-block="${clientId}"]`);
            if (blockElement) {
                const variables = generateCSSVariables(jankxControls, clientId);
                Object.keys(variables).forEach((property) => {
                    blockElement.style.removeProperty(property);
                });
            }
        };
    }, [clientId]);
};

/**
 * Live Preview Provider Component
 * Wraps block edit to enable live preview
 */
export const withLivePreview = (BlockEdit) => {
    return (props) => {
        const { clientId, attributes } = props;

        // Enable live preview
        useLivePreview(clientId, attributes.jankxControls);

        return <BlockEdit {...props} />;
    };
};

/**
 * Generate preview class name with CSS variables
 */
export const getPreviewClassName = (clientId, jankxControls) => {
    if (!jankxControls || Object.keys(jankxControls).length === 0) {
        return '';
    }

    return `jankx-preview-${clientId}`;
};

/**
 * Generate inline styles for preview
 */
export const getPreviewStyles = (jankxControls) => {
    if (!jankxControls) return {};

    const styles = {};

    // Extract relevant style values
    Object.entries(jankxControls).forEach(([key, value]) => {
        if (!value || typeof value !== 'object') return;

        // Background color
        if (value.solidColor && value.colorType === 'solid') {
            styles.backgroundColor = value.solidColor;
        }

        // Padding
        if (value.padding) {
            styles.padding = value.padding;
        }

        // Margin
        if (value.margin) {
            styles.margin = value.margin;
        }

        // Border radius
        if (value.borderRadius) {
            styles.borderRadius = value.borderRadius;
        }

        // Font size
        if (value.fontSize) {
            styles.fontSize = value.fontSize;
        }
    });

    return styles;
};

export default useLivePreview;
