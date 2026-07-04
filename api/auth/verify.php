<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../api/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$email = trim(strip_tags($data['email'] ?? ''));
$code = trim($data['code'] ?? '');

if (empty($email) || empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Email et code requis.']);
    exit();
}

$req = $pdo->prepare("SELECT * FROM users WHERE email = :email AND token = :token AND token_expires_at > NOW()");
$req->execute(['email' => $email, 'token' => $code]);
$user = $req->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Code invalide ou expiré.']);
    exit();
}

$req = $pdo->prepare("UPDATE users SET is_active = 1, is_verified = 1, token = NULL, token_expires_at = NULL WHERE id = :id");
$res = $req->execute(['id' => $user['id']]);

if ($res) {
    echo json_encode(['success' => true, 'message' => 'Compte vérifié avec succès ! Vous pouvez vous connecter.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Impossible de vérifier le compte.']);
}
exit();