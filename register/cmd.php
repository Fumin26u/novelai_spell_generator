<?php
$home = './';
require_once($home . 'database/commonlib.php');

try {
    $pdo = dbConnect();

    $dbArray = [];

    for ($i = 1; $i <= 25; $i++) {
        $st = $pdo->query('SELECT * FROM command WHERE genre_id =' . $i);
        $array = $st->fetchAll(PDO::FETCH_ASSOC); 
        $dbArray[] = $array;
    }

    $st = $pdo->query('TRUNCATE TABLE command');
    foreach ($dbArray as $array) {
        $i = 1;
        foreach ($array as $a) {
            $st = $pdo->prepare('INSERT INTO command (command_id, command_name, command_jp, genre_id, nsfw, detail) VALUES (:id, :name, :jp, :gid, :nsfw, NULL)');
            $tail = strlen($i) === 2 ? '00' : '000';
            $id = $a['genre_id'] . $tail . $i;
            $st->bindValue(':id', $id, PDO::PARAM_INT);
            $st->bindValue(':name', $a['command_name'], PDO::PARAM_STR);
            $st->bindValue(':jp', $a['command_jp'], PDO::PARAM_STR);
            $st->bindValue(':gid', $a['genre_id'], PDO::PARAM_INT);
            $st->bindValue(':nsfw', 21 <= $a['genre_id'] && $a['genre_id'] <= 24 ? 1 : 0, PDO::PARAM_INT);
            $st->execute();
            $i++;
        }

    }

} catch (PDOException $e) {
    echo $e;
}

echo 'update command table';