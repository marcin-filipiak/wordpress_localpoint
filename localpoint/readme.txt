=== LocalPoint ===
Contributors: marcinfilipiak
Tags: map, business location, opening hours, contact info, OpenStreetMap
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display your business location, opening hours and contact info using OpenStreetMap and Leaflet.js.

== Description ==

LocalPoint is a simple plugin that helps you show your business location on a map powered by OpenStreetMap. It also displays your contact details and opening hours in a clear format.

Features include:

    📍 Interactive map with marker showing your location

    🕒 Opening hours table with translations and support for closed days

    📞 Contact info: phone, email, address, and optional note

    Fully translatable using WordPress localization functions

== Installation ==

    Upload the plugin folder to the /wp-content/plugins/ directory or install it via the WordPress Plugin Directory.

    Activate the plugin through the "Plugins" menu in WordPress.

    Add your business data (location, hours, contact) in the data/config.json file.

    Use the shortcode [localpoint] on any post or page to display the map and info.

== Frequently Asked Questions ==

= Can I translate the day names and labels? =
Yes, the plugin supports translations. You can provide your own translations via .po files in the /languages folder.

= How do I change the location shown on the map? =
Edit the latitude and longitude values in the data/config.json file.

= Can I customize the opening hours format? =
Currently, the plugin reads hours from the JSON file and displays them in a table. You can edit the JSON file structure but should keep keys consistent.


== Changelog ==

= 1.0 =

    Initial release with map display, contact info, and opening hours.

    Uses OpenStreetMap and Leaflet.js for interactive map.

    Supports translation and localization.

== Upgrade Notice ==

= 1.0 =
First public version of LocalPoint plugin.

== License ==

This plugin is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License v2 or later.
