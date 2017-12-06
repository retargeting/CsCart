<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($mode == 'view' && !empty($_REQUEST['product_id'])) {

    $currencies = Registry::get('currencies');
    $coefficient = 1;

    foreach ($currencies as $currency) {
        if ($currency['status'] == 'A') {
            $coefficient = $currency['coefficient'];
        }
    }

    $product_id = $_REQUEST['product_id'];
    if (!empty($product_id)) {
        $catId = db_get_field('SELECT category_id FROM ?:products_categories WHERE product_id = ?i LIMIT 1', $product_id);

        if ($catId) {
            Registry::get('view')->assign('catid', $catId);
        } else {
            $catId = 0;
            Registry::get('view')->assign('catid', $catId);
        }

        list($products) = fn_get_products(array('pid' => $product_id));

        $product = reset($products);


        $ra_fullPrice = number_format($product['list_price'] / $coefficient, 2, ".", "");
        $ra_promoPrice = number_format($product['price'] / $coefficient, 2, ".", "");


        if ($ra_fullPrice <= $ra_promoPrice) {
            Registry::get('view')->assign('ra_fullPrice', $ra_promoPrice);
            Registry::get('view')->assign('ra_promoPrice', $ra_fullPrice);
        } else {
            Registry::get('view')->assign('ra_fullPrice', $ra_fullPrice);
            Registry::get('view')->assign('ra_promoPrice', $ra_promoPrice);
        }

    }
}
