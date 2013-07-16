<?php
/**
 * wp-e-commerce-weight-countryregion-shipping.php
 *
 * @package wp-e-commerce-weightregion-shipping
 */


class ses_weightcountryregion_shipping extends ses_weightregion_module {

	var $countryregion_list;


	/**
	 *
	 *
	 * @return unknown
	 */
	function __construct() {
		$this->internal_name = 'ses_weightcountryregion_shipping';
		$this->name = 'Weight / Country and Region Shipping';
		$this->is_external = FALSE;
		$this->ses_weightcountryregion_getcountryregions();
		return true;
	}

	/**
	 *
	 */
	function ses_weightcountryregion_getcountryregions() {

		global $wpdb, $table_prefix;

		$countryregions = $wpdb->get_results(
		                                     "SELECT cl.id as country_id,
		                                             cl.country as country_name,
		                                             rt.id as region_id,
  			                                         rt.name as region_name
		                                        FROM {$table_prefix}wpsc_currency_list cl
		                                   LEFT JOIN {$table_prefix}wpsc_region_tax rt
                                                  ON cl.id = rt.country_id
		                                       WHERE cl.visible = '1'
		                                    ORDER BY cl.country ASC, rt.name ASC", ARRAY_A
		                                    );

		if ( $countryregions ) {
			foreach ( $countryregions as $country ) {
				$composite_key = $country['country_id'] . '|' . $country['region_id'];
				$results[$composite_key] = $country;
			}
		}
		$this->countryregion_list = $results;

	}



	/**
	 *
	 *
	 * @return unknown
	 */
	function show_layers_form() {

		$settings_element = 'div#wpsc_shipping_settings_ses_weightcountryregion_shipping_form input.edit-shipping-module-update';

		$shipping = get_option( $this->getInternalName().'_options' );

		if ( ! isset( $_GET['countryregion'] ) || $_GET['countryregion'] == '' ) {
			return $this->getForm();
		} else {
			$countryregion = $_GET['countryregion'];
		}

		echo 'Configure weight rates for shipping to ';
		echo esc_html( $this->countryregion_list[$countryregion]['country_name'] );
		if ( isset( $this->countryregion_list[$countryregion]['region_id'] ) ) {
			echo ', ' . esc_html( $this->countryregion_list[$countryregion]['region_name'] );
		}
		echo '.';
		echo '<br/><br/>';
		echo '<div id="ses-weightcountryregion-layers">';
		echo '<input type="hidden" name="ses_weightcountryregion_shipping_region" value="'.$countryregion.'">';

		if ( isset( $shipping[$countryregion] ) && count( $shipping[$countryregion] ) ) {
			$weights = array_keys( $shipping[$countryregion] );
			foreach ( $weights as $weight ) {
				echo 'Weight over: <input type="text" name="' . $this->getInternalName() . '_weights[]" style="width: 50px;" size="8" value="'.htmlentities( $weight ).'">lbs - ';
				echo 'Shipping: <input type="text" name="' . $this->getInternalName() . '_rates[]" style="width: 50px;" size="8" value="'.htmlentities( $shipping[$countryregion][$weight] ).'"><br/>';
			}
		} else {
			echo 'Weight over: <input type="text" name="'.$this->getInternalName().'_weights[]" style="width: 50px;" size="8" value="0">lbs - ';
			echo 'Shipping: <input type="text" name="'.$this->getInternalName().'_rates[]" style="width: 50px;" size="8"><br/>';
		}
		echo '</div>';
		echo '<br/>';
		echo '<a id="ses-weightcountryregion-newlayer">New Layer</a>';
		echo '<script type="text/javascript">
    	          jQuery("'.$settings_element.'").show();
		      </script>';
		exit();

	}


