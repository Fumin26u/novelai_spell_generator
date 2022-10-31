<?php
// データ登録リクエストがジェネレーターから来た場合のDB登録API
header('Content-Type: application.json; charset=utf-8');
$_POST = json_decode(file_get_contents('php://input'), true);

$home = '../';
require_once($home . 'database/commonlib.php');

if (!isset($_POST['from']) && $_POST['from'] !== 'generator' && !isset($_SESSION['user_id'])) exit;

try {
    $pdo = dbConnect();
    $pdo->beginTransaction();

    $sql = <<<SQL
    INSERT INTO preset 
    (user_id, commands, commands_ban, description, seed, resolution, others, created_at, updated_at)
    VALUES 
    (:user_id, :commands, :commands_ban, :description, :seed, :resolution, :others, NOW(), NOW())
    SQL;

    $st = $pdo->prepare($sql);
    if (isset($_GET['preset_id'])) $st->bindValue(':preset_id', h($_GET['preset_id']), PDO::PARAM_INT); 
    $st->bindValue(':user_id', h($_SESSION['user_id']), PDO::PARAM_STR);
    $st->bindValue(':commands', h($_POST['commands']), PDO::PARAM_STR);
    $st->bindValue(':commands_ban', isset($_POST['commands_ban']) ? h($_POST['commands_ban']) : null, PDO::PARAM_STR);
    $st->bindValue(':description', isset($_POST['description']) ? h($_POST['description']) : null, PDO::PARAM_STR);
    $st->bindValue(':seed', isset($_POST['seed']) ? h($_POST['seed']) : null, PDO::PARAM_STR);
    $st->bindValue(':resolution', isset($_POST['resolution']) ? h($_POST['resolution']) : null, PDO::PARAM_STR);
    $st->bindValue(':others', isset($_POST['others']) ? h($_POST['others']) : null, PDO::PARAM_STR);

    $st->execute();
    $pdo->commit();
} catch (PDOException $e) {
    echo 'データベース接続に失敗しました。';
    if (DEBUG) echo $e;
}
exit;
?>