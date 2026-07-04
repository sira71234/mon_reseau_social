<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../api/config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$username = trim(strip_tags($data['username'] ?? ''));
$surname = trim(strip_tags($data['surname'] ?? ''));
$email = trim(strip_tags($data['email'] ?? ''));
$password = $data['password'] ?? '';
$birthdate = $data['birthdate'] ?? '';
$gender = $data['gender'] ?? '';
$num = trim(strip_tags($data['num'] ?? ''));

if (empty($username) || empty($surname) || empty($email) || empty($password) || empty($birthdate) || empty($gender) || empty($num)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email invalide.']);
    exit();
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 8 caractères.']);
    exit();
}

$req = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
$req->execute(['email' => $email]);
if ((int) $req->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
    exit();
}

$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$token = strval(random_int(100000, 999999));
$token_expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

try {
    $req = $pdo->prepare("INSERT INTO users (username, surname, email, password, token, token_expires_at, is_active, birthdate, gender, num, created_at) VALUES (:username, :surname, :email, :password, :token, :token_expires_at, 0, :birthdate, :gender, :num, NOW())");
    $req->execute([
        'username' => $username,
        'surname' => $surname,
        'email' => $email,
        'password' => $hashedPassword,
        'token' => $token,
        'token_expires_at' => $token_expires_at,
        'birthdate' => $birthdate,
        'gender' => $gender,
        'num' => $num
    ]);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'agora7119@gmail.com';
        $mail->Password = 'dklscnivupuimhne';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->setFrom('agora7119@gmail.com', 'Agora');
        $mail->addAddress($email);
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        $mail->Subject = 'Vérification de votre compte Agora';
        $mail->Body = '<div style="font-family:Arial;max-width:600px;margin:auto;border:1px solid #ddd;border-radius:8px;overflow:hidden">' .
            '<div style="background:#1A56A0;padding:20px;text-align:center"><h1 style="color:#fff;margin:0">Agora</h1></div>' .
            '<div style="padding:30px"><p>Bonjour <strong>' . htmlspecialchars($surname . ' ' . $username) . '</strong>,</p>' .
            '<p>Votre code de vérification est :</p>' .
            '<h1 style="text-align:center;letter-spacing:10px;color:#1A56A0">' . $token . '</h1>' .
            '<p style="color:#999;font-size:12px">Ce code expire dans 10 minutes.</p></div></div>';
        $mail->send();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur envoi mail : ' . $mail->ErrorInfo]);
        exit();
    }

    echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Vérifiez votre email.', 'email' => $email]);
    exit();

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Inscription échouée : ' . $e->getMessage()]);
    }
    exit();
}