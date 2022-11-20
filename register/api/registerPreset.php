<?php
// データ登録リクエストがジェネレーターから来た場合のDB登録API
$home = '../';

header('Content-Type: application/json; charset=utf-8', true, 200);
$post = json_decode(file_get_contents('php://input'), true);

require_once($home . 'database/commonlib.php');
require_once($home . 'api/setPreset.php');

// 画像がBase64形式で送られてくるのでデコードして固有ファイル名に変換し保存
$imageDirPath = '../images/preset/original/';
$imageBase64String = substr($post['image'], strpos($post['image'], ',')+1);
$imageData = base64_decode($imageBase64String);
$imageFileName = makeUniqueID();
file_put_contents($imageDirPath . $imageFileName, $imageData);
// 保存した画像からサムネイルを抽出
makeThumbnail($imageDirPath, $imageFileName, '../');
// POSTされたデータのDB登録
setPreset($post, $imageFileName);

echo json_encode($post, JSON_UNESCAPED_UNICODE);
?>