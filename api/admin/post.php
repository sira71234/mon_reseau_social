-<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, DELETE');

session_start();
require_once '../config/db.php';

// Vérifier que l'admin est connecté
if (!isset($_SESSION['rss_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET — Liste tous les articles
if ($method === 'GET') {
    try {
        $stmt = $pdo->query(
            "SELECT p.id, p.content, p.image, p.created_at,
                    u.nom, u.prenom, u.email
             FROM posts p
             JOIN users u ON p.user_id = u.id
             ORDER BY p.created_at DESC"
        );
        $posts = $stmt->fetchAll();

        echo json_encode(['success' => true, 'posts' => $posts]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }
}

// DELETE — Supprimer un article
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID manquant.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$data['id']]);

        echo json_encode(['success' => true, 'message' => 'Article supprimé.']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }
}