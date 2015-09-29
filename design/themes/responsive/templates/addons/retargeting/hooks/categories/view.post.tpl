<script>
var _ra = _ra || {};
	_ra.sendCategoryInfo = {
		"id": {$catid},
		"name" : "{$catn}",
		"parent": false,
		"category_breadcrumb": []
	}
	if (_ra.ready !== undefined) {
		_ra.sendCategory(_ra.sendCategoryInfo);
	}
</script>