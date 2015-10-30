<?php

use Tygh\Registry;

if(!defined('BOOTSTRAP')) { die('Access denied'); }

if($mode == 'complete'){
	$orders_info = array();
	$order_info = Registry::get('view')->getTemplateVars('order_info');
	if (!fn_allowed_for('MULTIVENDOR') || (fn_allowed_for('MULTIVENDOR') && $order_info['is_parent_order'] == 'N')) {
		$orders_info[0] = $order_info;
	}
	Registry::get('view')->assign('orders_info', $orders_info);
}

if($mode == 'checkout' || $mode == 'cart'){
	$checkoutIds = array();
	
	foreach ($_SESSION['cart']['products'] as $product) {
		$checkoutIds[] = $product['product_id'];
	}

	$checkoutId = json_encode($checkoutIds);
	
	Registry::get('view')->assign('checkoutid', $checkoutId);
}
