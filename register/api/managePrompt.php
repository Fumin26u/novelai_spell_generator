<?php
$home = '../';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8', true, 200);
    $post = json_decode(file_get_contents('php://input'), true);
}

require_once($home . 'database/commonlib.php');
require_once($home . 'api/controllers/DBControllers.php');
require_once($home . 'api/controllers/PromptController.php');

// user_idが自分でなければ強制終了
if ($_SERVER['HTTP_HOST'] !== 'localhost' && $_SESSION['user_id'] !== 'Fumiya0719') {
    echo json_encode([
        'error' => '不正なリクエストです。',
    ], JSON_UNESCAPED_UNICODE);
}

$promptController = new PromptController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($post['edit']) {
        $promptController->update($post);
    } else {
        $promptController->create($post);
    }
}

echo json_encode($post, JSON_UNESCAPED_UNICODE);