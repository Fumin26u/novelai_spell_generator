<?php
// データ登録リクエストがジェネレーターから来た場合のDB登録API
$home = '../';

header('Content-Type: application/json; charset=utf-8', true, 200);
$post = json_decode(file_get_contents('php://input'), true);

require_once($home . 'database/commonlib.php');
require_once($home . 'api/setPreset.php');

// 画像がBase64形式で送られてくるのでデコード
$image_data = base64_decode($post['image']);
$mime = ()

$file = [
    // 'file_name' => $_FILES['image']['name'],
    // 'tmp_file' => $_FILES['image']['tmp_name'],
    // 'error' => $_FILES['image']['error'],
    'content' => $post,
    'image' => $image_data,
];

// setPreset(json_decode($_POST['text_content']));
echo json_encode($file, JSON_UNESCAPED_UNICODE);
?>