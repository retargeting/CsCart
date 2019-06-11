<div class="control-group">
	<label class="control-label" for="retargeting">{__("retargeting_products")}</label>
	<div class="controls">
		<select name="retargeting" id="retargeting">
			<option value="">--</option>
			<option value="Y" {if $search.retargeting == "Y"}selected="selected"{/if}>{__("yes")}</option>
			<option value="N" {if $search.retargeting == "N"}selected="selected"{/if}>{__("no")}</option>
		</select>
	</div>
</div>
