<?php
/**
 * wp-e-commerce-weight-continent-shipping.php
 *
 * @package wp-e-commerce-weightregion-shipping
 */


/* This is confusingly named - it's actually Weight / Continent shipping */

class ses_weightregion_shipping {

	var $internal_name;
	var $name;
	var $is_external;

	var $region_list;


	/**
	 *
	 */
	function ses_weightregion_getregions() {

		global $wpdb, $table_prefix;

		$standard_continents = array('local' => 'Local',
			'northamerica' => 'North America',
			'southamerica' => 'South America',
			'asiapacific' => 'Asia and Pacific',
			'europe' => 'Europe',
			'antarctica' => 'Antarctica',
			'africa' => 'Africa');

		$sql = "SELECT DISTINCT(continent)
		          FROM {$table_prefix}wpsc_currency_list";

		$continents = $wpdb->get_results($sql, ARRAY_A);

		$results = array();

		if (count($continents)) {

			foreach ($continents as $continent) {

				if ($continent['continent']=='')
					continue;

				if (isset($standard_continents[$continent['continent']])) {
					$results[$continent['continent']] = $standard_continents[$continent['continent']];
				} else {
					$results[$continent['continent']] = $continent['continent'];
				}

			}

		}

		$this->region_list = $results;

	}


	/**
	 *
	 *
	 * @return unknown
	 */
	function ses_weightregion_shipping() {

		$this->internal_name = "ses_weightregion_shipping";
		$this->name = "Weight / Continent Shipping";
		$this->is_external = FALSE;
		$this->ses_weightregion_getregions();
		return true;
	}



	/**
	 * You must always supply this
	 *
	 * @return unknown
	 */
	function getName() {
		return $this->name;
	}



	/**
	 * You must always supply this
	 *
	 * @return unknown
	 */
	function getInternalName() {
		return $this->internal_name;
	}





	/**
	 *
	 *
	 * @return unknown
	 */
	function hide_donate_link() {

		$blogurl = get_bloginfo('url');

		$options = get_option($this->getInternalName().'_options');
		if (isset($options['validated']) && $options['validated'] > time()) {
			return TRUE;
		}
		if (isset($options['validation_fail_check']) && $options['validation_fail_check'] > time()) {
			return FALSE;
		}

		$validation_url = "http://www.leewillis.co.uk/?action=ses_plugin_validate";
		$validation_url .= "&plugin=ses_weightregion_shipping";
		$validation_url .= "&site=".urlencode($blogurl);

		$response = wp_remote_get($validation_url);
		if (is_wp_error($response)) {
			$options['validation_fail_check'] = time() + 86400;
			update_option($this->getInternalName().'_options', $options);
			return FALSE;
		}
		$response_code = $response['response']['code'];

		if ($response_code == '200') {
			$options['validated'] = time() + 2592000;
			update_option($this->getInternalName().'_options', $options);
			return TRUE;
		} else {
			$options['validation_fail_check'] = time() + 86400;
			update_option($this->getInternalName().'_options', $options);
			return FALSE;
		}
	}



	/**
	 *
	 *
	 * @return unknown
	 */
	function show_layers_form() {

		if ( version_compare( WPSC_VERSION, '3.8.8', '>=' ) ) {
			$settings_element = "td#wpsc-shipping-module-settings div.inside p.submit";
			$toplevel_element = "td#wpsc-shipping-module-settings";
		} else {
			$settings_element = "td.gateway_settings div.inside div.submit";
			$toplevel_element = "td.gateway_settings";
		}

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
                        jQuery("'.$settings_element.'").expire();
			jQuery("'.$settings_element.'").livequery(function() { jQuery(this).show();});
		      </script>';

		exit();

	}


