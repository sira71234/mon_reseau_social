<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../api/config/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = json_decode(file_get_contents('php://input'), true);
    $username = strip_tags($data['username'] ?? '');
    $surname = strip_tags($data['surname'] ?? '');
    $birthdate = $data['birthdate'] ?? '';
    $gender = $data['gender'] ?? '';
    $email = strip_tags($data['email'] ?? '');
    $num = strip_tags($data['num'] ?? '');
    $password = $data['password'] ?? '';

    if (empty($username) || empty($surname) || empty($birthdate) || empty($gender) || empty($email) || empty($num) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));
    $token_expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    try {
        $req = $pdo->prepare("INSERT INTO users (username, surname, birthdate, gender, email, num, password, token, token_expires_at, is_verified) VALUES (:username, :surname, :birthdate, :gender, :email, :num, :password, :token, :token_expires_at, 0)");
        $req->execute([
            'username' => $username,
            'surname' => $surname,
            'birthdate' => $birthdate,
            'gender' => $gender,
            'email' => $email,
            'num' => $num,
            'password' => $hashedPassword,
            'token' => $token,
            'token_expires_at' => $token_expires_at
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
            $mail->Body = "Clique sur ce lien pour vérifier votre compte : <a href='$verify_link'>$verify_link</a>";
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