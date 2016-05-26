{if $addons.retargeting.retargeting_domain_api}

<script type="text/javascript">
	(function(){
	ra_key = "{$addons.retargeting.retargeting_domain_api}";
 	ra_params = {
		add_to_cart_button_id: "{$addons.retargeting.retargeting_qs_addToCart}",
		price_label_id: "{$addons.retargeting.retargeting_qs_price}",
	};
	var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
	document.location.protocol ? "https://" : "http://") + "tracking.retargeting.biz/v3/rajs/" + ra_key + ".js";
	var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();
</script>

{else}

<script type="text/javascript">
	console.info("Retargeting Tracker Error: Please set the Domain API Key from your Retargeting Account.");
</script>

{/if}

