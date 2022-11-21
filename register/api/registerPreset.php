<?php
// データ登録リクエストがジェネレーターから来た場合のDB登録API
$home = '../';

header('Content-Type: application/json; charset=utf-8', true, 200);
$post = json_decode(file_get_contents('php://input'), true);

require_once($home . 'database/commonlib.php');
require_once($home . 'api/PresetController.php');
require_once($home . 'api/ImageController.php');

// データ削除が要求された場合削除し終了
if (isset($post['delete']) && $post['delete'] !== '') {
    $presetController = new PresetController();
    $presetController->delete((int) h($post['delete']));
    echo json_encode($post, JSON_UNESCAPED_UNICODE);
    exit;
}

// 画像がBase64形式で送られてくるのでデコードして固有ファイル名に変換し保存
$imageDirPath = '../images/preset/original/';
$imageFileName = '';

if (strpos($post['image'], ',') !== false) {
    $imageBase64String = substr($post['image'], strpos($post['image'], ',')+1);
    $imageData = base64_decode($imageBase64String);
    
    $imageController = new ImageController($imageDirPath);
    
    $imageFileName = $imageController->getUniqueID();
    file_put_contents($imageDirPath . $imageFileName, $imageData);
    // 保存した画像からサムネイルを抽出
    $imageController->makeThumbnail($home);
} else if (!is_null($post['image']) && $post['image'] !== '') {
    // 編集時画像更新が無い場合は画像ファイル名をそのまま使用
    $imageFileName = $post['image'];
}

// POSTされたデータのDB登録
$presetController = new PresetController();
if (isset($post['preset_id']) && $post['preset_id'] !== '') {
    $presetController->update($post, $imageFileName, (int) h($post['preset_id']));
} else {
    $presetController->create($post, $imageFileName);
}
?>