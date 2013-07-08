<?php
/**
 * wp-e-commerce-weight-region-region-manager.php
 *
 * @package wp-e-commerce-weightregion-shipping
 */


/**
 *
 *
 * @return unknown
 */
function region_shipping_menu() {

    if ( current_user_can( 'manage_options' ) ) {
        if ( ! defined( 'WPSC_VERSION' ) || WPSC_VERSION < 3.8 ) {
            add_submenu_page( 'wpsc-sales-logs', 'Manage Regions', 'Manage Regions', 'manage_options', 'wpsc-region-settings', 'manage_wpsc_regions' );
        } else {
            add_submenu_page( 'options-general.php', 'Store &raquo; Manage Regions', 'Store/Edit Regions', 'manage_options', 'wpsc-region-settings', 'manage_wpsc_regions' );
        }
    }

}


add_action( 'wpsc_add_submenu', 'region_shipping_menu' );



add_action( 'wp_ajax_get_country_callback', 'get_country_callback' );


/**
 *
 */
function get_country_callback() {

    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $country_id = $_POST['country'];

    $sql = $wpdb->prepare( 'SELECT * FROM `'.WPSC_TABLE_REGION_TAX.'` WHERE `country_id` = %d ORDER BY `name` ASC;', $country_id );
    $regions = $wpdb->get_results( $sql );

?>
    <script type="text/javascript" charset="utf-8">
        region_count = 0;
        function add_region() {
            region_count += 1;
            jQuery('#no_defined_regions').remove();
            jQuery('#the_regions tr:last').after('<tr id="new-' + region_count + '"><td><input type="text" name="name-new-' + region_count + '" value="" id="name-new-' + region_count + '" /></td><td><input type="text" size="4" name="code-new-' + region_count + '" value="" id="code-new-' + region_count + '" /></td><td><a href="#" onclick="delete_region(' + region_count + ', \'yes\'); return false;">delete</a></td></tr>');
        }

        function delete_region(rid, isnew) {
            var conf = confirm("Are you sure you want to delete this region?  This cannot be undone.");
            if(conf) {
                if(isnew == 'no') {
                    jQuery.post(ajaxurl,{rid: rid, action: "delete_region_callback"});
                    jQuery('#old-'+rid).remove();
                } else {
                    jQuery('#new-'+rid).remove();
                }

            }
        }

        function save_regions() {
                jQuery.post(ajaxurl,jQuery('#the_regions').serialize(),
                 function(data){
                    jQuery('#the_region_data').html(data);
                });
        }
    </script>
    <form action="" method="post" id="the_regions" accept-charset="utf-8" onsubmit="save_regions(); return false;">

        <input type="hidden" name="action" value="save_regions_callback" id="action">
        <input type="hidden" name="country_id" value="<?php echo $country_id; ?>" id="country_id">

        <table border="0" cellspacing="5" cellpadding="5" id="regions_table">
            <tr><th>Region Name</th><th>Abbreviation</th><th>&nbsp;</th></tr>
    <?php

    if ( count( $regions ) == 0 ) :
        echo '<tr><td colspan="3" id="no_defined_regions"><strong><em>This country has no defined regions.</em></strong></td></tr>';
    else :
        foreach ($regions as $r) :
?>
            <tr id="old-<?php echo $r->id; ?>"><td><input type="text" name="name-<?php echo $r->id; ?>" value="<?php echo $r->name; ?>" id="name-<?php echo $r->id; ?>" /></td><td><input type="text" size="4" name="code-<?php echo $r->id; ?>" value="<?php echo $r->code; ?>" id="code-<?php echo $r->id; ?>" /></td><td><a href="#" onclick="delete_region(<?php echo $r->id; ?>, 'no'); return false;">delete</a></td></tr>
        <?php
    endforeach;
    endif;

?>
        </table>
            <a href="#" onclick="add_region(); return false;">Add a Region</a>
            <p><input type="submit" value="Save &rarr;"></p>
        </form>
    <?php
    die();
}


add_action( 'wp_ajax_delete_region_callback', 'delete_region_callback' );


/**
 *
 */
