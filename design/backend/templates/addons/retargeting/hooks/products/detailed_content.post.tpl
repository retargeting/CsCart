{include file="common/subheader.tpl" title=__("retargeting") target="#retargeting_block"}
<div id="retargeting_block" class="collapse in">
	<div class="control-group">
		<label class="control-label" for="retargeting">{__("enable")}</label>
		<div class="controls">
			<label class="checkbox">
				<input type="hidden" name="product_data[retargeting]" value="N" />
				<input type="checkbox" name="product_data[retargeting]" id="retargeting" value="Y" {if $product_data.retargeting == "Y"}checked="checked"{/if}/>
			</label>
		</div>
	</div>
</div>
