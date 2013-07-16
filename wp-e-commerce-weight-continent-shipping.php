<?php
/**
 * wp-e-commerce-weight-continent-shipping.php
 *
 * @package wp-e-commerce-weightregion-shipping
 */


/* This is confusingly named - it's actually Weight / Continent shipping */

class ses_weightregion_shipping extends ses_weightregion_module {


    var $region_list;


    /**
     *
     *
     * @return unknown
     */
    function __construct() {

        $this->internal_name = 'ses_weightregion_shipping';
        $this->name = 'Weight / Continent Shipping';
        $this->is_external = FALSE;
        $this->ses_weightregion_getregions();
        return true;
    }



    /**
     *
     */
    function ses_weightregion_getregions() {

        global $wpdb, $table_prefix;

        $standard_continents = array(
            'local' => 'Local',
            'northamerica' => 'North America',
            'southamerica' => 'South America',
            'asiapacific' => 'Asia and Pacific',
            'europe' => 'Europe',
            'antarctica' => 'Antarctica',
            'africa' => 'Africa',
        );

        $sql = "SELECT DISTINCT(continent)
                  FROM {$table_prefix}wpsc_currency_list";

        $continents = $wpdb->get_results( $sql, ARRAY_A );

        $results = array();

        if ( count( $continents ) ) {
            foreach ( $continents as $continent ) {
                if ( $continent['continent'] == '' )
                    continue;
                if ( isset( $standard_continents[$continent['continent']] ) ) {
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
    function show_layers_form() {

		$settings_element = 'div#wpsc_shipping_settings_ses_weightregion_shipping_form input.edit-shipping-module-update';

        $shipping = get_option( $this->getInternalName() . '_options' );

        if ( ! isset( $_GET['region']) || $_GET['region'] == '' ) {
            return $this->getForm();
        } else {
            $region = $_GET['region'];
        }

        echo 'Configure weight rates for ';
	        switch ( $region ) {
		        case 'local':
		            echo 'local shipping.';
		            break;
		        default:
		            echo 'shipping to ' . $this->region_list[$region] . '.';
		            break;
	        }
        echo '<br/><br/>';

        echo '<div id="ses-weightregion-layers">';
        echo '<input type="hidden" name="ses_weightregion_shipping_region" value="'.$region.'">';

        if ( isset( $shipping[$region] ) && count( $shipping[$region] ) ) {
            $weights = array_keys( $shipping[$region] );
            foreach ( $weights as $weight ) {
                echo 'Weight over: <input type="text" name="' . $this->getInternalName() . '_weights[]" style="width: 50px;" size="8" value="' . htmlentities( $weight ) . '">lbs -  ';
                echo 'Shipping: <input type="text" name="' . $this->getInternalName() . '_rates[]" style="width: 50px;" size="8" value="' . htmlentities( $shipping[$region][$weight] ).'"><br/>';
            }
        } else {
            echo 'Weight over: <input type="text" name="'.$this->getInternalName().'_weights[]" style="width: 50px;" size="8" value="0">lbs -  ';
            echo 'Shipping: <input type="text" name="'.$this->getInternalName().'_rates[]" style="width: 50px;" size="8"><br/>';
        }
        echo '</div>';
        echo '<br/>';
        echo '<a id="ses-weightregion-newlayer">New Layer</a>';
        // Reveal the submit button
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

		$settings_element = 'div#wpsc_shipping_settings_ses_weightregion_shipping_form input.edit-shipping-module-update';

        if ( isset( $_POST['region'] ) && $_POST['region'] != '' ) {
            $output = $this->show_layers_form( $_POST['region'] );
        } else {
            $output = '<tr><td>';
            if ( ! $this->hide_donate_link() ) {
                $output .= '<div style="float: right; margin-left: 20px;"><div class="donate" style="background: rgb(255,247,124); padding: 10px; margin-right: 5px; margin-bottom: 5px; color: #000; text-align: center; border: 1px solid #333; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; width: 190px; height: 9em;">
				<p>If you\'ve found this plugin useful, consider being one of the people that supports its continued development by donating here:</p>
				<p>
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K8SZHFA67KEMA" target="_blank">$10</a> -
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=CFX94YKKVE7TJ" target="_blank">$20</a> -
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=X9M83NUHGFSPA" target="_blank">$30</a>
				</p></div></div>';
            }
            $output .= '<div id="ses-weightregion-shipping-container">';
            $output .= 'Pick a region to configure the weight layers:<br/><br/>';
            $output .= '<select id="ses-weightregion-select" name="region"><option value="">-- Choose --</option>';
            foreach ( $this->region_list as $region_id => $region_desc ) {
                $output .= '<option value="'.$region_id.'">'.$region_desc.'</option>';
            }
            $output .= '
                </select>
                <script type="text/javascript">
		        	// Hide the submit button
                    jQuery(document).ready(function() {
                    	jQuery("'.$settings_element.'").hide();
                    });
					// Load the second form when requested
                    jQuery("#ses-weightregion-select").change(function() {
                    	jQuery.ajax({
                    		url: "admin-ajax.php?action=ses-weightregion-layers&region="+jQuery(this).val(),
                            success: function( data ) {
                            	jQuery("#ses-weightregion-shipping-container").html(data);
                            }
                        });
					});
                    jQuery(document).on("click", "#ses-weightregion-newlayer", function(event) {
                 		jQuery("#ses-weightregion-layers").append("Weight over: <input type=\"text\" name=\"'.$this->getInternalName().'_weights[]\" style=\"width: 50px;\" size=\"8\">lbs - Shipping: <input type=\"text\" name=\"'.$this->getInternalName().'_rates[]\" style=\"width: 50px;\" size=\"8\"><br/>");
                 	});
                </script>';

            $options = get_option( $this->getInternalName() . '_options' );
            if ( ! isset( $options['quote_method'] ) ) {
                $options['quote_method'] = 'total';
            }
            $output .= '<br/>Prices based on:<br/>';
            $output .= '<input type="radio" class="ses-weightregion-quote-method" name="'.$this->getInternalName().'_quote_method" value="total" '.($options['quote_method'] == 'total' ? 'checked' : '').'>Single quote for total cart weight<br>';
            $output .= '<input type="radio" class="ses-weightregion-quote-method" name="'.$this->getInternalName().'_quote_method" value="items" '.($options['quote_method'] == 'items' ? 'checked' : '').'>Sum of quotes for individual items<br>';
            $output .= '<input type="radio" class="ses-weightregion-quote-method" name="'.$this->getInternalName().'_quote_method" value="consolidateditems" '.($options['quote_method'] == 'consolidateditems' ? 'checked' : '').'>Sum of quotes for consolidated items<br>';
            $output .= '
                <script type="text/javascript">
                jQuery("input[name=\''.$this->getInternalName().'_quote_method\']").change(function() {
                        jQuery.ajax(
                            {
                            	url: "admin-ajax.php",
                                type: "post",
                                data: "action=ses-weightregion-quote-method&quote_method="+jQuery("input[name=\''.$this->getInternalName().'_quote_method\']:checked").val()
                            }
                        )});
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
            // Don't set rates if they're blank
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
     * This function returns an array of possible shipping choices, and associated costs.
     * This is for the cart in general, per item charges (As returned from get_item_shipping (above))
     * will be added on as well.
     *
     * @return unknown
     */
    function getQuote() {

        global $wpdb, $wpsc_cart;

        $this->validate_posted_country_info();
        $country_id = $this->get_shipping_var( 'ses_ps_delivery_country_id' );
        $country = $this->get_shipping_var( 'shipping_country' );

        // Retrieve the options set by submit_form() above
        $options = get_option( $this->getInternalName() . '_options' );

        // Work out which continent this country is in
        $results = $wpdb->get_var(
        	$wpdb->prepare(
        	 'SELECT `continent`
                FROM `'.WPSC_TABLE_CURRENCY_LIST.'`
               WHERE `isocode` = %s
               LIMIT 1',
             $country
            )
        );
        $region = $results;

        if ( isset( $options[$region] ) && count( $options[$region] ) ) {
            $layers = $options[$region];
        } else {
            // No shipping layers configured for this region
            return array();
        }

        // Previous releases had a bug where "empty" config settings could be saved
        // We strip them out here
        foreach ( $layers as $key => $value ) {
            if ( $value == '' ) {
                unset( $layers[$key] );
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
                if ( $weight >= (float)$key ) {
                    return array( 'Shipping' => (float)$shipping );
                }
            }
        } else {
            if ( isset( $wpsc_cart ) && isset( $wpsc_cart->cart_items ) && count( $wpsc_cart->cart_items ) ) {
                $subtotal = 0;
                foreach ( $wpsc_cart->cart_items as $cart_item ) {
                    foreach ( $layers as $key => $shipping ) {
                        if ( $options['quote_method'] == 'items' ) {
                            if ( $cart_item->weight >= (float)$key ) {
                                $subtotal += (float)( $shipping * $cart_item->quantity );
                                break;
                            }
                        } elseif ( $options['quote_method'] == 'consolidateditems' ) {
                            if ( ( $cart_item->weight * $cart_item->quantity ) >= (float)$key ) {
                                $subtotal += (float)$shipping;
                                break;
                            }
                        }
                    }
                }
                return array( 'Shipping' => (float)$subtotal );
            }
        }
        // We couldn't find a rate - exit out.
        return array();

    }

}