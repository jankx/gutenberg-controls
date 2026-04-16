/**
 * Template Library
 *
 * Import/export complete sections/blocks as JSON templates.
 * Share designs between sites or create reusable components.
 */

import { useState, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as blockEditorStore } from '@wordpress/block-editor';
import { store as noticesStore } from '@wordpress/notices';
import {
    Button,
    Modal,
    TextControl,
    TextareaControl,
    Panel,
    PanelBody,
    PanelRow,
    Card,
    CardHeader,
    CardBody,
    CardFooter,
    ButtonGroup,
    __experimentalHStack as HStack,
    __experimentalVStack as VStack,
} from '@wordpress/components';
import { __, sprintf } from '@wordpress/i18n';
import {
    download,
    upload,
    copy,
    check,
    trash,
    external,
    cloudUpload,
    mediaDocument,
} from '@wordpress/icons';

const TEMPLATE_VERSION = '1.0';

/**
 * Generate template metadata
 */
const generateTemplateMetadata = (name, description, author = '') => ({
    version: TEMPLATE_VERSION,
    name,
    description,
    author,
    createdAt: new Date().toISOString(),
    wordpress: {
        version: window.wpVersion || '6.4',
        jankx: window.jankxVersion || '1.0',
    },
});

/**
 * Export block as template
 */
const exportBlockAsTemplate = (block, metadata) => ({
    metadata,
    block: {
        name: block.name,
        attributes: block.attributes,
        innerBlocks: block.innerBlocks || [],
    },
});

/**
 * Validate imported template
 */
const validateTemplate = (template) => {
    if (!template || typeof template !== 'object') {
        return { valid: false, error: __('Invalid template format', 'jankx') };
    }

    if (!template.metadata) {
        return { valid: false, error: __('Missing template metadata', 'jankx') };
    }

    if (!template.block || !template.block.name) {
        return { valid: false, error: __('Missing block data', 'jankx') };
    }

    // Version compatibility check
    if (template.metadata.version) {
        const templateVersion = parseFloat(template.metadata.version);
        if (templateVersion > parseFloat(TEMPLATE_VERSION) + 0.5) {
            return {
                valid: false,
                error: __('Template version not supported. Please update Jankx.', 'jankx'),
            };
        }
    }

    return { valid: true };
};

/**
 * Template Library Component
 */
