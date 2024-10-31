=== OPR Place Reservations ===
Contributors: orwokki
Tags: reservations
Requires at least: 5.4
Tested up to: 6.7
Stable tag: 1.1.14
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin for showing and handling rentable places and their reservations

== Description ==

With this plugin you can show list of available places for renting and get reservations for those places.

Reservations can be created from client-side UI or from WP admin. When place is reserved then it will no longer be available for new reservations,
before previous reservation has been removed.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/placereservation` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Reservations->Types & locations screen to configure the plugin
1. Add shortcode [show-available-places] to page where you want show list of available places and the reservation form
1. Add attribute filtering="true" to the shortcode tag to show form for filtering the places list

== Screenshots ==

1. List of available rentable places
1. Reservation form at client-side
1. Admin UI 1
1. Admin UI 2
1. Admin UI 3
1. Admin UI 4

== Changelog ==

= 1.1.14 =
* Fixing version numbering in SVN repo

= 1.1.13 =
* Tested to work with WordPress 6.6
* Removed support for PHP 7.4

= 1.1.12 =
* Small bugfix to adding types and locations
* Tested to work with WordPress 6.3

= 1.1.11 =
* Bugfixes to problems with removing reservations and places

= 1.1.10 =
* More bugfixes for usage with PHP 8.1

= 1.1.9 =
* Tested for working with WordPress 6.1
* Bugfixes for PHP 8.1

= 1.1.8 =
* Tested for working with WordPress 6.0

= 1.1.7 =
* Tested for working with WordPress 5.8

= 1.1.6 =
* Tested for working with WordPress 5.7
 
= 1.1.5 =
* Ability to select which fields in add new reservation forms are mandatory

= 1.1.4 =
* Bug fixes

= 1.1.3 =
* Bug fixes

= 1.1.2 =
* Performance tweaks
* Filtering of reservations in admin
* Paging of reservations and places in admin
* Loading indicators to importer

= 1.1.1 =
* Critical bug fix to deployment

= 1.1.0 =
* Implemented new roles and capablities that can administrate reservation related things, but can't otherwise administrate WP
* Implemented data import functionalities
* Bug fixes

= 1.0.6 =
* Bug fixes

= 1.0.5 =
* Implemented remove of reservations and places
* Bug fixes

= 1.0.4 =
* Added TablePress CSS classes to customer view places listing table
* Bug fixes

= 1.0.3 =
* Implemented function to choose which fields are shown at the customer view
* Implemented function for setting order by field at the customer view places list
* Implemented email notification about a new reservation made
* Bug fixes

= 1.0.2 =
* Security fix: sanitation, validation and escaping data

= 1.0.1 =
* Bug fix to database table creation when activating plugin.

= 1.0 =
* Initial production version.

== Upgrade Notice ==

= 1.1.1 =
Critical bug fix to plugin deployment

= 1.0.2 =
Security fix

= 1.0 =
Initial version
