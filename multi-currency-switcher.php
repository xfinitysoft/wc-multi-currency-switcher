<?php
 /**
 * Plugin Name:         Multi Currency Switcher
 * Plugin URI:          https://www.xfinitysoft.com
 * Description:         WooCommerce add on for multi currency. 
 * Author:              Xfinity Soft
 * Author URI:          https://www.xfinitysoft.com/
 *
 * Version:             0.0.6
 * Requires at least:   4.4.0
 * Tested up to:        6.0
 * WC requires at least: 4.0
 * WC tested up to:      6.0
 *
 * Text Domain:         xs-mcs
 * Prefix				xs-mcs
 * Domain Path:         /languages/
 *
 * @category            Plugin
 
 * @author              Xfinity Soft
 * @package             xs-multi-currency-switcher
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit;   //Exit if accessed directly.
}
 
function xs_mcs_activation_hook(){
	if ( !class_exists( 'WooCommerce' ) ) { 
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( esc_html__( 'Multi Currency Switcher requires WooCommerce to run. Please install WooCommerce and activate before attempting to activate Advance WooCommerce Multi-Currency - Currency Switcher again.', 'xs-mcs' ) );
	}
	
	// Let the $wpdb global
	global $wpdb;
	$charset_collate = '';
	$order_currencies_table = $wpdb->prefix . "xsmcs_order_currencies";
	
	//Set Character Set
	if(!empty($wpdb->charset)) {
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
	}
	
	// if not empty
	if(!empty($wpdb->collate)) {
		$charset_collate .= " COLLATE $wpdb->collate";
	}
	
	
	
	// Building the query. Creating table for Donations
	if($wpdb->get_var("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA='{$wpdb->dbname}' AND TABLE_NAME = '{$order_currencies_table}'")!=$order_currencies_table){
		$order_currencies_table_query = "CREATE TABLE $order_currencies_table (
		  id int(10) NOT NULL AUTO_INCREMENT,
		  order_id int(10) NOT NULL,
		  order_currency varchar(4) NOT NULL,
		  currency_value float(10) NOT NULL,
		  order_total_exc_fee float(10) NOT NULL,
		  order_total_inc_fee float(10) NOT NULL,
		  exchange_fee float(10) NULL,
		  order_total_1_exc_fee float(10) NULL,
		  order_total_1_inc_fee float(10) NULL,
		  exchange_fee_1 float(10) NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";
		// Executing the query
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		// Execute the query one by one
		dbDelta($order_currencies_table_query);
	}
}
register_activation_hook(__FILE__, 'xs_mcs_activation_hook');

// Defining Constants
define('XSMCS_ROOT_FILE', __FILE__);
define('XSMCS_ROOT_PATH', dirname(__FILE__));
define('XSMCS_ROOT_URL', plugins_url('', __FILE__));
define('XSMCS_PLUGIN_VERSION', '0.0.1');
define('XSMCS_PLUGIN_SLUG', basename(dirname(__FILE__)));
define('XSMCS_PLUGIN_BASE', plugin_basename(__FILE__));

// including plugin main file
if ( ! class_exists( 'XSMultiCurrency' ) ) {
	require XSMCS_ROOT_PATH . '/includes/class.xs-multi-currency.php';
}
$XSMultiCurrency =  new XSMultiCurrency();
?>