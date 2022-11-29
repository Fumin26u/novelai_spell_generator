<?php
$home = '../';
require_once($home . 'database/commonlib.php');

// 非ログイン時かつローカル環境でない場合強制終了
if ($_SERVER['HTTP_HOST'] !== 'localhost' && !isset($_SESSION['user_id'])) exit;
$user_id = $_SERVER['HTTP_HOST'] === 'localhost' ? 'Fumiya0719':h($_SESSION['user_id']);

try {
    $pdo = dbConnect();
    $pdo->beginTransaction();
    
    // 検索ワードの設定
    $search_age = [];
    if (isset($_GET['age']) && !empty($_GET['age'])) {
        foreach ($_GET['age'] as $age) {
            $search_age[] = "nsfw = '" . $age . "'";
        }
    }
    $search_words = [];
    if ($_GET['word'] !== '' && isset($_GET['item']) && !empty($_GET['item'])) {
        foreach ($_GET['item'] as $item) {
            $search_words[] = $item . " LIKE '%" . h($_GET['word']) . "%'";
        }
    }

    $order = " ORDER BY "  . h($_GET['sort']) . " " . h($_GET['order']);
    $search_str = '';
    if (!empty($search_age) || !empty($search_words)) {
        $search_str .= ' AND ';
        if (!empty($search_age) && !empty($search_words)) {
            $search_str .= '(' . implode(" OR ", $search_age) . ')';
            $search_str .= ' AND (' . implode(" OR ", $search_words) . ')';
        } else if (!empty($search_age)) {
            $search_str .= '(' . implode(" OR ", $search_age) . ')';
        } else if (!empty($search_words)) {
            $search_str .= '(' . implode(" OR ", $search_words) . ')';
        }
    }
    $search_str .= $order;
    $sql = 'SELECT * FROM preset WHERE user_id = :user_id' . $search_str;

    $st = $pdo->prepare($sql);
    $st->bindValue(':user_id', $user_id, PDO::PARAM_STR);
    $st->execute();
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    $data = $rows;
        
    $pdo->commit();
} catch (PDOException $e) {
    if (DEBUG) echo $e;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
