<?php
if (!isset($_SESSION['user_id'])) exit;

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

function setPreset($post) {
    try {
        $pdo = dbConnect();
        $pdo->beginTransaction();

        // 画像がアップロードされた場合、リネームとサムネイル抽出を行い特定フォルダに保存
        $imageFileName = isset($presets['image']) ? $presets['image'] : '';
        if ($post['from'] === 'saver' && $_FILES['image']['name'] !== '') $imageFileName = setImages();
    
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
        $st->bindValue(':commands', h($post['commands']), PDO::PARAM_STR);
        $st->bindValue(':commands_ban', isset($post['commands_ban']) ? h($post['commands_ban']) : null, PDO::PARAM_STR);
        $st->bindValue(':description', isset($post['description']) ? h($post['description']) : null, PDO::PARAM_STR);
        $st->bindValue(':image', $imageFileName !== '' ? $imageFileName : null, PDO::PARAM_STR);
        $st->bindValue(':nsfw', isset($post['nsfw']) ? h($post['nsfw']) : 'A', PDO::PARAM_STR);
        $st->bindValue(':seed', isset($post['seed']) ? h($post['seed']) : null, PDO::PARAM_STR);
        $st->bindValue(':resolution', isset($post['resolution']) ? h($post['resolution']) : null, PDO::PARAM_STR);
        $st->bindValue(':others', isset($post['others']) ? h($post['others']) : null, PDO::PARAM_STR);
    
        $st->execute();
        $pdo->commit();

        if ($_POST['from'] === 'saver') {
            return [
                'message' => isset($_GET['preset_id']) ? '更新しました' : '登録しました',
                'imagePath' => $imageFileName
            ];
        }
    } catch (PDOException $e) {
        if ($_POST['from'] === 'saver') {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        } else if ($_POST['from'] === 'generator') {
            $error = [
                'response' => $e
            ];
            echo json_encode($error, JSON_UNESCAPED_UNICODE);
        }
    }
}
?>