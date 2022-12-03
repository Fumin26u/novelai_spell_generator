<?php

class PromptController {
    use SetColumns;
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

                $this->columns = $this->setGenreColumns();                
                $sql = $this->makeCreateSql(array_column($this->columns, 'name'), 'genre');

            } else if ($post['identifier'] === 'prompt') {

                $this->columns = $this->setPromptColumns();        
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

                $this->columns = $this->setGenreColumns();                
                $sql = $this->makeUpdateSql(array_column($this->columns, 'name'), 'genre');

            } else if ($post['identifier'] === 'prompt') {

                $this->columns = $this->setPromptColumns();     
                $sql = $this->makeUpdateSql(array_column($this->columns, 'name'), 'command');

            }

            $this->bindToExecSQL($pdo, $sql, $post);

        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }
}


trait GetMasterData {
    // データベースから全マスタデータを取得
    private function getMasterData() {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();
        
            $sql = <<<SQL
            SELECT command.* FROM command 
            INNER JOIN genre ON command.genre_id = genre.genre_id
            SQL;
            $st = $pdo->query($sql);
            $commands = $st->fetchAll(PDO::FETCH_ASSOC);

            $st = $pdo->query('SELECT * FROM genre');
            $genres = $st->fetchAll(PDO::FETCH_ASSOC);

            $pdo->commit();
            return [$commands, $genres];
            
        } catch (PDOException $e) {
            echo $e;
        }
    }

    // マスタデータを1つの連想配列に成形
    private function shapeMasterData($genres, $commands) {
        $shapedMasterData = [];
        
        foreach ($genres as $genre) {
            $shapedMasterData[$genre['genre_id']] = [
                'jp' => $genre['genre_jp'],
                'slag' => $genre['genre_slag'],
                'caption' => $genre['caption'],
                'nsfw' => $genre['nsfw'],
                'content' => [],
            ];
        }
    
        foreach ($commands as $command) {
            $shapedMasterData[$command['genre_id']]['content'][] = [
                'id' => $command['command_id'],
                'tag' => $command['command_name'],
                'jp' => $command['command_jp'],
                'nsfw' => $command['nsfw'],
                'genre_id' => $command['genre_id'],
                'variation' => $command['variation'],
                'detail' => $command['detail'],
            ];
        }
        
        return $shapedMasterData;
    }
}

trait SetColumns {
    private $columns;

    private function setPromptColumns() {
        $this->columns = [
            [
                'name' => 'command_id',
                'init' => -1,
                'type' => PDO::PARAM_INT,
            ],
            [
                'name' => 'command_name',
                'init' => '',
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'command_jp',
                'init' => '',
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'genre_id',
                'init' => -1,
                'type' => PDO::PARAM_INT,
            ],
            [
                'name' => 'nsfw',
                'init' => 'A',
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'variation',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'detail',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
        ];

        return $this->columns;
    }

    private function setGenreColumns() {
        $this->columns = [
            [
                'name' => 'genre_id',
                'init' => -1,
                'type' => PDO::PARAM_INT,
            ],
            [
                'name' => 'genre_slag',
                'init' => '',
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'genre_jp',
                'init' => '',
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'caption',
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
            [
                'name' => 'nsfw',
                'init' => 'A',
                'type' => PDO::PARAM_STR,
            ],
        ];

        return $this->columns;
    }
}

trait MakeSqlQuery {
    private function makeCreateSql($columnList, $table) {
        $sql = '';

        $columnList = array_column($this->columns, 'name');
        $sql .= "INSERT INTO {$table} (";
        $sql .= implode(', ', $columnList);
        $sql .= ') VALUES (:';
        $sql .= implode(', :', $columnList);
        $sql .= ')';

        return $sql;
    }

    private function makeUpdateSql($columnList, $table) {
        $sql = '';

        $sql = "UPDATE {$table} SET \n";
        for ($i = 0; $i < count($columnList); $i++) {
            $columnName = $columnList[$i];
            if ($i === count($columnList) - 1) {
                $sql .= "{$columnName} = :{$columnName} \n";
            } else {
                $sql .= "{$columnName} = :{$columnName}, \n";
            }
        }
        $sql .= "WHERE {$table}_id = :{$table}_id";

        return $sql;
    }
}