<?php

use Tygh\Http;
use Tygh\Registry;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_dir()
{
	return __DIR__;
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

function fn_retargeting_get_price($base_price, $price, $list_price) {

    if(($base_price == $price) && ($list_price > $base_price)) {
        $ra_price = $list_price;
    } elseif(($base_price == $price) && ($list_price < $base_price)) {
        $ra_price = $base_price;
    } else {
        $ra_price = $base_price;
    }

    return $ra_price;
}


/**
 *
 * Get Currencies for addon.xml
 *
 * @return array
 */
function fn_settings_variants_addons_retargeting_retargeting_default_currency() {

    $currencies  = Registry::get('currencies');

    $currenciesArray = [];
    foreach ($currencies as $key => $currency) {
        $currenciesArray[$key] = $key;
    }

    return $currenciesArray;
}
function fn_settings_variants_addons_retargeting_retargeting_default_stock() {
    return [
        '1'=>'In Stock',
        '0'=>'Out of Stock'
    ];
}

function fn_settings_variants_addons_retargeting_with_vat() {
    return [
        '1'=>'Yes',
        '0'=>'No'
    ];
}
/*
function fn_settings_variants_addons_retargeting_retargeting_cron_feed() {
    return [
        '1'=>'Active',
        '0'=>'Not Active'
    ];
}
*/
function fn_retargeting_get_addon_variable($variable) {

    $addon = Registry::get('addons.retargeting');

    if (isset($addon[$variable])) {
        return $addon[$variable];
    } else {
        return null;
    }

}

function fn_regargeting_get_products($items = 250) {

    $productsLoop = true;
    $page = 1;
    $newList = [];

    while($productsLoop) {

        list($products) = fn_get_products(['page' => $page], $items);

        fn_gather_additional_products_data($products, [
            'get_icon'      => true,
            'get_detailed'  => true,
            'get_discounts' => false
        ]);
        $page++;

        if (empty($products)) {
            $productsLoop = false;
            break;
        }

        foreach ($products as $key => $product) {

            $category_name = fn_get_category_name(
                $product['main_category'],
                CART_LANGUAGE,
                false
            );

            fn_promotion_apply('catalog', $product, $_SESSION['auth']);

            $coefficient = fn_retargeting_get_coefficient();

            $price = fn_format_price($product['price']);
            $list_price = fn_format_price($product['list_price']);
            $base_price = fn_format_price($product['base_price']);

            $vat = 24;

            if (fn_retargeting_get_addon_variable('with_vat') == 1) {
                $price = $price + $price * $vat / 100;
                $list_price = $list_price + $list_price * $vat / 100;
                $base_price = $base_price + $base_price * $vat / 100;
            }

            $ra_price = fn_retargeting_get_price($base_price, $price, $list_price);

            $ra_promo = $price;

            $price = round($ra_price / $coefficient, 2);
            $promo = round($ra_promo / $coefficient, 2);

            if($price == 0 || $promo == 0 ||
                !isset($product['main_pair']) ||
                empty($product['main_pair']['detailed']['image_path'])) {
                continue;
            }

            
            $product['amount'] = $product['amount'] > 0 ?
                $product['amount'] : fn_retargeting_get_addon_variable('retargeting_default_stock');
    /*
            $price = fn_retargeting_get_price_to_default_currency($price);
            $promo = fn_retargeting_get_price_to_default_currency($promo);
    */
            $newList[] = [
                'product id' => $product['product_id'],
                'product name' => $product['product'],
                'product url' => fn_url('products.view?product_id=' . $product['product_id']),
                'image url' => $product['main_pair']['detailed']['image_path'],
                'stock' => $product['amount'],
                'price' => $price, //round($product['list_price'] / $coefficient, 2)
                'sale price' => $promo,
                'brand' => '',
                'category' => $category_name,
                'extra data' => json_encode(fn_retargeting_get_extra_data_product($product, $price, $promo))
            ];
        }
    }
    return $newList;
}

function fn_regargeting_get_prod($extra = null) {
    if ($extra === null) {
        $extra = [ 'getProducts' => true, 'page' => 1, 'limit' => 250 ];
    }

    list($products) = fn_get_products([
        'page' => $extra['page']
        //,
        // 'sort_by' => 'product_id',
        // 'sort_order'=>'desc'
    ], $extra['limit']);

    fn_gather_additional_products_data($products, [
        'get_icon'      => true,
        'get_detailed'  => true,
        'get_discounts' => false
    ]);

    $extra['page']++;
    $extra['list'] = [];

    if (empty($products)) {
        $extra['getProducts'] = false;

        return $extra;
    }

    foreach ($products as $key => $product) {

        $category_name = fn_get_category_name(
            $product['main_category'],
            CART_LANGUAGE,
            false
        );

        fn_promotion_apply('catalog', $product, $_SESSION['auth']);

        $coefficient = fn_retargeting_get_coefficient();

        $price = fn_format_price($product['price']);
        $list_price = fn_format_price($product['list_price']);
        $base_price = fn_format_price($product['base_price']);

        $vat = 24;

            if (fn_retargeting_get_addon_variable('with_vat') == 1) {
                $price = $price + $price * $vat / 100;
                $list_price = $list_price + $list_price * $vat / 100;
                $base_price = $base_price + $base_price * $vat / 100;
            }

        $ra_price = fn_retargeting_get_price($base_price, $price, $list_price);

        $ra_promo = $price;

        $price = round($ra_price / $coefficient, 2);
        $promo = round($ra_promo / $coefficient, 2);

        if($price == 0 || $promo == 0 ||
            !isset($product['main_pair']) ||
            empty($product['main_pair']['detailed']['image_path'])) {
            continue;
        }

        
        $product['amount'] = $product['amount'] > 0 ?
            $product['amount'] : fn_retargeting_get_addon_variable('retargeting_default_stock');
/*
        $price = fn_retargeting_get_price_to_default_currency($price);
        $promo = fn_retargeting_get_price_to_default_currency($promo);
*/
        $extra['list'][] = [
            'product id' => $product['product_id'],
            'product name' => $product['product'],
            'product url' => fn_url('products.view?product_id=' . $product['product_id']),
            'image url' => $product['main_pair']['detailed']['image_path'],
            'stock' => $product['amount'],
            'price' => $price, //round($product['list_price'] / $coefficient, 2)
            'sale price' => $promo,
            'brand' => '',
            'category' => $category_name,
            'extra data' => json_encode(fn_retargeting_get_extra_data_product($product, $price, $promo))
        ];
    }
    
    return $extra;
}

function fn_retargeting_get_price_to_default_currency($price) {

    $defaultCurrency = fn_retargeting_get_addon_variable('retargeting_default_currency');

    return fn_format_price_by_currency($price,CART_SECONDARY_CURRENCY, $defaultCurrency);

}

function fn_retargeting_get_extra_data_product($product, $price, $promo) {

    if (!isset($product['product_options'])) {
        return [];
    }

    $img = fn_retargeting_get_images($product);
    $img = empty($img) ? [] : $img;
    $img = empty($img[0]) ? [] : $img;
    $extraData = [];
    $extraData['margin'] = null;
    
    $extraData['categories'] = [];
    $extraData['media_gallery'] = $img;
    $extraData['in_supplier_stock'] = null;
    $extraData['variations'] = null;
    
    foreach(fn_retargeting_get_category_name($product) as $k=>$v){
        $extraData['categories'][$k] = $v;
    }
    $variations = null;
    foreach($product['product_options'] as $key => $option) {
        if (isset($option['variants'])) {
            $variations = $option['variants'];
            break;
        }
    }
    if ($variations !== null) {
        foreach ($variations as $key => $variation) {

            $extraData['variations'][] = [
                'code' => $variation['variant_id'],
                'price' => $price + $variation['modifier'],
                'sale_price' => $promo + $variation['modifier'],
                'stock' => 1,
                'margin' => null,
                'in_supplier_stock' => null
            ];

        }
    }

    return $extraData;
}

function fn_retargeting_get_coefficient() {

    $currencies  = Registry::get('currencies');
    $coefficient = 1;

    $priceFilterData = fn_get_product_filter_fields();

    if (array_key_exists($priceFilterData['P']['extra'], $currencies)) {
        $activeCurrency = $priceFilterData['P']['extra'];
    } else {
        $activeCurrency = CART_PRIMARY_CURRENCY;
    }

    if (array_key_exists($activeCurrency, $currencies)) {
        $coefficient = (float)$currencies[$activeCurrency]['coefficient'];
    }

    return $coefficient;
}

function fn_retargeting_get_category_name($product) {

    return fn_get_categories_list(implode(',', $product['category_ids']));

}

function fn_retargeting_get_images($product) {

    $additionalImages = fn_get_image_pairs($product['product_id'], 'product', 'A');

    $images = [];
    $images[] = $product['main_pair']['detailed']['https_image_path'];
    if (is_array($additionalImages)) {
        foreach ($additionalImages as $image) {
            $images[] = $image['detailed']['https_image_path'];
        }
    }
    return $images;
}