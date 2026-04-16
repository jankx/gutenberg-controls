/**
 * Preset Panel - One-click preset selector (Flatsome-style)
 *
 * High-UX component showing preset thumbnails in a grid
 * with categories, search, and hover preview.
 */

import { useState, useMemo } from '@wordpress/element';
import {
    PanelBody,
    Button,
    SearchControl,
    ButtonGroup,
    Modal,
    __experimentalGrid as Grid,
    Card,
    CardMedia,
    CardBody,
    Tooltip,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { check, update, layout } from '@wordpress/icons';

const PresetPanel = ({ presets, categories, currentValues, onApplyPreset }) => {
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [activeCategory, setActiveCategory] = useState('all');
    const [hoveredPreset, setHoveredPreset] = useState(null);

    // Filter presets
    const filteredPresets = useMemo(() => {
        let filtered = presets;

        // Category filter
        if (activeCategory !== 'all') {
            filtered = filtered.filter(p => p.category === activeCategory);
        }

        // Search filter
        if (searchQuery) {
            const query = searchQuery.toLowerCase();
            filtered = filtered.filter(p =>
                p.title.toLowerCase().includes(query) ||
                p.description.toLowerCase().includes(query) ||
                p.tags?.some(t => t.toLowerCase().includes(query))
            );
        }

        return filtered;
    }, [presets, activeCategory, searchQuery]);

    // Quick presets (most used)
    const quickPresets = useMemo(() => {
        return presets.slice(0, 4); // First 4 presets
    }, [presets]);

    const handleApplyPreset = (preset) => {
        onApplyPreset(preset);
        setIsModalOpen(false);
    };

    const PresetCard = ({ preset }) => (
        <Card
            className="jankx-preset-card"
            onMouseEnter={() => setHoveredPreset(preset.id)}
            onMouseLeave={() => setHoveredPreset(null)}
        >
            <CardMedia className="jankx-preset-card__media">
                {preset.thumbnail ? (
                    <img
                        src={`${jankxBlocks.assetsUrl}/presets/${preset.thumbnail}`}
                        alt={preset.title}
                    />
                ) : (
                    <div className="jankx-preset-card__placeholder">
                        <span>{preset.title.charAt(0)}</span>
                    </div>
                )}
                {hoveredPreset === preset.id && (
                    <div className="jankx-preset-card__overlay">
                        <Button
                            variant="primary"
                            icon={check}
                            onClick={() => handleApplyPreset(preset)}
                        >
                            {__('Apply', 'jankx')}
                        </Button>
                    </div>
                )}
            </CardMedia>
            <CardBody className="jankx-preset-card__body">
                <h4>{preset.title}</h4>
                <p>{preset.description}</p>
                <div className="jankx-preset-card__tags">
                    {preset.tags?.slice(0, 3).map(tag => (
                        <span key={tag} className="jankx-tag">{tag}</span>
                    ))}
                </div>
            </CardBody>
        </Card>
    );

    return (
        <>
            <PanelBody
                title={__('Design Presets', 'jankx')}
                icon={layout}
                initialOpen={true}
            >
                {/* Quick presets */}
                <div className="jankx-quick-presets">
                    <label>{__('Quick Apply', 'jankx')}</label>
                    <ButtonGroup className="jankx-quick-buttons">
                        {quickPresets.map(preset => (
                            <Tooltip key={preset.id} text={preset.title}>
                                <Button
                                    variant="secondary"
                                    onClick={() => handleApplyPreset(preset)}
                                    className="jankx-quick-btn"
                                >
                                    {preset.title.charAt(0)}
                                </Button>
                            </Tooltip>
                        ))}
                    </ButtonGroup>
                </div>

                {/* Browse all */}
                <Button
                    variant="primary"
                    onClick={() => setIsModalOpen(true)}
                    className="jankx-browse-presets"
                    icon={update}
                    style={{ width: '100%', marginTop: '12px' }}
                >
                    {__('Browse All Presets', 'jankx')}
                </Button>

                <p className="jankx-preset-hint">
                    {__('Apply a preset to quickly style your section', 'jankx')}
                </p>
            </PanelBody>

            {/* Full preset browser modal */}
            {isModalOpen && (
                <Modal
                    title={__('Choose a Design Preset', 'jankx')}
                    onRequestClose={() => setIsModalOpen(false)}
                    className="jankx-preset-modal"
                    size="large"
                >
                    {/* Search and filters */}
                    <div className="jankx-preset-toolbar">
                        <SearchControl
                            value={searchQuery}
                            onChange={setSearchQuery}
                            placeholder={__('Search presets...', 'jankx')}
                        />

                        <ButtonGroup className="jankx-category-filter">
                            <Button
                                variant={activeCategory === 'all' ? 'primary' : 'secondary'}
                                onClick={() => setActiveCategory('all')}
                            >
                                {__('All', 'jankx')}
                            </Button>
                            {Object.entries(categories).map(([key, cat]) => (
                                <Button
                                    key={key}
                                    variant={activeCategory === key ? 'primary' : 'secondary'}
                                    onClick={() => setActiveCategory(key)}
                                >
                                    {cat.title}
                                </Button>
                            ))}
                        </ButtonGroup>
                    </div>

                    {/* Presets grid */}
                    <Grid
                        className="jankx-presets-grid"
                        columns={3}
                        gap={4}
                    >
                        {filteredPresets.map(preset => (
                            <PresetCard key={preset.id} preset={preset} />
                        ))}
                    </Grid>

                    {filteredPresets.length === 0 && (
                        <div className="jankx-no-presets">
                            <p>{__('No presets found matching your criteria.', 'jankx')}</p>
                        </div>
                    )}
                </Modal>
            )}
        </>
    );
};

export default PresetPanel;
