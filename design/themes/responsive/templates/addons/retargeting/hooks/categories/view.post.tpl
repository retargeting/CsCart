{if $addons.retargeting.retargeting_domain_api}
<script>
var _ra = _ra || {};
	_ra.sendCategoryInfo = {
		"id": {$catid},
		"name" : "{$catn}",
		"parent": false,
		"breadcrumb": []
	}
	if (_ra.ready !== undefined) {
		_ra.sendCategory(_ra.sendCategoryInfo);
	}
</script>
{/if}