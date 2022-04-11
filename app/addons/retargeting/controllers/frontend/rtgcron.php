<?php
error_reporting(E_ALL & ~E_NOTICE);

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }
if (fn_retargeting_get_addon_variable('retargeting_discounts_api') === $_GET['key']) {
    $path = str_replace('app/www/app/addons/retargeting','',fn_dir());
echo "<pre><code>0 */4 * * * /usr/bin/php ".$path."index.php  --dispatch=rtgfeed.cron >/dev/null 2>&1
OR
0 */4 * * * wget --quiet -O /dev/null ".fn_url("index.php?dispatch=rtgfeed.cron")."</code></pre>";
    exit();
}
