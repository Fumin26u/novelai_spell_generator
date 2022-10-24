<?php
$home = './';
require_once($home . 'database/commonlib.php');

$message = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $err = [];
        $pdo = dbConnect();
    
        $st = $pdo->prepare('SELECT user_id FROM users WHERE user_id = :user_id');
        $st->bindValue(':user_id', h($_POST['user_id']), PDO::PARAM_STR);
        $st->execute();
        
        $rows = $st->fetch(PDO::FETCH_ASSOC);
        
        if (!empty($rows)) {
            $err[] = '既に使用されているユーザーIDです。';
        } else {
            $pdo->beginTransaction();

            $st = $pdo->prepare('INSERT INTO users (user_id, password) VALUES (:user_id, :password)');
            $st->bindValue(':user_id', h($_POST['user_id']), PDO::PARAM_STR);
            $st->bindValue(':password', password_hash(h($_POST['password']), PASSWORD_DEFAULT), PDO::PARAM_STR);
            $st->execute();
    
            $pdo->commit();
            header('location: ./login.php', true, 303);
            exit;
        }

        $message += $err;
        
    } catch (PDOException $e) {
        echo 'データベース接続に失敗しました。';
        if (DEBUG) echo $e;
    }
}

$title = 'アカウント登録 | NovelAI プロンプトセーバー';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="<?= $home ?>styles.css">
<title><?= $title ?></title>
</head>
<body>
<?php include($home . 'header.php') ?>
<main>
    <div>
        <p><?= implode(', ', $message) ?></p>
        <h2>ユーザー新規登録</h2>
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST" class="form-common">
            <dl>
                <div>
                    <dt>ユーザーID</dt>
                    <dd><input type="text" id="user_id" name="user_id" required></dd>
                </div>
                <div>
                    <dt>パスワード</dt>
                    <dd><input type="password" id="password" name="password" required></dd>
                </div>
            </dl>
            <input type="submit" value="登録" class="btn-common submit">
        </form>
    </div>
</main>
</body>
</html>