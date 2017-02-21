<script>
function _ra_helper_addLoadEvent(func){
	var oldonload = window.onload;
	if(typeof window.onload != 'function'){
		window.onload = func;
	} else {
		window.onload = function(){
			if(oldonload){
				oldonload();
			}
			func();
		}
	}
}

	var _ra = _ra || {};

	_ra.sendProductInfo = {
		"id": "{$product.product_id}",
		"name": "{$product.product}",
		"url": window.location.origin + window.location.pathname,
		"img": "{$product.main_pair.detailed.image_path}",
		{if $ra_oldPrice == $product.price or not is_numeric($ra_oldPrice)}
		"price": "{if $product.list_price > $product.price}{$product.list_price}{else}{$product.price}{/if}",
		"promo": "{if $product.list_price > $product.price}{$product.price}{else}0{/if}",
		{else}
		"price": "{$ra_oldPrice}",
		"promo": "{$product.price}",
		{/if}
		"brand": false,
		"category": [{
			"id": "{$catid}",
			"name": "{$product.main_category|fn_get_category_name}",
			"parent": false,
			"breadcrumb": []
		}],
		"inventory": {
			"variations": false,
			"stock": "{$product_amount = $product.inventory_amount|default:$product.amount}{if ($product_amount <= 0 || $product_amount < $product.min_qty) && $settings.General.inventory_tracking == "Y"}0{else}1{/if}"	
		}
	};

	if (_ra.ready !== undefined) {
		_ra.sendProduct(_ra.sendProductInfo);
	}

function raClickImage(){
	if(typeof _ra.clickImage !== 'undefined') {
		_ra.clickImage('{$product.product_id}');
	}
}

_ra_helper_addLoadEvent(function(){
	var raCartBtn = document.querySelector("[id^='button_cart_']");
	if(raCartBtn !== null){
		raCartBtn.addEventListener('click', function(){
			_ra.addToCart('{$product.product_id}', 1, _ra_getVariation());
		});
	}

	var raWishlist = document.querySelector("[id^='button_wishlist_']");
	if(raWishlist !== null){
		raWishlist.addEventListener('click', function(){
			_ra.addToWishlist('{$product.product_id}');
		});
	}
	
	if(document.querySelector("{$addons.retargeting.retargeting_qs_productImages}") !== null){
		document.querySelector("{$addons.retargeting.retargeting_qs_productImages}").onclick = raClickImage;
	}

	if(typeof FB !== "undefined"){
		FB.Event.subscribe('edge.create', function(){
			_ra.likeFacebook('{$product.product_id}');
		})
	}
});

// setVariation
var _ra_productId = {$product.product_id};
var _ra_arr = document.querySelector("[id^='option_::product_id::}_']");

if (_ra_arr !== null) {
	for (var i=0; i < _ra_arr.length; i++) {
		if (_ra_arr[i].type === 'textarea' || _ra_arr[i].type === 'text' ) {
			_ra_arr[i].addEventListener('change',function(){
				_ra_setVariation();
			});
		} else if (_ra_arr[i].type === 'select-one') {
			// for (var j = 0; j < _ra_arr[i].childNodes.length; j++){
			// 	_ra_arr[i].childNodes[j].addEventListener('click', function(){
			// 		_ra_setVariation(this);
			// 	});
			// }

	function _ra_onchangeModifier(onchange, element){
		return function(evt){
			evt = evt || event;
			var el  = this;

			if (onchange) {
				onchange(evt);
				// element.onchange = _ra_onchangeModifier(element.onchange, element);
			}
		}
	}
			_ra_arr[i].onchange = _ra_onchangeModifier(_ra_arr[i].onchange, _ra_arr[i]);
		} else if (_ra_arr[i].type === 'radio' || _ra_arr[i].type === 'checkbox'){
			_ra_arr[i].addEventListener('click',function(){
				_ra_setVariation();
			});
		}
	}
}

function _ra_setVariation(option){
	var _ra_pid = typeof _ra_productId !== 'undefined' ? _ra_productId : null;

	if(_ra_pid !== null) {
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
	var _ra_arr = document.querySelectorAll("[id^='option_::product_id::}_']");
	var _ra_variation = false,
		_ra_arr_code = [],
		_ra_arr_details = [];
	for (var i=0; i < _ra_arr.length; i++) {
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
				for (var j = 0; j < _ra_radios.length; j ++) {
					if (_ra_radios[j].checked) {
						_ra_ovalue = _ra_radios[j].value;
						break;
					}
				}
			}
		}
		if (_ra_ovalue !== null) {
			_ra_olabel = document.querySelector('label[for="'+_ra_arr[i].getAttribute('id')+'"]');
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
