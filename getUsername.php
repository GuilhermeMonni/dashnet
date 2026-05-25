<?php
session_start();
header('Content-Type: application/json');

require 'conexao.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $userId = (int)($_GET['userId'] ?? 0);

        if (!$userId) {
            echo json_encode([
                'sucess' => false,
                'error' => 'Sessão encerrada.'
            ]);
            exit;
        }
    }
    
    $stmt = $pdo->prepare("
    SELECT nome
    FROM usuarios
    WHERE id = :user_id
    ");
    $stmt->bindValue(':user_id', $userId);
    $stmt->execute();
    $name = $stmt->fetchColumn();

    echo json_encode([
        'user_name' => $name
    ]);
    exit;
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro no banco de dados'
    ]);
}