=== Bulk Convert Post Format ===
Contributors: razorfrog
Donate link: https://razorfrog.com/
Tags: post formats, bulk convert
Requires at least: 3.1
Tested up to: 6.5.3
Stable tag: 1.1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Bulk convert posts in a category to a selected post format.

== Description ==

Bulk convert posts in a category to a selected post format. Select from a dropdown of categories and a dropdown of defined post formats.

== Installation ==

1. Upload the `bulk-convert-post-format` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure and run the tool under Tools > Bulk Edit Post Format

== Frequently Asked Questions ==

= Can I convert custom post types or custom taxonomies? =

Our plugin currently supports only standard post types and the standard category taxonomy.

= Can I convert only some posts and not an entire category? =

Not at this time. An easy workaround would be to create a temporary category to select the specific posts you want to convert. After the conversion, you can delete the temporary category.

== Screenshots ==

1. Choose your category, post format, and submit
2. Location in the Tools menu

== Changelog ==

1.1.4

* Enhanced category dropdown to display terms hierarchically along with their post count.
* Improved post query to exclude posts in child categories, ensuring accurate conversions.
* Refined text in progress and completion pages for better clarity and user experience.
* WP Core compatibility update

1.1.3

* Fixed undefined array keys
* WP Core compatibility update

1.1.2

* WP Core compatibility update

1.1.1

* WP Core compatibility update

1.1.0

* UI improvements
* WP Core compatibility update

1.0.2

* WP Core compatibility update

1.0.1

* Properly enqueued javascript

1.0.0

* Initial plugin creation
