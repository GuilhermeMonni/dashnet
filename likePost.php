<?php
    session_start();
    header('Content-Type: application/json');

    require 'conexao.php';

    try {
        $data = json_decode(file_get_contents('php://input'), true);

        $postId = (int)($data['post_id'] ?? 0);
        $userId = (int)($_SESSION['id'] ?? 0);

        if (!$postId || !$userId) {
            echo json_encode([
                'success' => false,
                'error' => 'Dados inválidos'
            ]);
            exit;
        }

        $check = $pdo->prepare("
            SELECT id 
            FROM likes 
            WHERE post_id = :post_id AND user_id = :user_id
        ");
        $check->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $check->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $check->execute();

        $liked = $check->fetch();

        if ($liked) {
            $delete = $pdo->prepare("
                DELETE FROM likes
                WHERE post_id = :post_id AND user_id = :user_id
            ");
            $delete->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $delete->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $delete->execute();

            $isLiked = false;
        } else {
            $insert = $pdo->prepare("
                INSERT INTO likes (post_id, user_id, created_at)
                VALUES (:post_id, :user_id, NOW())
            ");
            $insert->bindValue(':post_id', $postId, PDO::PARAM_INT);
            $insert->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $insert->execute();

            $isLiked = true;
        }

        $countStmt = $pdo->prepare("
            SELECT COUNT(*) 
            FROM likes
            WHERE post_id = :post_id
        ");
        $countStmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
        $countStmt->execute();

        $likesCount = (int)$countStmt->fetchColumn();

        echo json_encode([
            'success' => true,
            'liked' => $isLiked,
            'likes_count' => $likesCount
        ]);

    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Erro no banco de dados'
        ]);
    }