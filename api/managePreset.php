<?php
$home = './';
require_once('./commonlib.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8', true, 200);
    $post = json_decode(file_get_contents('php://input'), true);
}

require_once('./controllers/DBControllers.php');
require_once('./controllers/PresetController.php');
require_once('./controllers/ImageController.php');

$presetController = new PresetController();
// GETの場合は検索フォームの入力内容に合わせてDBのデータをJSON化し返却
if (isset($_GET['method']) && $_GET['method'] === 'search') {

    $searchQuery = $presetController->makeSearchQuery($_GET);
    $presets = $presetController->get($searchQuery);

    echo json_encode($presets, JSON_UNESCAPED_UNICODE);
    exit;

} 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($post['delete']) && $post['delete'] !== '') {

        // データ削除が要求された場合削除し終了
        $presetController->delete((int) h($post['delete']));
        echo json_encode($post, JSON_UNESCAPED_UNICODE);
        exit;

    } else {

        if (strpos($post['image'], ',') !== false) {
            // 画像を新規登録または更新する場合Base64形式で送られてくるのでデコードして固有ファイル名に変換し保存
            $imageDirPath = './images/preset/original/';
            $imageBase64String = substr($post['image'], strpos($post['image'], ',')+1);
            $imageData = base64_decode($imageBase64String);
            
            $imageController = new ImageController($imageDirPath);
            $imageFileName = $imageController->getUniqueID();
            
            file_put_contents($imageDirPath . $imageFileName, $imageData);
            // 保存した画像からサムネイルを抽出
            $imageController->makeThumbnail($home);
            // postの画像の値を更新
            $post['image'] = $imageFileName;
        }
        
        // POSTされたデータのDB登録
        if (isset($post['preset_id']) && !is_null($post['preset_id']) && $post['preset_id'] !== -1) {
            $presetController->update($post, (int) h($post['preset_id']));
        } else {
            $presetController->create($post);
        }
    }
}
?>