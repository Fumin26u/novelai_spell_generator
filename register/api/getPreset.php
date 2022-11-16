<?php
$home = '../';
require_once($home . 'database/commonlib.php');

// 非ログイン時はログインページにリダイレクト
if (!isset($_SESSION['user_id'])) {
    // header('location: ' . $home . 'login.php', true, 303);
    exit;
};
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

echo json_encode($data, JSON_UNESCAPED_UNICODE);