	/**
	 *
	 *
	 * @return unknown
	 */
	function getForm() {

		$settings_element = 'div#wpsc_shipping_settings_ses_weightcountryregion_shipping_form input.edit-shipping-module-update';

		if ( isset( $_POST['countryregion'] ) && $_POST['countryregion'] != '' ) {
			$output = show_layers_form( $_POST['countryregion'] );
		} else {
			$output = '<tr><td>';
			if ( ! $this->hide_donate_link() ) {
				$output .= '<div style="float: right; margin-left: 20px;"><div class="donate" style="background: rgb(255,247,124); padding: 10px; margin-right: 5px; margin-bottom: 5px; color: #000; text-align: center; border: 1px solid #333; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; width: 210px; height: 9em;">
				<p>If you\'ve found this plugin useful, consider being one of the people that supports its continued development by donating here:</p>
				<p>
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K8SZHFA67KEMA" target="_blank">$10</a> -
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CFX94YKKVE7TJ" target="_blank">$20</a> -
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X9M83NUHGFSPA" target="_blank">$30</a>
				</p></div></div>';
			}
			$output .= '<div id="ses-weightcountryregion-shipping-container">';
			$output .= 'Pick a region to configure the weight layers:<br/><br/>';
			$output .= '<select id="ses-weightcountryregion-select" name="countryregion"><option value="">-- Choose --</option>';
			foreach ( $this->countryregion_list as $country ) {
				$composite_key = $country['country_id'] . '|' . $country['region_id'];
				$country_desc = $country['country_name'];
				if ( isset( $country['region_id'] ) ) {
					$country_desc .= ', ' . $country['region_name'];
				}
				$output .= '<option value="'.$composite_key.'">'.$country_desc.'</option>';
			}
			$output .= '
		        </select>
		        <script type="text/javascript">
		        	// Hide the submit button
                    jQuery(document).ready(function() {
                    	jQuery("'.$settings_element.'").hide();
                    });
					// Load the second form when requested
                    jQuery("#ses-weightcountryregion-select").change(function() {
		            	jQuery.ajax( {
		            		url: "admin-ajax.php?action=ses-weightcountryregion-layers&countryregion="+jQuery(this).val(),
                            success: function(data) {
    	                       	jQuery("#ses-weightcountryregion-shipping-container").html(data);
        	                }
                        });
					});
	                jQuery(document).on("click", "#ses-weightcountryregion-newlayer", function(event) {
		     			jQuery("#ses-weightcountryregion-layers").append("Weight over: <input type=\"text\" name=\"'.$this->getInternalName().'_weights[]\" style=\"width: 50px;\" size=\"8\">lbs - Shipping: <input type=\"text\" name=\"'.$this->getInternalName().'_rates[]\" style=\"width: 50px;\" size=\"8\"><br/>");
			     	});
		        </script>';
			$options = get_option( $this->getInternalName() . '_options' );

			if ( ! isset( $options['quote_method'] ) ) {
				$options['quote_method'] = 'total';
			}
			$output .= '<br/>Prices based on:<br/>';
			$output .= '<input type="radio" class="ses-weightcountryregion-quote-method" name="'.$this->getInternalName().'_quote_method" value="total" '.($options['quote_method'] == 'total' ? 'checked="checked"' : '').'>Single quote for total cart weight<br>';
			$output .= '<input type="radio" class="ses-weightcountryregion-quote-method" name="'.$this->getInternalName().'_quote_method" value="items" '.($options['quote_method'] == 'items' ? 'checked="checked"' : '').'>Sum of quotes for individual items<br>';
			$output .= '<input type="radio" class="ses-weightcountryregion-quote-method" name="'.$this->getInternalName().'_quote_method" value="consolidateditems" '.($options['quote_method'] == 'consolidateditems' ? 'checked="checked"' : '').'>Sum of quotes for consolidated items<br>';
			$output .= '
		        <script type="text/javascript">
				jQuery("input[name=\''.$this->getInternalName().'_quote_method\']").change(function() {
					jQuery.ajax( { url: "admin-ajax.php",
						           type: "post",
								   data: "action=ses-weightcountryregion-quote-method&quote_method="+jQuery("input[name=\''.$this->getInternalName().'_quote_method\']:checked").val(),
								 }
							   )
					}
				);
				</script>';
			$output .= '</div>';
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

		if ( ! isset( $_POST[$this->getInternalName() . '_region'] ) ||
			$_POST[$this->getInternalName() . '_region'] == '' ) {
			return FALSE;
		}

		// Get current settings array
		$shipping = get_option( $this->getInternalName() . '_options' );
		if ( ! $shipping ) {
			unset( $shipping );
		}

		$region = $_POST[$this->getInternalName().'_region'];
		$weights = $_POST[$this->getInternalName().'_weights'];
		$rates = $_POST[$this->getInternalName().'_rates'];

		$new_shipping = array();

		// Build submitted data into correct format
		for ( $i = 0; $i < count( $weights ); $i++ ) {
			// Ignore blank rates
			if ( isset( $rates[$i] ) && $rates[$i] != '' ) {
				$new_shipping[$weights[$i]] = $rates[$i];
			}
		}

		if ( count( $new_shipping ) ) {
			krsort( $new_shipping, SORT_NUMERIC );
		}
		$shipping[$region] = $new_shipping;
		update_option( $this->getInternalName() . '_options', $shipping );

		return true;

	}



	/**
	 *
	 *
	 * @return unknown
	 */
	function save_quote_method() {

		// Called via Ajax if the quote method is changed
		if ( ! isset( $_POST['quote_method'] ) ) {
			return FALSE;
		}
		$options = get_option( $this->getInternalName() . '_options' );
		if ( ! $options ) {
			unset( $option );
		}
		$options['quote_method'] = $_POST['quote_method'];
		update_option( $this->getInternalName() . '_options', $options );

	}



	/**
	 * This function returns an Array of possible shipping choices, and associated costs.
     * This is for the cart in general, per item charges (As returned from get_item_shipping (above))
     * will be added on as well.
	 *
	 * @return unknown
	 */
	function getQuote() {

		global $wpdb, $wpsc_cart, $table_prefix;

		// Get the plugin options
		$options = get_option( $this->getInternalName() . '_options' );

		$this->validate_posted_country_info();
		$country_id = $this->get_shipping_var( 'ses_ps_delivery_country_id' );
        $country = $this->get_shipping_var( 'shipping_country' );
        $region = $this->get_shipping_var( 'shipping_region' );

		$composite_key = $country_id . '|' . $region;

		// Get the weight layers for this country
		if ( isset( $options[$composite_key] ) && count( $options[$composite_key] ) ) {
			$layers = $options[$composite_key];
		} else {
			// No shipping layers configured for this region
			return array();
		}

		// Previous releases had a bug where "empty" config settings could be saved
		// We strip them out here
		foreach ( $layers as $key => $value ) {
			if ( $value == '' ) {
				unset ($layers[$key]);
			}
		}
		if ( ! count( $layers ) ) {
			return array();
		}

		if ( ! isset( $options['quote_method'] ) ||
			$options['quote_method'] == 'total' ) {
			// Get the cart weight
			$weight = wpsc_cart_weight_total();

			// Note the weight layers are sorted before being saved into the options
			// Here we assume that they're in (descending) order
			foreach ( $layers as $key => $shipping ) {
				if ( $weight >= (float) $key ) {
					return array( 'Shipping' => (float) $shipping );
				}
			}

			// We couldn't find a rate - exit out.
			return array();
		} else {
			if ( isset( $wpsc_cart ) && isset( $wpsc_cart->cart_items ) && count( $wpsc_cart->cart_items ) ) {
				$subtotal = 0;
				foreach ( $wpsc_cart->cart_items as $cart_item ) {
					foreach ( $layers as $key => $shipping ) {
						if ( $options['quote_method'] == 'items' ) {
							if ( $cart_item->weight >= (float) $key ) {
								$subtotal += (float)($shipping * $cart_item->quantity);
								break;
							}
						} elseif ( $options['quote_method'] == 'consolidateditems' ) {
							if ( ( $cart_item->weight * $cart_item->quantity ) >= (float) $key ) {
								$subtotal += (float) $shipping;
								break;
							}
						}
					}
				}
				return array( 'Shipping' => (float) $subtotal );
			}
		}
	}

}