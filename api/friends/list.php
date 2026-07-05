<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../api/config/db.php';

$user_id = $_SESSION['user_id'] ?? ($_GET['user_id'] ?? '');

if (empty($user_id)) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit();
}

// Toutes les relations (amitiés / invitations) impliquant l'utilisateur connecté
$req = $pdo->prepare("SELECT * FROM friends WHERE sender_id = :uid OR receiver_id = :uid");
$req->execute(['uid' => $user_id]);
$relations = $req->fetchAll(PDO::FETCH_ASSOC);

// On associe chaque relation à l'id de "l'autre" utilisateur pour la retrouver facilement
$relationMap = [];
foreach ($relations as $rel) {
    $isSender = (string) $rel['sender_id'] === (string) $user_id;
    $otherId = $isSender ? $rel['receiver_id'] : $rel['sender_id'];
    $relationMap[$otherId] = [
        'request_id' => $rel['id'],
        'status' => $rel['status'],
        'is_sender' => $isSender
    ];
}

// Tous les autres utilisateurs actifs de la plateforme
$req = $pdo->prepare("SELECT id, username, surname, avatar FROM users WHERE id != :uid AND is_active = 1 ORDER BY username");
$req->execute(['uid' => $user_id]);
$allUsers = $req->fetchAll(PDO::FETCH_ASSOC);

$requests = [];   // demandes reçues en attente de réponse
$friendsList = []; // amis déjà acceptés
$others = [];      // reste des utilisateurs (avec ou sans invitation envoyée)

foreach ($allUsers as $u) {
    $rel = $relationMap[$u['id']] ?? null;

    if ($rel && $rel['status'] === 'accepted') {
        $friendsList[] = $u;
    } elseif ($rel && $rel['status'] === 'pending' && !$rel['is_sender']) {
        // C'est cet utilisateur qui nous a envoyé une demande
        $u['request_id'] = $rel['request_id'];
        $requests[] = $u;
    } elseif ($rel && $rel['status'] === 'pending' && $rel['is_sender']) {
        // On lui a déjà envoyé une demande, en attente de sa réponse
        $u['pending_sent'] = true;
        $others[] = $u;
    } else {
        $u['pending_sent'] = false;
        $others[] = $u;
    }
}

echo json_encode([
    'success' => true,
    'requests' => $requests,
    'friends' => $friendsList,
    'others' => $others
]);
