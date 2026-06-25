<?php
// Constantes de connexion
define('DB_HOST', 'localhost');
define('DB_NAME', 'reseau_social');
define('DB_USER', 'root');
define('DB_PASS', '');
define('UPLOAD_DIR', 'assets/images/uploads/');

// Connexion PDO
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Connexion DB échouée : ' . $e->getMessage()]);
    exit;
}