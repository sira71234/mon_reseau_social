<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../api/config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    $email = strip_tags($data['email'] ?? '');

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => "L'email est requis."]);
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
    $token_expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    $req = $pdo->prepare("UPDATE users SET token = :token, token_expires_at = :token_expires_at WHERE email = :email");
    $req->execute(['token' => $token, 'token_expires_at' => $token_expires_at, 'email' => $email]);

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
        $mail->isHTML(true);
        $mail->Subject = 'Réinitialisation de mot de passe';
        $reset_link = "http://localhost/mon_reseau_social/api/auth/new_password.php?token=" . $token;
        $mail->Body = "Clique sur ce lien : <a href='$reset_link'>$reset_link</a>";
        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Email de réinitialisation envoyé.']);
        exit();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Erreur envoi mail : ' . $mail->ErrorInfo]);
        exit();
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}