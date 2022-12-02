<?php
$home = '../';
require_once($home . 'database/commonlib.php');
require_once($home. 'api/getMasterData.php');
// ログインユーザーが自分以外の場合トップページにリダイレクト
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== 'Fumiya0719') {
    header('location: ../', true, 303);
    exit;
}

$message = [];

// プロンプト一覧の読み込み
$masterData = getMasterData();
$shapedMasterData = shapeMasterData($masterData[1], $masterData[0]);

// [jsonをダウンロード]ボタンが押された場合、json文字列が定数に格納されたjsファイルをダウンロード
if (isset($_POST['dl_json'])) {
    if ($_SESSION['cToken'] !== $_POST['cToken']) {

        $message[] = '不正なアクセスが行われました';

    } else {
        $json = convertMasterDataToJson($shapedMasterData);

        $fileName = 'master_data.js';
        file_put_contents($fileName, $json);
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename=' . $fileName);

        readfile($fileName);
        exit;
    }
}

$cToken = bin2hex(random_bytes(32));
$_SESSION['cToken'] = $cToken;

$title = 'マスタデータ一覧 | NovelAI プロンプトセーバー';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="./master.css">
<title><?= $title ?></title>
</head>
<body>
<?php include($home . 'header.php') ?>
<main>
    <p><?= implode(', ', $message) ?></p>
    <section class="link-area">
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
            <input type="hidden" name="cToken" value="<?= $cToken ?>">
            <input type="submit" name="dl_json" value="jsonをダウンロード" class="btn-common submit">
        </form>
        <div>
            <a href="./register.php?genre_id=">ジャンル新規登録</a>
            <a href="./register.php?command_id=">プロンプト新規登録</a>
        </div>
    </section>
    <section class="masterData-list">
        <?php foreach($shapedMasterData as $i => $genres) { ?>
        <div>
            <table class="command-table">
                <thead>
                    <tr>
                        <th id="id">ID</th>
                        <th id="jp">日本語名</th>
                        <th id="slag">プロンプト名</th>
                        <th id="nsfw">年齢制限</th>
                        <th id="variation">色設定</th>
                        <th id="edit">編集</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="genre" onclick="displayCommands(<?= $i ?>)">
                        <td id="id"><?= $i ?></td>
                        <td id="jp"><?= $genres['jp'] ?></td>
                        <td id="slag"><?= $genres['slag'] ?></td>
                        <td id="nsfw"><?php 
                            switch ($genres['nsfw']) {
                                case 'A':
                                    echo '全年齢';
                                    break;
                                case 'C':
                                    echo 'R-15';
                                    break;
                                case 'Z':
                                    echo 'R-18';
                                    break;
                                default:
                                    echo '全年齢';
                                    break;
                            }
                        ?></td>
                        <td id="variation"></td>
                        <td id="edit">
                            <a href="./register.php?genre_id=<?= $i ?>">編集</a>
                        </td>
                    </tr>
                    <?php foreach($genres['content'] as $commands) { ?>
                        <tr 
                            class="command <?= 'command_' . $i ?>" 
                            style="display: none;"
                        >
                            <td id="id"><?= $commands['id'] ?></td>
                            <td id="jp"><?= $commands['jp'] ?></td>
                            <td id="slag"><?= $commands['tag'] ?></td>
                            <td id="nsfw"><?php 
                                switch ($commands['nsfw']) {
                                    case 'A':
                                        echo '全年齢';
                                        break;
                                    case 'C':
                                        echo 'R-15';
                                        break;
                                    case 'Z':
                                        echo 'R-18';
                                        break;
                                    default:
                                        echo '全年齢';
                                        break;
                                }
                            ?></td>
                            <td id="variation">
                                <?php
                                    switch ($commands['variation']) {
                                        case 'CC':
                                            echo 'マルチカラー';
                                            break;
                                        case 'CM':
                                            echo 'モノクロ';
                                            break;
                                        default:
                                            echo 'なし';
                                            break;
                                    }
                                ?>
                            </td>
                            <td id="edit">
                                <a href="./register.php?command_id=<?= $commands['id'] ?>&genre_id=<?= $i ?>">編集</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <?php } ?>
    </section>
</main>
</body>
<script lang="js">
{
    function displayCommands(id) {
        const command_id = 'command_' + id;
        const tr = document.getElementsByClassName(command_id);
        for (let i = 0; i < tr.length; i++) {
            tr[i].style.display = tr[i].style.display === 'table-row' ? 'none' : 'table-row';
        }
    }
}
</script>
</html>