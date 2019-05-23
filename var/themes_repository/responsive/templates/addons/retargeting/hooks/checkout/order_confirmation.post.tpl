{if $addons.retargeting.retargeting_domain_api}
    <script type="text/javascript">
        var _ra = _ra || {};

        _ra.saveOrderInfo = {
            "order_no": "{$ra_saveOrderInfo.order_no}",
            "lastname": "{$ra_saveOrderInfo.lastname}",
            "firstname": "{$ra_saveOrderInfo.firstname}",
            "email": "{$ra_saveOrderInfo.email}",
            "phone": "{$ra_saveOrderInfo.phone}",
            "state": "{$ra_saveOrderInfo.state}",
            "city": "{$ra_saveOrderInfo.city}",
            "address": "{$ra_saveOrderInfo.address}",
            "discount_code": "s",
            "discount": "{$ra_saveOrderInfo.discount}",
            "shipping": "{$ra_saveOrderInfo.shipping}",
            "rebates": 0,
            "fees": 0,
            "total": "{$ra_saveOrderInfo.total}"
        };

        _ra.saveOrderProducts = {$ra_saveOrderProducts|@json_encode nofilter};

        if (_ra.ready !== undefined) {
            _ra.saveOrder(_ra.saveOrderInfo, _ra.saveOrderProducts);
        }

    </script>
{/if}