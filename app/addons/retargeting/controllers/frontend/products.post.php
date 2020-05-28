<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($mode == 'view' && !empty($_REQUEST['product_id'])) {

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

    $product_id = $_REQUEST['product_id'];

    if (!empty($product_id)) {
        $catId = db_get_field('SELECT category_id FROM ?:products_categories WHERE product_id = ?i LIMIT 1', $product_id);

        if (!$catId) {
            $catId = 0;
        }

        Registry::get('view')->assign('catid', $catId);

        list($products) = fn_get_products(array('pid' => $product_id));

        fn_gather_additional_products_data($products, [
            'get_icon'      => true,
            'get_detailed'  => true,
            'get_discounts' => false
        ]);

        $product = reset($products);

        fn_promotion_apply('catalog', $product, $_SESSION['auth']);

        $price = fn_format_price($product['price']);
        $list_price = fn_format_price($product['list_price']);
        $base_price = fn_format_price($product['base_price']);

        if(($base_price == $price) && ($list_price > $base_price)) {
            $ra_price = $list_price;
        } elseif(($base_price == $price) && ($list_price < $base_price)) {
            $ra_price = $base_price;
        } else {
            $ra_price = $base_price;
        }

        $ra_promo = $price;

        Registry::get('view')->assign('ra_fullPrice', round($ra_price / $coefficient, 2));
        Registry::get('view')->assign('ra_promoPrice', round($ra_promo / $coefficient,2));
    }
}