export const TemplateLibrary = ({
    clientId,
    isOpen,
    onClose,
    mode = 'export', // 'export' | 'import'
}) => {
    const [activeTab, setActiveTab] = useState(mode);
    const [templateName, setTemplateName] = useState('');
    const [templateDescription, setTemplateDescription] = useState('');
    const [templateAuthor, setTemplateAuthor] = useState('');
    const [importData, setImportData] = useState('');
    const [copied, setCopied] = useState(false);
    const [previewTemplate, setPreviewTemplate] = useState(null);

    const { getBlock } = useSelect(blockEditorStore);
    const { insertBlocks, replaceBlock } = useDispatch(blockEditorStore);
    const { createSuccessNotice, createErrorNotice } = useDispatch(noticesStore);

    /**
     * Export current block
     */
    const handleExport = useCallback(() => {
        if (!templateName.trim()) {
            createErrorNotice(__('Please enter a template name', 'jankx'));
            return;
        }

        const block = getBlock(clientId);
        if (!block) {
            createErrorNotice(__('Block not found', 'jankx'));
            return;
        }

        const metadata = generateTemplateMetadata(
            templateName.trim(),
            templateDescription.trim(),
            templateAuthor.trim()
        );

        const template = exportBlockAsTemplate(block, metadata);
        const jsonData = JSON.stringify(template, null, 2);

        // Download as file
        const blob = new Blob([jsonData], { type: 'application/json' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `jankx-template-${templateName
            .toLowerCase()
            .replace(/\s+/g, '-')}.json`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);

        createSuccessNotice(
            sprintf(__('Template "%s" exported successfully!', 'jankx'), templateName)
        );

        // Also copy to clipboard
        navigator.clipboard
            .writeText(jsonData)
            .then(() => {
                setCopied(true);
                setTimeout(() => setCopied(false), 3000);
            })
            .catch(() => {
                // Clipboard failed but download succeeded
            });
    }, [
        clientId,
        templateName,
        templateDescription,
        templateAuthor,
        getBlock,
        createSuccessNotice,
        createErrorNotice,
    ]);

    /**
     * Import template from JSON
     */
    const handleImport = useCallback(() => {
        if (!importData.trim()) {
            createErrorNotice(__('Please paste template JSON', 'jankx'));
            return;
        }

        try {
            const template = JSON.parse(importData);
            const validation = validateTemplate(template);

            if (!validation.valid) {
                createErrorNotice(validation.error);
                return;
            }

            setPreviewTemplate(template);
        } catch (e) {
            createErrorNotice(__('Invalid JSON format', 'jankx'));
        }
    }, [importData, createErrorNotice]);

    /**
     * Confirm import and insert block
     */
    const handleConfirmImport = useCallback(() => {
        if (!previewTemplate) return;

        const { block } = previewTemplate;

        // Create new block with imported data
        const newBlock = {
            clientId: undefined, // Let WordPress generate new ID
            name: block.name,
            attributes: block.attributes,
            innerBlocks: block.innerBlocks,
        };

        // Insert after current block
        insertBlocks(newBlock, undefined, clientId);

        createSuccessNotice(
            sprintf(
                __('Template "%s" imported successfully!', 'jankx'),
                previewTemplate.metadata.name
            )
        );

        setPreviewTemplate(null);
        setImportData('');
        onClose();
    }, [previewTemplate, clientId, insertBlocks, createSuccessNotice, onClose]);

    /**
     * Import from file
     */
    const handleFileImport = useCallback(
        (event) => {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                setImportData(e.target.result);
                // Auto-trigger import
                try {
                    const template = JSON.parse(e.target.result);
                    const validation = validateTemplate(template);

                    if (validation.valid) {
                        setPreviewTemplate(template);
                        createSuccessNotice(__('Template loaded. Review and confirm import.', 'jankx'));
                    } else {
                        createErrorNotice(validation.error);
                    }
                } catch (err) {
                    createErrorNotice(__('Failed to read template file', 'jankx'));
                }
            };
            reader.readAsText(file);

            event.target.value = '';
        },
        [createSuccessNotice, createErrorNotice]
    );

    if (!isOpen) return null;

    return (
        <Modal
            title={__('Template Library', 'jankx')}
            onRequestClose={onClose}
            className="jankx-template-library"
            size="large"
        >
            <ButtonGroup className="jankx-template-tabs">
                <Button
                    variant={activeTab === 'export' ? 'primary' : 'secondary'}
                    onClick={() => {
                        setActiveTab('export');
                        setPreviewTemplate(null);
                    }}
                    icon={download}
                >
                    {__('Export', 'jankx')}
                </Button>
                <Button
                    variant={activeTab === 'import' ? 'primary' : 'secondary'}
                    onClick={() => {
                        setActiveTab('import');
                        setPreviewTemplate(null);
                    }}
                    icon={upload}
                >
                    {__('Import', 'jankx')}
                </Button>
            </ButtonGroup>

            {activeTab === 'export' && (
                <VStack spacing={4} className="jankx-template-export">
                    <TextControl
                        label={__('Template Name', 'jankx')}
                        value={templateName}
                        onChange={setTemplateName}
                        placeholder={__('e.g., Hero Banner with CTA', 'jankx')}
                        __next40pxDefaultSize
                    />

                    <TextareaControl
                        label={__('Description', 'jankx')}
                        value={templateDescription}
                        onChange={setTemplateDescription}
                        placeholder={__('Describe what this template is for...', 'jankx')}
                        rows={2}
                    />

                    <TextControl
                        label={__('Author (optional)', 'jankx')}
                        value={templateAuthor}
                        onChange={setTemplateAuthor}
                        placeholder={__('Your name or company', 'jankx')}
                        __next40pxDefaultSize
                    />

                    <div className="jankx-template-actions">
                        <Button
                            variant="primary"
                            icon={download}
                            onClick={handleExport}
                            disabled={!templateName.trim()}
                            __next40pxDefaultSize
                        >
                            {__('Download Template', 'jankx')}
                        </Button>

                        <Button
                            variant={copied ? 'primary' : 'secondary'}
                            icon={copied ? check : copy}
                            onClick={handleExport}
                            disabled={!templateName.trim()}
                            __next40pxDefaultSize
                        >
                            {copied ? __('Copied!', 'jankx') : __('Copy to Clipboard', 'jankx')}
                        </Button>
                    </div>

                    <p className="jankx-template-hint">
                        {__('The template will include all block settings, styles, and inner blocks.', 'jankx')}
                    </p>
                </VStack>
            )}

            {activeTab === 'import' && !previewTemplate && (
                <VStack spacing={4} className="jankx-template-import">
                    <div className="jankx-file-upload">
                        <input
                            type="file"
                            accept=".json"
                            onChange={handleFileImport}
                            id="jankx-template-file"
                            className="jankx-template-file-input"
                        />
                        <label
                            htmlFor="jankx-template-file"
                            className="jankx-template-file-label"
                        >
                            <span className="jankx-template-file-icon">📁</span>
                            <span>{__('Click to upload template file', 'jankx')}</span>
                        </label>
                    </div>

                    <div className="jankx-template-divider">
                        <span>{__('or paste JSON', 'jankx')}</span>
                    </div>

                    <TextareaControl
                        label={__('Template JSON', 'jankx')}
                        value={importData}
                        onChange={setImportData}
                        placeholder={__('Paste template JSON here...', 'jankx')}
                        rows={8}
                    />

                    <Button
                        variant="primary"
                        icon={upload}
                        onClick={handleImport}
                        disabled={!importData.trim()}
                        __next40pxDefaultSize
                    >
                        {__('Load Template', 'jankx')}
                    </Button>
                </VStack>
            )}

            {activeTab === 'import' && previewTemplate && (
                <VStack spacing={4} className="jankx-template-preview">
                    <Card>
                        <CardHeader>
                            <HStack>
                                <span className="jankx-template-preview-title">
                                    {previewTemplate.metadata.name}
                                </span>
                                <span className="jankx-template-version">
                                    v{previewTemplate.metadata.version || '1.0'}
                                </span>
                            </HStack>
                        </CardHeader>
                        <CardBody>
                            <VStack spacing={2}>
                                {previewTemplate.metadata.description && (
                                    <p>{previewTemplate.metadata.description}</p>
                                )}
                                <div className="jankx-template-meta">
                                    <span>
                                        {__('Block:', 'jankx')}{' '}
                                        <code>{previewTemplate.block.name}</code>
                                    </span>
                                    {previewTemplate.metadata.author && (
                                        <span>
                                            {__('By:', 'jankx')} {previewTemplate.metadata.author}
                                        </span>
                                    )}
                                    <span>
                                        {__('Created:', 'jankx')}{' '}
                                        {new Date(
                                            previewTemplate.metadata.createdAt
                                        ).toLocaleDateString()}
                                    </span>
                                </div>
                            </VStack>
                        </CardBody>
                        <CardFooter>
                            <ButtonGroup>
                                <Button
                                    variant="secondary"
                                    onClick={() => setPreviewTemplate(null)}
                                >
                                    {__('Back', 'jankx')}
                                </Button>
                                <Button
                                    variant="primary"
                                    icon={cloudUpload}
                                    onClick={handleConfirmImport}
                                    __next40pxDefaultSize
                                >
                                    {__('Import Template', 'jankx')}
                                </Button>
                            </ButtonGroup>
                        </CardFooter>
                    </Card>

                    <p className="jankx-template-warning">
                        {__('This will insert the template as a new block after the current one.', 'jankx')}
                    </p>
                </VStack>
            )}
        </Modal>
    );
};

/**
 * Quick export button for toolbar
 */
export const TemplateExportButton = ({ clientId }) => {
    const [isOpen, setIsOpen] = useState(false);

    return (
        <>
            <Button
                icon={mediaDocument}
                label={__('Template Library', 'jankx')}
                onClick={() => setIsOpen(true)}
                size="compact"
            />
            <TemplateLibrary
                clientId={clientId}
                isOpen={isOpen}
                onClose={() => setIsOpen(false)}
                mode="export"
            />
        </>
    );
};

export default TemplateLibrary;
