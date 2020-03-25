<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode === 'list') {
    /** @var \Tygh\Storefront\Storefront $storefront */
    $storefront = Tygh::$app['storefront'];
    header("Content-Disposition: attachment; filename=retargeting.csv");
    header("Content-type: text/csv");
    $outstream = fopen('php://output', 'w');

    $columns = [
        'product id',
        'product name',
        'product url',
        'image url',
        'stock',
        'price',
        'sale price',
        'brand',
        'category'
    ];

    fputcsv($outstream, array(
        'product id',
        'product name',
        'product url',
        'image url',
        'stock',
        'price',
        'sale price',
        'brand',
        'category'
    ), ',', '"');

    list($products) = fn_get_products([]);
    
    fn_gather_additional_products_data($products, [
        'get_icon'      => true,
        'get_detailed'  => true,
        'get_discounts' => false
    ]);

    foreach($products as $product) {

        $catId = db_get_field('SELECT category_id FROM ?:products_categories WHERE product_id = ?i LIMIT 1', $product['product_id']);
        $ra_price = $ra_promo = 0;

        if (!$catId) { $catId = 0; }

        $category_name = fn_get_category_name(
            $catId, 
            CART_LANGUAGE, 
            false
        );

        $price = fn_format_price($product['price']);
        $list_price = fn_format_price($product['list_price']);
        
        if($price < $list_price) {
            $ra_price = $list_price;
            $ra_promo = $price;
        } else {
            $ra_price = $price;
            $ra_promo = $price;
        }

        fputcsv($outstream, array(
            'product id' => $product['product_id'],
            'product name' => fn_get_product_name($product['product_id'], CART_LANGUAGE, false),
            'product url' => fn_url('products.view?product_id=' . $product['product_id']),
            'image url' => $product['main_pair']['detailed']['image_path'],
            'stock' => fn_get_product_amount($product['product_id']),
            'price' => $ra_price,
            'sale price' => $ra_promo,
            'brand' => '',
            'category' => $category_name
        ), ',', '"');

    }
    fclose($outstream);
    exit();
}
