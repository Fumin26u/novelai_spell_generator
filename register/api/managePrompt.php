<?php
$home = '../';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8', true, 200);
    $post = json_decode(file_get_contents('php://input'), true);
}

require_once($home . 'database/commonlib.php');
require_once($home . 'api/controllers/DBControllers.php');
require_once($home . 'api/controllers/PromptController.php');

echo json_encode($post, JSON_UNESCAPED_UNICODE);