<?php
defined('BOOTSTRAP') or die('Access denied');

use Tygh\Tygh;
use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === 'update' && $_REQUEST['addon'] === 'retargeting') {
    if (isset($_POST['rec_data'])) {
        Recengine::Update();
    }
}

/** @var string $mode */
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $mode === 'update' && $_REQUEST['addon'] === 'retargeting') {
    Tygh::$app['view']->assign('form', Recengine::form(Registry::get('addons.retargeting')).'<script>console.log('.json_encode(fn_get_addon_settings_values('retargeting')).')</script>');
}