	/**
	 *
	 *
	 * @return unknown
	 */
	function getForm() {

		if ( version_compare( WPSC_VERSION, '3.8.8', '>=' ) ) {
			$settings_element = "td#wpsc-shipping-module-settings div.inside p.submit";
			$toplevel_element = "td#wpsc-shipping-module-settings";
		} else {
			$settings_element = "td.gateway_settings div.inside div.submit";
			$toplevel_element = "td.gateway_settings";
		}

		if (isset($_POST['region']) && $_POST['region'] != "") {
			$output = $this->show_layers_form($_POST['region']);
		} else {
			$output = '<tr><td>';
			if (!$this->hide_donate_link()) {
				$output .= '<div align="center"><div class="donate" style="background: rgb(255,247,124); padding: 5px; margin-right: 5px; margin-bottom: 5px; color: #000; text-align: center; border: 1px solid #333; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; width: 240px;">This plugin is provided free of charge. If you find it useful, you should<strong><br><a href="http://www.leewillis.co.uk/wordpress-plugins/">donate here</a></strong><br/><small><a target="_blank" href="http://www.leewillis.co.uk/hide-donations/">Hide this message</a></div></div><br/>';
			}
			$output .= "Pick a region to configure the weight layers:<br/><br/>";
			$output .= '<select id="ses-weightregion-select" name="region"><option value="">-- Choose --</option>';
			foreach ($this->region_list as $region_id => $region_desc) {
				$output .= '<option value="'.$region_id.'">'.$region_desc.'</option>';
			}
			$output .= '
		        </select>
		        <script type="text/javascript">
                           jQuery("'.$settings_element.'").expire();
			   jQuery("'.$settings_element.'").livequery(function() { jQuery(this).hide("slow");});
                           jQuery("#ses-weightregion-select").change(function() {
		             jQuery.ajax( { url: "admin-ajax.php?action=ses-weightregion-layers&region="+jQuery(this).val(),
                                        success: function(data) { jQuery("'.$toplevel_element.' table.form-table").html(data); }
                                          }
                                        ) });
                           jQuery("#ses-weightregion-newlayer").expire();
                           jQuery("#ses-weightregion-newlayer").livequery("click", function(event){
			     jQuery("#ses-weightregion-layers").append("Weight over: <input type=\"text\" name=\"'.$this->getInternalName().'_weights[]\" style=\"width: 50px;\" size=\"8\">Shipping: <input type=\"text\" name=\"'.$this->getInternalName().'_rates[]\" style=\"width: 50px;\" size=\"8\"><br/>");});
		        </script>';
			$options = get_option($this->getInternalName().'_options');
			if (!isset($options['quote_method'])) {
				$options['quote_method'] = 'total';
			}
			$output .= '<br/>Prices based on:<br/>';
			$output .= '<input type="radio" class="ses-weightregion-quote-method" name="quote_method" value="total" '.($options['quote_method'] == 'total' ? 'checked' : '').'>Single quote for total cart weight<br>';
			$output .= '<input type="radio" class="ses-weightregion-quote-method" name="quote_method" value="items" '.($options['quote_method'] == 'items' ? 'checked' : '').'>Sum of quotes for individual items<br>';
			$output .= '<input type="radio" class="ses-weightregion-quote-method" name="quote_method" value="consolidateditems" '.($options['quote_method'] == 'consolidateditems' ? 'checked' : '').'>Sum of quotes for consolidated items<br>';
			$output .= '
				<script type="text/javascript">
				jQuery("input[name=\'quote_method\']").change(function() {
						jQuery.ajax( { url: "admin-ajax.php",
						               type: "post",
									   data: "action=ses-weightregion-quote-method&quote_method="+jQuery("input[name=\'quote_method\']:checked").val()
									   }
							)
						}
						);
				</script>';
			$output .= '</td></tr>';
		}
		return $output;
	}



	/**
	 * Use this function to store the settings submitted by the form above
	 * Submitted form data is in $_POST 
	 *
	 * @return unknown
	 */
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

		$new_shipping = array();

		// Build submitted data into correct format
		for ($i = 0; $i < count($weights); $i++) {

			// Don't set rates if they're blank
			if (isset($rates[$i]) && $rates[$i] != "") {
				$new_shipping[$weights[$i]] = $rates[$i];
			}

		}

		if (count($new_shipping)) {
			krsort($new_shipping, SORT_NUMERIC);
		}

		$shipping[$region] = $new_shipping;
		update_option($this->getInternalName().'_options', $shipping);

