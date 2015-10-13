<?php
global $woocommerce;
global $wpdb;
$number_of_orders_archived = 0;
$months_back = 0;

/*
 * 	If $_GET is set, the process runs.
 */

// if (isset($_GET['monthsback'])) {
if (isset($_POST['monthsback'])) {
	//$months_back = date("Y-m-d", strtotime("-" . $_GET['monthsback'] . " months"));
	$months_back = date("Y-m-d", strtotime("-" . $_POST['monthsback'] . " months"));
	
	$args = array(
    'post_type' => 'shop_order',
    'post_status' => 'wc_completed',
    'date_query' => array('before' => $months_back)
	);
	
	$orders_array = get_posts($args);
	
	/*
	 *	This is the order archiver's main function loop. Orders that appear in the $orders_array will be processed by it. 
	 */
	 
	foreach ($orders_array as $orderID) {
	    
		$number_of_orders_archived++;
		
		
	    /* This group of definitions manages different aspects that are deemed important by the all powerful Tobin, and
	     * thusly will find themselves catalogued in the $wpdb->prefix . 'orders_archived' table.
	     * Important items that don't have a value set will inherit a default value seen in the turnary events.
	     */
	     
	    $ourOrder       = new WC_Order($orderID->ID);
	    $orderArray		= get_post_meta($orderID->ID);
	    $subscriptionID = $orderArray['subscription_id'];
	    $source         = $orderArray['subscription_source'];
	    $customerUser   = isset($ourOrder->customer_user) ? $ourOrder->customer_user : '1';
	    $first          = isset($ourOrder->billing_first_name) ? $ourOrder->billing_first_name : '1';
	    $last           = isset($ourOrder->billing_last_name) ? $ourOrder->billing_last_name : '1';
	    $customerEmail  = isset($ourOrder->billing_email) ? $ourOrder->billing_email : '1';
	    $orderTotal     = isset($ourOrder->order_total) ? $ourOrder->order_total : '1';
	    $orderDate      = isset($ourOrder->order_date) ? $ourOrder->order_date : '1';
	    $items          = $ourOrder->get_items();
	    //$discounts      = isset($ourOrder->get_total_discount()) ? $ourOrder->get_total_discount() : '1';
	    $authProfile    = isset($ourOrder->wc_authorize_net_cim_customer_profile_id) ? $ourOrder->wc_authorize_net_cim_customer_profile_id : '1';
		
		/*
		 * 	The below foreach will pull the items array apart and process each item, finding its SKU and adding them to a comma separated list.
		 */
		
		
	    foreach ($items as $item) {
			
	        $skus   = array();
	        $id     = '';
	        $id     = (isset($item['item_meta']['_variation_id'])) ? $item['item_meta']['_variation_id'][0] : $item['item_meta']['_product_id'][0];
	        $item   = new WC_Product($id);
	        $skus[] = $item->get_sku();
			
		}
			
        $orderItems = implode($skus, ",");
        $params     = array(
            'order_Id' => $orderID->ID,
            'user_id' => $customerUser,
            'subscription_id' => $subscriptionID,
            'name' => $first . " " . $last,
            'date' => date('Y-m-d', strtotime($orderDate)),
            'email' => $customerEmail,
            'products' => $orderItems,
            'order_total' => $orderTotal,
            'auth_profile' => $authProfile,
            'address' => $ourOrder->formatted_shipping_address );
        $wpdb->insert($wpdb->prefix . 'orders_archived', $params);
		
		
		/*
		 * 	This is the scary part of the plugin. While active, the below few lines will delete the original orders from the many 
		 *  tables found in both wordpress and woocommerce after saving the important information to the archived orders table.
		 */
		
		
		//if ($_GET['delete']) {
		if ($_POST['delete']) {
	        wp_delete_post($orderID->ID);
	        $wpdb->delete($wpdb->prefix . 'woocommerce_order_items', array("order_item_id" => $orderID->ID));
	        $wpdb->delete($wpdb->prefix . 'woocommerce_order_itemmeta', array("order_item_id" => $orderID->ID));
		}
	}
};


?>

		<h1>Order Archiver.</h1>
		<p>This plugin should be used with great care, as it is very powerful and changes customer frontend functionality.</p>
		<p>Contact one of the IT guys for help if you feel like running this, maybe?</p>
		<form action="" method="POST">
			Process Deletions?&nbsp;<input type="checkbox" name="delete">
			<input type="number" name="monthsback" min="0" placeholder="Months Back" value="0">
			<input type="submit" class="button-primary" value="Run it!">
		</form>
		
<?php

//if (isset($_GET['monthsback'])) {
if (isset($_POST['monthsback'])) {
	
	if ($number_of_orders_archived > 0) {
		$string = "<h2 style='background: lightgreen; padding: 5px;' id='orders'>%d orders were archived from the beginning of time to %s.</h2>";
		echo sprintf($string, $number_of_orders_archived, $months_back);
	} else  {
		$string = "<h2 style='background: lightgreen; padding: 5px;' id='orders'>There were no orders to archive from the beginning of time to %s.</h2>";
		echo sprintf($string, $months_back);
	}
	
}

?>