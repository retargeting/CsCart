{if $addons.retargeting.retargeting_domain_api}

<script type="text/javascript">
	(function(){
	var ra_key = "{$addons.retargeting.retargeting_domain_api}";
	var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
	document.location.protocol ? "https://" : "http://") + "retargeting-data.eu/rajs/" + ra_key + ".js";
	var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();
</script>

{else}

<script type="text/javascript">
	(function(){
	var ra = document.createElement("script"); ra.type ="text/javascript"; ra.async = true; ra.src = ("https:" ==
	document.location.protocol ? "https://" : "http://") + "retargeting-data.eu/" +
	document.location.hostname.replace("www.","") + "/ra.js"; var s =
	document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(ra,s);})();
</script>

{/if}

