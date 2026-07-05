<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../api/config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'] ?? ($data['user_id'] ?? '');
$request_id = $data['request_id'] ?? '';
$action = $data['action'] ?? '';

if (empty($user_id) || empty($request_id) || !in_array($action, ['accept', 'refuse'], true)) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit();
}

$req = $pdo->prepare("SELECT * FROM friends WHERE id = :id");
$req->execute(['id' => $request_id]);
$friendRequest = $req->fetch(PDO::FETCH_ASSOC);

// Seul le destinataire de la demande peut y répondre
if (!$friendRequest || (string) $friendRequest['receiver_id'] !== (string) $user_id) {
    echo json_encode(['success' => false, 'message' => 'Demande introuvable.']);
    exit();
}

if ($friendRequest['status'] !== 'pending') {
    echo json_encode(['success' => false, 'message' => 'Cette demande a déjà été traitée.']);
    exit();
}

$newStatus = $action === 'accept' ? 'accepted' : 'refused';

$req = $pdo->prepare("UPDATE friends SET status = :status WHERE id = :id");
$res = $req->execute(['status' => $newStatus, 'id' => $request_id]);

if ($res) {
    $message = $action === 'accept' ? 'Demande acceptée.' : 'Demande refusée.';
    echo json_encode(['success' => true, 'message' => $message]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erreur lors du traitement de la demande.']);
}
