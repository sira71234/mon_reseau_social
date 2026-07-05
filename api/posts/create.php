<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../api/config/db.php';

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$data = [];
$image = null;

if (str_contains($contentType, 'multipart/form-data')) {
    $data = $_POST;
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            echo json_encode(['success' => false, 'message' => 'Format image non autorisé.']);
            exit();
        }
        $uploadDir = __DIR__ . '/../../assets/images/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);
        $fileName = uniqid('post_', true) . '.' . $extension;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $fileName)) {
            echo json_encode(['success' => false, 'message' => "Erreur upload image."]);
            exit();
        }
        $image = 'assets/images/uploads/' . $fileName;
    }
} else {
    $data = json_decode(file_get_contents('php://input'), true) ?? [];
    $image = $data['image'] ?? null;
}

$user_id = $_SESSION['user_id'] ?? ($data['user_id'] ?? '');
$content = trim($data['content'] ?? '');

if (empty($user_id) || empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Contenu requis.']);
    exit();
}

$req = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (:user_id, :content, :image)");
$res = $req->execute(['user_id' => $user_id, 'content' => $content, 'image' => $image]);

if ($res) {
    echo json_encode(['success' => true, 'message' => 'Post créé avec succès.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur création post.']);
}
