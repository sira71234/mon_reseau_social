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

    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.']);
        exit();
    }

    $req = $pdo->prepare("SELECT * FROM password_resets WHERE token = :token AND expires_at > NOW() AND used = 0");
    $req->execute(['token' => $token]);
    $reset = $req->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        echo json_encode(['success' => false, 'message' => 'Lien invalide ou expiré.']);
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $req = $pdo->prepare("UPDATE users SET password = :password WHERE email = :email");
    $updated = $req->execute(['password' => $hashedPassword, 'email' => $reset['email']]);

    if ($updated) {
        $req = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE id = :id");
        $req->execute(['id' => $reset['id']]);
        echo json_encode(['success' => true, 'message' => 'Mot de passe modifié avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Impossible de modifier le mot de passe.']);
    }
    exit();

} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}
