<?php

use Tygh\Http;
use Tygh\Registry;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }


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

