<?php
// 非ログイン時かつローカル環境でない場合強制終了
if ($_SERVER['HTTP_HOST'] !== 'localhost' && !isset($_SESSION['user_id'])) exit;

class PresetController {
    private $user_id;
    private $columns;

    public function __construct() {
        $this->user_id = $_SERVER['HTTP_HOST'] === 'localhost' ? 'Fumiya0719':h($_SESSION['user_id']);
        $this->columns = [
            // name: カラム名
            // init: 初期値、falseなら指定無し
            // type: DBに保存する際の型
            [
                'name' => 'user_id',
                'init' => false,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'commands',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'commands_ban',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'description',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'image',
                // 'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'nsfw',
                'init' => 'A',
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'seed',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'resolution',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'model',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'sampling',
                'init' => null,
                'type' => PDO::PARAM_INT,
            ],
            [
                'name' => 'sampling_algo',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'scale',
                'init' => null,
                'type' => PDO::PARAM_INT,
            ],
            [
                'name' => 'options',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'others',
                'init' => '',
                'type' => PDO::PARAM_STR,
            ],
        ];
    }

    public function get($preset_id) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $st = $pdo->prepare('SELECT * FROM preset WHERE preset_id = :preset_id');
            $st->bindValue(':preset_id', $preset_id, PDO::PARAM_INT);
            $st->execute();

            $rows = $st->fetch(PDO::FETCH_ASSOC);
            $pdo->commit();
            
            return $rows;
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    public function delete($preset_id) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $st = $pdo->prepare('DELETE FROM preset WHERE preset_id = :preset_id');
            $st->bindValue(':preset_id', $preset_id, PDO::PARAM_INT);
            $st->execute();

            $pdo->commit();
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    private function bindToExecSQL($pdo, $sql, $post, $imageFileName, $preset_id = null) {
        $st = $pdo->prepare($sql);
        
        $st->bindValue(':user_id', $this->user_id, PDO::PARAM_STR);
        
        if (!is_null($preset_id)) $st->bindValue(':preset_id', $preset_id, PDO::PARAM_INT); 
        
        $st->bindValue(':commands', h($post['commands']), PDO::PARAM_STR);
        $st->bindValue(':commands_ban', isset($post['commands_ban']) ? h($post['commands_ban']) : null, PDO::PARAM_STR);
        $st->bindValue(':description', isset($post['description']) ? h($post['description']) : null, PDO::PARAM_STR);
        $st->bindValue(':image', $imageFileName !== '' ? $imageFileName : null, PDO::PARAM_STR);
        $st->bindValue(':nsfw', isset($post['nsfw']) ? h($post['nsfw']) : 'A', PDO::PARAM_STR);
        $st->bindValue(':seed', isset($post['seed']) ? h($post['seed']) : null, PDO::PARAM_STR);
        $st->bindValue(':resolution', isset($post['resolution']) ? h($post['resolution']) : null, PDO::PARAM_STR);
        $st->bindValue(':model', isset($post['model']) ? h($post['model']) : null, PDO::PARAM_STR);
        $st->bindValue(':sampling', isset($post['sampling']) ? h($post['sampling']) : null, PDO::PARAM_INT);
        $st->bindValue(':sampling_algo', isset($post['sampling_algo']) ? h($post['sampling_algo']) : null, PDO::PARAM_STR);
        $st->bindValue(':scale', isset($post['scale']) ? h($post['scale']) : null, PDO::PARAM_INT);
        $st->bindValue(':options', isset($post['options']) ? h($post['options']) : null, PDO::PARAM_STR);
        $st->bindValue(':others', isset($post['others']) ? h($post['others']) : null, PDO::PARAM_STR);
        
        $st->execute();
        $pdo->commit();
    }

    public function create($post, $imageFileName) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $sql = <<<SQL
                INSERT INTO preset 
                (user_id, commands, commands_ban, description, image, nsfw, seed, resolution, model, sampling, sampling_algo, scale, options, others, created_at, updated_at)
                VALUES 
                (:user_id, :commands, :commands_ban, :description, :image, :nsfw, :seed, :resolution, :model, :sampling, :sampling_algo, :scale, :options, :others, NOW(), NOW())
            SQL;

            $this->bindToExecSQL($pdo, $sql, $post, $imageFileName);

            return '登録しました。';
            
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    public function update($post, $imageFileName, $preset_id) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $sql = <<<SQL
                UPDATE preset SET
                commands = :commands,
                commands_ban = :commands_ban,
                description = :description,
                image = :image,
                nsfw = :nsfw,
                seed = :seed,
                resolution = :resolution,
                model = :model,
                sampling = :sampling,
                sampling_algo = :sampling_algo,
                scale = :scale,
                options = :options,
                others = :others,
                updated_at = NOW()
                WHERE preset_id = :preset_id AND user_id = :user_id
            SQL;

            $this->bindToExecSQL($pdo, $sql, $post, $imageFileName, $preset_id);

            return '更新しました。';
            
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }
}
?>