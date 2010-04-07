=== Plugin Name ===
Contributors: leewillis77
Tags: e-commerce, shipping
Requires at least: 2.8
Tested up to: 2.9
Stable tag: 1.1

Shipping module for the WP E-Commerce system that offers a matrix of weight / region combinations.

== Description ==

Shipping module for the WP E-Commerce system that offers a matrix of weight / region combinations.

== Installation ==

*You Must* already have the following plugin installed:

1. [WP e-Commerce](http://wordpress.org/extend/plugins/wp-e-commerce/)

Support for the right hooks is only available in 3.7.6 beta 3 or newer of WP E-Commerce. If you need to use this on an earlier version you'll need to apply a small change to core WP E-Commerce. The line to add is [documented here](http://plugins.trac.wordpress.org/changeset/198151/wp-e-commerce/trunk/wp-shopping-cart.php)

2. Install the plugin

3. Make sure that the chosen shipping method is selected ( Products >> Settings >> Shipping ). Tick "Weight / Continent Shipping" and/or "Weight / Country and Region Shipping")

Configure weight rates for the areas you want to ship to.

Note: Your browser must support Javascript, and you must have it enabled to configure the shipping rates.

== Frequently Asked Questions ==

* I installed it, but nothing is showing up in my shipping settings?
Support for the right hooks is only available in 3.7.6 beta 3 or newer of WP E-Commerce. If you need to use this on an earlier version you'll need to apply a small change to core WP E-Commerce. The line to add is [documented here](http://plugins.trac.wordpress.org/changeset/198151/wp-e-commerce/trunk/wp-shopping-cart.php)

== Screenshots ==

1. Picking a region to configure
2. Setting weight bands per region

== Changelog ==

= 2.0 =
Offer two modules - one based on country (Optionally including a region), and one based on continents. You can use both of these at once if required to give specific pricing for a certain country and regions, plus blanket pricing for the other continents

= 1.2 =
Compatibility with WP e-Commerce 3.7.6
Don't hardcode regions - pull them from {prefix}wpsc_currency_list

= 1.1 =
UI Bugfixes in the admin area

= 1.0 =
* Initial Release
