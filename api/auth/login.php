<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../api/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    $username = strip_tags($data['username'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
        exit();
    }

    $req = $pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
    $req->execute(['username' => $username, 'email' => $username]);
    $user = $req->fetch(PDO::FETCH_ASSOC);

    if ($user && !$user['is_verified']) {
        echo json_encode(['success' => false, 'message' => 'Compte non vérifié. Vérifiez votre email.']);
        exit();
    } elseif ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        echo json_encode(['success' => true, 'message' => 'Connexion réussie.', 'user' => ['id' => $user['id'], 'username' => $user['username']]]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects.']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}