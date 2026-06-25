<?php

    session_start();

    require_once __DIR__ . '/api/config/db.php';

    $error = "";
    $succes= "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $token = $_POST['token'];

        if (empty($password) || empty($confirm_password)) {
            $error = "Erreur : Tous les champs sont requis.";
        } elseif ($password !== $confirm_password) {
            $error = "Erreur : Les mots de passe ne correspondent pas.";
        } else {
            $req = $bdd->prepare("SELECT * FROM users WHERE token = :token AND token_expires_at > NOW()");
            $req->execute(['token' => $token]);
            $user = $req->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $req = $bdd->prepare("UPDATE users SET password = :password, token = NULL, token_expires_at = NULL WHERE id = :id");
                $res = $req->execute(['password' => $hashedPassword, 'id' => $user['id']]);

                if ($res) {
                    $succes = "Votre mot de passe a été modifié avec succès.";
                    $_SESSION['succes'] = $succes;
                    header("Location: login.php");
                    exit();
                } else {
                    $error = "Erreur : Impossible de modifier le mot de passe.";
                }
            } else {
                $error = "Erreur : Le lien de réinitialisation est invalide ou a expiré.";
            }
        }
    }

?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password</title>
</head>
<body>
    <div class="container">
        <h1>New Password</h1>
        <p>Bienvenue sur Sora ! Veuillez remplir le formulaire ci-dessous pour modifier votre mot de passe.</p>
        <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form action="new_password.php" method="POST">
            <label for="password"> Nouveau mot de passe :</label>
            <input type="password" id="password" name="password" required><br><br>

            <label for="confirm_password">Confirmer le mot de passe :</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br><br>
            
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">

            <input class="btn btn-primary" type="submit" value="Modifier le mot de passe">
        </form>
    </div>
</body>
</html> 