<?php

/**
 * wp-e-commerce-weight-region-modules.php
 *
 * @package wp-e-commerce-weightregion-shipping
 */


abstract class ses_weightregion_module {



	// FIXME - these should be protected, but can't enable that yet
	// See https://github.com/wp-e-commerce/WP-e-Commerce/pull/572
    public $internal_name;
    public $name;
    public $is_external;



	abstract public function getForm();
	abstract public function submit_form();
	abstract public function getQuote();




	/**
	 * Return the name of the shipping module
	 *
	 * @return string  The name of the shipping module
	 */
	function getName() {
		return $this->name;
	}



	/**
	 * Return the internal name of the shipping module
	 *
	 * @return string  The internal name of the shipping module
	 */
	function getInternalName() {
		return $this->internal_name;
	}



    // Map the new style shipping var key names back to the pre 3.8.9
    // SESSION array key names if required
    protected function map_shipping_vars( $varname ) {

        // No mapping required for 3.8.9+
        if ( function_exists( 'wpsc_get_customer_meta' ) )
            return $varname;

        switch ( $varname ) {
            case 'shipping_country':
                return 'wpsc_delivery_country';
                break;

            case 'shipping_region':
                return 'wpsc_delivery_region';
                break;

            case 'ses_ps_delivery_country_id':
                return 'wpsc_delivery_country_id';
                break;

            case 'ses_ps_delivery_continent':
                return 'wpsc_delivery_continent';
                break;

            default:
                return $varname;
                break;
        }

    }



    protected function get_shipping_var( $key ) {

        $key = $this->map_shipping_vars( $key );

        // 3.8.9+
        if ( function_exists( 'wpsc_get_customer_meta' ) ) {
            return wpsc_get_customer_meta( $key );
        // Pre 3.8.9
        } else {
            return isset ( $_SESSION[$key] ) ? $_SESSION[$key] : null;
        }

    }



    protected function set_shipping_var( $key, $value ) {

        $key = $this->map_shipping_vars( $key );

        if ( function_exists( 'wpsc_update_customer_meta' ) ) {
            wpsc_update_customer_meta( $key, $value );
        } else {
            $_SESSION[$key] = $value;
        }

    }



    function validate_posted_country_info() {

        global $wpdb, $table_prefix;

        // Update country info based on posted values
        if ( isset( $_POST['country'] ) ) {
            $country = $_POST['country'];
            $this->set_shipping_var( 'shipping_country', $country );
        }
        $country = $this->get_shipping_var( 'shipping_country' );

        if ( empty ( $country ) ) {
            $country_id = null;
            $continent = null;
        } else {
            $sql = "SELECT id, continent FROM {$table_prefix}wpsc_currency_list WHERE isocode=%s";
            $results = $wpdb->get_row( $wpdb->prepare( $sql, $country ) );
            $country_id = $results->id;
            $continent = $results->continent;
        }

        $this->set_shipping_var( 'ses_ps_delivery_country_id', $country_id );
        $this->set_shipping_var( 'ses_ps_delivery_continent', $continent );

        if ( isset( $_POST['region'] ) ) {
            $region = $_POST['region'];
            $this->set_shipping_var( 'shipping_region', $region );
        }
        $region = $this->get_shipping_var( 'shipping_region' );

        // Check that the region is valid for this country (For when we're changing coutries)
        $sql = "SELECT id FROM {$table_prefix}wpsc_region_tax WHERE id = %s and country_id = %s";
        $region_id = $wpdb->get_var( $wpdb->prepare( $sql, $region, $country_id ) );

        if ( $region_id != $region ) {
            $this->set_shipping_var( 'shipping_region',null );
        }

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
	function get_item_shipping( &$cart_item ) {

		global $wpdb;

		$product_id = $cart_item->product_id;
		$quantity = $cart_item->quantity;
		$weight = $cart_item->weight;
		$unit_price = $cart_item->unit_price;

		// If we're calculating a price based on a product, and that the store has shipping enabled

		if ( is_numeric( $product_id ) && ( get_option( 'do_not_use_shipping' ) != 1 ) ) {
			$this->validate_posted_country_info();
        	$country_id = $this->get_shipping_var( 'ses_ps_delivery_country_id' );
			$country_code = $this->get_shipping_var( 'shipping_country' );

			$product_list = get_post_meta( $product_id, '_wpsc_product_metadata', TRUE );
			$no_shipping = $product_list['no_shipping'];
			$local_shipping = isset( $product_list['shipping']['local'] ) ? $product_list['shipping']['local'] : 0;
			$international_shipping = isset( $product_list['shipping']['international'] ) ? $product_list['shipping']['international'] : 0;

			// If the item has shipping enabled
			if ( $no_shipping == 0 ) {
				if ( $country_code == get_option( 'base_country' ) ) {
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
	 *
	 *
	 * @return unknown
	 */
	function hide_donate_link() {

		$blogurl = get_bloginfo( 'url' );

		$options = get_option( $this->getInternalName() . '_options' );
		if ( isset( $options['validated'] ) && $options['validated'] > time() ) {
			return TRUE;
		}
		if ( isset( $options['validation_fail_check'] ) && $options['validation_fail_check'] > time() ) {
			return FALSE;
		}

		$validation_url = 'http://www.leewillis.co.uk/?action=ses_plugin_validate';
		$validation_url .= '&plugin=ses_weightregion_shipping';
		$validation_url .= '&site=' . urlencode( $blogurl );

		$response = wp_remote_get( $validation_url );
		if ( is_wp_error( $response ) ) {
			$options['validation_fail_check'] = time() + 86400;
			update_option( $this->getInternalName() . '_options', $options );
			return FALSE;
		}
		$response_code = $response['response']['code'];

		if ( $response_code == '200' ) {
			$options['validated'] = time() + 2592000;
			update_option( $this->getInternalName() . '_options', $options );
			return TRUE;
		} else {
			$options['validation_fail_check'] = time() + 86400;
			update_option( $this->getInternalName() . '_options', $options );
			return FALSE;
		}
	}
}