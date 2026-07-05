<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../api/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$data = [];

if (str_contains($contentType, 'multipart/form-data')) {
    $data = $_POST;
} else {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
}

$user_id = $_SESSION['user_id'] ?? ($data['user_id'] ?? '');
$username = trim(strip_tags($data['username'] ?? ''));
$surname = trim(strip_tags($data['surname'] ?? ''));
$birthdate = $data['birthdate'] ?? '';
$gender = $data['gender'] ?? '';
$num = trim(strip_tags($data['num'] ?? ''));

if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit();
}

if (empty($username) || empty($surname)) {
    echo json_encode(['success' => false, 'message' => 'Le nom et le prénom sont requis.']);
    exit();
}

// Gestion de la photo de profil (optionnelle)
$avatarPath = null;
if (!empty($_FILES['avatar']['name']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (!in_array($extension, $allowedExtensions, true)) {
        echo json_encode(['success' => false, 'message' => 'Format image non autorisé.']);
        exit();
    }

    $uploadDir = __DIR__ . '/../../assets/images/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0775, true);
    }

    $fileName = uniqid('avatar_', true) . '.' . $extension;

    if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $fileName)) {
        echo json_encode(['success' => false, 'message' => 'Erreur upload de la photo.']);
        exit();
    }

    $avatarPath = 'assets/images/uploads/' . $fileName;
}

if ($avatarPath) {
    $req = $pdo->prepare("UPDATE users SET username = :username, surname = :surname, birthdate = :birthdate, gender = :gender, num = :num, avatar = :avatar WHERE id = :id");
    $res = $req->execute([
        'username' => $username,
        'surname' => $surname,
        'birthdate' => $birthdate,
        'gender' => $gender,
        'num' => $num,
        'avatar' => $avatarPath,
        'id' => $user_id
    ]);
} else {
    $req = $pdo->prepare("UPDATE users SET username = :username, surname = :surname, birthdate = :birthdate, gender = :gender, num = :num WHERE id = :id");
    $res = $req->execute([
        'username' => $username,
        'surname' => $surname,
        'birthdate' => $birthdate,
        'gender' => $gender,
        'num' => $num,
        'id' => $user_id
    ]);
}

if ($res) {
    $req = $pdo->prepare("SELECT id, username, surname, email, avatar, role FROM users WHERE id = :id");
    $req->execute(['id' => $user_id]);
    $user = $req->fetch(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'message' => 'Profil mis à jour.', 'user' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur mise à jour du profil.']);
}
