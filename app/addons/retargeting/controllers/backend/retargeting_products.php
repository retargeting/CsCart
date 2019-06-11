<?php

use Tygh\Http;
use Tygh\Registry;
use Retargeting\Client;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$show_process = (isset($_REQUEST['show_process'])) ? $_REQUEST['show_process'] : 'N';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

	return;

}

if ($mode == 'update') {

	require_once Registry::get('config.dir.addons') . 'retargeting/lib/Client.php';
	$client = new Client(Registry::get('addons.retargeting.retargeting_discounts_api'));
	$client->setResponseFormat('json');
	$client->setDecoding(false);

	$params = array(
		'only_short_fields' => true,
		'status' => 'A',
		'retargeting' => 'Y',
		//'pid' => 122492,
		//'limit' => 15,
    );

	if ($show_process) {
		fn_set_progress('echo','Preparing products...');
	}
	
    list($products, $search) = fn_get_products($params);

	if (!empty($products)) {

		if ($show_process) {
			fn_set_progress('parts', $search['total_items']);
		}
		
		$p = 0;
		$i = 0;
		
		$retargeting_products = array();
		
		foreach ($products as $v) {

			$v['main_pair'] = fn_get_image_pairs($v['product_id'], 'product', 'M');
			
			$retargeting_products[] = array(
				'productId' => $v['product_id'],
				'image' => (!empty($v['main_pair'])) ? $v['main_pair']['detailed']['image_path'] : '',
				'name' => $v['product'],
				'price' => fn_format_price($v['price']),
				'promo' => (floatval($v['list_price']) != 0) ? fn_format_price($v['list_price'] - $v['price']) : 0,
				'stock' => ($v['amount'] > 0) ? 1 : 0,
			);
			
			$i++;
			$p++;
			
			if ($show_process) {
                fn_set_progress('echo','Updating products...');
            } else {
                fn_echo('. ');
            }
	
			if ($i == 100 || $p == $search['total_items']) {

				$return = $client->products->update(json_encode($retargeting_products));

				$i = 0;
				$retargeting_products = array();
	
			}

		}
		
	}

	if ($show_process) {
		fn_set_notification('N', __('notice'), __('done'));
	}
	
	exit('done');
	
}
