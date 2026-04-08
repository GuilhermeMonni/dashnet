<?php

$server_remote = false;

if ($server_remote) {
    $pdo = new PDO(
        "mysql:host=" . getenv('DB_HOST') .
            ";dbname=" . getenv('DB_NAME') .
            ";charset=utf8mb4",
        getenv('DB_USER'),
        getenv('DB_PASS'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} else {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=database;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
}

try {
    $pdo->query("SELECT 1");
} catch (PDOException $e) {
    error_log("Erro de conexão: " . $e->getMessage());
    die("Erro ao conectar com o banco.");
}