<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../api/config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    $email = strip_tags($data['email'] ?? '');
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
        $_SESSION['rss_user'] = [
            'id' => $user['id'],
            'nom' => $user['nom'],
            'prenom' => $user['prenom'],
            'email' => $user['email'],
            'avatar' => $user['avatar']
        ];
        echo json_encode(['success' => true, 'message' => 'Connexion réussie.', 'user' => $_SESSION['rss_user']]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => 'Identifiants incorrects.']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}
