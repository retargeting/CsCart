<?php

use Tygh\Registry;

if(!defined('BOOTSTRAP')) { die('Access denied');}


if($mode == 'view' && !empty($_REQUEST['category_id'])){

	$catid = $_REQUEST['category_id'];

	if($catid){
		$catn = db_get_field("SELECT category FROM ?:category_descriptions WHERE category_id = ?i AND lang_code = ?s", $catid, CART_LANGUAGE);
		Registry::get('view')->assign('catid', $catid);
		Registry::get('view')->assign('catn', $catn);
	}

}