<?php
$home = './';
require_once($home . 'database/commonlib.php');

$user_id = isset($_SESSION['user_id']) ? h($_SESSION['user_id']) : ''; 

if (isset($_SESSION['user_id'])) {
    try {
        $pdo = dbConnect();
        $pdo->beginTransaction();

        $st = $pdo->prepare('SELECT * FROM preset WHERE user_id = :user_id');
        $st->bindValue(':user_id', $user_id, PDO::PARAM_STR);
        $st->execute();

        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            header('location: ./', true, 303);
            exit;
        }
        $pdo->commit();
        
        $presets = $rows;
    } catch (PDOException $e) {
        echo 'データベース接続に失敗しました。';
        if (DEBUG) echo $e;
    }    
}

$title = 'NovelAI コマンド登録機';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<title><?= $title ?></title>
</head>
<body>
<?php include($home . 'header.php') ?>
<main>
    <?php if (isset($_SESSION['user_id'])) { ?>
    <section class="spell-list">
        <p><?= $_SESSION['user_id'] ?>の登録コマンド一覧</p>
        <table>
            <thead>
                <tr>
                    <th>内容</th>
                    <th>コマンド</th>
                    <th>コマンドBAN</th>
                    <th>シード値</th>
                    <th>解像度</th>
                    <th>備考</th>
                    <th>更新日付</th>
                    <th>編集</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presets as $preset) { ?>
                    <tr>
                        <td><?= $preset['description'] ?></td>
                        <td><?= substr($preset['commands'], 0, 20) . '...' ?></td>
                        <td><?= substr($preset['commands_ban'], 0, 20) . '...' ?></td>
                        <td><?= $preset['seed'] ?></td>
                        <td><?= $preset['resolution'] ?></td>
                        <td><?= $preset['others'] ?></td>
                        <td><?= $preset['updated_at'] === NULL ? $preset['created_at'] : $preset['updated_at'] ?></td>
                        <td><a href="<?= $home ?>commands.php?preset_id=<?= $preset['preset_id'] ?>">編集</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </section>
    <?php } ?>
</main>
</body>
</html>