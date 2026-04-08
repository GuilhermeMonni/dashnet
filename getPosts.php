<?php
    session_start();
    require_once("conexao.php");

    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Não autorizado']);
        exit();
    }

    header('Content-Type: application/json');

    try {
        $stmt = $pdo->query("SELECT user_id, nome, content, created_at, idContent FROM posts ORDER BY created_at DESC LIMIT 50");
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'posts' => $posts]);
    } catch (PDOException $e) {
        error_log("Erro ao buscar posts: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao carregar posts']);
    }