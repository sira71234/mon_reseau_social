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
$email = trim(strip_tags($data['email'] ?? ''));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email invalide.']);
    exit();
}

$req = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$req->execute(['email' => $email]);
$user = $req->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'Aucun compte trouvé avec cet email.']);
    exit();
}

$token = bin2hex(random_bytes(32));
$expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

$req = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at, used) VALUES (:email, :token, :expires_at, 0)");
$req->execute(['email' => $email, 'token' => $token, 'expires_at' => $expires_at]);

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
    $mail->Subject = 'Réinitialisation de mot de passe';
    $reset_link = "http://localhost/mon_reseau_social/index.html?token=" . $token;
    $mail->Body = '<div style="font-family:Arial;max-width:600px;margin:auto;border:1px solid #ddd;border-radius:8px;overflow:hidden">' .
        '<div style="background:#1A56A0;padding:20px;text-align:center"><h1 style="color:#fff;margin:0">Agora</h1></div>' .
        '<div style="padding:30px"><p>Bonjour,</p><p>Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>' .
        '<a href="' . $reset_link . '" style="background:#1A56A0;color:#fff;padding:12px 24px;border-radius:4px;text-decoration:none">Réinitialiser mon mot de passe</a>' .
        '<p style="margin-top:20px;color:#999;font-size:12px">Ce lien expire dans 1 heure.</p></div></div>';
    $mail->AltBody = "Lien de réinitialisation : " . $reset_link;
    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Email de réinitialisation envoyé.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur envoi mail : ' . $mail->ErrorInfo]);
}
