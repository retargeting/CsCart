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

	{* CHECKOUT IDS & REMOVE FROM CART *}
	{if $cart_products && $runtime.controller eq 'checkout' && $runtime.mode eq 'cart'}
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

			var removeProduct = document.getElementsByClassName('ty-delete-big__icon');

			for (var i = 0; i < removeProduct.length; i++) {

				(function(index){
					removeProduct[i].addEventListener('click', function(){

						var productIds = {$ra_check_ids|@json_encode nofilter};

						_ra.removeFromCart(productIds[index], 1, false);

					});
				})(i);

			}

		</script>
	{/if}

			{* SEND CATEGORIES *}
	{if $runtime.controller eq 'categories' && $runtime.mode eq 'view'}
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

			{* setEmail	*}
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

		{* HELPER PAGE	*}
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

		{*	PRODUCT DETAIL *}
	{if $runtime.controller eq 'products' && $runtime.mode eq 'view'}
		<script>
			function _ra_helper_addLoadEvent(func) {
				var oldonload = window.onload;
				if (typeof window.onload != 'function') {
					window.onload = func;
				} else {
					window.onload = function () {
						if (oldonload) {
							oldonload();
						}
						func();
					}
				}
			}

			var _ra = _ra || {};

			_ra.sendProductInfo = {$ra_product_info nofilter};

			if ({$ra_fullPrice} == "0") {
				_ra.sendProductInfo.error = true;
			}

			if (_ra.ready !== undefined) {
				_ra.sendProduct(_ra.sendProductInfo);
			}

			function raClickImage() {
				if (typeof _ra.clickImage !== 'undefined') {
					_ra.clickImage('{$product.product_id}');
				}
			}

			$.ceEvent('on', 'ce.formpre_product_form_{$product.product_id}', function(frm, elm) {
				_ra.addToCart('{$product.product_id}', 1, false);
			});

			_ra_helper_addLoadEvent(function () {

				var raWishlist = document.querySelector("[id^='button_wishlist_']");
				if (raWishlist !== null) {
					raWishlist.addEventListener('click', function () {
						_ra.addToWishlist('{$product.product_id}');
					});
				}

				if (document.querySelector("{$addons.retargeting.retargeting_qs_productImages}") !== null) {
					document.querySelector("{$addons.retargeting.retargeting_qs_productImages}").onclick = raClickImage;
				}

				if (typeof FB !== "undefined") {
					FB.Event.subscribe('edge.create', function () {
						_ra.likeFacebook('{$product.product_id}');
					})
				}
			});

			// setVariation
			var _ra_productId = {$product.product_id};
			var _ra_arr = document.querySelector("[id^='option_']");

			if (_ra_arr !== null) {
				for (var i = 0; i < _ra_arr.length; i++) {
					if (_ra_arr[i].type === 'textarea' || _ra_arr[i].type === 'text') {
						_ra_arr[i].addEventListener('change', function () {
							_ra_setVariation();
						});
					} else if (_ra_arr[i].type === 'select-one') {
						// for (var j = 0; j < _ra_arr[i].childNodes.length; j++){
						// 	_ra_arr[i].childNodes[j].addEventListener('click', function(){
						// 		_ra_setVariation(this);
						// 	});
						// }

						function _ra_onchangeModifier(onchange, element) {
							return function (evt) {
								evt = evt || event;
								var el = this;

								if (onchange) {
									onchange(evt);
									// element.onchange = _ra_onchangeModifier(element.onchange, element);
								}
							}
						}

						_ra_arr[i].onchange = _ra_onchangeModifier(_ra_arr[i].onchange, _ra_arr[i]);
					} else if (_ra_arr[i].type === 'radio' || _ra_arr[i].type === 'checkbox') {
						_ra_arr[i].addEventListener('click', function () {
							_ra_setVariation();
						});
					}
				}
			}

			function _ra_setVariation(option) {
				var _ra_pid = typeof _ra_productId !== 'undefined' ? _ra_productId : null;

				if (_ra_pid !== null) {
					_ra = typeof _ra !== 'undefined' ? _ra : {};
					_ra.setVariationInfo = {
						"product_id": _ra_pid,
						"variation": _ra_getVariation()
					};

					if (_ra.ready !== undefined) {
						_ra.setVariation(_ra.setVariationInfo.product_id, _ra.setVariationInfo.variation);
					}
				}
			}

			function _ra_getVariation() {
				var _ra_arr = document.querySelectorAll("[id^='option_']");
				var _ra_variation = false,
						_ra_arr_code = [],
						_ra_arr_details = [];
				for (var i = 0; i < _ra_arr.length; i++) {
					var _ra_ovalue = null,
							_ra_olabel = null;

					if (_ra_arr[i].type === 'textarea') {
						_ra_ovalue = _ra_arr[i].value;
						_ra_ovalue = _ra_arr[i].value;

					} else if (_ra_arr[i].type === 'select-one') {
						_ra_ovalue = _ra_arr[i].options[_ra_arr[i].selectedIndex].innerText;
					} else if (_ra_arr[i].type === 'text') {
						_ra_ovalue = _ra_arr[i].value;
					} else if (_ra_arr[i].type === 'checkbox') {
						_ra_ovalue = (_ra_arr[i].checked ? 'yes' : 'no');
					} else {
						if (_ra_arr[i].getAttribute('id').match(/option_{$product.product_id}_[0-9]+_group/)) {
							var _ra_radios = _ra_arr[i].querySelectorAll('input[type="radio"]');
							for (var j = 0; j < _ra_radios.length; j++) {
								if (_ra_radios[j].checked) {
									_ra_ovalue = _ra_radios[j].value;
									break;
								}
							}
						}
					}
					if (_ra_ovalue !== null) {
						_ra_olabel = document.querySelector('label[for="' + _ra_arr[i].getAttribute('id') + '"]');
						if (_ra_olabel !== null) _ra_olabel = _ra_olabel.innerText; else _ra_olabel = 'Radio';
						_ra_ovalue = _ra_ovalue.replace(/-/g, '_');
						_ra_arr_code.push(_ra_ovalue);
						_ra_arr_details[_ra_ovalue] = {
							'category_name': _ra_olabel,
							'category': _ra_olabel,
							'value': _ra_ovalue
						};
					}
				}
				var res = false;
				if (_ra_arr_code.length > 0) {
					res = {
						"code": _ra_arr_code.join('-'),
						"stock": "{$product_amount = $product.inventory_amount|default:$product.amount}{if ($product_amount <= 0 || $product_amount < $product.min_qty) && $settings.General.inventory_tracking == "Y"}0{else}1{/if}",
						"details": _ra_arr_details
					};
				}
				return res;
			}
		</script>

	{/if}

		{* SET CART URL *}
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

		{* SAVE ORDER *}
	{if $runtime.controller eq 'checkout' && $runtime.mode eq 'complete'}
		<script type="text/javascript">
			var _ra = _ra || {};

			_ra.saveOrderInfo = {
				"order_no": "{$ra_saveOrderInfo.order_no}",
				"lastname": "{$ra_saveOrderInfo.lastname}",
				"firstname": "{$ra_saveOrderInfo.firstname}",
				"email": "{$ra_saveOrderInfo.email}",
				"phone": "{$ra_saveOrderInfo.phone}",
				"state": "{$ra_saveOrderInfo.state}",
				"city": "{$ra_saveOrderInfo.city}",
				"address": "{$ra_saveOrderInfo.address}",
				"discount_code": "s",
				"discount": "{$ra_saveOrderInfo.discount}",
				"shipping": "{$ra_saveOrderInfo.shipping}",
				"rebates": 0,
				"fees": 0,
				"total": "{$ra_saveOrderInfo.total}"
			};

			_ra.saveOrderProducts = {$ra_saveOrderProducts|@json_encode nofilter};

			if (_ra.ready !== undefined) {
				_ra.saveOrder(_ra.saveOrderInfo, _ra.saveOrderProducts);
			}

		</script>
	{/if}



{else}
	<script type="text/javascript">
		console.info("Retargeting Tracker Error: Please set the Domain API Key from your Retargeting Account.");
	</script>
{/if}
