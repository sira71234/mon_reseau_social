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
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
    exit();
}

$req = $pdo->prepare("SELECT * FROM users WHERE email = :email AND is_active = 1");
$req->execute(['email' => $email]);
$user = $req->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $userData = [
        'id' => $user['id'],
        'username' => $user['username'],
        'surname' => $user['surname'],
        'email' => $user['email'],
        'avatar' => $user['avatar'] ?? null,
        'role' => $user['role'] ?? 'user'
    ];
    $_SESSION['rss_user'] = $userData;
    echo json_encode(['success' => true, 'message' => 'Connexion réussie.', 'user' => $userData]);
    exit();
} else {
    echo json_encode(['success' => false, 'message' => 'Identifiants incorrects ou compte non activé.']);
    exit();
}
