<?php
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

// GET — Liste tous les utilisateurs
if ($method === 'GET') {
    try {
        $stmt = $pdo->query(
            "SELECT id, nom, prenom, email, role, is_active, created_at 
             FROM users 
             ORDER BY created_at DESC"
        );
        $users = $stmt->fetchAll();

        echo json_encode(['success' => true, 'users' => $users]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }
}

// DELETE — Supprimer un utilisateur
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID manquant.']);
        exit;
    }

    // Empêcher de supprimer un admin
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$data['id']]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
        exit;
    }

    if ($user['role'] === 'admin') {
        echo json_encode(['success' => false, 'message' => 'Impossible de supprimer un admin.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$data['id']]);

        echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé.']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }
}