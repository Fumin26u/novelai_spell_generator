<?php
// データ登録リクエストがジェネレーターから来た場合のDB登録API
header('Content-Type: application.json; charset=utf-8');
$post = json_decode(file_get_contents('php://input'), true);

$home = '../';
require_once($home . 'database/commonlib.php');
require_once($home . 'api/registerPreset.php');
setPreset($post);

exit;
?>