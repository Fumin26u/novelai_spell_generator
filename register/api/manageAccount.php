<?php
$home = '../';

header('Content-Type: application/json; charset=utf-8', true, 200);
$post = json_decode(file_get_contents('php://input'), true);