<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../api/config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'] ?? ($data['user_id'] ?? '');
$post_id = $data['post_id'] ?? '';
$content = trim($data['content'] ?? '');

if (empty($user_id) || empty($post_id) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit();
}

$req = $pdo->prepare("INSERT INTO comments (user_id, post_id, content) VALUES (:user_id, :post_id, :content)");
$res = $req->execute(['user_id' => $user_id, 'post_id' => $post_id, 'content' => $content]);

if ($res) {
    echo json_encode(['success' => true, 'message' => 'Commentaire ajouté.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur ajout commentaire.']);
}
