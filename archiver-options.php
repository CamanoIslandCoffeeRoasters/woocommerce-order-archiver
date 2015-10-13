<?php


/*
	Plugin Name: Woocommerce Order Archiving
 	Description: Customized Order Archiving
 	Author: Tanner Legasse
 	Author URI: 
 	Version: 0.9
*/
define( 'WOOCOMMERCE_ARCHIVING_URL', __FILE__);
define( 'WOOCOMMERCE_ARCHIVING_PATH', untrailingslashit(plugin_dir_path(__FILE__)));


function woocommerce_order_archiver() {
	add_menu_page('Archiving', 'Archiving', 'manage_options', 'archiver_dashboard', 'order_archiver_dashboard_callback', '');
	}
	register_activation_hook( __FILE__, 'build_table' );
	add_action('admin_menu', "woocommerce_order_archiver");
	
	
	function order_archiver_dashboard_callback() {
	include WOOCOMMERCE_ARCHIVING_PATH . '/admin/dashboard.php';
}


function build_table() {
	global $wpdb;
	$table_name = $wpdb->prefix . "orders_archived";
	
	$sql = 
	"CREATE TABLE IF NOT EXISTS `" . $table_name . "` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `order_id` int(11) NOT NULL,
	  `user_id` int(11) NOT NULL,
	  `subscription_id` int(11) NOT NULL,
	  `name` varchar(255) NOT NULL,
	  `date` date NOT NULL,
	  `email` varchar(255) NOT NULL,
	  `products` varchar(255) NOT NULL,
	  `order_total` decimal(15,2) NOT NULL,
	  `auth_profile` int(11) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}

?>