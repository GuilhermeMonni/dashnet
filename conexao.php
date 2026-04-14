<?php

$server_remote = false;

if ($server_remote) {
    $db_host = 'sql111.infinityfree.com';
    $db_name = 'if0_41200684_database';
    $db_user = 'if0_41200684';
    $db_pass = '6uwEnAXchkBjw71';
} else {
    $db_host = 'localhost';
    $db_name = 'database';
    $db_user = 'root';
    $db_pass = '';
}
try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";

    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    error_log("Erro de conexão: " . $e->getMessage());
    die("Erro ao conectar com o banco.");
}