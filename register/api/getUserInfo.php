<?php
$home = '../';
require_once($home . 'database/commonlib.php');

$data = [
    'user_id' => isset($_SESSION['user_id']) ? h($_SESSION['user_id']) : '', 
];

echo json_encode($data);
exit;