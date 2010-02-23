<?php
/*
 Plugin Name: WP E-Commerce Weight & Destination Shipping Module
 Plugin URI: http://www.leewillis.co.uk/wordpress-plugins/
 Description: Shipping Module For WP E-Commerce bases prices on region and weight bands
 Version: 1.0
 Author: Lee Willis
 Author URI: http://www.leewillis.co.uk/
*/

/*
 This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

class ses_weightregion_shipping {

	var $internal_name;
	var $name;
	var $is_external;

	var $region_list = Array('local' => 'Local',
	                         'northamerica' => 'North America',
	                         'southamerica' => 'South America',
				 'asiapacific' => 'Asia and Pacific',
	                         'europe' => 'Europe',
	                         'africa' => 'Africa');

	function ses_weightregion_shipping () {

		$this->internal_name = "ses_weightregion_shipping";
		$this->name = "Weight / Region Shipping";
		$this->is_external = FALSE;
		return true;
	}
	
	/* You must always supply this */
	function getName() {
		return $this->name;
	}
	
	/* You must always supply this */
	function getInternalName() {
		return $this->internal_name;
	}
	
	function show_layers_form() {

		$shipping = get_option($this->getInternalName().'_options');
		
		if (!isset($_GET['region']) || $_GET['region'] == "") {
			return $this->getForm();
		} else {
			$region = $_GET['region'];
		}

		echo "Configure weight rates for ";
		switch ($region) {
			case 'local':
				echo "local shipping.";
				break;
			default:
				echo "shipping to ".$this->region_list[$region].".";
				break;
		}
		echo "<br/><br/>";

		echo '<div id="ses-weightregion-layers">';
		echo '<input type="hidden" name="ses_weightregion_shipping_region" value="'.$region.'">';

		if (isset($shipping[$region]) && count($shipping[$region])) {
			$weights = array_keys($shipping[$region]);
			foreach ($weights as $weight) {
				echo 'Weight over: <input type="text" name="'.$this->getInternalName().'_weights[]" style="width: 50px;" size="8" value="'.htmlentities($weight).'"> ';
				echo 'Shipping: <input type="text" name="'.$this->getInternalName().'_rates[]" style="width: 50px;" size="8" value="'.htmlentities($shipping[$region][$weight]).'"><br/>';
			}
		} else {
			echo 'Weight over: <input type="text" name="'.$this->getInternalName().'_weights[]" style="width: 50px;" size="8" value="0"> ';
			echo 'Shipping: <input type="text" name="'.$this->getInternalName().'_rates[]" style="width: 50px;" size="8"><br/>';
		}
		echo '</div>';
		echo '<br/>';
		echo '<a id="ses-weightregion-newlayer">New Layer</a>';
		echo '<script type="text/javascript">
                        jQuery("div.inside div.submit").expire();
			jQuery("div.inside div.submit").livequery(function() { jQuery(this).show();});
		      </script>';

		exit();
		
	}

	function getForm() {

		if (isset($_POST['region']) && $_POST['region'] != "") {
			$output = show_layers_form($_POST['region']);
		} else {
			$output = '<div align="center"><div class="donate" style="background: rgb(255,247,124); padding: 5px; margin-right: 5px; margin-bottom: 5px; color: #000; text-align: center; border: 1px solid #333; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; width: 180px;">This plugin is provided free of charge. If you find it useful, you should<strong><br><a href="http://www.leewillis.co.uk/wordpress-plugins/">donate here</a></strong></div></div>';
			$output .= "<br/>Pick a region to configure the weight layers:<br/><br/>";
			$output .= '<select id="ses-weightregion-select" name="region"><option value="">-- Choose --</option>';
			foreach ($this->region_list as $region_id => $region_desc) {
				$output .= '<option value="'.$region_id.'">'.$region_desc.'</option>';
			}
			$output .= '
		        </select>
		        <script type="text/javascript">
                           jQuery("div.inside div.submit").expire();
			   jQuery("div.inside div.submit").livequery(function() { jQuery(this).hide("slow");});
                           jQuery("#ses-weightregion-select").change(function() {
		             jQuery.ajax( { url: "admin-ajax.php?action=ses-weightregion-layers&region="+jQuery(this).val(),
                                        success: function(data) { jQuery("td.gateway_settings table.form-table").html(data); }
                                          }
                                        ) });
                           jQuery("#ses-weightregion-newlayer").expire();
                           jQuery("#ses-weightregion-newlayer").livequery("click", function(event){
			     jQuery("#ses-weightregion-layers").append("Weight over: <input type=\"text\" name=\"'.$this->getInternalName().'_weights[]\" style=\"width: 50px;\" size=\"8\">Shipping: <input type=\"text\" name=\"'.$this->getInternalName().'_rates[]\" style=\"width: 50px;\" size=\"8\"><br/>");});
		        </script>
			';
		}
		return $output;
	}
	


	/* Use this function to store the settings submitted by the form above
	 * Submitted form data is in $_POST */

	function submit_form() {

		if (!isset($_POST[$this->getInternalName().'_region']) ||
		    $_POST[$this->getInternalName().'_region'] == "") {
			return FALSE; 
		}

		// Get current settings array
		$shipping = get_option($this->getInternalName().'_options');
		if (!$shipping) {
			unset($shipping);
		}

		$region = $_POST[$this->getInternalName().'_region'];
		$weights = $_POST[$this->getInternalName().'_weights'];
		$rates = $_POST[$this->getInternalName().'_rates'];

		// Build submitted data into correct format
		for ($i = 0; $i < count($weights); $i++) {
			$new_shipping[$weights[$i]] = $rates[$i];
		}
		krsort($new_shipping,SORT_NUMERIC);
		$shipping[$region] = $new_shipping;
			
		update_option($this->getInternalName().'_options',$shipping);

		return true;

	}
	
	/* If there is a per-item shipping charge that applies irrespective of the chosen shipping method
         * then it should be calculated and returned here. The value returned from this function is used
         * as-is on the product pages. It is also included in the final cart & checkout figure along
         * with the results from GetQuote (below) */

	function get_item_shipping($unit_price, $quantity, $weight, $product_id) {

		global $wpdb;

		// If we're calculating a price based on a product, and that the store has shipping enabled

    		if (is_numeric($product_id) && (get_option('do_not_use_shipping') != 1)) {

			$country_code = $_SESSION['wpsc_delivery_country'];

			// Get product information
      			$product_list = $wpdb->get_row("SELECT *
			                                  FROM `".WPSC_TABLE_PRODUCT_LIST."`
				                         WHERE `id`='{$product_id}'
			                                 LIMIT 1",ARRAY_A);

       			// If the item has shipping enabled
      			if($product_list['no_shipping'] == 0) {

        			if($country_code == get_option('base_country')) {

					// Pick up the price from "Local Shipping Fee" on the product form
          				$additional_shipping = $product_list['pnp'];

				} else {

					// Pick up the price from "International Shipping Fee" on the product form
          				$additional_shipping = $product_list['international_pnp'];

				}          

				// Item shipping charges are per unit quantity
        			$shipping = $quantity * $additional_shipping;

			} else {

        			//if the item does not have shipping
        			$shipping = 0;

			}

		} else {

      			//if the item is invalid or store is set not to use shipping
			$shipping = 0;

		}

    		return $shipping;	
	}
	


	/* This function returns an Array of possible shipping choices, and associated costs.
         * This is for the cart in general, per item charges (As returned from get_item_shipping (above))
         * will be added on as well. */

	function getQuote() {

		global $wpdb, $wpsc_cart;

		// Get the cart weight
		$weight = wpsc_cart_weight_total();

		// Get the weight layers for this country
		if (isset($_POST['country'])) {

			$country = $_POST['country'];
			$_SESSION['wpsc_delivery_country'] = $country;

		} else {

			$country = $_SESSION['wpsc_delivery_country'];

		}
		// Retrieve the options set by submit_form() above
		$shipping = get_option($this->getInternalName().'_options');

		if (get_option('base_country') == $country) {
			$region = "local";
		} else {
			$results = $wpdb->get_var("SELECT `continent`
			                             FROM `".WPSC_TABLE_CURRENCY_LIST."`
			                            WHERE `isocode` IN('{$country}')
			                            LIMIT 1");
			$region = $results;
		}
		
		if (isset($shipping[$region]) && count($shipping[$region])) {
			$layers = $shipping[$region]; 
		} else {
			// No shipping layers configured for this region
			return Array();
		}
		
		// Note the weight layers are sorted before being saved into the options
		// Here we assume that they're in (descending) order
		foreach ($layers as $key => $shipping) {
			if ($weight >= (float)$key) {
				return array("Shipping"=>(float)$shipping);
			}
		}

		// We couldn't find a rate - exit out.
		return Array();
		
	}
	
	
} 

function ses_weightregion_shipping_add($wpsc_shipping_modules) {

	global $ses_weightregion_shipping;
	$ses_weightregion_shipping = new ses_weightregion_shipping();

	$wpsc_shipping_modules[$ses_weightregion_shipping->getInternalName()] = $ses_weightregion_shipping;

	return $wpsc_shipping_modules;
}
	
add_filter('wpsc_shipping_modules', 'ses_weightregion_shipping_add');
add_action('wp_ajax_ses-weightregion-layers',array(&$ses_weightregion_shipping,"show_layers_form"));

?>
