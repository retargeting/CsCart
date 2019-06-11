<?php
if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'retargeting_post',
	'get_products',
	'update_category_post',
	'update_product_post'
);
