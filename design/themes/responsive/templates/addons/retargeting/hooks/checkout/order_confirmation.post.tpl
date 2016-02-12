<script type="text/javascript">
var _ra = _ra || {};

{foreach from=$orders_info item="retargeting_order_info"}
_ra.saveOrderInfo = {
            "order_no"      : "{$retargeting_order_info.order_id}",
            "lastname"      : "{$retargeting_order_info.b_lastname}",
            "firstname"     : "{$retargeting_order_info.b_firstname}",
            "email"         : "{$retargeting_order_info.email}",
            "phone"         : "{$retargeting_order_info.b_phone}",
            "state"         : "{$retargeting_order_info.b_state}",
            "city"          : "{$retargeting_order_info.b_city}",
            "address"       : "{$retargeting_order_info.b_address}",
            "discount_code" : "",
            "discount"      : "{$retargeting_order_info.discount}",
            "shipping"      : "{$retargeting_order_info.shipping_cost}",
            "total"         : "{$retargeting_order_info.total}"
        };
        
_ra.saveOrderProducts = [
    {foreach from=$order_info.products item="product" key="key"}
        {
            "id": "{$product.product_id}",
            "quantity": "{$product.amount}",
            "price": "{$product.price}",
            "variation_code": false
        },
    {/foreach}
    ]
{/foreach}

if( _ra.ready !== undefined ){
            _ra.saveOrder(_ra.saveOrderInfo, _ra.saveOrderProducts);
}
	
</script>
