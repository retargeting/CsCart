<?php

use Tygh\Http;
use Tygh\Registry;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

class Recengine {
    private static $rec_engine = array(
        "index.index" => "home_page",
        "checkout.complete" => "thank_you_page", /* Importanta Ordinea checkout_onepage_success */

        "checkout.cart" => "shopping_cart",
        "checkout.checkout" => "shopping_cart",

        "categories.view" => "category_page",

        "products.view" => "product_page",

        "products.search" => "search_page",
        "_no_page" => "page_404"
    );

    /* TODO: RecEngine */
    private static $def = array(
        "value" => "",
        "selector" => ".tygh-content",
        "place" => "after"
    );

    private static $blocks = array(
        'block_1' => array(
            'title' => 'Block 1',
            'def_rtg' => array(
                "value"=>"",
                "selector"=>".tygh-content",
                "place"=>"before"
            )
        ),
        'block_2' => array(
            'title' => 'Block 2',
        ),
        'block_3' => array(
            'title' => 'Block 3'
        ),
        'block_4' => array(
            'title' => 'Block 4'
        )
    );

    private static $fields = [
        'home_page' => array(
            'title' => 'Home Page',
            'type'  => 'rec_engine'
        ),
        'category_page' => array(
            'title' => 'Category Page',
            'type'  => 'rec_engine'
        ),
        'product_page' => array(
            'title' => 'Product Page',
            'type'  => 'rec_engine'
        ),
        'shopping_cart' => array(
            'title' => 'Shopping Cart',
            'type'  => 'rec_engine'
        ),
        'thank_you_page' => array(
            'title' => 'Thank you Page',
            'type'  => 'rec_engine'
        ),
        'search_page' => array(
            'title' => 'Search Page',
            'type'  => 'rec_engine'
        ),
        'page_404' => array(
            'title' => 'Page 404',
            'type'  => 'rec_engine'
        )
    ];

    public static function Update() {
        foreach ($_POST['rec_data'] as $key => $value) {
            Settings::instance()->updateValue($key, self::serialized($value), 'retargeting');
        }

        return false;
    }

    public static function serialized($value) {
        return base64_encode(is_array($value) ? serialize($value) : array($value));
    }

    public static function unserialized($value) {
        return unserialize(base64_decode($value));
    }

    public static function form($data) {
        $form = array();

        foreach (self::$fields as $row=>$selected) {
            $key = $row;

            $value = isset($data[$key]) && !empty($data[$key]) ? self::unserialized($data[$key]) : null;

            if (!is_array($value)) {
                $value = array();
            }

            $form[] = '<div id="container_addon_option_retargeting_rec_data_'.$key.'" class="control-group setting-wide  retargeting">
                <label class="control-label ">
                <strong>'.$selected['title'].'</strong>
                </label>
                <div class="controls" style="padding-top: 5px;">
                ';   
            foreach (self::$blocks as $k=>$v) {
                
                if (empty($value[$k]['value']) && empty($value[$k]['selector'])) {
                    $def = isset($v['def_rtg']) ?
                        $v['def_rtg'] : (isset($selected['def_rtg']) ? $selected['def_rtg'] : null);
    
                    $value[$k] = $def !== null ? $def : self::$def;
                }
    
                $form[] = '<label for="addon_option_retargeting_rec_data_'.$key.'_'.$row.'_'.$k.'">
                <strong>'.$v['title'].'</strong>
                </label>';
                $form[] = '<textarea style="min-width: 50%; height: 75px;" class="form-control"'.
                        ' id="addon_option_retargeting_rec_data_'.$key.'_'.$row.'_'.$k.'" name="rec_data['.$key.']['.$k.'][value]" spellcheck="false">'.
                        $value[$k]['value'].'</textarea>'."\n";
    
                $form[] = '<p><span><strong>'.
                '<a href="javascript:void(0);" onclick="document.querySelectorAll(\'#'.$row.'_advace\').forEach((e)=>{e.style.display=e.style.display===\'none\'?\'block\':\'none\';});">'.
                'Show/Hide Advance</a></strong></span></p>';
    
                $form[] = '<span id="'.$row.'_advace" style="display:none" >'.
                        '<input style="width:100%; max-width:225px;display:inline;" class="form-control"'.
                        ' id="'.$row.'_'.$k.'_adv_sel" type="text" name="rec_data['.$key.']['.$k.'][selector]" '.
                        'value="'.$value[$k]['selector'].'" />'."\n";
    
                $form[] = '<select style="width:100%;max-width:100px;display:inline;" class="form-control" id="'.$row.'_'.$k.'_adv_opt" name="rec_data['.$key.']['.$k.'][place]">'."\n";
    
                foreach (['before', 'after'] as $v)
                {
                    $form[] = '<option value="'.$v.'"'.($value[$k]['place'] === $v ? ' selected="selected"' : '' );
                    $form[] = '>'.$v.'</option>'."\n";  
                }
    
                $form[] = '</select></span><br />'."\n";
            }

            $form[] = '</div>
            </div>';
        }
        return implode("", $form);
    }

    public static function recstatus() {
        return (bool) self::cfg();
    }

    public static function apistatus() {
        return (Registry::get('addons.retargeting.status') == 'A');
    }

    public static function cfg($key = 'rec_status') {
        
        $v = Settings::instance()->getValue($key, 'retargeting');
        $v = $key === 'rec_status' ? $v : self::unserialized($v);

        if (is_array($v)) {
            foreach($v as $k=>$vv) {
                $v[$k]['value'] = html_entity_decode($vv['value']);
            }
        }

        return $v;
    }

