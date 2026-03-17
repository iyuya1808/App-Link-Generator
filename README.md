=== App Link Generator ===
Contributors: iyuya0623
Tags: app store, google play, link, block
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily display App Store and Google Play Store app links in your WordPress posts with a beautiful, customizable block.

== Description ==

**App Link Generator** is a WordPress block plugin that allows you to easily add beautiful app store links to your posts and pages. Simply search for an app, and the plugin automatically fetches app information including icons, ratings, and download links.

= Features =

* **App Search**: Search for iOS and Android apps directly from the block editor
* **Automatic Data Fetching**: Automatically retrieves app name, icon, developer, price, rating, and review count
* **Dual Platform Support**: Display links for both App Store and Google Play Store
* **Customizable Display**: Choose which store links to show
* **Auto-Update**: Daily automatic updates of app information via cron job
* **Responsive Design**: Works beautifully on mobile, tablet, and desktop
* **Official Badges**: Uses official App Store and Google Play badges

= How It Works =

1. Add the "App Link Generator" block to your post or page
2. Search for an app by name
3. Select the app from search results
4. The block automatically displays app information with download links
5. Customize which store links to show (App Store, Google Play, or both)

= Technical Features =

* Uses iTunes Search API for iOS apps
* Web scraping for Google Play Store data
* 24-hour caching for fast performance
* Daily cron job for automatic updates
* REST API endpoints for app search and lookup
* Block API version 3 compatible

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/app-link-generator` directory, or install the plugin through the WordPress plugins screen directly
2. Activate the plugin through the 'Plugins' screen in WordPress
3. Add the "App Link Generator" block to any post or page
4. Search for apps and start displaying beautiful app links

== Frequently Asked Questions ==

= Does this plugin require any API keys? =

No, the plugin uses the public iTunes Search API for iOS apps and web scraping for Google Play Store. No API keys are required.

= How often is app information updated? =

App information is cached for 24 hours and automatically updated daily via WordPress cron job.

= Can I customize the appearance? =

The plugin uses a clean, AppReach-style design. You can customize the appearance using CSS in your theme.

= Which app stores are supported? =

Currently, the plugin supports Apple App Store (iOS) and Google Play Store (Android).

= Is the plugin translation ready? =

Yes, the plugin is translation ready with the text domain 'app-link-generator'.

== Screenshots ==

1. Block editor interface with app search
2. App link display on the frontend
3. Block settings in the sidebar

== Changelog ==

= 1.1.0 =
* Updated to Block API version 3
* Added unique prefixes to all functions and classes (applige_)
* Enhanced security with ABSPATH checks
* Updated REST API endpoints to /app-link-generator/v1/
* Improved code organization and WordPress.org compliance

= 1.0.0 =
* Initial release
* App search functionality for iOS and Android
* Automatic app information fetching
* Customizable display options
* Daily auto-update via cron job

== Upgrade Notice ==

= 1.1.0 =
This version includes important security enhancements and WordPress.org compliance updates. Please update to ensure compatibility with WordPress 7.0 and future versions.