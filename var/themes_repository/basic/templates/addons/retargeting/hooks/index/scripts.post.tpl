{if $addons.retargeting.retargeting_domain_api}
	<script type="text/javascript">
		(function(){
		ra_key = "{$addons.retargeting.retargeting_domain_api}";
		ra_params = {
			add_to_cart_button_id: ".ty-btn__add-to-cart",
			price_label_id: ".new-price",
		};
		var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
		document.location.protocol ? "https://" : "http://") + "tracking.retargeting.biz/v3/rajs/" + ra_key + ".js";
		var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();
	</script>

	{if $cart_products && $runtime.controller eq checkout && $runtime.mode eq 'cart'}
		{$ra_check_ids = []}
		{foreach $cart_products as $ra_cart_prod}
			{$ra_check_ids[] = $ra_cart_prod['product_id']}
		{/foreach}

		<script>
			var _ra = _ra || {};

			_ra.checkoutIdsInfo = {$ra_check_ids|@json_encode nofilter};

			if (_ra.ready !== undefined) {
				_ra.checkoutIds(_ra.checkoutIdsInfo);
			}
		</script>
	{/if}
{else}
	<script type="text/javascript">
		console.info("Retargeting Tracker Error: Please set the Domain API Key from your Retargeting Account.");
	</script>
{/if}
