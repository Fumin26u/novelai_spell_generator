<?php
$home = '../';
require_once($home . 'database/commonlib.php');

$message = [];
// ログインユーザーが自分以外の場合トップページにリダイレクト
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] !== 'Fumiya0719') {
    header('location: ../', true, 303);
    exit;
}
 
// URL引数が設定されていない場合、マスタデータトップにリダイレクト
if (!isset($_GET['genre_id']) && !isset($_GET['command_id'])) {
    header('location: ./', true, 303);
    exit;
}

try {
    $pdo = dbConnect();
    // URL引数に応じて登録・編集する内容を指定
    // 同時にフォームに必要なデータも取得
    $content = '';
    $select_list = [];
    if (isset($_GET['genre_id']) && !isset($_GET['command_id'])) {
        $content = 'genre';
        // セレクトボックス用のカテゴリ一覧を取得
        $st = $pdo->query('SELECT category_id, category_jp FROM category');
        $select_list = $st->fetchAll(PDO::FETCH_ASSOC);

        // ジャンルIDの現在の最大値
        $st = $pdo->query('SELECT MAX(genre_id) FROM genre');
        $row = $st->fetch(PDO::FETCH_NUM);
        $max_genre_id = $row[0];
    }
    
    if (isset($_GET['command_id'])) { 
        $content = 'command';         
        // セレクトボックス用のジャンル一覧を取得
        $st = $pdo->query('SELECT genre_id, genre_jp, category_jp FROM genre INNER JOIN category ON genre.category_id = category.category_id');
        $select_list = $st->fetchAll(PDO::FETCH_ASSOC);
    }

    // データ登録処理
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $err = [];
        $pdo->beginTransaction();

        if ($content === 'genre') {
            if ($_GET['genre_id'] === '') {
                $sql = <<<SQL
                INSERT INTO genre 
                (genre_id, genre_slag, genre_jp, category_id, detail) 
                VALUES
                (:id, :slag, :jp, :parent_group, :detail)
                SQL;
            } else {
                $sql = <<<SQL
                UPDATE genre SET
                genre_slag = :slag,
                genre_jp = :jp,
                category_id = :parent_group,
                detail = :detail
                WHERE genre_id = :id
                SQL;
            }
        } else if ($content === 'command') {
            if ($_GET['command_id'] === '') {
                // 設定されたプロンプトIDが既存の場合エラー
                $st = $pdo->prepare('SELECT * FROM command WHERE command_id = :command_id');
                $st->bindValue(':command_id', h($_POST['id']), PDO::PARAM_INT);
                $st->execute();

                $row = $st->fetch(PDO::FETCH_ASSOC);
                if (!empty($row)) $err[] = '既に存在するIDです。';

                $sql = <<<SQL
                INSERT INTO command 
                (command_id, command_name, command_jp, genre_id, detail) 
                VALUES
                (:id, :slag, :jp, :parent_group, :detail)
                SQL;
            } else {
                $sql = <<<SQL
                UPDATE command SET
                command_name = :slag,
                command_jp = :jp,
                genre_id = :parent_group,
                detail = :detail
                WHERE command_id = :id
                SQL;
            }
        }

        if (empty($err)) {
            $st = $pdo->prepare($sql);
            $st->bindValue(':id', h($_POST['id']), PDO::PARAM_INT);
            $st->bindValue(':slag', h($_POST['slag']), PDO::PARAM_STR);
            $st->bindValue(':jp', h($_POST['jp']), PDO::PARAM_STR);
            $st->bindValue(':parent_group', h($_POST['parent_group']), PDO::PARAM_INT);
            $st->bindValue(':detail', isset($_POST['detail']) ? h($_POST['detail']) : null, PDO::PARAM_STR);
            $st->execute();

            $message[] = '登録しました。';
            
            // 入力データを画面に反映
            if ($content === 'genre') {
                $prompt_info = [
                    'genre_id' => h($_POST['id']),
                    'genre_slag' => h($_POST['slag']),
                    'genre_jp' => h($_POST['jp']),
                    'category_id' => h($_POST['parent_group']),
                    'detail' => isset($_POST['detail']) ? h($_POST['detail']) : '',
                ];
            } else if ($content === 'command') {
                $prompt_info = [
                    'command_id' => h($_POST['id']),
                    'command_name' => h($_POST['slag']),
                    'command_jp' => h($_POST['jp']),
                    'genre_id' => h($_POST['parent_group']),
                    'detail' => isset($_POST['detail']) ? h($_POST['detail']) : '',
                ];
            }
        }
        $pdo->commit();
        $message += $err;
    }
    
    // URL引数にパラメータが設定されている場合、該当IDのデータをデータベースから取得
    if ($content === 'genre' && $_GET['genre_id'] !== '') {
        $genre_id = h($_GET['genre_id']);
        $pdo->beginTransaction();
    
        $st = $pdo->prepare('SELECT * FROM genre WHERE genre_id = :genre_id');
        $st->bindValue(':genre_id', $genre_id, PDO::PARAM_INT);
        $st->execute();
    
        $prompt_info = $st->fetch(PDO::FETCH_ASSOC);    
        $pdo->commit();
    
    } else if ($content === 'command' && $_GET['command_id'] !== '') {
        $command_id = h($_GET['command_id']);
        $pdo = dbConnect();
        $pdo->beginTransaction();
    
        $st = $pdo->prepare('SELECT * FROM command WHERE command_id = :command_id');
        $st->bindValue(':command_id', $command_id, PDO::PARAM_INT);
        $st->execute();
        
        $prompt_info = $st->fetch(PDO::FETCH_ASSOC);   
        $pdo->commit();
    }
} catch (PDOException $e) {
    echo 'データベース接続に失敗しました。';
    if (DEBUG) echo $e;
}

