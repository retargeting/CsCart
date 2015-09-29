<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'list') {
header("Content-type: text/xml");
	$auth = & $_SESSION['auth'];

	$raFeedProducts = fn_get_products(array());
	$raFeedProducts = $raFeedProducts[0];
	
	$product_url = '//';
	if (Registry::get('config.https_host') != '') {
		$product_url = (Registry::get('config.http_host') == Registry::get('config.https_host') ? 'http://' : 'https://');
		$product_url .= Registry::get('config.https_host') . Registry::get('config.https_path');
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>';
	echo '<products>';
	
	foreach ($raFeedProducts as $product) {

		$product_data = fn_get_product_data($product['product_id'], $auth);

		$category_data = fn_get_category_data($product['main_category']);

		$category_path = '';
		$category_path = $category_data['seo_name'].$category_path;

		while ($category_data['level'] > 1) {
			
			$category_data = fn_get_category_data($category_data['parent_id']);

			$category_path = $category_data['seo_name'].'/'.$category_path;
		}

		
		echo '
			<product>
				<id>'.$product['product_id'].'</id>
				<stock>'.($product['amount'] > 0 ? 1 : 0).'</stock>
				<price>'.($product['list_price'] != 0 ? $product['list_price'] : $product['price']).'</price>
				<promo>'.($product['list_price'] != 0 ? $product['price'] : $product['list_price']).'</promo>
				<url>'.$product_url.'/'.$category_path.'/'.$product_data['seo_name'].'</url>
				<image>'.$product_data['main_pair']['detailed']['http_image_path'].'</image>
			</product>';
	}

	echo '</products>';

	die();
	return false;
}