function delete_region_callback() {

    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $rid = $_POST['rid'];
    /////////////////////////////////////////////////////////////////////
    // Get the country ID for the row before we delete it, so we can see if it's the last region for the country
    /////////////////////////////////////////////////////////////////////
    $sql = $wpdb->prepare( 'SELECT `country_id` FROM `'.WPSC_TABLE_REGION_TAX.'` WHERE id = %d limit 1', $rid);
    $cids = $wpdb->get_results( $sql );
    $cid = $cids[0]->country_id;


    /////////////////////////////////////////////////////////////////////
    // delete the old row
    /////////////////////////////////////////////////////////////////////
    $sql = $wpdb->prepare( 'DELETE FROM '.WPSC_TABLE_REGION_TAX.' WHERE id = %d limit 1', $rid );
    $wpdb->query( $sql );

    /////////////////////////////////////////////////////////////////////
    // check the number of regions left-- if it's one, we need to modify the currency list
    /////////////////////////////////////////////////////////////////////
    $sql = $wpdb->prepare( 'SELECT id FROM `'.WPSC_TABLE_REGION_TAX.'` WHERE country_id = %d', $cid );
    $regs = $wpdb->get_results( $sql );

    if ( count( $regs ) == 0 ) {
        $w['id'] = $cid;
        $u['has_regions'] = 0;
        $wpdb->update( WPSC_TABLE_CURRENCY_LIST, $u, $w );
    }
}


add_action( 'wp_ajax_save_regions_callback', 'save_regions_callback' );


/**
 *
 */
function save_regions_callback() {

    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $p = $_POST;
    unset($p['action']);
    $cid = $p['country_id'];
    unset($p['country_id']);

    foreach ($p as $k => $v) :
        /////////////////////////////////////////////////////////////////////
        // Split up each posted object and save to the DB, the key is fieldname-id
        /////////////////////////////////////////////////////////////////////
        $e = explode( '-', $k );

    if ($e[0] == 'name' || $e[0] == 'code') : //make sure it's one of the two fields we're looking for, and nobody's trying to fool us
        if ($e[1] == 'new') : //this is a new row
            $ins[$e[2]][$e[0]] = $v; //we'll deal with the new rows later, for now, just put the data together
        else : //this is an old row, we're just updating it
            unset($w);
        unset($u);
    $w['id'] = $e[1];
    $u[$e[0]] = $v;
    $wpdb->update( WPSC_TABLE_REGION_TAX, $u, $w );
    endif;
    endif;
    endforeach;

    if ($ins) : //handle new rows
        foreach ($ins as $in) :
            unset($i);
        $i['country_id'] = $cid;
    $i['name'] = $in['name'];
    $i['code'] = $in['code'];
    $wpdb->insert( WPSC_TABLE_REGION_TAX, $i );
    endforeach;

    //make sure the currency list knows that we set up multiple regions here
    unset($w);
    unset($u);
    $w['id'] = $cid;
    $u['has_regions'] = 1;
    $wpdb->update( WPSC_TABLE_CURRENCY_LIST, $u, $w );
    endif;

?>
        <p><strong>Your changes have been saved!</strong></p>
        <p><em>Would you like to <a href="#" onclick="switch_countries(); return false;">keep editing this country's regions</a>?</em></p>

    <?php

    die();
}


/**
 *
 */
function manage_wpsc_regions() {

    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    $sql = 'select `id`, `country`, `tax` from `'.WPSC_TABLE_CURRENCY_LIST.'` where `visible` = 1 order by `country` ASC';
    $countries = $wpdb->get_results( $sql );


?>
    <script type="text/javascript" charset="utf-8">
        function switch_countries() {
            jQuery.post(ajaxurl,jQuery('#country_select_form').serialize(),
             function(data){
                jQuery('#the_region_data').html(data);
            });
        }

    </script>
    <div class="wrap"><h2>Region Settings</h2>
        <form action="" id="country_select_form" method="post" accept-charset="utf-8">
        <input type="hidden" name="action" value="get_country_callback" id="action">
        Select a country to modify its regions: <select name="country" id="country"  onchange="switch_countries();" size="1">
            <?php
    foreach ($countries as $c) :
?>
            <option value="<?php echo $c->id; ?>"><?php echo $c->country ?></option>
            <?php
    endforeach;
?>
        </select>
        <em>You can change the countries in this list on <a href="<?php bloginfo( 'siteurl' ); ?>/wp-admin/admin.php?page=wpsc-settings">this page</a></em><br /><br />
        <script type="text/javascript" charset="utf-8">
            switch_countries();
        </script>
        </form>
        <div id="the_region_data">

        </div>
    </div>

    <?php
}