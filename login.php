    <?php

    session_start();

    require_once __DIR__ . '/api/config/db.php';
    $error = "";
    $succes= "";

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $username = strip_tags($_POST['username']);
        $password = $_POST['password'];
        if (empty($username) || empty($password)) {
            $error = "Erreur : Tous les champs sont requis.";
        } else {
            $req = $bdd->prepare("SELECT * FROM users WHERE username = :username OR email = :username");
            $req->execute(['username' => $username]);
            $user = $req->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Erreur : Nom d'utilisateur ou mot de passe incorrect.";
            }
        }
    }

    ?>

    <!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connexion</title>
    </head>
    <body>
        <div class="container">
            <h1>Connexion</h1>
            <p>Bienvenue sur Sora ! Veuillez remplir le formulaire ci-dessous pour vous connecter.</p>
            <?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
            <form action="login.php" method="POST">
                <label for="username">Nom d'utilisateur ou Email :</label>
                <input type="text" id="username" name="username" required><br><br>

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required><br><br>

                <input class="btn btn-primary" type="submit" value="Se connecter">
            </form>
            <a href="register.php">Je n'ai pas de compte</a>
            <a href="resset_password.php">Mot de passe oublié</a>
        </div>
    </body>
    </html> 