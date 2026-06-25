<?php

    session_start();

    require_once 'db.php';

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