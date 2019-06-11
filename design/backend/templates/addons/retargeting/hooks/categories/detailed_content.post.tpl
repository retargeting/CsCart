{include file="common/subheader.tpl" title=__("retargeting") target="#retargeting_block"}
<div id="retargeting_block" class="collapse in">
	<div class="control-group">
		<label class="control-label" for="retargeting">{__("enable")}</label>
		<div class="controls">
			<label class="checkbox">
				<input type="hidden" name="category_data[retargeting]" value="N" />
				<input type="checkbox" name="category_data[retargeting]" id="retargeting" value="Y" {if $category_data.retargeting == "Y"}checked="checked"{/if}/>
			</label>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label" for="retargeting_apply_to_subcategories">{__("to_all_subcats")}</label>
		<div class="controls">
			<input id="retargeting_apply_to_subcategories" type="checkbox" name="category_data[retargeting_apply_to_subcategories]" value="Y" />
		</div>
	</div>
</div>
