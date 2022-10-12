<?php
$home = './';
require_once($home . 'database/commonlib.php');

$message = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $err = [];

        $pdo = dbConnect();
    
        $pdo->beginTransaction();

        $st = $pdo->prepare('SELECT * FROM users WHERE user_id = :user_id');
        $st->bindValue(':user_id', h($_POST['user_id']), PDO::PARAM_STR);
        $st->execute();

        $rows = $st->fetch(PDO::FETCH_ASSOC);
        $pdo->commit();

        // 返された配列が空の場合、ユーザ名が存在しない
        if (empty($rows)) {
            $err[] = '入力されたユーザー名は存在しません。';
            // パスワードの照合
        } else if(!password_verify(h($_POST['password']), $rows['password'])) {
            v($rows);
            $err[] = '入力されたパスワードが間違っています。';
        }

        if (empty($err)) {
            session_start();
            $_SESSION['user_id'] = $rows['user_id'];
            header('location: ./index.php', true, 303);
            exit;
        }

        $message += $err;
    } catch (PDOException $e) {
        echo 'データベース接続に失敗しました。';
        if (DEBUG) echo $e;
    }
}

$title = 'ログイン | NovelAI コマンド登録機';
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
        <h2>ユーザーログイン</h2>
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
                <input type="submit" value="ログイン" class="btn-common submit">
            </dl>           
        </form>
    </div>
</main>
</body>
</html>