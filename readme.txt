=== Plugin Name ===
Contributors: leewillis77
Donate link: http://plugins.leewillis.co.uk/donate/
Tags: e-commerce, shipping
Requires at least: 3.5
Tested up to: 4.3
Stable tag: 5.0
License: GPLv2

Shipping module for the WP E-Commerce system that offers weight based shipping to various destination types.

== Description ==

This plugin provides two shipping modules for the WP E-Commerce system that offer shipping:

* By weight and continent
* By weight and country / region

Quotes can be calculated either by looking up the entire cart total weight against the configured weight bands, or alternatively
by looking up each individual item, and summing up the individual costs.

Both modules can be used at the same time - see (http://www.leewillis.co.uk/region-based-shipping-wp-e-commerce/)

This plugin also provides an online tool to allow you to split countries into whatever regions are relevant for your business.

**Note:** This plugin requires WP e-Commerce 3.8.12 or above

== Installation ==

*You Must* already have the following plugin installed:

1. [WP e-Commerce](http://wordpress.org/extend/plugins/wp-e-commerce/) - This plugin requires WP e-Commerce v3.8.12 or above.
1. Install the plugin
1. Make sure that the chosen shipping method is selected ( Products >> Settings >> Shipping ). Tick "Weight / Continent Shipping" and/or "Weight / Country and Region Shipping")

Configure weight rates for the areas you want to ship to.

Note: Your browser must support Javascript, and you must have it enabled to configure the shipping rates.

== Frequently Asked Questions ==

* How do I get support for this plugin?

If you're hitting an error (PHP warning or fatal error displayed) then please get in touch and let me know. The best way to get in touch is through my [contact form](http://plugins.leewillis.co.uk/contact/). I can't guarantee any timelines for a fix though as all work on this plugin is undertaken in my spare time. Please note - **I do not monitor the support forms on wordpress.org**

* How do I do xxxxxxxx?

Unfortunately I can't provide support or consultancy on how to use my free plugins unless it's on a paid for basis. If you need support - please [get in touch](http://plugins.leewillis.co.uk/contact/) and I'll be happy to provide a quote.

* The weight rates aren't getting calculated / don't change when I add more products?

Make sure you have a weight rate for "0 and above". Also ensure that you've entered shipping weights in pounds (lbs), regardless of what units you've set your products up in.

* What is the difference between the various charging methods?
The plugin offers three different ways of mapping a customers order onto your weight bands.

**Single quote for total cart weight**

The weight of the entire cart is calculated, and this weight is used to check against the configured weight bands for the customer's selected destination

**Sum of quotes for individual items**

Assumes that each item will be shipped individually. For each item in the cart, the weight is calculated for that product, and used to check against your configured weight bands. This shipping cost is multiplied by the quantity of that item that the user is buying. All of the prices are summed up to give the final customer cost.

**Sum of quotes for consolidated items**

Assumes that each item will be shipped in bundles. For each item in the cart, the consolidated weight is calculated for that product (According to the quantity being purchased), and that consolidated weight is used to check against your configured weight bands. All of the prices are summed up to give the final customer cost.

== Screenshots ==

1. Picking a region to configure
2. Setting weight bands per region

== Changelog ==

= 5.0 =
Update to work with latest versions of WP e-Commerce

= 4.5.1 =
Remove erroneous return statement avoiding a PHP warning

= 4.5 =
Compatibility with WP e-Commerce 3.8.8. Updates kindly sponsored by Jamie at New Vision Media (http://www.newvisionmedia.co.uk)

== Upgrade Notice ==

= 5.0 =
Version 5.0 *only* works with WP e-Commerce 3.8.12 and above. Do not upgrade unless you are running an appropriate version of WP e-Commerce