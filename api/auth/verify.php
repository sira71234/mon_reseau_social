<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../api/config/db.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Token invalide.']);
    exit();
}

$req = $pdo->prepare("SELECT * FROM users WHERE token = :token AND token_expires_at > NOW()");
$req->execute(['token' => $token]);
$user = $req->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Lien invalide ou expiré.']);
    exit();
}

$req = $pdo->prepare("UPDATE users SET is_verified = 1, token = NULL, token_expires_at = NULL WHERE id = :id");
$res = $req->execute(['id' => $user['id']]);

if ($res) {
    echo json_encode(['success' => true, 'message' => 'Compte vérifié avec succès.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Impossible de vérifier le compte.']);
}
exit();