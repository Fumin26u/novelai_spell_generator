<?php

trait GetMasterData {
    // データベースから全マスタデータを取得
    protected function getMasterData() {
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
    protected function shapeMasterData($genres, $commands) {
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