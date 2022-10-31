<?php
$home = './';
require_once($home . 'database/commonlib.php');

$message = [];
$err = [];

if (!isset($_SESSION['user_id'])) {
    header('location: ./', true, 303);
    exit;
}

$presets = [];
if (isset($_GET['preset_id'])) {
    $preset_id = h($_GET['preset_id']);

    if ($preset_id === null || $preset_id === '') {
        header('location: ./', true, 303);
        exit;
    }

    try {
        $pdo = dbConnect();
        $pdo->beginTransaction();

        $st = $pdo->prepare('SELECT * FROM preset WHERE preset_id = :preset_id');
        $st->bindValue(':preset_id', $preset_id, PDO::PARAM_INT);
        $st->execute();

        $rows = $st->fetch(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            header('location: ./index.php', true, 303);
            exit;
        }
        $pdo->commit();

        $presets = $rows;
    } catch (PDOException $e) {
        echo 'データベース接続に失敗しました。';
        if (DEBUG) echo $e;
    }
    
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['cToken'] !== $_POST['cToken']) {

        $message[] = '不正なアクセスが行われました';

    } else if (isset($_POST['delete'])) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();
            
            $st = $pdo->prepare('DELETE FROM preset WHERE preset_id = :preset_id AND user_id = :user_id');
            $st->bindValue(':preset_id', h($_POST['preset_id']), PDO::PARAM_INT);
            $st->bindValue(':user_id', h($_SESSION['user_id']), PDO::PARAM_STR);
            $st->execute();

            $pdo->commit();
            header('location: ./', true, 303);
            exit;
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    } else {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();
            if (isset($_GET['preset_id'])) {
                $sql = <<<SQL
                    UPDATE preset SET
                    commands = :commands,
                    commands_ban = :commands_ban,
                    description = :description,
                    seed = :seed,
                    resolution = :resolution,
                    others = :others,
                    updated_at = NOW()
                    WHERE preset_id = :preset_id AND user_id = :user_id
                SQL;
            } else {
                $sql = <<<SQL
                INSERT INTO preset 
                (user_id, commands, commands_ban, description, seed, resolution, others, created_at, updated_at)
                VALUES 
                (:user_id, :commands, :commands_ban, :description, :seed, :resolution, :others, NOW(), NOW())
                SQL;
            }
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

            $message[] = isset($_GET['preset_id']) ? '更新しました' : '登録しました';

            $presets = [
                'user_id' => h($_SESSION['user_id']),
                'commands' => h($_POST['commands']),
                'commands_ban' => isset($_POST['commands_ban']) ? h($_POST['commands_ban']) : '',
                'description' => isset($_POST['description']) ? h($_POST['description']) : '',
                'seed' => isset($_POST['seed']) ? h($_POST['seed']) : '',
                'resolution' => isset($_POST['resolution']) ? h($_POST['resolution']) : '',
                'others' => isset($_POST['others']) ? h($_POST['others']) : '',
            ];

        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }
}

$resolutions = [
    'Portrait (Normal) 512x768',
    'LandScape (Normal) 768x512',
    'Square (Normal) 640x640',
    'Portrait (Small) 384x640',
    'LandScape (Small) 640x384',
    'Square (Small) 512x512',
    'Portrait (Large) 512x1024',
    'LandScape (Large) 1024x512',
    'Square (Large) 1024x1024',
];

$form_action = isset($_GET['preset_id']) ? $_SERVER['PHP_SELF'] . '?preset_id=' . h($_GET['preset_id']) : $_SERVER['PHP_SELF'];
$cToken = bin2hex(random_bytes(32));
$_SESSION['cToken'] = $cToken;

$title = 'プロンプト登録 | NovelAI プロンプトセーバー';
// デスクリプション
$description = "AIによるイラスト自動生成サービスである「NovelAI」の呪文(プロンプト)の生成・管理を補助するサービスです。マウスクリックによる簡単操作で呪文を生成することや、お気に入りの画像を生成する呪文を保存することが可能です。";
$keywords = "NovelAI,NAI,プロンプト,登録,セーブ,保存,生成,自動";
$canonical = "https://nai-pg.com/register/";
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="<?= $home ?>styles.css">
<title><?= $title ?></title>
<link rel="canonical" href="<?= $canonical ?>">
<meta property="og:description" content="<?= $description ?>">
<meta name="keywords" content="<?= $keywords ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="NovelAI プロンプトセーバー">
<meta property="og:url" content="<?= $canonical ?>">
<meta property="og:title" content="<?= $title ?>">
</head>
<body>
<?php include($home . 'header.php') ?>
<main>
    <p><?= implode(', ', $message) ?></p>
    <h2>プロンプト登録・編集</h2>
    <section class="spell-register">
        <form action="<?= $form_action ?>" method="POST" class="form-common">
            <?php if (isset($_GET['preset_id'])) { ?>
            <div class="delete-area">
                <input type="hidden" name="preset_id" value="<?= h($_GET['preset_id']) ?>">
                <input type="submit" name="delete" value="削除" class="btn-common red" onclick="return alertDeleteMessage()">
            </div>
            <?php } ?>
            <dl>
                <div>
                    <dt>プロンプト</dt>
                    <dd>
                        <input 
                            type="text" 
                            name="commands" 
                            value="<?= isset($presets['commands']) ? $presets['commands'] : '' ?>" 
                            required
                        >
                    </dd>
                </div>
                <div>
                    <dt>BANプロンプト</dt>
                    <dd>
                        <input 
                            type="text" 
                            name="commands_ban" 
                            value="<?= isset($presets['commands_ban']) ? $presets['commands_ban'] : '{{extra fingers}}, poorly drawn hands, poorly drawn face, {mutation},{deformed}, bad anatomy ,bad proportions,{{extra limbs}},heterochromia' ?>"
                        >
                    </dd>
                </div>
                <div>
                    <dt>説明</dt>
                    <dd>
                        <input 
                            type="text" 
                            name="description" 
                            value="<?= isset($presets['description']) ? $presets['description'] : '' ?>"
                        >
                    </dd>
                </div>
                <div>
                    <dt>シード値</dt>
                    <dd>
                        <input 
                            type="number" 
                            name="seed" 
                            value="<?= isset($presets['seed']) ? $presets['seed'] : '' ?>"
                        >
                    </dd>
                </div>
                <div>
                    <dt>解像度</dt>
                    <dd>
                        <select name="resolution">
                            <?php foreach ($resolutions as $resolution) { ?>
                                <option 
                                    value="<?= $resolution ?>" 
                                    <?= isset($presets['resolution']) && $presets['resolution'] === $resolution ? ' selected' : '' ?>
                                >
                                    <?= $resolution ?>
                                </option>
                            <?php } ?>
                        </select>
                    </dd>
                </div>
                <div>
                    <dt>その他</dt>
                    <dd><textarea name="others" cols="30" rows="10"><?= isset($presets['others']) ? h($presets['others']) : '' ?></textarea></dd>
                </div>
                <input type="hidden" name="cToken" value="<?= $cToken ?>">
                <input type="submit" value="<?= isset($_GET['preset_id']) ? '更新' : '登録' ?>" class="btn-common submit">
            </dl>
        </form>
    </section>
</main>
</body>
<script lang="js">
{
    function alertDeleteMessage() {
        return window.confirm("本当に削除しますか?") ? true : false;
    }
}
</script>
</html>