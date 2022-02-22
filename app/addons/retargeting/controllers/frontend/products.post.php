<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

// json encode - float precision
if (version_compare(phpversion(), '7.1', '>=')) {
    ini_set( 'serialize_precision', -1 );
}

if ($mode == 'view' && !empty($_REQUEST['product_id'])) {

    $coefficient = fn_retargeting_get_coefficient();

    $product_id = $_REQUEST['product_id'];

    if (!empty($product_id)) {

        list($products) = fn_get_products(array('pid' => $product_id));

        fn_gather_additional_products_data($products, [
            'get_icon'      => true,
            'get_detailed'  => true,
            'get_discounts' => false
        ]);

        $product = reset($products);

        fn_promotion_apply('catalog', $product, $_SESSION['auth']);

        $product_stock = ($product['amount'] <= 0 || $product['amount'] < $product['min_qty']) ? 0 : 1;

        $price = fn_format_price($product['price']);
        $list_price = fn_format_price($product['list_price']);
        $base_price = fn_format_price($product['base_price']);

        $ra_price = fn_retargeting_get_price($base_price, $price, $list_price);

        $ra_promo = $price;


        $price = round($ra_price / $coefficient, 2);
        $promo = round($ra_promo / $coefficient, 2);
/*
        $price = fn_retargeting_get_price_to_default_currency($price);
        $promo = fn_retargeting_get_price_to_default_currency($promo);
*/
        $promo = empty($promo) ? $price : $promo;

        $product_details = [
            "id" => $product['product_id'],
            'name' => $product['product'],
            'url' => fn_url('products.view?product_id=' . $product['product_id']),
            'img' => $product['main_pair']['detailed']['image_path'],
            'price' => $price,
            'promo' => $promo,
            'brand' => false,
            'category' => [[
                'id' => $product['main_category'],
                'name' => fn_get_category_name($product['main_category']),
                'parent' => false,
                'breadcrumb' => []
            ]],
            'inventory' => [
                'variations' => false,
                "stock" => $product_stock
            ]
        ];

        Registry::get('view')->assign('ra_product_info', json_encode($product_details, JSON_PRETTY_PRINT));
        Registry::get('view')->assign('ra_fullPrice', $price);

    }
}