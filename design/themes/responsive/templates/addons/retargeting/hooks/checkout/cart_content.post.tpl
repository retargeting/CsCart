{if $addons.retargeting.retargeting_domain_api}
{if $cart_products}
<script>
	var _ra = _ra || {};
	_ra.checkoutIdsInfo = {$checkoutid};
	
	if (_ra.ready !== undefined) {
		_ra.checkoutIds(_ra.checkoutIdsInfo);
	}
</script>
{/if}
<script>
	var _ra = _ra || {};
    _ra.setCartUrlInfo = {
		"url": window.location.toString()
    };
    
    if (_ra.ready !== undefined) {
		_ra.setCartUrl(_ra.setCartUrlInfo.url);
    }
</script>
{/if}