<?php
use \PDO;

if (!isset($_SESSION['user_id'])) exit;

class PresetController {
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

    private function bindToExecSQL($pdo, $sql, $post, $imageFileName, $preset_id = null) {
        $st = $pdo->prepare($sql);
        if (!is_null($preset_id)) $st->bindValue(':preset_id', $preset_id, PDO::PARAM_INT); 
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
    }

    public function create($post, $imageFileName) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $sql = <<<SQL
                INSERT INTO preset 
                (user_id, commands, commands_ban, description, image, nsfw, seed, resolution, others, created_at, updated_at)
                VALUES 
                (:user_id, :commands, :commands_ban, :description, :image, :nsfw, :seed, :resolution, :others, NOW(), NOW())
            SQL;

            $this->bindToExecSQL($pdo, $sql, $post, $imageFileName);
            
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
                others = :others,
                updated_at = NOW()
                WHERE preset_id = :preset_id AND user_id = :user_id
            SQL;

            $this->bindToExecSQL($pdo, $sql, $post, $imageFileName, $preset_id);
            
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }
}
?>