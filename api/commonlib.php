<?php
header("Access-Control-Allow-Origin: http://localhost:8080");
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');   

// ClickJacking対策
header('X-FRAME-OPTIONS: SAMEORIGIN');

require_once("./database/system-conf.php");

// タイムゾーン設定
date_default_timezone_set('Asia/Tokyo');

function dbConnect() {
    $pdo = new PDO(DSN, DBUSER, DBPASS);
    return $pdo;
}

// タイムアウト制限時間
ini_set("max_execution_time", 300);
// セッションの開始
session_start();

// ダンプの簡略化
function v($arg) {
    return var_dump($arg);
}

// 文字列のエスケープ
function h($str) {
    return htmlspecialchars($str);
}

// メール関連
// 改行コードの置換
function brReplace($text) {
    $pattern = '/\r(?!\n)|(?<!\r)\n/';
    $text = preg_replace($pattern, "\r\n", $text);
    return $text;
}

// ピリオドの置換
function periodReplace($text) {
    $pattern = '/^\.\r$/m';
    $text = preg_replace($pattern, "..\r", $text);
    return $text;
}

// ログアウト処理
if(isset($_GET['logout'])) {
    $_SESSION = [];
    header('location: ' . $home . 'login.php', true, 303);
}

// CORS使用リクエストを受け付けるドメイン
$origins = [
    'http://localhost:8080',
    'https://fuminsv.sakura.ne.jp/sgtest',
    'https://nai-pg.com',
];