		return true;

	}





	/**
	 *
	 *
	 * @return unknown
	 */
	function save_quote_method() {

		// Called via Ajax if the quote method is changed
		if (!isset($_POST['quote_method'])) {
			return FALSE;
		}

		$options = get_option($this->getInternalName().'_options');
		if (!$options) {
			unset($option);
		}

		$options['quote_method'] = $_POST['quote_method'];

		update_option($this->getInternalName().'_options', $options);

	}


	/**
	 *
	 *
	 * @return unknown
	 */
	function validate_posted_country_info() {

		global $wpdb, $table_prefix;

		if (isset($_POST['country'])) {
			$country = $_POST['country'];
			$_SESSION['wpsc_delivery_country'] = $country;
		} else {
			$country = $_SESSION['wpsc_delivery_country'];
		}

		$sql = "SELECT id FROM {$table_prefix}wpsc_currency_list WHERE isocode=%s";
		$country_id = $wpdb->get_var($wpdb->prepare($sql, $country));

		if (isset($_POST['region'])) {
			$region = $_POST['region'];
			$_SESSION['wpsc_delivery_region'] = $region;
		} else {
			$region = $_SESSION['wpsc_delivery_region'];
		}

		// Check that the region is valid for this country (For when we're changing coutries)
		$sql = "SELECT id FROM {$table_prefix}wpsc_region_tax WHERE id = %s and country_id = %s";
		$region_id = $wpdb->get_var($wpdb->prepare($sql, $region, $country_id));
		if ($region_id != $region) {
			unset($_SESSION['wpsc_delivery_region']);
		}

		return $country_id;
	}



	/**
	 * If there is a per-item shipping charge that applies irrespective of the chosen shipping method
     * then it should be calculated and returned here. The value returned from this function is used
     * as-is on the product pages. It is also included in the final cart & checkout figure along
     * with the results from GetQuote (below)
	 *
	 * @param unknown $cart_item (reference)
	 * @return unknown
	 */
	function get_item_shipping(&$cart_item) {

		global $wpdb;

		$product_id = $cart_item->product_id;
		$quantity = $cart_item->quantity;
		$weight = $cart_item->weight;
		$unit_price = $cart_item->unit_price;

		// If we're calculating a price based on a product, and that the store has shipping enabled

		if (is_numeric($product_id) && (get_option('do_not_use_shipping') != 1)) {

			$country_id = $this->validate_posted_country_info();
			$country_code = $_SESSION['wpsc_delivery_country'];

			if ( ! defined( 'WPSC_VERSION' ) || WPSC_VERSION < 3.8 ) {

				// Get product information
				$product_list = $wpdb->get_row("SELECT *
				                                  FROM `".WPSC_TABLE_PRODUCT_LIST."`
					                         WHERE `id`='{$product_id}'
				                                 LIMIT 1", ARRAY_A);

				$no_shipping = $product_list['no_shipping'];
				$local_shipping = isset ( $product_list['pnp'] ) ? $product_list['pnp'] : 0;
				$international_shipping = isset ( $product_list['international_pnp'] ) ? $product_list['international_pnp'] : 0;

			} else {

				$product_list = get_post_meta ( $product_id, '_wpsc_product_metadata', TRUE );

				$no_shipping = $product_list['no_shipping'];
				$local_shipping = isset ( $product_list['shipping']['local'] ) ? $product_list['shipping']['local'] : 0;
				$international_shipping = isset ( $product_list['shipping']['international'] ) ? $product_list['shipping']['international'] : 0;

			}

			// If the item has shipping enabled
			if ($no_shipping == 0) {

				if ($country_code == get_option('base_country')) {

					// Pick up the price from "Local Shipping Fee" on the product form
					$additional_shipping = $local_shipping;

				} else {

					// Pick up the price from "International Shipping Fee" on the product form
					$additional_shipping = $international_shipping;

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



	/**
	 * This function returns an Array of possible shipping choices, and associated costs.
     * This is for the cart in general, per item charges (As returned from get_item_shipping (above))
     * will be added on as well.
	 *
	 * @return unknown
	 */
	function getQuote() {

		global $wpdb, $wpsc_cart;

		$country_id = $this->validate_posted_country_info();
		$country = $_SESSION['wpsc_delivery_country'];

		// Retrieve the options set by submit_form() above
		$options = get_option($this->getInternalName().'_options');

		$results = $wpdb->get_var("SELECT `continent`
									 FROM `".WPSC_TABLE_CURRENCY_LIST."`
									WHERE `isocode` IN('{$country}')
									LIMIT 1");
		$region = $results;

		if (isset($options[$region]) && count($options[$region])) {
			$layers = $options[$region];
		} else {
			// No shipping layers configured for this region
			return array();
		}

		// Previous releases had a bug where "empty" config settings could be saved
		// We strip them out here
		foreach ($layers as $key => $value) {
			if ($value == '') {
				unset ($layers[$key]);
			}
		}
		if (!count($layers)) {
			return array();
		}

		if (!isset($options['quote_method']) ||
			$options['quote_method'] == 'total') {

			// Get the cart weight
			$weight = wpsc_cart_weight_total();

			// Note the weight layers are sorted before being saved into the options
			// Here we assume that they're in (descending) order
			foreach ($layers as $key => $shipping) {
				if ($weight >= (float)$key) {
					return array("Shipping"=>(float)$shipping);
				}
			}

		} else {

			if (isset($wpsc_cart) && isset($wpsc_cart->cart_items) && count($wpsc_cart->cart_items)) {

				$subtotal = 0;
				foreach ($wpsc_cart->cart_items as $cart_item) {

					foreach ($layers as $key => $shipping) {

						if ($options['quote_method'] == 'items') {
							if ($cart_item->weight >= (float)$key) {
								$subtotal += (float)($shipping * $cart_item->quantity);
								break;
							}
						} elseif ($options['quote_method'] == 'consolidateditems') {
							if (($cart_item->weight * $cart_item->quantity) >= (float)$key) {
								$subtotal += (float)$shipping;
								break;
							}

						}

					}

				}
				return array("Shipping"=>(float)$subtotal);

			}

		}
		// We couldn't find a rate - exit out.
		return array();


	}


}