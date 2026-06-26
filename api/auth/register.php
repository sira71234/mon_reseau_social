<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../api/config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    $nom = trim(strip_tags($data['nom'] ?? ''));
    $prenom = trim(strip_tags($data['prenom'] ?? ''));
    $email = strip_tags($data['email'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
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
    $token = bin2hex(random_bytes(32));

    try {
        $req = $pdo->prepare("INSERT INTO users (nom, prenom, email, password, token, is_active, created_at) VALUES (:nom, :prenom, :email, :password, :token, 0, NOW())");
        $req->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'password' => $hashedPassword,
            'token' => $token
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
            $mail->isHTML(true);
            $mail->Subject = 'Vérification de votre compte';
            $verify_link = "http://localhost/mon_reseau_social/api/auth/verify.php?token=" . $token;
            $mail->Body = '<div style="font-family:Arial;max-width:600px;margin:auto;border:1px solid #ddd;border-radius:8px;overflow:hidden">' .
                '<div style="background:#1A56A0;padding:20px;text-align:center"><h1 style="color:#fff;margin:0">Réseau Social</h1></div>' .
                '<div style="padding:30px"><p>Bonjour <strong>' . htmlspecialchars($nom) . '</strong>,</p>' .
                '<p>Cliquez sur le bouton ci-dessous pour activer votre compte :</p>' .
                '<a href="' . $verify_link . '" style="background:#1A56A0;color:#fff;padding:12px 24px;border-radius:4px;text-decoration:none">Activer mon compte</a></div></div>';
            $mail->send();
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur envoi mail : ' . $mail->ErrorInfo]);
            exit();
        }

        echo json_encode(['success' => true, 'message' => 'Inscription réussie ! Vérifiez votre email.']);
        exit();

    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Inscription échouée.']);
        }
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
    exit();
}
