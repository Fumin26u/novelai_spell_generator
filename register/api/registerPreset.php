<?php
header('Content-Type: application/json; charset=utf-8', true, 200);
// データ登録リクエストがジェネレーターから来た場合のDB登録API
// $post = json_decode(file_get_contents('php://input'), true);
$post =  [
    'data' => json_decode($_POST['text_content']),
    'image' => $_POST['image'],
];
$home = '../';
require_once($home . 'database/commonlib.php');
require_once($home . 'api/setPreset.php');

$file = [
    'file_name' => $_FILES['file']['name'],
    'tmp_file' => $_FILES['file']['tmp_name'],
    'error' => $_FILES['file']['error'],
];

setPreset(json_decode($_POST['text_content']));
echo json_encode($file, JSON_UNESCAPED_UNICODE);
?>