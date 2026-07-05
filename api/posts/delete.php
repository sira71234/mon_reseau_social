<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../api/config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';
$user_id = $_SESSION['user_id'] ?? ($data['user_id'] ?? '');

if (empty($id) || empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'ID requis.']);
    exit();
}

$req = $pdo->prepare("SELECT * FROM posts WHERE id = :id AND user_id = :user_id");
$req->execute(['id' => $id, 'user_id' => $user_id]);
$post = $req->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    echo json_encode(['success' => false, 'message' => 'Post introuvable ou non autorisé.']);
    exit();
}

$req = $pdo->prepare("DELETE FROM posts WHERE id = :id");
$res = $req->execute(['id' => $id]);

if ($res) {
    echo json_encode(['success' => true, 'message' => 'Post supprimé.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur suppression.']);
}
