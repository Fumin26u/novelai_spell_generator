<?php
$home = '../';
require_once($home . 'database/commonlib.php');

// 非ログイン時強制終了し、ログインユーザーIDを取得
if (!isset($_SESSION['user_id'])) exit;
$user_id = h($_SESSION['user_id']);

try {
    $pdo = dbConnect();
    $pdo->beginTransaction();

    $st = $pdo->prepare('SELECT * FROM preset WHERE user_id = :user_id');
    $st->bindValue(':user_id', $user_id, PDO::PARAM_STR);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    $data = $rows;
        
    $pdo->commit();
} catch (PDOException $e) {
    if (DEBUG) echo $e;
}

echo json_encode($data);
