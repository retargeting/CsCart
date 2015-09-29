{if $cart_products}
<script>
	var _ra = _ra || {};
	_ra.checkoutIdsInfo = {$checkoutid};
	
	if (_ra.ready !== undefined) {
		_ra.checkoutIds(_ra.checkoutIdsInfo);
	}
</script>
{/if}