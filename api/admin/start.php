<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();
require_once '../config/db.php';

// Vérifier que l'admin est connecté
if (!isset($_SESSION['rss_admin'])) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit;
}

try {
    // Nombre total d'utilisateurs
    $users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    // Nombre total d'articles
    $posts = $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn();

    // Nombre total de messages
    $messages = $pdo->query("SELECT COUNT(*) FROM messages")->fetchColumn();

    // Nombre total de likes
    $likes = $pdo->query("SELECT COUNT(*) FROM likes")->fetchColumn();

    // 5 derniers utilisateurs inscrits
   $stmt = $pdo->query(
    "SELECT username, surname, email, role, created_at 
     FROM users 
     ORDER BY created_at DESC 
     LIMIT 5"

    );
    $last_users = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'stats'   => [
            'users'      => $users,
            'posts'      => $posts,
            'messages'   => $messages,
            'likes'      => $likes,
            'last_users' => $last_users,
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}