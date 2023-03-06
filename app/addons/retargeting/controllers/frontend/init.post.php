<?php

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Registry;

if( !empty($_SESSION['auth']['user_id']) ){
	$retargeting_set_email = db_get_field("SELECT email FROM ?:users WHERE user_id=?i", $_SESSION['auth']['user_id']);
	Registry::get('view')->assign('retargeting_set_email', $retargeting_set_email);
}

/* RecEngine Zone */
if (Recengine::apistatus() && Recengine::recstatus()) {
	Registry::get('view')->assign('recengine',  Recengine::rec_engine_load(isset($_REQUEST['dispatch']) ? $_REQUEST['dispatch'] : 'index.index'));
}
/* RecEngine Zone */
