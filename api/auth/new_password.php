<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../api/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    $token = $data['token'] ?? '';
    $password = $data['password'] ?? '';
    $confirm_password = $data['confirm_password'] ?? '';

    if (empty($token) || empty($password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
        exit();
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
        exit();
    }

    $req = $pdo->prepare("SELECT * FROM users WHERE token = :token AND token_expires_at > NOW()");
    $req->execute(['token' => $token]);
    $user = $req->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Lien invalide ou expiré.']);
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $req = $pdo->prepare("UPDATE users SET password = :password, token = NULL, token_expires_at = NULL WHERE id = :id");
    $res = $req->execute(['password' => $hashedPassword, 'id' => $user['id']]);

    if ($res) {
        echo json_encode(['success' => true, 'message' => 'Mot de passe modifié avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Impossible de modifier le mot de passe.']);
    }
    exit();

} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}