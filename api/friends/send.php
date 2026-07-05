<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../api/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$sender_id = $_SESSION['user_id'] ?? ($data['user_id'] ?? '');
$receiver_id = $data['receiver_id'] ?? '';

if (empty($sender_id) || empty($receiver_id)) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit();
}

if ((string) $sender_id === (string) $receiver_id) {
    echo json_encode(['success' => false, 'message' => 'Action impossible.']);
    exit();
}

// Vérifie si une relation existe déjà entre les deux utilisateurs (dans un sens ou dans l'autre)
$req = $pdo->prepare("SELECT * FROM friends WHERE (sender_id = :a AND receiver_id = :b) OR (sender_id = :b AND receiver_id = :a)");
$req->execute(['a' => $sender_id, 'b' => $receiver_id]);
$existing = $req->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    if ($existing['status'] === 'refused') {
        // La demande précédente avait été refusée : on peut relancer une nouvelle invitation
        $req = $pdo->prepare("UPDATE friends SET sender_id = :sender_id, receiver_id = :receiver_id, status = 'pending', created_at = NOW() WHERE id = :id");
        $res = $req->execute(['sender_id' => $sender_id, 'receiver_id' => $receiver_id, 'id' => $existing['id']]);
        echo json_encode(['success' => (bool) $res, 'message' => $res ? 'Invitation envoyée.' : 'Erreur envoi invitation.']);
        exit();
    }

    echo json_encode(['success' => false, 'message' => 'Une relation existe déjà avec cet utilisateur.']);
    exit();
}

$req = $pdo->prepare("INSERT INTO friends (sender_id, receiver_id, status, created_at) VALUES (:sender_id, :receiver_id, 'pending', NOW())");
$res = $req->execute(['sender_id' => $sender_id, 'receiver_id' => $receiver_id]);

echo json_encode(['success' => (bool) $res, 'message' => $res ? 'Invitation envoyée.' : 'Erreur envoi invitation.']);
