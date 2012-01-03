=== Plugin Name ===
Contributors: fcc
Donate link: 
Tags: search, faceted search, .gov, gov, open gov, refine, widget
Requires at least: 3.1
Tested up to: 3.4
Stable tag: 1.5

Sidebar Widget to allow filtering indexes by builtin and custom taxonomies.

== Description ==

Allows filtering of any search results of archive (tag, category, year, month, etc.) by any built in (category, tag) or custom taxonomies.

Places clickable list of taxonomies and terms in sidebar widget with count of posts for each. Allows users to query by multiple taxonomies.

Used for internal FCC project and open sourced July, 2011.

== Installation ==

1. Upload `faceted-search-widget.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Drag and drop widget into sidebar

== Changelog ==

= 1.6 =
* Option to specify maximum depth for hierarchical taxonomies
* Option to sort by number of posts (rather than alphabetically)
* Optional Ajax loading of results with `history.pushState()` where supported
* Better internationalization support

= 1.5 =
* Fixed bug where plugin would generate MySQL errors if taxonomy had large number of terms (scalability)
* Plugin now supports faceted search within the children of a hierarchical taxonomy term

= 1.4 =
* Fixed bug where post count would not display properly when browsing within a facet
* More robust handling of queries when filtering by multiple taxonomies

= 1.3 =
* Added support for hierarchical taxonomies
* Now relies on wp_list_categories

= 1.2 =
* Added support for internationalization of number formatting

= 1.1 =
* Now uses the [Widget API](http://codex.wordpress.org/Widgets_API)
* Ability to specify widget title
* Removed trailing whitespace from file

= 1.0 =
* Initial Release
