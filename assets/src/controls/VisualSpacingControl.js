/**
 * Visual Spacing Control - Drag handles for margin/padding (Flatsome-style)
 *
 * High-UX component showing visual box model with draggable handles
 * instead of input fields. Much more intuitive.
 */

import { useState, useCallback, useRef } from '@wordpress/element';
import {
    BaseControl,
    Button,
    ButtonGroup,
    __experimentalUnitControl as UnitControl,
    Tooltip,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { link, linkOff, deviceDesktop, deviceTablet, deviceMobile } from '@wordpress/icons';

const PRESET_VALUES = ['0', '8px', '16px', '24px', '32px', '48px', '64px', '96px', '128px'];

const VisualSpacingControl = ({
    label,
    values = { top: '', right: '', bottom: '', left: '' },
    onChange,
    allowResponsive = true,
}) => {
    const [isLinked, setIsLinked] = useState(true);
    const [activeDevice, setActiveDevice] = useState('desktop');
    const [dragging, setDragging] = useState(null);
    const [hoveredSide, setHoveredSide] = useState(null);
    const boxRef = useRef(null);
    const startValueRef = useRef(0);
    const startYRef = useRef(0);

    // Get current values for active device
    const currentValues = allowResponsive && values[activeDevice]
        ? values[activeDevice]
        : values;

    const parseValue = (val) => {
        if (!val) return 0;
        const num = parseInt(val, 10);
        return isNaN(num) ? 0 : num;
    };

    const formatValue = (num) => {
        if (num === 0) return '0';
        return `${num}px`;
    };

    const updateValue = (side, value) => {
        const newValues = { ...currentValues };

        if (isLinked) {
            // Update all sides
            newValues.top = value;
            newValues.right = value;
            newValues.bottom = value;
            newValues.left = value;
        } else {
            newValues[side] = value;
        }

        if (allowResponsive) {
            onChange({
                ...values,
                [activeDevice]: newValues,
            });
        } else {
            onChange(newValues);
        }
    };

    // Drag handlers
    const handleDragStart = (e, side) => {
        e.preventDefault();
        setDragging(side);
        startValueRef.current = parseValue(currentValues[side]);
        startYRef.current = e.clientY || e.touches?.[0]?.clientY;

        document.addEventListener('mousemove', handleDragMove);
        document.addEventListener('mouseup', handleDragEnd);
        document.addEventListener('touchmove', handleDragMove);
        document.addEventListener('touchend', handleDragEnd);
    };

    const handleDragMove = useCallback((e) => {
        if (!dragging) return;

        const clientY = e.clientY || e.touches?.[0]?.clientY;
        const delta = (startYRef.current - clientY) / 2; // Slower for precision
        const newValue = Math.max(0, Math.round(startValueRef.current + delta));

        updateValue(dragging, formatValue(newValue));
    }, [dragging, currentValues, isLinked]);

    const handleDragEnd = useCallback(() => {
        setDragging(null);
        document.removeEventListener('mousemove', handleDragMove);
        document.removeEventListener('mouseup', handleDragEnd);
        document.removeEventListener('touchmove', handleDragMove);
        document.removeEventListener('touchend', handleDragEnd);
    }, [handleDragMove]);

    // Calculate visual dimensions
    const boxSize = 140;
    const maxSpacing = 60;
    const scale = 0.5;

    const topH = Math.min(parseValue(currentValues.top) * scale, maxSpacing);
    const rightW = Math.min(parseValue(currentValues.right) * scale, maxSpacing);
    const bottomH = Math.min(parseValue(currentValues.bottom) * scale, maxSpacing);
    const leftW = Math.min(parseValue(currentValues.left) * scale, maxSpacing);

    // Visual box styles
    const containerStyle = {
        position: 'relative',
        width: boxSize + leftW + rightW,
        height: boxSize + topH + bottomH,
        margin: '20px auto',
    };

    const contentStyle = {
        position: 'absolute',
        top: topH,
        left: leftW,
        width: boxSize,
        height: boxSize,
        background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        borderRadius: '4px',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        color: 'white',
        fontSize: '12px',
        fontWeight: 'bold',
    };

    const sideStyles = {
        top: {
            position: 'absolute',
            top: 0,
            left: leftW,
            width: boxSize,
            height: topH,
            background: dragging === 'top' ? '#ff5722' : '#e0e0e0',
            cursor: 'ns-resize',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '10px',
            color: '#666',
            borderRadius: topH > 0 ? '4px 4px 0 0' : '0',
        },
        right: {
            position: 'absolute',
            top: topH,
            right: 0,
            width: rightW,
            height: boxSize,
            background: dragging === 'right' ? '#ff5722' : '#e0e0e0',
            cursor: 'ew-resize',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '10px',
            color: '#666',
            borderRadius: rightW > 0 ? '0 4px 4px 0' : '0',
        },
        bottom: {
            position: 'absolute',
            bottom: 0,
            left: leftW,
            width: boxSize,
            height: bottomH,
            background: dragging === 'bottom' ? '#ff5722' : '#e0e0e0',
            cursor: 'ns-resize',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '10px',
            color: '#666',
            borderRadius: bottomH > 0 ? '0 0 4px 4px' : '0',
        },
        left: {
            position: 'absolute',
            top: topH,
            left: 0,
            width: leftW,
            height: boxSize,
            background: dragging === 'left' ? '#ff5722' : '#e0e0e0',
            cursor: 'ew-resize',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: '10px',
            color: '#666',
            borderRadius: leftW > 0 ? '4px 0 0 4px' : '0',
        },
    };

    return (
        <BaseControl label={label} className="jankx-visual-spacing">
            {/* Device selector */}
            {allowResponsive && (
                <ButtonGroup className="jankx-device-selector">
                    <Button
                        icon={deviceDesktop}
                        variant={activeDevice === 'desktop' ? 'primary' : 'secondary'}
                        onClick={() => setActiveDevice('desktop')}
                        size="small"
                    />
                    <Button
                        icon={deviceTablet}
                        variant={activeDevice === 'tablet' ? 'primary' : 'secondary'}
                        onClick={() => setActiveDevice('tablet')}
                        size="small"
                    />
                    <Button
                        icon={deviceMobile}
                        variant={activeDevice === 'mobile' ? 'primary' : 'secondary'}
                        onClick={() => setActiveDevice('mobile')}
                        size="small"
                    />
                </ButtonGroup>
            )}

            {/* Visual drag box */}
            <div ref={boxRef} style={containerStyle} className="jankx-spacing-box">
                {/* Top */}
                <Tooltip text={__('Drag to adjust top spacing', 'jankx')}>
                    <div
                        style={sideStyles.top}
                        onMouseDown={(e) => handleDragStart(e, 'top')}
                        onTouchStart={(e) => handleDragStart(e, 'top')}
                        onMouseEnter={() => setHoveredSide('top')}
                        onMouseLeave={() => setHoveredSide(null)}
                        className={`jankx-spacing-side ${hoveredSide === 'top' ? 'is-hovered' : ''}`}
                    >
                        {topH > 20 && parseValue(currentValues.top)}
                        <div className="jankx-spacing-handle" />
                    </div>
                </Tooltip>

                {/* Right */}
                <Tooltip text={__('Drag to adjust right spacing', 'jankx')}>
                    <div
                        style={sideStyles.right}
                        onMouseDown={(e) => handleDragStart(e, 'right')}
                        onTouchStart={(e) => handleDragStart(e, 'right')}
                        onMouseEnter={() => setHoveredSide('right')}
                        onMouseLeave={() => setHoveredSide(null)}
                        className={`jankx-spacing-side ${hoveredSide === 'right' ? 'is-hovered' : ''}`}
                    >
                        {rightW > 30 && parseValue(currentValues.right)}
                        <div className="jankx-spacing-handle" />
                    </div>
                </Tooltip>

                {/* Bottom */}
                <Tooltip text={__('Drag to adjust bottom spacing', 'jankx')}>
                    <div
                        style={sideStyles.bottom}
                        onMouseDown={(e) => handleDragStart(e, 'bottom')}
                        onTouchStart={(e) => handleDragStart(e, 'bottom')}
                        onMouseEnter={() => setHoveredSide('bottom')}
                        onMouseLeave={() => setHoveredSide(null)}
                        className={`jankx-spacing-side ${hoveredSide === 'bottom' ? 'is-hovered' : ''}`}
                    >
                        {bottomH > 20 && parseValue(currentValues.bottom)}
                        <div className="jankx-spacing-handle" />
                    </div>
                </Tooltip>

                {/* Left */}
                <Tooltip text={__('Drag to adjust left spacing', 'jankx')}>
                    <div
                        style={sideStyles.left}
                        onMouseDown={(e) => handleDragStart(e, 'left')}
                        onTouchStart={(e) => handleDragStart(e, 'left')}
                        onMouseEnter={() => setHoveredSide('left')}
                        onMouseLeave={() => setHoveredSide(null)}
                        className={`jankx-spacing-side ${hoveredSide === 'left' ? 'is-hovered' : ''}`}
                    >
                        {leftW > 30 && parseValue(currentValues.left)}
                        <div className="jankx-spacing-handle" />
                    </div>
                </Tooltip>

                {/* Content box */}
                <div style={contentStyle}>
                    Content
                </div>
            </div>

            {/* Link toggle and inputs */}
            <div className="jankx-spacing-controls">
                <Button
                    icon={isLinked ? link : linkOff}
                    onClick={() => setIsLinked(!isLinked)}
                    variant={isLinked ? 'primary' : 'secondary'}
                    size="small"
                    className="jankx-link-toggle"
                />

                <div className="jankx-spacing-inputs">
                    {['top', 'right', 'bottom', 'left'].map((side) => (
                        <UnitControl
                            key={side}
                            label={side.charAt(0).toUpperCase() + side.slice(1)}
                            value={currentValues[side]}
                            onChange={(value) => updateValue(side, value)}
                            units={[{ value: 'px', label: 'px', default: 0 }]}
                            size="small"
                        />
                    ))}
                </div>
            </div>

            {/* Quick presets */}
            <div className="jankx-spacing-presets">
                <label>{__('Quick Presets', 'jankx')}</label>
                <ButtonGroup className="jankx-preset-buttons">
                    {PRESET_VALUES.map((val) => (
                        <Button
                            key={val}
                            variant="secondary"
                            size="small"
                            onClick={() => updateValue('all', val)}
                        >
                            {val}
                        </Button>
                    ))}
                </ButtonGroup>
            </div>
        </BaseControl>
    );
};

export default VisualSpacingControl;
