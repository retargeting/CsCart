<?php
error_reporting(E_ALL & ~E_NOTICE);

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$lang_code = CART_LANGUAGE;

$isStatic = in_array($mode, ['static', 'cron']);
$tmp = false;

$f = [
    'fileName' => 'retargeting',
    'extra' => null,
    'path' => null,
    'output' => 'php://output'
];

if ($isStatic) {
    $f['extra'] = time();
    $f['path'] = fn_dir().'/';
    $f['output'] = $f['path'].$f['fileName'].'.csv';
}

// json encode - float precision
if (version_compare(phpversion(), '7.1', '>=')) {
    ini_set('serialize_precision', -1);
}

if ($mode === 'list') {
    header("Content-Disposition: attachment; filename=".$f['fileName'].".csv");
    header("Content-type: text/csv; charset=utf-8");

    $outstream = fopen('php://output', 'w');

} else if(!file_exists($f['output']) || $mode === 'cron'){
    $outstream = fopen($f['path'].$f['fileName'].'.'.$f['extra'].'.csv', 'w');
    $tmp = true;
}

if (!$isStatic || $tmp) {

    fputcsv($outstream, array(
        'product id',
        'product name',
        'product url',
        'image url',
        'stock',
        'price',
        'sale price',
        'brand',
        'category',
        'extra data'
    ), ',', '"');

    $extra = null;

    while($extra === null || $extra['getProducts']) {
        $extra = fn_regargeting_get_prod($extra);

        foreach($extra['list'] as $product) {
            fputcsv($outstream, $product, ',', '"');
        }
    }
    
    
    fclose($outstream);

    if ($tmp) {
        try {
            copy($f['path'].$f['fileName'].'.'.$f['extra'].'.csv', $f['path'].$f['fileName'].'.csv');

            unlink($f['path'].$f['fileName'].'.'.$f['extra'].'.csv');

        } catch (\Exception $e) {
            echo json_encode(['status'=>'readProblem', 'message'=> $e->getMessage()]);
            exit;
        }
    }
}

if ($mode === 'static') {

    header("Content-Disposition: attachment; filename=".$f['fileName'].".csv");
    header("Content-type: text/csv; charset=utf-8");

    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($f['path'].$f['fileName'].'.csv'));
    
    readfile($f['path'].$f['fileName'].'.csv');  
}

if ($mode === 'cron') {
    header("Content-type: text/json; charset=utf-8");

    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');

    echo '{"status":"success"}';
}
exit();