<?php
$home = './';
require_once($home . 'database/commonlib.php');

$message = [];
$err = [];

if (!isset($_SESSION['user_id'])) {
    header('location: ./', true, 303);
    exit;
}

// アップロードされた画像からサムネイルを抽出し、ファイル名を変更した後指定フォルダに保存
function setImages() {
    $image = $_FILES['image']['name'];
    $imageLocalPath = $_FILES['image']['tmp_name'];
    $imageDirPath = './images/preset/original/';
    
    // ファイルを保存
    move_uploaded_file($imageLocalPath, $imageDirPath . $image);

    // ファイル名の変更
    $imageFileName = uniqid("img") . '.png';
    rename($imageDirPath . $image, $imageDirPath . $imageFileName);

    // ファイルの解像度を取得
    list($width, $height, $type, $attr) = getimagesize($imageDirPath . $imageFileName);
    // サムネイル用にオリジナル画像を16:10の比率で切り取る
    $cropWidth = $width;
    $cropHeight = $height;
    while (true) {
        $h = ($cropWidth / 16) * 10;
        if ($h < $cropHeight) {
            $cropHeight = $h;
            break;
        } else {
            $cropWidth -= 10;
        }
    }
    // 画像の切り取る位置の0ベクトル地点
    $cropX = ($width / 2) - ($cropWidth / 2);
    $cropY = 0;
    // 画像を切り取る
    $croppedImage = imagecrop(
        imagecreatefrompng($imageDirPath . $imageFileName),
        ['x' => $cropX, 'y' => $cropY, 'width' => $cropWidth, 'height' => $cropHeight]
    );
    // 切り取った画像をthumbnailフォルダに出力
    if ($croppedImage !== false) {
        imagepng($croppedImage, './images/preset/thumbnail/' . $imageFileName);
        imagedestroy($croppedImage);
    }

    return $imageFileName;
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
            // nsfwにチェックが付いているか、プロンプトセットにnsfwが入っていればnsfw指定にする
            $isNsfw = 
                strpos($_POST['commands'], 'nsfw') !== false || 
                (isset($_POST['nsfw']) && $_POST['nsfw'] === 'on')
                ? 1 : 0;

            $pdo = dbConnect();
            $pdo->beginTransaction();

            // 画像がアップロードされた場合、リネームとサムネイル抽出を行い特定フォルダに保存
            $imageFileName = isset($presets['image']) ? $presets['image'] : '';
            if ($_FILES['image']['name'] !== '') $imageFileName = setImages();

            if (isset($_GET['preset_id'])) {
                $sql = <<<SQL
                    UPDATE preset SET
                    commands = :commands,
                    commands_ban = :commands_ban,
                    description = :description,
                    image = :image,
                    nsfw = :nsfw,
                    seed = :seed,
                    resolution = :resolution,
                    others = :others,
                    updated_at = NOW()
                    WHERE preset_id = :preset_id AND user_id = :user_id
                SQL;
            } else {
                $sql = <<<SQL
                INSERT INTO preset 
                (user_id, commands, commands_ban, description, image, nsfw, seed, resolution, others, created_at, updated_at)
                VALUES 
                (:user_id, :commands, :commands_ban, :description, :image, :nsfw, :seed, :resolution, :others, NOW(), NOW())
                SQL;
            }
            $st = $pdo->prepare($sql);
            if (isset($_GET['preset_id'])) $st->bindValue(':preset_id', h($_GET['preset_id']), PDO::PARAM_INT); 
            $st->bindValue(':user_id', h($_SESSION['user_id']), PDO::PARAM_STR);
            $st->bindValue(':commands', h($_POST['commands']), PDO::PARAM_STR);
            $st->bindValue(':commands_ban', isset($_POST['commands_ban']) ? h($_POST['commands_ban']) : null, PDO::PARAM_STR);
            $st->bindValue(':description', isset($_POST['description']) ? h($_POST['description']) : null, PDO::PARAM_STR);
            $st->bindValue(':image', $imageFileName !== '' ? $imageFileName : null, PDO::PARAM_STR);
            $st->bindValue(':nsfw', $isNsfw, PDO::PARAM_INT);
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
                'image' => $imageFileName !== '' ? $imageFileName : null,
                'description' => isset($_POST['description']) ? h($_POST['description']) : '',
                'seed' => isset($_POST['seed']) ? h($_POST['seed']) : '',
                'nsfw' => $isNsfw === 1 ? true : false,
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
                            onchange="previewImage(this)"
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
                    <dt>nsfw</dt>
                    <dd>
                        <input 
                            type="checkbox" 
                            name="nsfw" 
                            id="nsfw"
                            <?= isset($presets['nsfw']) && $presets['nsfw'] ? ' checked' : '' ?>
                        >
                        <label for="nsfw">nsfwに設定する</label>
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
        <div class="preview">
            <div id="image-preview">
                <p>アップロード画像</p>
                <img src="<?= isset($presets['image'])  ? './images/preset/original/' . $presets['image'] : '' ?>" id="image-preview-area">
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
    function previewImage(image) {
        fileReader.onload = (() => {
            imagePreviewArea.src = fileReader.result;
        });
        fileReader.readAsDataURL(image);
    }

    function importImage(e) {
        imageDnd.style.border = "2px solid #ccc";
        
        const file = e.dataTransfer.files;
        if (file.length !== 1) return alert('複数ファイルのアップロードはできません。');
        imageFile.files = file;
        imagePreview.classList.add('on');
        imageDnd.style.display = "none";

        e.stopPropagation();
        e.preventDefault();
        previewImage(file[0]);
    }

    function dragImage(e, state) {
        e.stopPropagation();
        e.preventDefault();
        imageDnd.style.border = state === 'over' ?
            "2px solid skyblue" :
            "2px solid #ccc";
    }

    function deleteImage(e) {
        e.stopPropagation();
        e.preventDefault();

        imageFile.value = '';
        imagePreviewArea.src = '';
        imagePreview.classList.remove('on');
        imageDnd.style.display = 'block';
    }

    window.onload = () => {
        if (imagePreviewArea.src !== '') {
            imagePreview.classList.add('on');
            imageDnd.style.display = "none";
        }
    }
}
</script>
</html>