<?php
// 非ログイン時かつローカル環境でない場合強制終了
if ($_SERVER['HTTP_HOST'] !== 'localhost' && !isset($_SESSION['user_id'])) exit;

class PresetController implements DBControllers {
    private $user_id;
    private $columns;

    public function __construct() {
        $this->user_id = $_SERVER['HTTP_HOST'] === 'localhost' ? 'Fumiya0719':h($_SESSION['user_id']);    
    }

    // データ登録・編集の際必要なDBのカラム名・初期値・型を設定する
    private function setPresetColumns() {
        $this->columns = [
            // name: カラム名
            // init: 初期値、falseなら指定無し
            // type: DBに保存する際の型
            [
                'name' => 'user_id',
                'init' => $this->user_id,
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
                'init' => null,
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
                'init' => null,
                'type' => PDO::PARAM_STR,
            ],
        ];
    }

    public function makeSearchQuery($get) {
        $searchAge = [];
        if (isset($get['age']) && !empty($get['age'])) {
            foreach ($get['age'] as $age) {
                $searchAge[] = "nsfw = '" . $age . "'";
            }
        }
        $searchWords = [];
        if ($get['word'] !== '' && isset($get['item']) && !empty($get['item'])) {
            foreach ($get['item'] as $item) {
                $searchWords[] = $item . " LIKE '%" . h($get['word']) . "%'";
            }
        }
    
        $order = " ORDER BY "  . h($get['sort']) . " " . h($get['order']);
        $searchQuery = '';
        if (!empty($searchAge) || !empty($searchWords)) {
            $searchQuery .= ' AND ';
            if (!empty($searchAge) && !empty($searchWords)) {
                $searchQuery .= '(' . implode(" OR ", $searchAge) . ')';
                $searchQuery .= ' AND (' . implode(" OR ", $searchWords) . ')';
            } else if (!empty($searchAge)) {
                $searchQuery .= '(' . implode(" OR ", $searchAge) . ')';
            } else if (!empty($searchWords)) {
                $searchQuery .= '(' . implode(" OR ", $searchWords) . ')';
            }
        }
        $searchQuery .= $order;

        return $searchQuery;
    }

    public function get($searchQuery = '') {
        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            $st = $pdo->prepare('SELECT * FROM preset WHERE user_id = :user_id' . $searchQuery);
            $st->bindValue(':user_id', $this->user_id, PDO::PARAM_STR);
            $st->execute();

            $rows = $st->fetchAll(PDO::FETCH_ASSOC);
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

            return 'データを削除しました。';
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    private function bindToExecSQL($pdo, $sql, $post, $preset_id = null) {
        $st = $pdo->prepare($sql);
        
        if (!is_null($preset_id)) $st->bindValue(':preset_id', $preset_id, PDO::PARAM_INT); 

        foreach ($this->columns as $column) {
            // 見づらくなりそうなので代入
            $columnName = $column['name'];
            $columnInit = $column['init'];
            $columnType = $column['type'];

            $st->bindValue(
                ":{$columnName}", 
                isset($post[$columnName]) ? h($post[$columnName]) : $columnInit,
                $columnType
            );
        }
        
        $st->execute();
        $pdo->commit();
    }

    public function create($post) {
        $this->setPresetColumns();

        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            // SQL文の構築
            $sql = 'INSERT INTO preset (';
            $sql .= implode(', ', array_column($this->columns, 'name'));
            $sql .= ', created_at, updated_at) VALUES (';
            foreach ($this->columns as $column) {
                $columnName = $column['name'];
                $sql .= ":{$columnName}, ";
            }
            $sql .= 'NOW(), NOW())';   

            // SQL文にデータバインドを行い登録
            $this->bindToExecSQL($pdo, $sql, $post);

            return 'データを登録しました。';
            
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }

    public function update($post, $preset_id) {
        $this->setPresetColumns();

        try {
            $pdo = dbConnect();
            $pdo->beginTransaction();

            // SQL文の構築
            $sql = "UPDATE preset SET \n";
            foreach ($this->columns as $column) {
                if ($column['name'] === 'user_id') continue;
                $columnName = $column['name'];
                $sql .= "{$columnName} = :{$columnName}, \n";
            }
            $sql .= "updated_at = NOW() \n";
            $sql .= "WHERE preset_id = :preset_id AND user_id = :user_id";  

            // SQL文にデータバインドを行い更新
            $this->bindToExecSQL($pdo, $sql, $post, $preset_id);

            return 'データを更新しました。';
            
        } catch (PDOException $e) {
            echo 'データベース接続に失敗しました。';
            if (DEBUG) echo $e;
        }
    }
}
?>