<?php
$home = './';
require_once($home . 'database/commonlib.php');

$user_id = isset($_SESSION['user_id']) ? h($_SESSION['user_id']) : ''; 

$presets = [];
if (isset($_SESSION['user_id'])) {
    try {
        $pdo = dbConnect();
        $pdo->beginTransaction();

        $st = $pdo->prepare('SELECT * FROM preset WHERE user_id = :user_id');
        $st->bindValue(':user_id', $user_id, PDO::PARAM_STR);
        $st->execute();

        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="<?= $home ?>styles.css">
<title><?= $title ?></title>
</head>
<body>
<?php include($home . 'header.php') ?>
<main>
    <?php if (isset($_SESSION['user_id'])) { ?>
    <section class="spell-list">
        <p><?= $_SESSION['user_id'] ?>の登録コマンド一覧</p>
        <?php if (empty($presets)) { ?>
            <div class="no-presets">
                <p>まだコマンドが登録されていません。</p>
                <a href="<?= $home ?>commands.php">新規登録</a>
            </div>
        <?php } else { ?>
        <span id="copy-alert" style="padding-left:2em;"></span>
        <p style="display: block; font-size: 16px;"><span style="color: #cc8c00; font-weight: bold;">オレンジ色</span>の項目をクリックするとデータをコピーできます。</p>
        <table class="command-table">
            <thead>
                <tr>
                    <th id="description">内容</th>
                    <th id="commands">コマンド</th>
                    <th id="commands_ban">BANコマンド</th>
                    <th id="seed">シード値</th>
                    <th id="resolution">解像度</th>
                    <th id="others">備考</th>
                    <th id="edit">編集</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presets as $preset) { ?>
                    <tr>
                        <td id="description"><?= $preset['description'] ?></td>
                        <td id="commands" onclick="copyPreset('<?= $preset['commands'] ?>', '<?= $preset['description'] . 'のコマンド' ?>')"><?= $preset['commands'] ?></td>
                        <td id="commands_ban" onclick="copyPreset('<?= $preset['commands_ban'] ?>', '<?= $preset['description'] . 'のBANコマンド' ?>')"><?= $preset['commands_ban'] ?></td>
                        <td id="seed" onclick="copyPreset('<?= $preset['seed'] ?>', '<?= $preset['description'] . 'のシード値' ?>')"><?= $preset['seed'] ?></td>
                        <td id="resolution"><?= $preset['resolution'] ?></td>
                        <td id="others"><?= $preset['others'] ?></td>
                        <td id="edit"><a href="<?= $home ?>commands.php?preset_id=<?= $preset['preset_id'] ?>">編集</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </section>
    <?php } ?>
</main>
</body>
<script lang="js">
{
    function copyPreset(command, title) {
        navigator.clipboard.writeText(command);
        const copyAlert = document.getElementById('copy-alert');
        copyAlert.innerHTML = title + 'をコピーしました。'
    };
}
</script>
</html>