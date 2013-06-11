<?php

/**
 * wp-e-commerce-weight-region-modules.php
 *
 * @package wp-e-commerce-weightregion-shipping
 */


abstract class ses_weightregion_module {



	abstract public function getName();
	abstract public function getInternalName();
	abstract public function getForm();
	abstract public function submit_form();
	abstract public function get_item_shipping( &$cart_item );
	abstract public function getQuote();




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

        return; //FIXME callers are expecting a country ID

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