<?php
/*
 Plugin Name: WP E-Commerce Weight & Destination Shipping Modules
 Plugin URI: http://www.leewillis.co.uk/wordpress-plugins/
 Description: Shipping Modules For WP E-Commerce bases prices on region (Continent, or country / region) and weight bands
 Version: 4.2
 Author: Lee Willis
 Author URI: http://www.leewillis.co.uk/
*/

/*
 This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.
 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */



/* This first module is confusingly named. It's actually weight / continent shipping */

require_once("wp-e-commerce-weight-continent-shipping.php");

function ses_weightregion_shipping_add($wpsc_shipping_modules) {

	global $ses_weightregion_shipping;
	$ses_weightregion_shipping = new ses_weightregion_shipping();

	$wpsc_shipping_modules[$ses_weightregion_shipping->getInternalName()] = $ses_weightregion_shipping;

	return $wpsc_shipping_modules;
}
	



/* Weight / Country & Region Shipping */

require_once("wp-e-commerce-weight-countryregion-shipping.php");

function ses_weightcountryregion_shipping_add($wpsc_shipping_modules) {

	global $ses_weightcountryregion_shipping;
	$ses_weightcountryregion_shipping = new ses_weightcountryregion_shipping();

	$wpsc_shipping_modules[$ses_weightcountryregion_shipping->getInternalName()] = $ses_weightcountryregion_shipping;

	return $wpsc_shipping_modules;
}




add_filter('wpsc_shipping_modules', 'ses_weightcountryregion_shipping_add');
add_filter('wpsc_shipping_modules', 'ses_weightregion_shipping_add');
add_action('wp_ajax_ses-weightregion-layers',array(&$ses_weightregion_shipping,"show_layers_form"));
add_action('wp_ajax_ses-weightcountryregion-layers',array(&$ses_weightcountryregion_shipping,"show_layers_form"));
add_action('wp_ajax_ses-weightregion-quote-method',array(&$ses_weightregion_shipping,"save_quote_method"));
add_action('wp_ajax_ses-weightcountryregion-quote-method',array(&$ses_weightcountryregion_shipping,"save_quote_method"));

if (is_admin()) {
	require_once ('wp-e-commerce-weight-region-region-manager.php');
}

?>
