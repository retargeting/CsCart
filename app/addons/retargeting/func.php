<?php

use Tygh\Http;
use Tygh\Registry;
use Tygh\Settings;
use Retargeting\Client;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_retargeting_get_products(&$params, &$fields, &$sortings, &$condition, &$join, &$sorting, &$group_by, &$lang_code, &$having) {

	if (isset($params['retargeting']) && !empty($params['retargeting'])) {
		$condition .= db_quote(' AND products.retargeting = ?s ', $params['retargeting']);
	}

}

function fn_retargeting_update_category_post(&$category_data, &$category_id, &$lang_code) {

	if (isset($category_data['description']) && isset($category_data['retargeting_apply_to_subcategories'])) {
		$id_path = db_get_field('SELECT id_path FROM ?:categories WHERE category_id = ?i', $category_id);
		$cat_ids = db_get_fields('SELECT category_id FROM ?:categories WHERE id_path LIKE ?l', $id_path . '/%');
		if (!empty($cat_ids)) {
			db_query('UPDATE ?:categories SET retargeting = ?s WHERE category_id IN (?a)', $category_data['retargeting'], $cat_ids);
		}
	}

}

function fn_retargeting_update_product_post(&$product_data, &$product_id, &$lang_code, &$create) {

	require_once Registry::get('config.dir.addons') . 'retargeting/lib/Client.php';
	$client = new Client(Registry::get('addons.retargeting.retargeting_discounts_api'));
	$client->setResponseFormat('json');
	$client->setDecoding(false);

	$product_data['main_pair'] = fn_get_image_pairs($product_id, 'product', 'M');

	$retargeting_products = array(
		'productId' => $product_id,
		'image' => (!empty($product_data['main_pair'])) ? $product_data['main_pair']['detailed']['image_path'] : '',
		'name' => $product_data['product'],
		'price' => fn_format_price($product_data['list_price']),
		'promo' => fn_format_price($product_data['price']),
		'stock' => ($product_data['amount'] > 0) ? 1 : 0,
	);

	$return = $client->products->update($retargeting_products);
	//fn_print_die($return, $retargeting_products);
}

// Get main cat
function fn_retargeting_get_main_category($product_id, $lang_code = DESCR_SL)
{
	$category = '';

    if (!empty($product_id))
    {
        $category = db_get_field("SELECT ?:category_descriptions.category FROM ?:category_descriptions RIGHT JOIN ?:products_categories ON ?:category_descriptions.category_id = ?:products_categories.category_id AND ?:products_categories.product_id = ?i AND link_type = 'M' WHERE lang_code = ?s", $product_id, $lang_code);
    }

    return $category;
}

/*
* Get Retargeting Domain API Key
*/

function fn_retargeting_get_domain_api_key($company_id = null)
{
	if (!fn_allowed_for('ULTIMATE'))
	{
        $company_id = null;
    }

    return Settings::instance()->getValue('retargeting_domain_api','retargeting');
}

function fn_retargeting_get_order_items_info_post(&$order, &$v, &$k)
{
	$order['products'][$k]['retargeting_category_name'] = fn_retargeting_get_main_category($v['product_id'], $order['lang_code']);
}

