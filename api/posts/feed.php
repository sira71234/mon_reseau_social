<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../api/config/db.php';

$req = $pdo->query("
    SELECT
        posts.*,
        users.username,
        users.surname,
        users.avatar,
        SUM(CASE WHEN likes.type = 'like' THEN 1 ELSE 0 END) AS likes_count,
        SUM(CASE WHEN likes.type = 'dislike' THEN 1 ELSE 0 END) AS dislikes_count,
        COUNT(DISTINCT comments.id) AS comments_count
    FROM posts
    JOIN users ON posts.user_id = users.id
    LEFT JOIN likes ON likes.post_id = posts.id
    LEFT JOIN comments ON comments.post_id = posts.id
    GROUP BY posts.id
    ORDER BY posts.created_at DESC
");
$posts = $req->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(['success' => true, 'posts' => $posts]);
