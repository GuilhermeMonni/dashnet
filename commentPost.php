<?php
session_start();
header('Content-Type: application/json');

require 'conexao.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $postId = (int)($_GET['post_id'] ?? 0);
        $userID = (int)($_SESSION['id'] ?? 0);

        if (!$postId || !$userID) {
            echo json_encode([
                'sucess' => false,
                'error' => 'Post inválido ou sessão encerrada.'
            ]);
            exit;
        }

        //comments
        $stmt = $pdo->prepare("
                SELECT *
                FROM comments
                WHERE post_id = :post_id
            ");
        $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $stmt->execute();

        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'comments_content' => $comments
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);

        $postId = (int)($data['post_id'] ?? 0);
        $userId = (int)($_SESSION['id'] ?? 0);
        $content = trim($data['content'] ?? '');

        if (!$postId || !$userId || $content === '') {
            echo json_encode([
                'success' => false,
                'error' => 'Dados inválidos'
            ]);
            exit;
        }

        if (mb_strlen($content) > 500) {
            echo json_encode([
                'success' => false,
                'error' => 'Comentário muito grande'
            ]);
            exit;
        }

        $stmt = $pdo->prepare("
            INSERT INTO comments (post_id, user_id, content, created_at)
            VALUES (:post_id, :user_id, :content, NOW())
        ");

        $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':content', $content, PDO::PARAM_STR);
        $stmt->execute();

        $commentId = (int)$pdo->lastInsertId();

        $countStmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM comments
            WHERE post_id = :post_id
        ");
        $countStmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $countStmt->execute();

        $commentsCount = (int)$countStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'comment_id' => $commentId,
            'comments_count' => $commentsCount
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro no banco de dados'
    ]);
}