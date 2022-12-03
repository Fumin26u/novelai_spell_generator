<?php

require_once('./prompt/SetColumns.php');
require_once('./prompt/GetMasterData.php');
require_once('./prompt/MakeSqlQuery.php');

class PromptController {
    use SetPromptColumns;
    use GetMasterData;
    use MakeSqlQuery;

    private $user_id;
    private $columns;

    public function __construct() {
        $this->user_id = $_SERVER['HTTP_HOST'] === 'localhost' ? 'Fumiya0719':h($_SESSION['user_id']); 
    }

    public function delete($id, $table) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $sql = $table === 'genre' ?
                'DELETE FROM genre WHERE genre_id = :id':
                'DELETE FROM command WHERE command_id = :id';

            $st = $pdo->prepare($sql);
            $st->bindValue(':id', h($id), PDO::PARAM_INT);
            $st->execute();

            $pdo->commit();
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    // 整形したマスタデータをJsonに変換
    public function convertMasterDataToJson($masterData) {
        $json = json_encode($masterData, JSON_UNESCAPED_UNICODE);
        $json = "const master_data = `" . $json . "`\nexport default master_data";
        return $json;
    } 

    // マスタデータを取得
    public function get() {
        $masterData = $this->getMasterData();
        $shapedMasterData = $this-> shapeMasterData($masterData[1], $masterData[0]);
        return $shapedMasterData;
    }

    private function bindToExecSQL($pdo, $sql, $post) {
        $st = $pdo->prepare($sql);

        // プロパティ名とカラム名が異なるので新規で連想配列を作り再挿入
        $data = [];
        if ($post['identifier'] === 'genre') {
            $data = [
                'genre_id' => $post['id'],
                'genre_slag' => $post['slag'],
                'genre_jp' => $post['jp'],
                'caption' => $post['caption'],
                'nsfw' => $post['nsfw'],
            ];
        } else if ($post['identifier'] === 'prompt') {
            $data = [
                'command_id' => $post['id'],
                'command_name' => $post['tag'],
                'command_jp' => $post['jp'],
                'genre_id' => $post['genre_id'],
                'nsfw' => $post['nsfw'],
                'variation' => $post['variation'],
                'detail' => $post['detail'],
            ];
        }

        foreach ($this->columns as $column) {
            // 見づらくなりそうなので代入
            $columnName = $column['name'];
            $columnInit = $column['init'];
            $columnType = $column['type'];

            $st->bindValue(
                ":{$columnName}", 
                isset($data[$columnName]) ? h($data[$columnName]) : $columnInit,
                $columnType
            );
        }
        
        $st->execute();
        $pdo->commit();
    }

    public function create($post) {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();
            
            if ($post['identifier'] === 'genre') {

                $this->setGenreColumns();                
                $sql = $this->makeCreateSql(array_column($this->columns, 'name'), 'genre');

            } else if ($post['identifier'] === 'prompt') {

                $this->setPromptColumns();        
                $sql = $this->makeCreateSql(array_column($this->columns, 'name'), 'command');

            }

            $this->bindToExecSQL($pdo, $sql, $post);

        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    public function update($post) {
        
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();
            
            if ($post['identifier'] === 'genre') {

                $this->setGenreColumns();                
                $sql = $this->makeUpdateSql(array_column($this->columns, 'name'), 'genre');

            } else if ($post['identifier'] === 'prompt') {

                $this->setPromptColumns();     
                $sql = $this->makeUpdateSql(array_column($this->columns, 'name'), 'command');

            }

            $this->bindToExecSQL($pdo, $sql, $post);

        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }
}