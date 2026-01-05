<?php
/**
 * Plugin Name: App Link Generator
 * Plugin URI: https://github.com/iyuya1808/App-Link-Generator
 * Description: AppStoreとGoogle Play Storeのインストールリンクを簡単に表示できるブロックエディタ対応プラグイン
 * Version: 1.1.0
 * Author: Technophere
 * Author URI: https://technophere.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: app-link-generator
 */

// 直接アクセスを防止
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ブロックの登録
 */
function app_store_links_register_block() {
    // block.jsonからブロックを登録
    register_block_type(__DIR__ . '/build');
}
add_action('init', 'app_store_links_register_block');

// Include Scraper & Manager
require_once plugin_dir_path(__FILE__) . 'includes/GooglePlayScraper.php';
require_once plugin_dir_path(__FILE__) . 'includes/AppStoreLinks_Manager.php';

// Initialize Manager
AppStoreLinks_Manager::init();

// Activation: Schedule Cron
register_activation_hook(__FILE__, function() {
    if (!wp_next_scheduled('app_store_links_daily_update')) {
        wp_schedule_event(time(), 'daily', 'app_store_links_daily_update');
    }
});

// Deactivation: Clear Cron
register_deactivation_hook(__FILE__, function() {
    $timestamp = wp_next_scheduled('app_store_links_daily_update');
    if ($timestamp) {
        wp_unschedule_event($timestamp, 'app_store_links_daily_update');
    }
});

// Register REST API
add_action('rest_api_init', function () {
    register_rest_route('app-store-links/v1', '/search', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {
            $term = $request->get_param('term');
            if (empty($term)) {
                return new WP_Error('no_term', 'Search term matches nothing', array('status' => 400));
            }

            $scraper = new GooglePlayScraper();
            $results = $scraper->search($term);

            return rest_ensure_response($results);
        }
    ));

    register_rest_route('app-store-links/v1', '/lookup', array(
        'methods' => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function ($request) {
            $id = $request->get_param('id');
            if (empty($id)) {
                return new WP_Error('no_id', 'App ID is required', array('status' => 400));
            }

            $scraper = new GooglePlayScraper();
            $result = $scraper->get_details($id);

            if (!$result) {
                return new WP_Error('not_found', 'App not found or scrape failed', array('status' => 404));
            }

            return rest_ensure_response($result);
        }
    ));
});
