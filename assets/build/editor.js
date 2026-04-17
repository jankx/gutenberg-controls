/**
 * Jankx Gutenberg Controls - Editor Entry Point (Test Build)
 */

(function() {
    'use strict';

    const { addFilter } = wp.hooks;
    const { createHigherOrderComponent } = wp.compose;
    const { InspectorControls, BlockControls } = wp.blockEditor;
    const { PanelBody, TabPanel, ToolbarGroup, ToolbarButton, Button } = wp.components;
    const { useState, useCallback, useRef } = wp.element;
    const { useDispatch, useSelect } = wp.data;
    const { __ } = wp.i18n;

    console.log('Jankx Gutenberg Controls - Editor initialized');

    // Simple HOC to add Jankx panel to blocks
    const withJankxControls = createHigherOrderComponent(function(BlockEdit) {
        return function(props) {
            const { name, attributes, setAttributes, isSelected } = props;

            // Only add to Jankx blocks
            if (!name || !name.startsWith('jankx/')) {
                return wp.element.createElement(BlockEdit, props);
            }

            // Check if block supports controls
            const blockConfig = window.jankxBlocks?.controls?.[name] || {};
            const hasControls = Object.keys(blockConfig).length > 0;

            return wp.element.createElement(
                wp.element.Fragment,
                null,
                wp.element.createElement(BlockEdit, props),
                isSelected && hasControls && wp.element.createElement(
                    InspectorControls,
                    { group: 'styles' },
                    wp.element.createElement(
                        PanelBody,
                        { title: __('Jankx Controls', 'jankx'), initialOpen: true },
                        wp.element.createElement(
                            'div',
                            { className: 'jankx-controls-placeholder' },
                            wp.element.createElement(
                                'p',
                                { style: { color: '#ff5722', fontWeight: 'bold' } },
                                '✨ Jankx Controls Active'
                            ),
                            wp.element.createElement(
                                'p',
                                null,
                                'Block: ' + name
                            ),
                            Object.keys(blockConfig).map(function(controlName) {
                                return wp.element.createElement(
                                    'div',
                                    { key: controlName, style: { margin: '8px 0', padding: '8px', background: '#f5f5f5', borderRadius: '4px' } },
                                    wp.element.createElement(
                                        'strong',
                                        null,
                                        blockConfig[controlName].label || controlName
                                    ),
                                    wp.element.createElement(
                                        'span',
                                        { style: { display: 'block', fontSize: '11px', color: '#666' } },
                                        'Type: ' + blockConfig[controlName].type
                                    )
                                );
                            })
                        )
                    )
                )
            );
        };
    }, 'withJankxControls');

    // Register filter
    addFilter('editor.BlockEdit', 'jankx/gutenberg-controls', withJankxControls);

    console.log('Jankx Gutenberg Controls filter registered');
})();