$title = 'マスタデータ登録・編集 | NovelAI プロンプトセーバー';
$h2_title = $content === 'command' ? 'プロンプト登録・編集' : 'ジャンル登録・編集';
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
    <div>
        <p><?= implode(', ', $message) ?></p>
        <h2><?= $h2_title ?></h2>
        <form action="<?= $_SERVER['REQUEST_URI'] ?>" method="POST" class="form-common">
            <?php if ($content === 'genre') { ?>
            <dl>
                <div>
                    <dt>ID</dt>
                    <dd>
                        <input 
                            type="number"
                            value="<?= isset($prompt_info['genre_id']) ? h($prompt_info['genre_id']) : $max_genre_id + 1 ?>"
                            readonly
                            required
                        >
                    </dd>
                </div>
                <div>
                    <dt>カテゴリ</dt>
                    <dd>
                        <select name="parent_group">
                            <?php foreach($select_list as $option) { ?>
                                <option 
                                    value="<?= $option['category_id'] ?>" 
                                    <?= isset($prompt_info['category_id']) && $option['category_id'] === $prompt_info['category_id'] ? ' selected' : '' ?>
                                >
                                    <?= $option['category_jp'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </dd>
                </div>
                <div>
                    <dt>スラッグ</dt>
                    <dd>
                        <input 
                            type="text" 
                            name="slag" 
                            value="<?= isset($prompt_info['genre_slag']) ? h($prompt_info['genre_slag']) : '' ?>" 
                            required
                        >
                    </dd>
                </div>
                <div>
                    <dt>日本語名</dt>
                    <dd>
                        <input 
                            type="text" 
                            name="jp" 
                            value="<?= isset($prompt_info['genre_jp']) ? h($prompt_info['genre_jp']) : '' ?>" 
                            required
                        >
                    </dd>
                </div>
                <div>
                    <dt>詳細・その他</dt>
                    <dd>
                        <input 
                            type="text" 
                            name="detail" 
                            value="<?= isset($prompt_info['detail']) ? h($prompt_info['detail']) : '' ?>" 
                        >
                    </dd>
                </div>
            </dl>
            <?php } else if ($content === 'command') { ?>
            <dl>
                <div>
                    <dt>ID</dt>
                    <dd>
                        <input 
                            type="number"
                            name="id"
                            id="command_id"
                            value="<?= isset($prompt_info['command_id']) ? h($prompt_info['command_id']) : '' ?>"
                            required
                        >
                    </dd>
                </div>
                <div>
                    <dt>ジャンル</dt>
                    <dd>
                        <select name="parent_group" oninput="changeCommandID(event)" id="genre_list">
                            <?php foreach($select_list as $option) { ?>
                                <option 
                                    value="<?= $option['genre_id'] ?>" 
                                    <?= isset($prompt_info['genre_id']) && $option['genre_id'] === $prompt_info['genre_id'] ? ' selected' : '' ?>
                                >
                                    <?= $option['category_jp'] . ' - ' .  $option['genre_jp'] ?>
                                </option>
                            <?php } ?>
                        </select>
                    </dd>
                </div>
                <div>
                    <dt>プロンプト名</dt>
                    <dd>
                        <input 
                            type="text" 
                            name="slag" 
                            value="<?= isset($prompt_info['command_name']) ? h($prompt_info['command_name']) : '' ?>" 
                            required
                        >
                    </dd>
                </div>
                <div>
                    <dt>日本語名</dt>
                    <dd>
                        <input 
                            type="text" 
                            name="jp" 
                            value="<?= isset($prompt_info['command_jp']) ? h($prompt_info['command_jp']) : '' ?>" 
                            required
                        >
                    </dd>
                </div>
                <div>
                    <dt>詳細・その他</dt>
                    <dd>
                        <input 
                            type="text" 
                            name="detail" 
                            value="<?= isset($prompt_info['detail']) ? h($prompt_info['detail']) : '' ?>" 
                        >
                    </dd>
                </div>
            </dl>
            <?php } ?>
            <input type="submit" value="登録" class="btn-common submit">
        </form>
    </div>
</main>
</body>
<script lang="js">
{
    function changeCommandID(event) {
        const ci = document.getElementById('command_id');
        ci.value = event.target.value + '0000'
    }

    <?php if ($content === 'command') { ?>
        window.onload = () => {
            const gl = document.getElementById('genre_list');
            gl.addEventListener('input', changeCommandID);
        }
    <?php } ?>
}
</script>
</html>