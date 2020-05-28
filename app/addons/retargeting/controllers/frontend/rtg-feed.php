<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$lang_code = CART_LANGUAGE;

if ($mode === 'list') {
    header("Content-Disposition: attachment; filename=retargeting.csv");
    header("Content-type: text/csv");
    $outstream = fopen('php://output', 'w');

    fputcsv($outstream, array(
        'product id',
        'product name',
        'product url',
        'image url',
        'stock',
        'price',
        'sale price',
        'brand',
        'category',
        'extra data'
    ), ',', '"');

    list($products) = fn_get_products([]);

    fn_gather_additional_products_data($products, [
        'get_icon'      => true,
        'get_detailed'  => true,
        'get_discounts' => false
    ]);

    foreach($products as $product) {

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
        $ra_price = $ra_promo = 0;

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

        if(($base_price == $price) && ($list_price > $base_price)) {
            $ra_price = $list_price;
        } elseif(($base_price == $price) && ($list_price < $base_price)) {
            $ra_price = $base_price;
        } else {
            $ra_price = $base_price;
        }

        $ra_promo = $price;

        if($ra_price == 0 || $ra_promo == 0) {
            continue;
        }

        fputcsv($outstream, array(
            'product id' => $product['product_id'],
            'product name' => fn_get_product_name($product['product_id'], CART_LANGUAGE, false),
            'product url' => fn_url('products.view?product_id=' . $product['product_id']),
            'image url' => $product['main_pair']['detailed']['image_path'],
            'stock' => fn_get_product_amount($product['product_id']),
            'price' => round($ra_price / $coefficient, 2), //round($product['list_price'] / $coefficient, 2)
            'sale price' => round($ra_promo / $coefficient,2),
            'brand' => '',
            'category' => $category_name,
            'extra data' => json_encode(['currency' => $activeCurrency, 'language' => CART_LANGUAGE])
        ), ',', '"');

    }
    fclose($outstream);
    exit();
}
