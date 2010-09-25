=== Plugin Name ===
Contributors: leewillis77
Tags: e-commerce, shipping
Requires at least: 2.8
Tested up to: 3.0.1
Stable tag: 4.0

Shipping module for the WP E-Commerce system that offers weight based shipping to various destination types

== Description ==

This plugin provides two shipping modules for the WP E-Commerce system that offer shipping:
- By weight and continent
- By weight and country / region

Quotes can be calculated either by looking up the entire cart total weight against the configured weight bands, or alternatively
by looking up each individual item, and summing up the individual costs.

Both modules can be used at the same time - see (http://www.leewillis.co.uk/region-based-shipping-wp-e-commerce/)

This plugin also provides an online tool to allow you to split countries into whatever regions are relevant for your business.

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

* What is the difference between the various charging methods?
The plugin offers three different ways of mapping a customers order onto your weight bands. 

1. Single quote for total cart weight
The weight of the entire cart is calculated, and this weight is used to check against the configured weight bands for the customer's selected destination
2. Sum of quotes for individual items
Assumes that each item will be shipped individually. For each item in the cart, the weight is calculated for that product, and used to check against your configured weight bands. This shipping cost is multiplied by the quantity of that item that the user is buying. All of the prices are summed up to give the final customer cost.
3. Sum of quotes for consolidated items
Assumes that each item will be shipped in bundles. For each item in the cart, the consolidated weight is calculated for that product (According to the quantity being purchased), and that consolidated weight is used to check against your configured weight bands. All of the prices are summed up to give the final customer cost.

== Screenshots ==

1. Picking a region to configure
2. Setting weight bands per region

== Changelog ==

= 4.0 =
Calculate based on either the total cart weight, or individual product weights

= 3.0 =
Online tool to allow you to edit regions through the WordPress admin area. Courtest of Instinct and Sam @ Hotchkiss Consulting (http://hotchkissconsulting.net/)

= 2.0 =
Offer two modules - one based on country (Optionally including a region), and one based on continents. You can use both of these at once if required to give specific pricing for a certain country and regions, plus blanket pricing for the other continents

= 1.2 =
Compatibility with WP e-Commerce 3.7.6
Don't hardcode regions - pull them from {prefix}wpsc_currency_list

= 1.1 =
UI Bugfixes in the admin area

= 1.0 =
* Initial Release
