<?php
$home = './';
require_once($home . 'database/commonlib.php');
require_once($home . 'api/PresetController.php');
require_once($home . 'api/ImageController.php');

$message = [];
$err = [];

if (!isset($_SESSION['user_id'])) {
    header('location: ./login.php', true, 303);
    exit;
}


$presets = [];
$presetController = new PresetController();
if (isset($_GET['preset_id'])) {
    $preset_id = h($_GET['preset_id']);

    if ($preset_id === null || $preset_id === '') {
        header('location: ../saves/', true, 303);
        exit;
    }

    $presets = $presetController->get($preset_id);
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
            header('location: ../#/saves/', true, 303);
            exit;
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    } else {
        // 画像がアップロードされた場合指定フォルダに移動させ、サムネイルを抽出
        $imageFileName = null;
        $imageDirPath = './images/preset/original/';

        if (isset($_FILES['image']) && $_FILES['image']['name'] !== '') {

            $imageController = new ImageController($imageDirPath);
            $imageFileName = $imageController->saveImageWithUniqueName();
            $imageController->makeThumbnail($home);

        } else if (isset($presets['image'])) {

            // プリセット編集時に画像を更新しない場合は保持
            $imageFileName = $presets['image'];
            
        }  
        
        $response = '';
        if (isset($preset_id)) {
            $response = $presetController->update($_POST, $imageFileName, $preset_id);
        } else {
            $response = $presetController->create($_POST, $imageFileName);
        }
        $message[] = $response;

        $presets = [
            'user_id' => h($_SESSION['user_id']),
            'commands' => h($_POST['commands']),
            'commands_ban' => isset($_POST['commands_ban']) ? h($_POST['commands_ban']) : '',
            'image' => $imageFileName,
            'description' => isset($_POST['description']) ? h($_POST['description']) : '',
            'seed' => isset($_POST['seed']) ? h($_POST['seed']) : '',
            'nsfw' => $_POST['nsfw'],
            'resolution' => isset($_POST['resolution']) ? h($_POST['resolution']) : '',
            'others' => isset($_POST['others']) ? h($_POST['others']) : '',
        ];
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
<link rel="stylesheet" href="<?= $home ?>prompt.css">
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
        <form action="<?= $form_action ?>" method="POST" class="form-common" enctype="multipart/form-data">
            <?php if (isset($_GET['preset_id'])) { ?>
            <div class="delete-area">
                <input type="hidden" name="preset_id" value="<?= h($_GET['preset_id']) ?>">
                <input type="submit" name="delete" value="削除" class="btn-common red" onclick="return alertDeleteMessage()">
            </div>
            <?php } ?>
            <dl>
                <div>
                    <dt>画像</dt>
                    <dd>
                        <input 
                            type="file" 
                            name="image"
                            onchange="previewImage(event)"
                            accept="image/png"
                            id="image-file"
                        >
                    </dd>
                </div>
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
                            value="<?= isset($presets['commands_ban']) ? $presets['commands_ban'] : '' ?>"
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
                    <dt>年齢制限</dt>
                    <dd>
                        <input 
                            type="radio" 
                            name="nsfw" 
                            id="nsfw_a"
                            value="A"
                            <?= !isset($presets['nsfw']) || $presets['nsfw'] === 'A' ? ' checked' : '' ?>
                        >
                        <label for="nsfw_a">全年齢</label>
                        <input 
                            type="radio" 
                            name="nsfw" 
                            id="nsfw_c"
                            value="C"
                            <?= isset($presets['nsfw']) && $presets['nsfw'] === 'C' ? ' checked' : '' ?>
                        >
                        <label for="nsfw_c">R-15</label>
                        <input 
                            type="radio" 
                            name="nsfw" 
                            id="nsfw_z"
                            value="Z"
                            <?= isset($presets['nsfw']) && $presets['nsfw'] === 'Z' ? ' checked' : '' ?>
                        >
                        <label for="nsfw_z">R-18</label>
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
                <input type="hidden" name="from" value="saver">
                <?php if ($_SERVER['REQUEST_METHOD'] !== 'POST') { ?>
                <input type="submit" value="<?= isset($_GET['preset_id']) ? '更新' : '登録' ?>" class="btn-common submit">
                <?php } ?>
            </dl>
        </form>
        <div class="preview">
            <div id="image-preview">
                <p>アップロード画像</p>
                <img src="<?= isset($presets['image']) && !is_null($presets['image']) ? $home . 'images/preset/original/' . $presets['image'] : '' ?>" id="image-preview-area">
                <div class="delete-image" onclick="deleteImage(event)">
                    <span>×</span>
                </div>
            </div>
            <div 
                id="image-dnd" 
                ondrop="importImage(event)" 
                ondragover="dragImage(event, 'over')"
                ondragleave="dragImage(event, 'leave')"
            >
                <div>
                    ここに画像をドロップ
                </div>
            </div>
        </div>
    </section>
</main>
</body>
<script lang="js">
{
    function alertDeleteMessage() {
        return window.confirm("本当に削除しますか?") ? true : false;
    }
    
    const imageFile = document.getElementById('image-file');
    const imageDnd = document.getElementById('image-dnd');
    const imagePreview = document.getElementById('image-preview');
    const imagePreviewArea = document.getElementById('image-preview-area')
    const fileReader = new FileReader();
    function displayImagePreview(image) {
        fileReader.onload = (() => {
            imagePreviewArea.src = fileReader.result;
        });
        fileReader.readAsDataURL(image);
    }
    
    // 画像がセットされた際にプレビューエリアを更新する
    function changeImageAreaStyle() {
        imagePreview.classList.add('on');
        imageDnd.style.display = "none";
    }
    
    // ファイル選択から直接送られた画像をプレビューに反映
    function previewImage(event) {
        if (event.target.files) displayImagePreview(event.target.files[0]);
        changeImageAreaStyle()
    }

    // 画像を取得しファイル名とプレビューに反映
    function importImage(e) {
        imageDnd.style.border = "2px solid #ccc";
        
        const file = e.dataTransfer.files;
        if (file.length !== 1) return alert('複数ファイルのアップロードはできません。');
        imageFile.files = file;
        changeImageAreaStyle()

        e.stopPropagation();
        e.preventDefault();
        console.log(file[0])
        displayImagePreview(file[0]);
    }

    // dndエリアに画像がドラッグされた際の処理
    function dragImage(e, state) {
        e.stopPropagation();
        e.preventDefault();
        imageDnd.style.border = state === 'over' ?
            "2px solid skyblue" :
            "2px solid #ccc";
    }

    // 画像上の×ボタンが押された際の処理
    function deleteImage(e) {
        e.stopPropagation();
        e.preventDefault();

        imageFile.value = '';
        imagePreviewArea.src = '';
        imagePreview.classList.remove('on');
        imageDnd.style.display = 'block';
    }

    // 画面読み込み時、画像が同時に読み込まれていた場合はその画像を表示
    window.onload = () => {
        if (imagePreviewArea.src.slice(-4) === '.png') {
            changeImageAreaStyle()
        }
    }
}
</script>
</html>