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
{if $addons.retargeting.cart_url !== ""}
		<script>
			var _ra = _ra || {};
		    _ra.setCartUrlInfo = {
						"url": "{$addons.retargeting.cart_url}"
		    };
		    
		    if (_ra.ready !== undefined) {
						_ra.setCartUrl(_ra.setCartUrlInfo.url);
		    }
		</script>
{else}
		<script type="text/javascript">
				console.info("Retargeting Tracker Error: Please set the Cart URL in Retargeting Tracker settings.");
		</script>
{/if}
{/if}