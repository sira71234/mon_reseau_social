<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../api/config/db.php';

$viewer_id = $_SESSION['user_id'] ?? ($_GET['viewer_id'] ?? '');
$profile_id = $_GET['id'] ?? $viewer_id;

if (empty($profile_id)) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
    exit();
}

$req = $pdo->prepare("SELECT id, username, surname, email, avatar, birthdate, gender, num, role, created_at FROM users WHERE id = :id AND is_active = 1");
$req->execute(['id' => $profile_id]);
$user = $req->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur introuvable.']);
    exit();
}

// Nombre de publications de cet utilisateur
$req = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE user_id = :id");
$req->execute(['id' => $profile_id]);
$user['posts_count'] = (int) $req->fetchColumn();

// Nombre d'amis de cet utilisateur
$req = $pdo->prepare("SELECT COUNT(*) FROM friends WHERE (sender_id = :id OR receiver_id = :id) AND status = 'accepted'");
$req->execute(['id' => $profile_id]);
$user['friends_count'] = (int) $req->fetchColumn();

// Statut de la relation entre le visiteur et ce profil (none / pending_sent / pending_received / friends)
$relation = 'none';
$request_id = null;

if (!empty($viewer_id) && (string) $viewer_id !== (string) $profile_id) {
    $req = $pdo->prepare("SELECT * FROM friends WHERE (sender_id = :a AND receiver_id = :b) OR (sender_id = :b AND receiver_id = :a)");
    $req->execute(['a' => $viewer_id, 'b' => $profile_id]);
    $rel = $req->fetch(PDO::FETCH_ASSOC);

    if ($rel) {
        $request_id = $rel['id'];
        if ($rel['status'] === 'accepted') {
            $relation = 'friends';
        } elseif ($rel['status'] === 'pending') {
            $relation = ((string) $rel['sender_id'] === (string) $viewer_id) ? 'pending_sent' : 'pending_received';
        }
    }
}

$user['relation'] = $relation;
$user['request_id'] = $request_id;
$user['is_self'] = (string) $viewer_id === (string) $profile_id;

echo json_encode(['success' => true, 'user' => $user]);
