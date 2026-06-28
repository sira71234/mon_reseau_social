<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../api/config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'] ?? ($data['user_id'] ?? '');
$post_id = $data['post_id'] ?? '';
$type = $data['type'] ?? 'like';

if (empty($user_id) || empty($post_id)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit();
}

if (!in_array($type, ['like', 'dislike'], true)) {
    echo json_encode(['success' => false, 'message' => 'Type invalide.']);
    exit();
}

$req = $pdo->prepare("SELECT * FROM likes WHERE user_id = :user_id AND post_id = :post_id");
$req->execute(['user_id' => $user_id, 'post_id' => $post_id]);
$like = $req->fetch(PDO::FETCH_ASSOC);

if ($like) {
    if ($like['type'] === $type) {
        $req = $pdo->prepare("DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id");
        $req->execute(['user_id' => $user_id, 'post_id' => $post_id]);
        echo json_encode(['success' => true, 'action' => 'unliked']);
    } else {
        $req = $pdo->prepare("UPDATE likes SET type = :type WHERE user_id = :user_id AND post_id = :post_id");
        $req->execute(['type' => $type, 'user_id' => $user_id, 'post_id' => $post_id]);
        echo json_encode(['success' => true, 'action' => 'changed']);
    }
} else {
    $req = $pdo->prepare("INSERT INTO likes (user_id, post_id, type) VALUES (:user_id, :post_id, :type)");
    $req->execute(['user_id' => $user_id, 'post_id' => $post_id, 'type' => $type]);
    echo json_encode(['success' => true, 'action' => 'liked']);
}
