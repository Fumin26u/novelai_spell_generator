<?php
class ImageController {

    private $imageDirPath;
    private $imageName;

    public function __construct($imageDirPath) {
        $this->imageDirPath = $imageDirPath;
        $this->imageName = uniqid("img") . '.png';
    }

    // アップロードされた画像を指定されたPathに移動
    public function saveImage() {
        $image = $_FILES['image']['name'];
        $imageLocalPath = $_FILES['image']['tmp_name'];
        
        // ファイルを保存
        move_uploaded_file($imageLocalPath, $this->imageDirPath . $image);
    
        // ファイル名の変更
        rename($this->imageDirPath . $image, $this->imageDirPath . $this->imageName);
    }

    // 固有IDを取得
    public function getUniqueID() {
        return $this->imageName;
    }

    // 画像からサムネイルを作成し保存
    public function makeThumbnail($homeDir) {
        // ファイルの解像度を取得
        list($width, $height, $type, $attr) = getimagesize($this->imageDirPath . $this->imageName);
        // サムネイル用にオリジナル画像を16:10の比率で切り取る
        $cropWidth = $width;
        $cropHeight = $height;
        for ($i = 0; $i < 50; $i++) {
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
            imagecreatefrompng($this->imageDirPath . $this->imageName),
            ['x' => $cropX, 'y' => $cropY, 'width' => $cropWidth, 'height' => $cropHeight]
        );
        // 切り取った画像をthumbnailフォルダに出力
        if ($croppedImage !== false) {
            imagepng($croppedImage, $homeDir . 'images/preset/thumbnail/' . $this->imageName);
            imagedestroy($croppedImage);
        }
    }
}