/**
 * Custom Preset Manager
 *
 * Allows users to save their own design presets
 * with undo/redo support via WordPress core history.
 */

import { useState, useCallback, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { store as noticesStore } from '@wordpress/notices';
import {
    Button,
    TextControl,
    Modal,
    PanelBody,
    TextareaControl,
    DropdownMenu,
    MenuGroup,
    MenuItem,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import {
    chevronDown,
    check,
    trash,
    edit,
    download,
    upload,
    plus,
} from '@wordpress/icons';

// Storage key for user presets
const STORAGE_KEY = 'jankx_custom_presets';
const MAX_PRESETS = 50;

/**
 * Load custom presets from localStorage
 */
const loadCustomPresets = () => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        if (stored) {
            return JSON.parse(stored);
        }
    } catch (e) {
        console.warn('Failed to load custom presets:', e);
    }
    return [];
};

/**
 * Save custom presets to localStorage
 */
const saveCustomPresets = (presets) => {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(presets));
        return true;
    } catch (e) {
        console.warn('Failed to save custom presets:', e);
        return false;
    }
};

/**
 * Generate unique ID
 */
const generateId = () => {
    return `preset_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
};

/**
 * Create preset from current control values
 */
const createPreset = (name, description, controls, isGlobal = false) => {
    return {
        id: generateId(),
        name,
        description,
        controls: { ...controls },
        isGlobal,
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString(),
    };
};

/**
 * Custom Preset Manager Component
 */
export const CustomPresetManager = ({
    currentControls,
    onApplyPreset,
    onPresetsChange,
}) => {
    const [presets, setPresets] = useState(() => loadCustomPresets());
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [isEditModalOpen, setIsEditModalOpen] = useState(false);
    const [presetName, setPresetName] = useState('');
    const [presetDescription, setPresetDescription] = useState('');
    const [isGlobal, setIsGlobal] = useState(false);
    const [editingPreset, setEditingPreset] = useState(null);

    const { createSuccessNotice, createErrorNotice } = useDispatch(noticesStore);

    // Sync with parent
    useEffect(() => {
        onPresetsChange?.(presets);
    }, [presets, onPresetsChange]);

    /**
     * Save current controls as new preset
     */
    const handleSavePreset = useCallback(() => {
        if (!presetName.trim()) {
            createErrorNotice(__('Please enter a preset name', 'jankx'));
            return;
        }

        if (presets.length >= MAX_PRESETS) {
            createErrorNotice(
                __('Maximum number of presets reached. Please delete some first.', 'jankx')
            );
            return;
        }

        const newPreset = createPreset(
            presetName.trim(),
            presetDescription.trim(),
            currentControls,
            isGlobal
        );

        const updatedPresets = [...presets, newPreset];

        if (saveCustomPresets(updatedPresets)) {
            setPresets(updatedPresets);
            setIsModalOpen(false);
            setPresetName('');
            setPresetDescription('');
            setIsGlobal(false);
            createSuccessNotice(
                __('Preset saved successfully!', 'jankx'),
                { type: 'snackbar' }
            );
        } else {
            createErrorNotice(__('Failed to save preset', 'jankx'));
        }
    }, [
        presetName,
        presetDescription,
        currentControls,
        isGlobal,
        presets,
        createSuccessNotice,
        createErrorNotice,
    ]);

    /**
     * Apply preset with undo/redo support
     */
    const handleApplyPreset = useCallback(
        (preset) => {
            // Store current state for undo
            const undoState = { ...currentControls };

            // Apply preset
            onApplyPreset(preset.controls);

            createSuccessNotice(
                sprintf(__('Applied preset: %s', 'jankx'), preset.name),
                {
                    type: 'snackbar',
                    actions: [
                        {
                            label: __('Undo', 'jankx'),
                            onClick: () => {
                                onApplyPreset(undoState);
                                createSuccessNotice(__('Changes reverted', 'jankx'));
                            },
                        },
                    ],
                }
            );
        },
        [currentControls, onApplyPreset, createSuccessNotice]
    );

    /**
     * Delete preset
     */
    const handleDeletePreset = useCallback(
        (presetId) => {
            const updatedPresets = presets.filter((p) => p.id !== presetId);

            if (saveCustomPresets(updatedPresets)) {
                setPresets(updatedPresets);
                createSuccessNotice(__('Preset deleted', 'jankx'));
            }
        },
        [presets, createSuccessNotice]
    );

    /**
     * Update existing preset
     */
    const handleUpdatePreset = useCallback(() => {
        if (!editingPreset || !presetName.trim()) return;

        const updatedPresets = presets.map((p) =>
            p.id === editingPreset.id
                ? {
                      ...p,
                      name: presetName.trim(),
                      description: presetDescription.trim(),
                      controls: { ...currentControls },
                      updatedAt: new Date().toISOString(),
                  }
                : p
        );

        if (saveCustomPresets(updatedPresets)) {
            setPresets(updatedPresets);
            setIsEditModalOpen(false);
            setEditingPreset(null);
            setPresetName('');
            setPresetDescription('');
            createSuccessNotice(__('Preset updated!', 'jankx'));
        }
    }, [editingPreset, presetName, presetDescription, currentControls, presets, createSuccessNotice]);

    /**
     * Export presets to JSON file
     */
    const handleExport = useCallback(() => {
        const dataStr = JSON.stringify(presets, null, 2);
        const dataBlob = new Blob([dataStr], { type: 'application/json' });
        const url = URL.createObjectURL(dataBlob);

        const link = document.createElement('a');
        link.href = url;
        link.download = `jankx-presets-${new Date().toISOString().split('T')[0]}.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        createSuccessNotice(__('Presets exported successfully!', 'jankx'));
    }, [presets, createSuccessNotice]);

    /**
     * Import presets from JSON file
     */
    const handleImport = useCallback(
        (event) => {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const imported = JSON.parse(e.target.result);

                    if (!Array.isArray(imported)) {
                        throw new Error('Invalid format');
                    }

                    // Validate imported presets
                    const validPresets = imported.filter(
                        (p) => p.id && p.name && p.controls
                    );

                    // Merge with existing, avoid duplicates by ID
                    const existingIds = new Set(presets.map((p) => p.id));
                    const newPresets = validPresets.filter(
                        (p) => !existingIds.has(p.id)
                    );

                    if (newPresets.length === 0) {
                        createErrorNotice(
                            __('No new presets to import (all already exist)', 'jankx')
                        );
                        return;
                    }

                    const combined = [...presets, ...newPresets].slice(0, MAX_PRESETS);

                    if (saveCustomPresets(combined)) {
                        setPresets(combined);
                        createSuccessNotice(
                            sprintf(
                                __('Imported %d presets successfully!', 'jankx'),
                                newPresets.length
                            )
                        );
                    }
                } catch (err) {
                    createErrorNotice(
                        __('Failed to import presets. Invalid file format.', 'jankx')
                    );
                }
            };
            reader.readAsText(file);

            // Reset input
            event.target.value = '';
        },
        [presets, createSuccessNotice, createErrorNotice]
    );

    /**
     * Open edit modal
     */
    const openEditModal = (preset) => {
        setEditingPreset(preset);
        setPresetName(preset.name);
        setPresetDescription(preset.description || '');
        setIsEditModalOpen(true);
    };

    return (
        <>
            <PanelBody title={__('My Presets', 'jankx')} initialOpen={true}>
                <div className="jankx-custom-presets">
                    {/* Save Current Button */}
                    <Button
                        variant="secondary"
                        icon={plus}
                        onClick={() => setIsModalOpen(true)}
                        className="jankx-save-preset-btn"
                        __next40pxDefaultSize
                    >
                        {__('Save Current Design', 'jankx')}
                    </Button>

                    {/* Presets List */}
                    {presets.length > 0 ? (
                        <div className="jankx-presets-list">
                            {presets.map((preset) => (
                                <div
                                    key={preset.id}
                                    className="jankx-preset-item"
                                >
                                    <div className="jankx-preset-info">
                                        <span className="jankx-preset-name">
                                            {preset.name}
                                        </span>
                                        {preset.description && (
                                            <span className="jankx-preset-desc">
                                                {preset.description}
                                            </span>
                                        )}
                                        <span className="jankx-preset-date">
                                            {new Date(
                                                preset.createdAt
                                            ).toLocaleDateString()}
                                        </span>
                                    </div>

                                    <DropdownMenu
                                        icon={chevronDown}
                                        label={__('Preset actions', 'jankx')}
                                        popoverProps={{
                                            placement: 'bottom-end',
                                        }}
                                    >
                                        {({ onClose }) => (
                                            <MenuGroup>
                                                <MenuItem
                                                    icon={check}
                                                    onClick={() => {
                                                        handleApplyPreset(preset);
                                                        onClose();
                                                    }}
                                                >
                                                    {__('Apply', 'jankx')}
                                                </MenuItem>
                                                <MenuItem
                                                    icon={edit}
                                                    onClick={() => {
                                                        openEditModal(preset);
                                                        onClose();
                                                    }}
                                                >
                                                    {__('Update with Current', 'jankx')}
                                                </MenuItem>
                                                <MenuItem
                                                    icon={trash}
                                                    onClick={() => {
                                                        handleDeletePreset(preset.id);
                                                        onClose();
                                                    }}
                                                    isDestructive
                                                >
                                                    {__('Delete', 'jankx')}
                                                </MenuItem>
                                            </MenuGroup>
                                        )}
                                    </DropdownMenu>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p className="jankx-no-presets">
                            {__('No custom presets yet. Save your current design!', 'jankx')}
                        </p>
                    )}

                    {/* Import/Export */}
                    <div className="jankx-preset-actions">
                        <Button
                            variant="tertiary"
                            icon={download}
                            onClick={handleExport}
                            disabled={presets.length === 0}
                            size="small"
                        >
                            {__('Export', 'jankx')}
                        </Button>
                        <div className="jankx-file-input-wrapper">
                            <Button
                                variant="tertiary"
                                icon={upload}
                                size="small"
                                className="jankx-import-btn"
                            >
                                {__('Import', 'jankx')}
                            </Button>
                            <input
                                type="file"
                                accept=".json"
                                onChange={handleImport}
                                className="jankx-file-input"
                            />
                        </div>
                    </div>
                </div>
            </PanelBody>

            {/* Save Preset Modal */}
            {isModalOpen && (
                <Modal
                    title={__('Save Design Preset', 'jankx')}
                    onRequestClose={() => setIsModalOpen(false)}
                    className="jankx-preset-modal"
                >
                    <TextControl
                        label={__('Preset Name', 'jankx')}
                        value={presetName}
                        onChange={setPresetName}
                        placeholder={__('e.g., Hero Section Dark', 'jankx')}
                        __next40pxDefaultSize
                    />
                    <TextareaControl
                        label={__('Description (optional)', 'jankx')}
                        value={presetDescription}
                        onChange={setPresetDescription}
                        placeholder={__('Describe this design...', 'jankx')}
                        rows={3}
                    />
                    <div className="jankx-modal-actions">
                        <Button
                            variant="tertiary"
                            onClick={() => setIsModalOpen(false)}
                        >
                            {__('Cancel', 'jankx')}
                        </Button>
                        <Button
                            variant="primary"
                            onClick={handleSavePreset}
                            disabled={!presetName.trim()}
                            __next40pxDefaultSize
                        >
                            {__('Save Preset', 'jankx')}
                        </Button>
                    </div>
                </Modal>
            )}

            {/* Edit Preset Modal */}
            {isEditModalOpen && editingPreset && (
                <Modal
                    title={__('Update Preset', 'jankx')}
                    onRequestClose={() => {
                        setIsEditModalOpen(false);
                        setEditingPreset(null);
                    }}
                    className="jankx-preset-modal"
                >
                    <TextControl
                        label={__('Preset Name', 'jankx')}
                        value={presetName}
                        onChange={setPresetName}
                        __next40pxDefaultSize
                    />
                    <TextareaControl
                        label={__('Description', 'jankx')}
                        value={presetDescription}
                        onChange={setPresetDescription}
                        rows={3}
                    />
                    <p className="jankx-modal-hint">
                        {__('This will update the preset with current control values.', 'jankx')}
                    </p>
                    <div className="jankx-modal-actions">
                        <Button
                            variant="tertiary"
                            onClick={() => {
                                setIsEditModalOpen(false);
                                setEditingPreset(null);
                            }}
                        >
                            {__('Cancel', 'jankx')}
                        </Button>
                        <Button
                            variant="primary"
                            onClick={handleUpdatePreset}
                            disabled={!presetName.trim()}
                            __next40pxDefaultSize
                        >
                            {__('Update', 'jankx')}
                        </Button>
                    </div>
                </Modal>
            )}
        </>
    );
};

export default CustomPresetManager;
