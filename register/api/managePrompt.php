<?php
$home = './';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8', true, 200);
    $post = json_decode(file_get_contents('php://input'), true);
}

require_once('./commonlib.php');
require_once('./controllers/DBControllers.php');
require_once('./controllers/PromptController.php');

$promptController = new PromptController();
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $response = $promptController->get();
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;

} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = '';

    // user_idが自分でなければ強制終了
    if ($_SERVER['HTTP_HOST'] !== 'localhost' && $_SESSION['user_id'] !== 'Fumiya0719') {
        echo json_encode([
            'error' => true,
            'content' => '不正なリクエストです。'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (isset($post['method']) && $post['method'] === 'delete') {
        $response = $promptController->delete($post['id'], $post['table']); 
    } else if ($post['edit']) {
        $response = $promptController->update($post);
    } else {
        $response = $promptController->create($post);
    }

    echo json_encode([
        'error' => false,
        'content' => $response
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
