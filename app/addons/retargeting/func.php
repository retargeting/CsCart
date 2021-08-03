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


            $catId = db_get_field('SELECT category_id FROM ?:products_categories WHERE product_id = ?i LIMIT 1', $product['product_id']);

            if (!$catId) { $catId = 0; }

            $category_name = fn_get_category_name(
                $catId,
                CART_LANGUAGE,
                false
            );

            fn_promotion_apply('catalog', $product, $_SESSION['auth']);

            $price = fn_format_price($product['price']);
            $list_price = fn_format_price($product['list_price']);
            $base_price = fn_format_price($product['base_price']);

            $ra_price = fn_retargeting_get_price($base_price, $price, $list_price);

            $ra_promo = $price;

            $price = round($ra_price / $coefficient, 2);
            $promo = round($ra_promo / $coefficient,2);
            $price = fn_retargeting_get_price_to_default_currency($price);
            $promo = fn_retargeting_get_price_to_default_currency($promo);

            if($ra_price == 0 || $ra_promo == 0 || !isset($product['main_pair']) || $product['main_pair']['detailed']['image_path'] ==="") {
                continue;
            }


            yield [     'product id' => $product['product_id'],
                        'product name' => fn_get_product_name($product['product_id'], CART_LANGUAGE, false),
                        'product url' => fn_url('products.view?product_id=' . $product['product_id']),
                        'image url' => $product['main_pair']['detailed']['image_path'],
                        'stock' => fn_get_product_amount($product['product_id']),
                        'price' => $price, //round($product['list_price'] / $coefficient, 2)
                        'sale price' => $promo,
                        'brand' => '',
                        'category' => $category_name,
                        'extra data' => json_encode([
                                'currency' => $activeCurrency,
                                'language' => CART_LANGUAGE
                            ] + fn_retargeting_get_extra_data_product($product))
                ];

        }

    }

}

function fn_retargeting_get_price_to_default_currency($price) {

    if ( floatval(PRODUCT_VERSION) > 4.9 ) {
        $defaultCurrency = fn_retargeting_get_addon_variable('retargeting_default_currency');
        $price = fn_format_price_by_currency($price,CART_SECONDARY_CURRENCY, $defaultCurrency);
    }
   
    return $price;
}

function fn_retargeting_get_extra_data_product($product) {

    if (!isset($product['product_options'])) {
        return [];
    }

    foreach($product['product_options'] as $key => $option) {

        if (isset($option['variants'])) {
            $variations = $option['variants'];
            break;
        }

    }

    $coefficient = fn_retargeting_get_coefficient();

    $price = fn_format_price($product['price']);
    $list_price = fn_format_price($product['list_price']);
    $base_price = fn_format_price($product['base_price']);

    $ra_price = fn_retargeting_get_price($base_price, $price, $list_price);

    $ra_promo = $price;


    $price = round($ra_price / $coefficient, 2);
    $promo = round($ra_promo / $coefficient,2);

    $price = fn_retargeting_get_price_to_default_currency($price);
    $promo = fn_retargeting_get_price_to_default_currency($promo);

    $extraData = [];
    $extraData['margin'] = null;
    $extraData['categories'] = implode(' | ', fn_retargeting_get_category_name($product));
    $extraData['media_gallery'] = fn_retargeting_get_images($product);
    $extraData['in_supplier_stock'] = null;
    $extraData['variations'] = null;

    foreach ($variations as $key => $variation) {

        $extraData['variations'][] = [
            'id' => $variation['variant_id'],
            'price' => $price + $variation['modifier'],
            'sale price' => $promo + $variation['modifier'],
            'stock' => true,
            'margin' => null,
            'in_supplier_stock' => null
        ];

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
    if (!isset($product['main_pair']) || $product['main_pair']['detailed']['image_path'] ==="" ){
        return false;
    }
    $additionalImages = fn_get_image_pairs($product['product_id'], 'product', 'A');

    $images = [];
    $images[] = $product['main_pair']['detailed']['https_image_path'];
    foreach ($additionalImages as $image) {
        $images[] = $image['detailed']['https_image_path'];
    }

    return $images;
}
