<?php
$home = '../';
require_once($home . 'database/commonlib.php');

// データベースから全マスタデータを取得
function getMasterData() {
    try {
        $pdo = dbConnect();
        $pdo->beginTransaction();
    
        $sql = <<<SQL
        SELECT command_name, command_jp, command.detail, command.genre_id, genre.category_id FROM command 
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

// マスタデータをJsonに変換
function convertMasterDataToJson($categories, $genres, $commands) {
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
            'tag' => $command['command_name'],
            'jp' => $command['command_jp'],
            'detail' => $command['detail'],
        ];
    }

    $json = json_encode($shapedMasterData, JSON_UNESCAPED_UNICODE);
    return $json;
} 


if (isset($_POST['dl_json'])) {
    $masterData = getMasterData();
    $json = "const master_data = `" . convertMasterDataToJson($masterData[2], $masterData[1], $masterData[0]) . "`\nexport default master_data";

    $fileName = 'master_data.js';
    file_put_contents($fileName, $json);
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename=' . $fileName);

    readfile($fileName);
    exit;
}

// echo '<pre>';
// v($json);
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
    <form action="<?= $_SERVER['PHP_SELF'] ?>" method="POST">
        <input type="submit" name="dl_json" value="jsonをダウンロード" class="btn-common submit">
    </form>
</main>
</body>
<script></script>
</html>