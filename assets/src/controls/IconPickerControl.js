/**
 * Icon Picker Control - Visual icon library browser
 *
 * High-UX component for selecting icons with:
 * - Searchable icon library
 * - Categories (UI, social, arrows, etc.)
 * - Favorites
 * - Recent used
 * - Size and color controls
 */

import { useState, useMemo, useEffect } from '@wordpress/element';
import {
    BaseControl,
    Button,
    SearchControl,
    Popover,
    ButtonGroup,
    TabPanel,
    __experimentalGrid as Grid,
    Tooltip,
    ColorPicker,
    RangeControl,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { starFilled, starEmpty, search } from '@wordpress/icons';

// Icon libraries data (would be loaded from JSON in production)
const ICON_LIBRARIES = {
    wordpress: {
        name: 'WordPress',
        prefix: '',
        icons: [
            'heart', 'star-filled', 'star-empty', 'home', 'admin-site',
            'admin-page', 'admin-post', 'admin-media', 'admin-links',
            'admin-comments', 'admin-appearance', 'admin-plugins',
            'admin-users', 'admin-tools', 'admin-settings', 'admin-network',
            'admin-generic', 'admin-home', 'arrow-up', 'arrow-down',
            'arrow-left', 'arrow-right', 'arrow-up-alt', 'arrow-down-alt',
            'arrow-left-alt', 'arrow-right-alt', 'arrow-up-alt2',
            'arrow-down-alt2', 'arrow-left-alt2', 'arrow-right-alt2',
        ],
    },
    social: {
        name: 'Social',
        prefix: '',
        icons: [
            'facebook', 'twitter', 'instagram', 'linkedin', 'youtube',
            'tiktok', 'pinterest', 'whatsapp', 'telegram', 'reddit',
            'tumblr', 'snapchat', 'twitch', 'discord', 'slack',
        ],
    },
    ecommerce: {
        name: 'Ecommerce',
        prefix: '',
        icons: [
            'cart', 'money-alt', 'payment', 'shipping', 'rewards',
            'coupon', 'tag', 'percent', 'store', 'product',
            'inventory', 'package', 'truck', 'receipt', 'credit-card',
        ],
    },
    communication: {
        name: 'Communication',
        prefix: '',
        icons: [
            'email', 'phone', 'chat', 'feedback', 'megaphone',
            'share', 'share-alt', 'share-alt2', 'embed-audio',
            'embed-video', 'embed-photo', 'embed-generic',
        ],
    },
    actions: {
        name: 'Actions',
        prefix: '',
        icons: [
            'plus', 'minus', 'plus-alt', 'minus-alt', 'plus-alt2',
            'minus-alt2', 'yes', 'no', 'no-alt', 'update',
            'edit', 'trash', 'trash-undo', 'visibility', 'hidden',
            'lock', 'unlock', 'external', 'move', 'randomize',
            'grid-view', 'list-view', 'excerpt-view', 'duplicate',
        ],
    },
};

const RECENT_ICONS_KEY = 'jankx_recent_icons';
const FAVORITE_ICONS_KEY = 'jankx_favorite_icons';

const IconPickerControl = ({
    label,
    value = {},
    onChange,
    allowColor = true,
    allowSize = true,
}) => {
    const [isOpen, setIsOpen] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');
    const [activeTab, setActiveTab] = useState('wordpress');
    const [recentIcons, setRecentIcons] = useState([]);
    const [favoriteIcons, setFavoriteIcons] = useState([]);
    const [showColorPicker, setShowColorPicker] = useState(false);

    // Load recent and favorites from localStorage
    useEffect(() => {
        const recent = localStorage.getItem(RECENT_ICONS_KEY);
        const favorites = localStorage.getItem(FAVORITE_ICONS_KEY);
        if (recent) setRecentIcons(JSON.parse(recent));
        if (favorites) setFavoriteIcons(JSON.parse(favorites));
    }, []);

    // Save recent icon
    const saveRecentIcon = (icon) => {
        const updated = [icon, ...recentIcons.filter(i => i !== icon)].slice(0, 12);
        setRecentIcons(updated);
        localStorage.setItem(RECENT_ICONS_KEY, JSON.stringify(updated));
    };

    // Toggle favorite
    const toggleFavorite = (icon, e) => {
        e.stopPropagation();
        const isFav = favoriteIcons.includes(icon);
        const updated = isFav
            ? favoriteIcons.filter(i => i !== icon)
            : [...favoriteIcons, icon];
        setFavoriteIcons(updated);
        localStorage.setItem(FAVORITE_ICONS_KEY, JSON.stringify(updated));
    };

    // Filter icons by search
    const filteredIcons = useMemo(() => {
        if (!searchQuery) return null;

        const query = searchQuery.toLowerCase();
        const results = [];

        Object.entries(ICON_LIBRARIES).forEach(([lib, data]) => {
            data.icons.forEach(icon => {
                if (icon.toLowerCase().includes(query)) {
                    results.push({ library: lib, icon });
                }
            });
        });

        return results;
    }, [searchQuery]);

    // Current library icons
    const currentLibrary = ICON_LIBRARIES[activeTab];

    // Handle icon selection
    const selectIcon = (icon, library) => {
        onChange({
            ...value,
            icon,
            library,
        });
        saveRecentIcon(icon);
        setIsOpen(false);
        setSearchQuery('');
    };

    // Clear icon
    const clearIcon = () => {
        onChange({
            ...value,
            icon: '',
            library: '',
        });
    };

    // Icon button component
    const IconButton = ({ icon, library, isSelected, showFavorite = true }) => {
        const isFav = favoriteIcons.includes(icon);
        const Icon = icon; // In real implementation, this would be a dynamic icon component

        return (
            <Tooltip text={icon}>
                <div className={`jankx-icon-item ${isSelected ? 'is-selected' : ''}`}>
                    <Button
                        variant="secondary"
                        className="jankx-icon-btn"
                        onClick={() => selectIcon(icon, library)}
                    >
                        <span className="dashicons dashicons-{icon}"></span>
                        {/* Use actual icon component in production */}
                        <span className="jankx-icon-symbol">{icon.charAt(0).toUpperCase()}</span>
                    </Button>
                    {showFavorite && (
                        <Button
                            icon={isFav ? starFilled : starEmpty}
                            className={`jankx-icon-fav ${isFav ? 'is-favorite' : ''}`}
                            onClick={(e) => toggleFavorite(icon, e)}
                            size="small"
                        />
                    )}
                </div>
            </Tooltip>
        );
    };

    return (
        <BaseControl label={label} className="jankx-icon-picker">
            {/* Selected icon preview */}
            <div className="jankx-icon-preview">
                {value.icon ? (
                    <>
                        <div
                            className="jankx-icon-display"
                            style={{
                                color: value.color || 'currentColor',
                                fontSize: value.size || '24px',
                            }}
                        >
                            <span className="dashicons dashicons-{value.icon}"></span>
                            <span className="jankx-icon-symbol">{value.icon.charAt(0).toUpperCase()}</span>
                        </div>
                        <div className="jankx-icon-info">
                            <span>{value.icon}</span>
                            <Button
                                variant="link"
                                onClick={clearIcon}
                                isDestructive
                                size="small"
                            >
                                {__('Remove', 'jankx')}
                            </Button>
                        </div>
                    </>
                ) : (
                    <span className="jankx-icon-placeholder">
                        {__('No icon selected', 'jankx')}
                    </span>
                )}

                <Button
                    variant="primary"
                    onClick={() => setIsOpen(true)}
                    className="jankx-icon-browse"
                >
                    {value.icon ? __('Change Icon', 'jankx') : __('Select Icon', 'jankx')}
                </Button>
            </div>

            {/* Icon picker popover */}
            {isOpen && (
                <Popover
                    position="bottom center"
                    onClose={() => setIsOpen(false)}
                    className="jankx-icon-popover"
                >
                    <div className="jankx-icon-picker-content">
                        {/* Search */}
                        <SearchControl
                            value={searchQuery}
                            onChange={setSearchQuery}
                            placeholder={__('Search icons...', 'jankx')}
                        />

                        {/* Color and size controls */}
                        {(allowColor || allowSize) && (
                            <div className="jankx-icon-customize">
                                {allowColor && (
                                    <div className="jankx-icon-color">
                                        <Button
                                            onClick={() => setShowColorPicker(!showColorPicker)}
                                            style={{
                                                backgroundColor: value.color || '#000',
                                                width: '30px',
                                                height: '30px',
                                                borderRadius: '4px',
                                            }}
                                        />
                                        {showColorPicker && (
                                            <Popover onClose={() => setShowColorPicker(false)}>
                                                <ColorPicker
                                                    color={value.color}
                                                    onChange={(color) => onChange({ ...value, color })}
                                                />
                                            </Popover>
                                        )}
                                    </div>
                                )}
                                {allowSize && (
                                    <RangeControl
                                        label={__('Size', 'jankx')}
                                        value={parseInt(value.size) || 24}
                                        onChange={(size) => onChange({ ...value, size: `${size}px` })}
                                        min={12}
                                        max={96}
                                        step={4}
                                    />
                                )}
                            </div>
                        )}

                        {/* Search results or tabbed library */}
                        {searchQuery ? (
                            <div className="jankx-icon-search-results">
                                <Grid columns={6} gap={2}>
                                    {filteredIcons?.map(({ library, icon }) => (
                                        <IconButton
                                            key={`${library}-${icon}`}
                                            icon={icon}
                                            library={library}
                                            isSelected={value.icon === icon}
                                        />
                                    ))}
                                </Grid>
                                {filteredIcons?.length === 0 && (
                                    <p className="jankx-no-results">
                                        {__('No icons found', 'jankx')}
                                    </p>
                                )}
                            </div>
                        ) : (
                            <TabPanel
                                className="jankx-icon-tabs"
                                activeClass="is-active"
                                onSelect={setActiveTab}
                                tabs={[
                                    { name: 'recent', title: __('Recent', 'jankx') },
                                    { name: 'favorites', title: __('Favorites', 'jankx') },
                                    ...Object.entries(ICON_LIBRARIES).map(([key, data]) => ({
                                        name: key,
                                        title: data.name,
                                    })),
                                ]}
                            >
                                {(tab) => {
                                    // Recent tab
                                    if (tab.name === 'recent') {
                                        return recentIcons.length > 0 ? (
                                            <Grid columns={6} gap={2}>
                                                {recentIcons.map((icon) => (
                                                    <IconButton
                                                        key={icon}
                                                        icon={icon}
                                                        library="wordpress"
                                                        isSelected={value.icon === icon}
                                                        showFavorite={false}
                                                    />
                                                ))}
                                            </Grid>
                                        ) : (
                                            <p className="jankx-empty-state">
                                                {__('No recent icons', 'jankx')}
                                            </p>
                                        );
                                    }

                                    // Favorites tab
                                    if (tab.name === 'favorites') {
                                        return favoriteIcons.length > 0 ? (
                                            <Grid columns={6} gap={2}>
                                                {favoriteIcons.map((icon) => (
                                                    <IconButton
                                                        key={icon}
                                                        icon={icon}
                                                        library="wordpress"
                                                        isSelected={value.icon === icon}
                                                        showFavorite={true}
                                                    />
                                                ))}
                                            </Grid>
                                        ) : (
                                            <p className="jankx-empty-state">
                                                {__('No favorite icons. Click the star to add favorites.', 'jankx')}
                                            </p>
                                        );
                                    }

                                    // Library tabs
                                    const library = ICON_LIBRARIES[tab.name];
                                    if (library) {
                                        return (
                                            <Grid columns={6} gap={2}>
                                                {library.icons.map((icon) => (
                                                    <IconButton
                                                        key={icon}
                                                        icon={icon}
                                                        library={tab.name}
                                                        isSelected={value.icon === icon}
                                                    />
                                                ))}
                                            </Grid>
                                        );
                                    }

                                    return null;
                                }}
                            </TabPanel>
                        )}
                    </div>
                </Popover>
            )}
        </BaseControl>
    );
};

export default IconPickerControl;
