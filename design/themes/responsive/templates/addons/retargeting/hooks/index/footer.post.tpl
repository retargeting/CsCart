{if $retargeting_set_email}
<script>

var _ra = _ra || {};

if (_ra.ready !== undefined) {
	_ra.setEmail({
		"email": "{$retargeting_set_email}"
	});		
}
</script>
{/if}



<script>
	var helpPages = "{$addons.retargeting.help_pages}";
	helpPages.split(",");
	var currentPage = window.location.pathname;

	for(var i = 0; i<helpPages.length; i++){
		if(currentPage.indexOf(helpPages[i]) !== -1){
			
			var _ra = _ra || {};
			
			_ra.visitHelpPageInfo = {
				"visit" : true
			}
				
			if (_ra.ready !== undefined) {
				_ra.visitHelpPage();
			}
			break;
		}
	}
</script>