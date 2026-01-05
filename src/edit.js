import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, ToggleControl, Button, Spinner, Placeholder, ExternalLink } from '@wordpress/components';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';

export default function Edit({ attributes, setAttributes }) {
    const { appName, iconUrl, developer, price, appStoreUrl, googlePlayUrl, showAppStore, showGooglePlay, rating, reviewCount, trackId, store, lastUpdated } = attributes;
    const [searchTerm, setSearchTerm] = useState('');
    const [isSearching, setIsSearching] = useState(false);
    const [searchResults, setSearchResults] = useState({ ios: [], android: [] });
    const [hasSearched, setHasSearched] = useState(false);

    const searchApps = async () => {
        if (!searchTerm) return;
        setIsSearching(true);
        setHasSearched(true);
        setSearchResults({ ios: [], android: [] });
        
        try {
            // Parallel search: iTunes and Google Play (via our REST API)
            const [itunesResponse, googleResponse] = await Promise.allSettled([
                fetch(`https://itunes.apple.com/search?term=${encodeURIComponent(searchTerm)}&entity=software&country=JP&limit=5`).then(r => r.json()),
                apiFetch({ path: `/app-store-links/v1/search?term=${encodeURIComponent(searchTerm)}` })
            ]);

            let newResults = { ios: [], android: [] };

            if (itunesResponse.status === 'fulfilled' && itunesResponse.value.results) {
                newResults.ios = itunesResponse.value.results.map(item => ({
                    ...item,
                    store: 'ios'
                }));
            }

            if (googleResponse.status === 'fulfilled' && Array.isArray(googleResponse.value)) {
                newResults.android = googleResponse.value;
            }

            setSearchResults(newResults);
        } catch (error) {
            console.error('Search failed:', error);
        } finally {
            setIsSearching(false);
        }
    };

    const selectApp = async (app) => {
        let reviewCount = app.userRatingCount || 0;
        
        // Format review count for iOS (iTunes API returns a number)
        if (app.store === 'ios' && typeof reviewCount === 'number') {
            if (reviewCount >= 10000) {
                reviewCount = (reviewCount / 10000).toFixed(1) + '万件';
            } else {
                reviewCount = reviewCount.toLocaleString() + '件';
            }
        }

        const now = new Date();
        const formattedDate = `${now.getFullYear()}.${String(now.getMonth() + 1).padStart(2, '0')}.${String(now.getDate()).padStart(2, '0')}`;

        let newAttributes = {
            appName: app.trackName,
            // Retain search result fallback
            iconUrl: app.artworkUrl512 || app.artworkUrl100, 
            developer: app.artistName,
            price: app.formattedPrice,
            rating: app.averageUserRating || 0,
            reviewCount: reviewCount,
            trackId: String(app.trackId),
            store: app.store,
            lastUpdated: formattedDate
        };

        if (app.store === 'ios') {
            newAttributes.appStoreUrl = app.trackViewUrl;
        } else if (app.store === 'google_play') {
            newAttributes.googlePlayUrl = app.trackViewUrl;
            
            // Fetch detailed info
            setIsSearching(true); 
            try {
                const details = await apiFetch({ path: `/app-store-links/v1/lookup?id=${encodeURIComponent(app.trackId)}` });
                if (details) {
                    newAttributes.appName = details.trackName || newAttributes.appName;
                    newAttributes.developer = details.artistName || newAttributes.developer;
                    newAttributes.iconUrl = details.artworkUrl512 || newAttributes.iconUrl;
                    newAttributes.rating = details.averageUserRating || newAttributes.rating;
                    newAttributes.reviewCount = details.userRatingCount || newAttributes.reviewCount;
                }
            } catch (err) {
                console.warn('Failed to fetch Google Play details:', err);
            } finally {
                setIsSearching(false);
            }
        }

        setAttributes(newAttributes);
    };

    const blockProps = useBlockProps();

    const renderAppItem = (app, index) => (
        <div 
            key={app.trackId || index} 
            onClick={() => selectApp(app)}
            className="app-search-item"
            style={{ 
                padding: '10px', 
                border: '1px solid #ddd', 
                borderRadius: '4px',
                marginBottom: '8px',
                cursor: 'pointer', 
                display: 'flex', 
                alignItems: 'center', 
                gap: '10px',
                background: '#fff'
            }}
        >
            <img 
                src={app.artworkUrl60 || app.artworkUrl512} 
                alt={app.trackName} 
                style={{ borderRadius: '10px', width: '50px', height: '50px', objectFit: 'cover' }} 
            />
            <div style={{ flex: 1, overflow: 'hidden' }}>
                <div style={{ fontWeight: 'bold', fontSize: '13px', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>{app.trackName}</div>
                <div style={{ fontSize: '11px', color: '#666' }}>{app.artistName || (app.store === 'google_play' ? 'Google Play' : '')}</div>
                <div style={{ fontSize: '11px', color: '#666' }}>{app.formattedPrice || '無料'}</div>
            </div>
        </div>
    );

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('アプリ詳細', 'app-store-links')}>
                    <TextControl
                        label={__('アプリ名', 'app-store-links')}
                        value={appName}
                        onChange={(value) => setAttributes({ appName: value })}
                    />
                    <TextControl
                        label={__('アイコンURL', 'app-store-links')}
                        value={iconUrl}
                        onChange={(value) => setAttributes({ iconUrl: value })}
                    />
                    <TextControl
                        label={__('開発者', 'app-store-links')}
                        value={developer}
                        onChange={(value) => setAttributes({ developer: value })}
                    />
                    <TextControl
                        label={__('価格', 'app-store-links')}
                        value={price}
                        onChange={(value) => setAttributes({ price: value })}
                    />
                    <TextControl
                        label={__('評価 (0-5)', 'app-store-links')}
                        value={rating}
                        type="number"
                        step="0.1"
                        min="0"
                        max="5"
                        onChange={(value) => setAttributes({ rating: parseFloat(value) })}
                    />
                    <TextControl
                        label={__('レビュー数', 'app-store-links')}
                        value={reviewCount}
                        onChange={(value) => setAttributes({ reviewCount: value })}
                    />
                </PanelBody>
                <PanelBody title={__('ストアリンク', 'app-store-links')}>
                    <TextControl
                        label={__('App Store URL', 'app-store-links')}
                        value={appStoreUrl}
                        onChange={(value) => setAttributes({ appStoreUrl: value })}
                    />
                    <ToggleControl
                        label={__('App Storeリンクを表示', 'app-store-links')}
                        checked={showAppStore}
                        onChange={(value) => setAttributes({ showAppStore: value })}
                    />
                    <TextControl
                        label={__('Google Play URL', 'app-store-links')}
                        value={googlePlayUrl}
                        onChange={(value) => setAttributes({ googlePlayUrl: value })}
                    />
                    <ToggleControl
                        label={__('Google Playリンクを表示', 'app-store-links')}
                        checked={showGooglePlay}
                        onChange={(value) => setAttributes({ showGooglePlay: value })}
                    />
                </PanelBody>
            </InspectorControls>

            {/* Search Interface */}
            <div className="app-store-links-editor-search" style={{ marginBottom: '20px', padding: '15px', border: '1px solid #ddd', background: '#f9f9f9' }}>
                <div style={{ display: 'flex', gap: '10px', alignItems: 'center', marginBottom: '15px' }}>
                    <TextControl
                        value={searchTerm}
                        onChange={setSearchTerm}
                        placeholder={__('アプリを検索...', 'app-store-links')}
                        className="app-search-input"
                        style={{ marginBottom: 0, flex: 1 }}
                    />
                    <Button variant="primary" onClick={searchApps} isBusy={isSearching} disabled={isSearching || !searchTerm}>
                        {__('検索', 'app-store-links')}
                    </Button>
                </div>
                
                {hasSearched && (
                    <div className="app-search-results-columns" style={{ display: 'flex', gap: '20px', flexWrap: 'wrap' }}>
                        {/* iOS Column */}
                        <div style={{ flex: '1 1 300px' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '10px', color: '#666', borderBottom: '1px solid #eee', paddingBottom: '5px' }}>
                                <span className="dashicons dashicons-smartphone" style={{ color: '#ff6b6b' }}></span>
                                <span>{__('iPhoneアプリ検索結果', 'app-store-links')}</span>
                            </div>
                            <div style={{ maxHeight: '400px', overflowY: 'auto' }}>
                                {searchResults.ios.length > 0 ? (
                                    searchResults.ios.map((app, index) => renderAppItem(app, index))
                                ) : (
                                    <div style={{ color: '#999', fontSize: '12px', padding: '10px' }}>{__('見つかりませんでした。', 'app-store-links')}</div>
                                )}
                            </div>
                        </div>

                        {/* Android Column */}
                        <div style={{ flex: '1 1 300px' }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: '8px', marginBottom: '10px', color: '#666', borderBottom: '1px solid #eee', paddingBottom: '5px' }}>
                                <span className="dashicons dashicons-tablet" style={{ color: '#4da6ff' }}></span>
                                <span>{__('Androidアプリ検索結果', 'app-store-links')}</span>
                            </div>
                            <div style={{ maxHeight: '400px', overflowY: 'auto' }}>
                                {searchResults.android.length > 0 ? (
                                    searchResults.android.map((app, index) => renderAppItem(app, index))
                                ) : (
                                    <div style={{ color: '#999', fontSize: '12px', padding: '10px' }}>{__('見つかりませんでした。', 'app-store-links')}</div>
                                )}
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* Preview (AppReach Style) */}
            {appName ? (
                <div className="appreach">
                    {iconUrl && (
                        <img 
                            src={iconUrl} 
                            alt={appName} 
                            className="appreach__icon" 
                            style={{ 
                                float: 'left',
                                borderRadius: '10%',
                                overflow: 'hidden',
                                margin: '0 3% 0 0',
                                width: '25%',
                                height: 'auto',
                                maxWidth: '120px'
                            }} 
                        />
                    )}
                    <div className="appreach__detail" style={{ display: 'inline-block', width: '72%', maxWidth: '72%' }}>
                        <p className="appreach__name" style={{ fontSize: '16px', lineHeight: '1.5em', fontWeight: 'bold', margin: 0 }}>{appName}</p>
                        <p className="appreach__info" style={{ fontSize: '12px', margin: 0 }}>
                            <span className="appreach__developper" style={{ marginRight: '0.5em' }}>{developer}</span>
                            <span className="appreach__price" style={{ marginRight: '0.5em' }}>{price}</span>
                            {rating > 0 && (
                                <span className="appreach__star">
                                    <span className="appreach__star__base">★★★★★</span>
                                    <span className="appreach__star__evaluate" style={{ width: `${rating * 20}%` }}>★★★★★</span>
                                </span>
                            )}
                            {reviewCount && (
                                <span style={{ marginLeft: '5px', color: '#666' }}>({reviewCount})</span>
                            )}
                            <span className="appreach__posted" style={{ display: 'block', marginTop: '2px' }}>
                                posted with <a href="https://technophere.com" title="テクノフィア" target="_blank" rel="nofollow">テクノフィア</a>
                            </span>
                        </p>
                    </div>
                    <div className="appreach__links" style={{ clear: 'both', marginTop: '8px' }}>
                        {showAppStore && appStoreUrl && (
                            <a href={appStoreUrl} rel="nofollow" className="appreach__aslink" style={{ display: 'inline-block', marginRight: '10px' }}>
                                <img src="https://nabettu.github.io/appreach/img/itune_ja.svg" style={{ height: '40px', width: '135px' }} />
                            </a>
                        )}
                        {showGooglePlay && googlePlayUrl && (
                            <a href={googlePlayUrl} rel="nofollow" className="appreach__gplink" style={{ display: 'inline-block' }}>
                                <img src="https://nabettu.github.io/appreach/img/gplay_ja.png" style={{ height: '40px', width: '134.5px' }} />
                            </a>
                        )}
                    </div>
                    {lastUpdated && (
                        <div className="appreach__date">{lastUpdated}時点</div>
                    )}
                </div>
            ) : (
                <Placeholder icon="smartphone" label="App Store Links">
                    <p>{__('アプリを検索して詳細を表示してください。', 'app-store-links')}</p>
                </Placeholder>
            )}
        </div>
    );
}
