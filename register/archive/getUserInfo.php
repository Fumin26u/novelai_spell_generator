<?php
$home = '../';
require_once($home . 'database/commonlib.php');

$user_info = [
    'user_id' => $_SERVER['HTTP_HOST'] === 'localhost' ? 'Fumiya0719':h($_SESSION['user_id']),
];
echo json_encode($user_info, JSON_UNESCAPED_UNICODE);