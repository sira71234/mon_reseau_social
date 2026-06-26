<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config/db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $req = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC");
        $posts = $req->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'posts' => $posts]);
        break;
   case 'POST':
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['user_id'] ?? '';
    $content = $data['content'] ?? '';
    $image = $data['image'] ?? null;

    if (empty($user_id) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
        exit();
    }

    $req = $pdo->prepare("INSERT INTO posts (user_id, content, image) VALUES (:user_id, :content, :image)");
    $res = $req->execute([
        'user_id' => $user_id,
        'content' => $content,
        'image' => $image
    ]);

    if ($res) {
        echo json_encode(['success' => true, 'message' => 'Post créé avec succès.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du post.']);
    }
    break;
    case 'DELETE':
        $id = $_GET['id'] ?? '';

        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID requis.']);
            exit();
        }

        $req = $pdo->prepare("DELETE FROM posts WHERE id = :id");
        $res = $req->execute(['id' => $id]);

        if ($res) {
            echo json_encode(['success' => true, 'message' => 'Post supprimé.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur suppression.']);
        }
    break;
    default:
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
        break;
}
