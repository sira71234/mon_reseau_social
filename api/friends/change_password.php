<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../api/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'] ?? ($data['user_id'] ?? '');
$current_password = $data['current_password'] ?? '';
$new_password = $data['new_password'] ?? '';
$confirm_password = $data['confirm_password'] ?? '';

if (empty($user_id) || empty($current_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
    exit();
}

if (strlen($new_password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.']);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Les mots de passe ne correspondent pas.']);
    exit();
}

$req = $pdo->prepare("SELECT password FROM users WHERE id = :id");
$req->execute(['id' => $user_id]);
$user = $req->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($current_password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Mot de passe actuel incorrect.']);
    exit();
}

$hashed = password_hash($new_password, PASSWORD_BCRYPT);

$req = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
$res = $req->execute(['password' => $hashed, 'id' => $user_id]);

if ($res) {
    echo json_encode(['success' => true, 'message' => 'Mot de passe modifié avec succès.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors de la modification du mot de passe.']);
}
