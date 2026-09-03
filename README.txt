=== Santo do Dia ===

Contributors: fellipesoares
Donate link: https://fellipesoares.com.br/wp-santo-do-dia/
Tags: catholic, saint
Requires at least: 6.3
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 2.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display the Catholic saint of the day anywhere on your site with the [santododia] shortcode.

== Description ==

WP Santo do Dia displays the Catholic saint for the current day and month.

The plugin creates a small database table and updates it daily with information from https://catolicoapp.com. Add the `[santododia]` shortcode to a post, page, or widget area to display the saint's name and image.

== Installation ==

1. Upload the plugin files to the `wp-content/plugins/santo-do-dia` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the Plugins screen.
3. Add the `[santododia]` shortcode where the card should appear.
4. If the site receives no daily traffic, configure the system scheduler to trigger `wp-cron.php` at least once per day.

== Frequently Asked Questions ==

= Does the plugin add the saints automatically? =

Yes. No manual content management is required.

= How do I display the saint of the day? =

Add the `[santododia]` shortcode to any supported content area.

== Changelog ==

= 2.2.0 =
* Validated compatibility with WordPress 7.1 and PHP 7.4 or later.
* Fixed the daily update event and added automatic upgrades for existing installations.
* Strengthened API response validation and unavailable-data handling.
* Preserved plugin data on deactivation and moved destructive cleanup to uninstall.
* Added output escaping, safer external links, and HTTP request limits.
* Added automated tests and continuous integration.

= 2.1.0 =
* Improved performance and page-loading behavior.

= 2.0.9 =
* Updated the URL displayed in the saint card.

= 2.0.8 =
* Updated the API integration to CatolicoApp.

= 2.0.7 =
* Replaced direct file retrieval with an HTTP request.

= 2.0.6 =
* Fixed versioning issues.

= 2.0.5 =
* Fixed the stylesheet.

= 2.0.4 =
* Added a stylesheet for the shortcode card.

= 2.0.3 =
* Fixed API update scheduling.

= 2.0.2 =
* Added CSS classes to the image and title.

= 2.0.1 =
* Changed the verification frequency and avoided duplicate API requests.

= 2.0 =
* Replaced the custom post type with online API data.

= 1.1.1 =
* Fixed custom post type permalinks returning 404 pages.

= 1.1 =
* Added support for the first post image when no featured image exists.

= 1.0 =
* Initial release.
