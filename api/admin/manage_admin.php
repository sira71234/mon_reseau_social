<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');

session_start();
require_once '../config/db.php';

// Vérifier que c'est un ADMIN (pas modérateur)
if (!isset($_SESSION['rss_admin']) || $_SESSION['rss_admin']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Accès réservé aux admins.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET — Liste admins et modérateurs
if ($method === 'GET') {
    try {
        $stmt = $pdo->query(
            "SELECT id, nom, prenom, email, role, created_at 
             FROM users 
             WHERE role IN ('admin', 'moderateur')
             ORDER BY role, created_at DESC"
        );
        $admins = $stmt->fetchAll();

        echo json_encode(['success' => true, 'admins' => $admins]);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }
}

// POST — Promouvoir un utilisateur en admin ou modérateur
if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id']) || empty($data['role'])) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
        exit;
    }

    if (!in_array($data['role'], ['admin', 'moderateur'])) {
        echo json_encode(['success' => false, 'message' => 'Rôle invalide.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$data['role'], $data['id']]);

        echo json_encode(['success' => true, 'message' => 'Rôle mis à jour.']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }
}

// DELETE — Rétrograder en utilisateur simple
if ($method === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['id'])) {
        echo json_encode(['success' => false, 'message' => 'ID manquant.']);
        exit;
    }

    // Empêcher de se rétrograder soi-même
    if ($data['id'] == $_SESSION['rss_admin']['id']) {
        echo json_encode(['success' => false, 'message' => 'Impossible de vous rétrograder vous-même.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
        $stmt->execute([$data['id']]);

        echo json_encode(['success' => true, 'message' => 'Utilisateur rétrogradé.']);

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
    }
}