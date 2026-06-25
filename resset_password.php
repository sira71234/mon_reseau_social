<?php

    session_start();

    require_once __DIR__ . '/api/config/db.php';
    require_once 'vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $error = "";
    $succes= "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $email = strip_tags($_POST['email']);
        if (empty($email)) {
            $error = "Erreur : L'email est requis.";
        } else {
            $req = $bdd->prepare("SELECT * FROM users WHERE email = :email");
            $req->execute(['email' => $email]);
            $user = $req->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $token_expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                $req = $bdd->prepare("UPDATE users SET token = :token, token_expires_at = :token_expires_at WHERE email = :email");
                $req->execute(['email'=> $email, 'token' => $token, 'token_expires_at' => $token_expires_at]);
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'agora@gmail.com';
                    $mail->Password = '';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                } catch (Exception $e) {
                    $error = "Erreur d'envoi : " . $mail->ErrorInfo;
                }
                $mail->setFrom('monreseausocial@gmail.com', 'Mon Réseau Social');
                $mail->addAddress($email);
                $succes = "Un email de réinitialisation a été envoyé à votre adresse.";
            } else {
                $error = "Erreur : Aucun compte trouvé avec cet email.";
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body>
    <div class="container">
            <h1>Reset Password</h1>
            <p>Veuillez remplir le formulaire ci-dessous pour réinitialiser votre mot de passe.</p>
            <form action="reset_password.php" method="POST">
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" required><br><br>

                <input class="btn btn-primary" type="submit" value="Réinitialiser le mot de passe">
            </form>
        </div>
</body>
</html>