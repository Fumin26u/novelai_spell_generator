<?php

class PromptController {
    private $user_id;
    private $columns;

    public function __construct() {
        $this->user_id = $_SERVER['HTTP_HOST'] === 'localhost' ? 'Fumiya0719':h($_SESSION['user_id']); 
    }

    // データ登録・編集の際必要なDBのカラム名・初期値・型を設定する
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
                'type' => PDO::PARAM_INT,
            ],
            [
                'name' => 'nsfw',
                'init' => 'A',
                'type' => PDO::PARAM_STR,
            ],
        ];
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
        foreach ($columnList as $column) {
            $columnName = $column;
            $sql .= "{$columnName} = :{$columnName}, \n";
        }
        $sql .= "WHERE {$table}_id = :{$table}_id";

        return $sql;
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
                'caption' => $post['caption'],
                'nsfw' => $post['nsfw'],
            ];
        }
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

        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }
}