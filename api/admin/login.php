<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

session_start();
require_once '../config/db.php';

// Récupération des données
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['email']) || empty($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Champs manquants.']);
    exit;
}

$email    = trim($data['email']);
$password = trim($data['password']);

try {
    // Vérifier que l'utilisateur est admin ou modérateur
    $stmt = $pdo->prepare(
        "SELECT * FROM users 
         WHERE email = ? 
         AND is_active = 1 
         AND role IN ('admin', 'moderateur')"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Accès refusé ou compte inexistant.']);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect.']);
        exit;
    }

    // Stocker en session serveur
    $_SESSION['rss_admin'] = [
        'id'     => $user['id'],
        'nom'    => $user['nom'],
        'prenom' => $user['prenom'],
        'email'  => $user['email'],
        'role'   => $user['role'],
    ];

    // Réponse JSON (jamais le password !)
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie.',
        'user'    => [
            'id'     => $user['id'],
            'nom'    => $user['nom'],
            'prenom' => $user['prenom'],
            'email'  => $user['email'],
            'role'   => $user['role'],
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur.']);
}