<?php
$home = '../';
require_once($home . 'database/commonlib.php');
// ログインユーザーが自分以外の場合トップページにリダイレクト
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== 'Fumiya0719') {
    header('location: ../', true, 303);
    exit;
}

// データベースから全マスタデータを取得
function getMasterData() {
    try {
        $pdo = dbConnect();
        $pdo->beginTransaction();
    
        $sql = <<<SQL
        SELECT command_id, command_name, command_jp, command.detail, command.genre_id, genre.category_id FROM command 
        INNER JOIN genre ON command.genre_id = genre.genre_id
        SQL;
        $st = $pdo->query($sql);
        $commands = $st->fetchAll(PDO::FETCH_ASSOC);

        $st = $pdo->query('SELECT genre_id, genre_slag, genre_jp, detail, category_id FROM genre');
        $genres = $st->fetchAll(PDO::FETCH_ASSOC);

        $st = $pdo->query('SELECT category_id, category_slag, category_jp, detail FROM category');
        $categories = $st->fetchAll(PDO::FETCH_ASSOC);

        $pdo->commit();
        return [$commands, $genres, $categories];
        
    } catch (PDOException $e) {
        echo $e;
    }
}

// マスタデータを1つの連想配列に成形
function shapeMasterData($categories, $genres, $commands) {
    $shapedMasterData = [];
    foreach ($categories as $category) {
        $shapedMasterData[$category['category_id']] = [
            'jp' => $category['category_jp'],
            'slag' => $category['category_slag'],
            'detail' => $category['detail'],
            'content' => [],
        ];
    }
    
    foreach ($genres as $genre) {
        $shapedMasterData[$genre['category_id']]['content'][$genre['genre_id']] = [
            'jp' => $genre['genre_jp'],
            'slag' => $genre['genre_slag'],
            'detail' => $genre['detail'],
            'type' => 'checkbox',
            'content' => [],
        ];
    }

    foreach ($commands as $command) {
        $shapedMasterData[$command['category_id']]['content'][$command['genre_id']]['content'][] = [
            'id' => $command['command_id'],
            'tag' => $command['command_name'],
            'jp' => $command['command_jp'],
            'detail' => $command['detail'],
        ];
    }

    return $shapedMasterData;
}

// 整形したマスタデータをJsonに変換
function convertMasterDataToJson($masterData) {
    $json = json_encode($masterData, JSON_UNESCAPED_UNICODE);
    $json = "const master_data = `" . $json . "`\nexport default master_data";
    return $json;
} 

// コマンド一覧の読み込み
$masterData = getMasterData();
$shapedMasterData = shapeMasterData($masterData[2], $masterData[1], $masterData[0]);

// [jsonをダウンロード]ボタンが押された場合、json文字列が定数に格納されたjsファイルをダウンロード
if (isset($_POST['dl_json'])) {
    $json = convertMasterDataToJson($shapedMasterData);

    $fileName = 'master_data.js';
    file_put_contents($fileName, $json);
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename=' . $fileName);

    readfile($fileName);
    exit;
}

// echo '<pre>';
// v($shapedMasterData);
// echo '</pre>';

$title = 'マスタデータ一覧 | NovelAI コマンド登録機';
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
    <section class="link-area">
        <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
            <input type="submit" name="dl_json" value="jsonをダウンロード" class="btn-common submit">
        </form>
        <div>
            <a href="./register.php?genre_id=">ジャンル新規登録</a>
            <a href="./register.php?command_id=">コマンド新規登録</a>
        </div>
    </section>
    <section class="masterData-list">
        <?php foreach($shapedMasterData as $i => $categories) { ?>
        <div>
            <h2><?= $categories['jp'] ?></h2>
            <table class="command-table">
                <thead>
                    <tr>
                        <th id="id">ID</th>
                        <th id="jp">日本語名</th>
                        <th id="slag">スラッグ・コマンド名</th>
                        <th id="detail">説明</th>
                        <th id="edit">編集</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($categories['content'] as $j => $genres) { ?>
                        <tr class="genre" onclick="displayCommands(<?= $j ?>)">
                            <td id="id"><?= $j ?></td>
                            <td id="jp"><?= $genres['jp'] ?></td>
                            <td id="slag"><?= $genres['slag'] ?></td>
                            <td id="detail"><?= $genres['detail'] ?></td>
                            <td id="edit">
                                <a href="./register.php?genre_id=<?= $j ?>">編集</a>
                            </td>
                        </tr>
                        <?php foreach($genres['content'] as $commands) { ?>
                            <tr 
                                class="command <?= 'command_' . $j ?>" 
                                style="display: none;"
                            >
                                <td id="id"><?= $commands['id'] ?></td>
                                <td id="jp"><?= $commands['jp'] ?></td>
                                <td id="slag"><?= $commands['tag'] ?></td>
                                <td id="detail"><?= $commands['detail'] ?></td>
                                <td id="edit">
                                    <a href="./register.php?command_id=<?= $commands['id'] ?>&genre_id=<?= $j ?>">編集</a>
                                </td>
                            </tr>
                        <?php } ?>
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