<?php
require_once 'api/config/db.php';

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(['admin@test.com']);
$user = $stmt->fetch();

echo "Utilisateur trouvé : ";
var_dump($user);
echo "<br><br>";
echo "Test password : ";
var_dump(password_verify('password', $user['password']));