    public static function rec_engine_load($ActionName = null) {
        if (self::apistatus() && self::recstatus()) {
            if (isset(self::$rec_engine[$ActionName])) {
                return '
                var _ra_rec_engine = {};
    
                _ra_rec_engine.init = function () {
                    let list = this.list;
                    for (let key in list) {
                        _ra_rec_engine.insert(list[key].value, list[key].selector, list[key].place);
                    }
                };
    
                _ra_rec_engine.insert = function (code = "", selector = null, place = "before") {
                    if (code !== "" && selector !== null) {
                        let newTag = document.createRange().createContextualFragment(code);
                        let content = document.querySelector(selector);
    
                        content.parentNode.insertBefore(newTag, place === "before" ? content : content.nextSibling);
                    }
                };
                _ra_rec_engine.list = '.json_encode(self::cfg(self::$rec_engine[$ActionName])).';
                _ra_rec_engine.init();';
            }
        }
        return "";
    }
}


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
    }else{
        $ra_price = $list_price;
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

function fn_settings_variants_addons_retargeting_status() {
    return [
        'D'=>'Disabled',
        'A'=>'Enabled'
    ];
}
function fn_settings_variants_addons_retargeting_rec_status() {
    return [
        0=>'Disabled',
        1=>'Enabled'
    ];
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

            if($promo == 0) {
                $promo = $price - fn_get_promotion_value($product);
            }

            if ($price == 0 ) {
                $price = $promo + fn_get_promotion_value($product);
            }

            if($price == 0 || $promo == 0 ||
                !isset($product['main_pair']) ||
                empty($product['main_pair']['detailed']['image_path'])) {
                continue;
            }

            $productImage = (!empty($product['main_pair']['detailed']['image_path'])) ? $product['main_pair']['detailed']['image_path'] : '';

            
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
                'image url' => fixUrl($productImage),
                'stock' => $product['amount'],
                'price' => $price, //round($product['list_price'] / $coefficient, 2)
                'sale price' => $promo,
                'brand' => '',
                'category' => json_encode($category_name),
                'extra data' => json_encode(fn_retargeting_get_extra_data_product($product, $price, $promo))
            ];
        }
    }
    return $newList;
}

function fn_get_promotion_value($product) {
    $keys = array_keys($product['promotions']);
    $discount_value = 0;
    foreach ($keys as $key) {
        for ($i = 0; $i<count($product['promotions'][$key]['bonuses']);$i++)
            $discount_value = $discount_value + $product['promotions'][$key]['bonuses'][$i]['discount'];
    }
    return $discount_value;
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

        $productImage = (!empty($product['main_pair']['detailed']['image_path'])) ? $product['main_pair']['detailed']['image_path'] : '';

        
        $product['amount'] = $product['amount'] > 0 ?
            $product['amount'] : fn_retargeting_get_addon_variable('retargeting_default_stock');

        $product['amount'] = !empty($product['amount']) ? $product['amount'] : 0;
/*
        $price = fn_retargeting_get_price_to_default_currency($price);
        $promo = fn_retargeting_get_price_to_default_currency($promo);
*/
        $extra['list'][] = [
            'product id' => $product['product_id'],
            'product name' => $product['product'],
            'product url' => fn_url('products.view?product_id=' . $product['product_id']),
            'image url' => fixUrl($productImage),
            'stock' => $product['amount'],
            'price' => $price, //round($product['list_price'] / $coefficient, 2)
            'sale price' => $promo,
            'brand' => '',
            'category' => json_encode($category_name),
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
    $extraData['weight'] = fn_retargeting_get_product_weight($product);
    
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
    $images[] = fixUrl($product['main_pair']['detailed']['https_image_path']);
    if (is_array($additionalImages)) {
        foreach ($additionalImages as $image) {
            $images[] = fixUrl($image['detailed']['https_image_path']);
        }
    }
    return $images;
}

function fixUrl($url) {

    $url = str_replace("&amp;", "&", $url);

    $new_URL = explode("?", $url, 2);
    $newURL = explode("/",$new_URL[0]);
    
    $checkHttp = !empty(array_intersect(["https:","http:"], $newURL));

    foreach ($newURL as $k=>$v ){
        if (!$checkHttp || $checkHttp && $k > 2) {
            $newURL[$k] = rawurlencode($v);
        }
    }

    if (isset($new_URL[1])) {
        $new_URL[0] = implode("/",$newURL);
        $new_URL[1] = str_replace("&amp;","&",$new_URL[1]);
        return implode("?", $new_URL);
    } else {
        return implode("/",$newURL);
    }
    
    return $url;
}

function fn_retargeting_get_product_weight($product){
    $productWeight = isset($product['weight']) ? $product['weight'] : 0.1;
    $productWeightMeasurmentUnit = fn_retargeting_get_product_weight_measurement_unit();

    if (strtolower($productWeightMeasurmentUnit) !== 'kg') {
        $productWeight = fn_retargeting_convert_Weight_To_Kg($productWeight,$productWeightMeasurmentUnit);
    }

    return floatval(number_format($productWeight,2,'.',','));
}

function fn_retargeting_get_product_weight_measurement_unit() {
    return Registry::get('settings.General.weight_symbol');
}

function fn_retargeting_convert_Weight_To_Kg($weight,$unit) {
    if(strtolower($unit) === 'g') {
        return $weight/1000;
    }else if(strtolower($unit) === 'lb' || strtolower($unit) === 'lbs') {
        return $weight*0.45359237;
    }else if(strtolower($unit) === 'oz') {
        return $weight/35.27396195;
    } else return $weight;
}
