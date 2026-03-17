=== App Link Generator ===
Contributors: iyuya0623
Tags: app store, google play, mobile app, app link, block editor
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display App Store and Google Play Store installation links easily with block editor support.

== Description ==

App Link Generator is a WordPress plugin that allows you to easily display installation links for mobile apps from the App Store and Google Play Store. This plugin is fully compatible with the WordPress block editor (Gutenberg).

**Features:**

* Search and select apps from App Store and Google Play Store
* Display app information including icon, name, developer, price, and ratings
* Customizable display options for each store
* Automatic caching of app data for better performance
* Daily automatic updates of app information
* Block editor (Gutenberg) support

== External Services ==

This plugin connects to external services to retrieve app information. By using this plugin, you acknowledge and agree to the following:

**iTunes Search API (Apple Inc.)**

* **Purpose**: Used to search for iOS apps and retrieve app metadata (name, icon, price, ratings, etc.)
* **When data is sent**: When you search for an app in the block editor
* **Data sent**: Search term (app name or keyword)
* **Service provider**: Apple Inc.
* **Terms of Service**: https://www.apple.com/legal/internet-services/itunes/
* **Privacy Policy**: https://www.apple.com/legal/privacy/

**Google Play Store (Google LLC)**

* **Purpose**: Used to search for Android apps and retrieve app information
* **When data is sent**: When you search for an app in the block editor
* **Data sent**: Search term (app name or keyword)
* **How it works**: The plugin scrapes publicly available information from Google Play Store web pages
* **Service provider**: Google LLC
* **Terms of Service**: https://play.google.com/about/play-terms/
* **Privacy Policy**: https://policies.google.com/privacy

**Important Notes:**

* No personal user data is sent to these services
* Only search queries entered by the site administrator are transmitted
* App information is cached locally to minimize external requests
* The plugin does not track or collect any user behavior data

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/app-link-generator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the "App Store Links" block in the block editor to add app links to your posts or pages.

== Frequently Asked Questions ==

= How do I add an app link? =

1. In the block editor, click the "+" button to add a new block
2. Search for "App Store Links" or "App Link Generator"
3. Enter the app name in the search field
4. Select the app from the search results
5. The app information will be displayed automatically

= Can I customize the appearance? =

Yes, you can customize the appearance using CSS. The plugin uses the "appreach" class for styling.

= How often is app information updated? =

App information is automatically updated once daily. You can also manually refresh the data by re-selecting the app in the block editor.

= Does this plugin work with the classic editor? =

No, this plugin is designed for the block editor (Gutenberg) only.

== Screenshots ==

1. App search interface in the block editor
2. App information display on the frontend

== Changelog ==

= 1.1.0 =
* Localized badge images (removed external dependencies)
* Added proper documentation for external service usage
* Improved caching mechanism
* Bug fixes and performance improvements

= 1.0.0 =
* Initial release
* App Store and Google Play Store support
* Block editor integration
* Automatic caching and daily updates

== Upgrade Notice ==

= 1.1.0 =
This version removes external dependencies for badge images and adds proper documentation for external service usage, as required by WordPress.org plugin guidelines.
