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
}