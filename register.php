<?php

session_start();

require_once __DIR__ . '/api/config/db.php';

$error = "";
$succes= "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = strip_tags($_POST['username']);
    $surname = strip_tags($_POST['surname']);
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $email = strip_tags($_POST['email']);
    $num = strip_tags($_POST['num']);
    $password = $_POST['password'];
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(32));

    if (empty($username) || empty($surname) || empty($birthdate) || empty($gender) || empty($email) || empty($num) || empty($password)) {
        $error = "Erreur : Tous les champs sont requis.";
    } else {
        // requête PDO ici
    }
    
    if (!isset($_POST['username']) || !isset($_POST['surname']) || !isset($_POST['birthdate']) || !isset($_POST['gender']) || !isset($_POST['email']) || !isset($_POST['num']) || !isset($_POST['password'])) {
        $error = "Erreur : Tous les champs sont requis.";
    } else {
        if (empty($username) || empty($surname) || empty($birthdate) || empty($gender) || empty($email) || empty($num) || empty($password)) {
            $error = "Erreur : Tous les champs sont requis.";
        } else {
        
            $req = $bdd->prepare("INSERT INTO users (username, surname, birthdate, gender, email, num, password, token) VALUES (:username, :surname, :birthdate, :gender, :email, :num, :password, :token)");
            $res = $req->execute([
                'username' => $username,
                'surname' => $surname,
                'birthdate' => $birthdate,
                'gender' => $gender,
                'email' => $email,
                'num' => $num,
                'password' => $hashedPassword,
                'token' => $token
            ]);

            if ($res) {
                $succes = "Votre inscription a réussi.";
                $_SESSION['succes'] = $succes;
                header("Location: login.php");
                exit();
            } else {
                $error = "Erreur : Votre inscription a échoué.";
            }
        } 
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>inscription</title>
</head>
<body>
    <div class="container">
        <h1>Inscription</h1>
        <p>Bienvenue sur Sora ! Veuillez remplir le formulaire ci-dessous pour vous inscrire.</p>
        <form action="register.php" method="POST">
            <label for="name">Prénom de l'utilisateur :</label>
            <input type="text" id="name" name="username" required><br><br>

            <label for="surname">Nom d'utilisateur :</label>
            <input type="text" id="surname" name="surname" required><br><br>

            <label for="birthdate">Date de naissance :</label>
            <input type="date" id="birthdate" name="birthdate" required><br><br>

            <label for="gender">Genre :</label>
            <input type="radio" id="gender_male" name="gender" value="male" required> Homme
            <input type="radio" id="gender_female" name="gender" value="female"> Femme
            <input type="radio" id="gender_not_specified" name="gender" value="not specified"> Ne pas spécifier
            <input type="radio" id="gender_other" name="gender" value="other"> Autre
            <br><br>

            <label for="email">Email :</label>
            <input type="email" id="email" name="email" required><br><br>

            <label for="num">Numéro de téléphone :</label>
            <input type="text" id="num" name="num" required><br><br>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required><br><br>

            <input class="btn btn-primary" type="submit" value="S'inscrire">
        </form>
        <a href="login.php">J'ai déjà un compte</a>
    </div>
</body>
</html>