<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if ($mode == 'view' && !empty($_REQUEST['product_id'])) {

    $currencies  = Registry::get('currencies');
    $coefficient = 1;

    $priceFilterData = fn_get_product_filter_fields();

    if (array_key_exists($priceFilterData['P']['extra'], $currencies))
    {
        $activeCurrency = $priceFilterData['P']['extra'];
    }
    else
    {
        $activeCurrency = CART_PRIMARY_CURRENCY;
    }

    if (array_key_exists($activeCurrency, $currencies))
    {
        $coefficient = (float)$currencies[$activeCurrency]['coefficient'];
    }

    $product_id = $_REQUEST['product_id'];

    if (!empty($product_id))
    {
        $catId = db_get_field('SELECT category_id FROM ?:products_categories WHERE product_id = ?i LIMIT 1', $product_id);

        if (!$catId)
        {
            $catId = 0;
        }

        Registry::get('view')->assign('catid', $catId);

        list($products) = fn_get_products(array('pid' => $product_id));

        $product = reset($products);

        fn_promotion_apply('catalog', $product, $_SESSION['auth']);

        $ra_fullPrice  = round($product['list_price'] / $coefficient, 2);
        $ra_promoPrice = round($product['price'] / $coefficient, 2);

        if ($ra_fullPrice <= 0)
        {
            $ra_fullPrice  = $ra_promoPrice;
            $ra_promoPrice = 0;
        }

        if($ra_promoPrice >= $ra_fullPrice)
        {
            $ra_promoPrice = 0;
        }

        Registry::get('view')->assign('ra_fullPrice', $ra_fullPrice);
        Registry::get('view')->assign('ra_promoPrice', $ra_promoPrice);
    }
}
