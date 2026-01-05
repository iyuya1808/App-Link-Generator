<?php

class AppStoreLinks_Manager {
    private static $option_name = 'app_store_links_registry';
    private static $cache_time = 86400; // 24 hours

    public static function init() {
        add_action('app_store_links_daily_update', array(__CLASS__, 'cron_update_all'));
    }

    /**
     * Get app data using attributes as fallback/key
     * If cached data exists, it merges/overrides attributes.
     */
    public static function get_app_data($attributes) {
        if (empty($attributes['trackId']) || empty($attributes['store'])) {
            return $attributes;
        }

        $id = $attributes['trackId'];
        $store = $attributes['store'];

        // Register if new (just in case it was missed during save, though save hook is hard for blocks)
        self::register_app($id, $store);

        // Check transient
        $transient_key = 'asl_' . $store . '_' . $id;
        $cached_data = get_transient($transient_key);

        if ($cached_data !== false) {
            // Merge cached data into attributes
            // We only override specific fields that are dynamic
            $overrides = array(
                'appName' => $cached_data['appName'],
                'iconUrl' => $cached_data['iconUrl'],
                'developer' => $cached_data['developer'],
                'price' => $cached_data['price'],
                'rating' => $cached_data['rating'],
                'reviewCount' => $cached_data['reviewCount'],
            );
            
            // For URLs, we generally trust the ID + Store construction, but cache might have them too.
            if (!empty($cached_data['appStoreUrl'])) {
                $overrides['appStoreUrl'] = $cached_data['appStoreUrl'];
            }
            if (!empty($cached_data['googlePlayUrl'])) {
                $overrides['googlePlayUrl'] = $cached_data['googlePlayUrl'];
            }
            if (!empty($cached_data['lastUpdated'])) {
                $overrides['lastUpdated'] = $cached_data['lastUpdated'];
            }

            return array_merge($attributes, $overrides);
        }

        // If no cache, return original attributes and trigger async update if needed?
        // Ideally we don't block render. We schedule an update or let cron handle it later.
        // For now, just return attributes.
        return $attributes;
    }

    /**
     * Add app to registry for Cron updates
     */
    public static function register_app($id, $store) {
        $registry = get_option(self::$option_name, array());
        $key = $store . ':' . $id;

        if (!isset($registry[$key])) {
            $registry[$key] = array(
                'id' => $id,
                'store' => $store,
                'added' => time()
            );
            update_option(self::$option_name, $registry);
            
            // Initial fetch if not cached
            if (get_transient('asl_' . $store . '_' . $id) === false) {
                // Determine which scraper to use
                self::fetch_and_cache($id, $store);
            }
        }
    }

    /**
     * Fetch data from source and update cache
     */
    public static function fetch_and_cache($id, $store) {
        $data = false;

        if ($store === 'ios') {
            if (!class_exists('AppStoreScraper')) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'includes/AppStoreScraper.php';
            }
            $scraper = new AppStoreScraper();
            $data = $scraper->get_details($id);

        } else if ($store === 'google_play') {
            if (!class_exists('GooglePlayScraper')) {
                require_once plugin_dir_path(dirname(__FILE__)) . 'includes/GooglePlayScraper.php';
            }
            $scraper = new GooglePlayScraper();
            $raw_data = $scraper->get_details($id);
            
            if ($raw_data) {
                // Map GoogleScraper format to standard block attributes format
                
                 // Format review count
                 $review_count_raw = isset($raw_data['userRatingCount']) ? $raw_data['userRatingCount'] : '';
                 // Google Play scraper currently returns raw string like "100" or already formatted?
                 // Let's check GooglePlayScraper implementation. 
                 // It returns 'userRatingCount' which is the text content of the review count element.
                 // It might be "1.2万 reviews" or just "1.2万". The scraper logic tries to get the text.
                 // We should assume it's display-ready or close to it.
                 // If the user modified the scraper earlier, it captures 'text content'.
                 
                $data = array(
                    'appName' => $raw_data['trackName'],
                    'iconUrl' => $raw_data['artworkUrl512'],
                    'developer' => $raw_data['artistName'],
                    'price' => isset($raw_data['formattedPrice']) ? $raw_data['formattedPrice'] : '', // Scraper might not return price yet?
                    'googlePlayUrl' => $raw_data['trackViewUrl'],
                    'rating' => isset($raw_data['averageUserRating']) ? $raw_data['averageUserRating'] : 0,
                    'reviewCount' => $review_count_raw
                );
            }
        }

        if ($data) {
            $data['lastUpdated'] = date('Y.m.d');
            $transient_key = 'asl_' . $store . '_' . $id;
            set_transient($transient_key, $data, self::$cache_time);
            return true;
        }

        return false;
    }

    /**
     * Cron Job callback
     */
    public static function cron_update_all() {
        $registry = get_option(self::$option_name, array());
        
        if (empty($registry)) return;

        foreach ($registry as $key => $item) {
            self::fetch_and_cache($item['id'], $item['store']);
            // Sleep slightly to respect rate limits if huge list?
            // usleep(200000); // 0.2s
        }
    }
}
