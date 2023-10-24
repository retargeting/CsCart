<?php
use Tygh\Registry;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

if(file_exists(dirname(__FILE__ . '/Retargeting_REST_API_Client.php'))) {
    require(dirname(__FILE__) . '/Retargeting_REST_API_Client.php');
}

if(!function_exists('ra_price_calc')) {
    function ra_price_calc($price, $coefficient)
    {
        $total = ($price / $coefficient);
        return number_format($total, 2, '.', '');
    }
}

if ($mode == 'complete') {
    $orders_info = array();
    $order_info = Registry::get('view')->getTemplateVars('order_info');
    if (!fn_allowed_for('MULTIVENDOR') || (fn_allowed_for('MULTIVENDOR') && $order_info['is_parent_order'] == 'N')) {
        $orders_info[0] = $order_info;
    }
    Registry::get('view')->assign('orders_info', $orders_info);

    $currencies = Registry::get('currencies');
    $coefficient = 1;
    $orderCurrency = $order_info['secondary_currency'];

    if (array_key_exists($order_info['secondary_currency'], $currencies)) {
        $coefficient = $currencies[$orderCurrency]['coefficient'];
    }

    $orderInfo = array(
        "order_no" => $order_info['order_id'],
        "lastname" => $order_info['lastname'],
        "firstname" => $order_info['firstname'],
        "email" => $order_info['email'],
        "phone" => $order_info['phone'] ?? '',
        "state" => $order_info['s_country'],
        "city" => $order_info['s_city'],
        "address" => $order_info['b_address'],
        "discount_code" => '',
        "discount" => '',
        "shipping" => ra_price_calc($order_info['shipping_cost'], $coefficient),
        "total" => ra_price_calc($order_info['total'], $coefficient)
    );

    $productsArray = array(
        'line_items' => array(),
    );

    foreach ($order_info['products'] as $product) {
        $productDetails = array(
            'id' => $product['product_id'],
            'quantity' => $product['amount'],
            'price' => ra_price_calc($product['price'], $coefficient),
            'variation_code' => ''
        );
        $productsArray['line_items'][] = $productDetails;
    }

    $discountApi = Settings::instance()->getValue('retargeting_discounts_api', 'retargeting');

    if ($discountApi && $discountApi != '' && class_exists('Retargeting_REST_API_Client')) {
        $orderClient = new Retargeting_REST_API_Client($discountApi);
        $orderClient->setResponseFormat("json");
        $orderClient->setDecoding(false);
        $response = $orderClient->order->save($orderInfo, $productsArray['line_items']);
    }

    Registry::get('view')->assign('ra_saveOrderInfo', $orderInfo);
    Registry::get('view')->assign('ra_saveOrderProducts', $productsArray['line_items']);

}

if ($mode == 'checkout' || $mode == 'cart') {
    $checkoutIds = array();

    foreach ($_SESSION['cart']['products'] as $product) {
        $checkoutIds[] = $product['product_id'];
    }

    $checkoutId = json_encode($checkoutIds);

    Registry::get('view')->assign('checkoutid', $checkoutId);
}
