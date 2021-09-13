<?php
error_reporting(E_ALL & ~E_NOTICE);

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$lang_code = CART_LANGUAGE;

if ($mode === 'list') {

    // json encode - float precision
    if (version_compare(phpversion(), '7.1', '>=')) {
        ini_set( 'serialize_precision', -1 );
    }

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

    foreach(fn_regargeting_get_products() as $product) {
        fputcsv($outstream, $product, ',', '"');
    }
    
    fclose($outstream);
    exit();
}
