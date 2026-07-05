# Agora - Réseau Social Web

Projet PHP, MySQL et AJAX pour l'examen final L2 IRT / ESGIS.

## Membres

- Siracide : authentification, API posts, point d'entree SPA, README
- Ya : profil et amis
- Veronique : chat et UI
- Estrasse : back-office et base de donnees

## Installation locale

1. Placer le projet dans `htdocs/mon_reseau_social`.
2. Demarrer Apache et MySQL avec XAMPP.
3. Importer `api/config/database.sql` dans MySQL.
4. Verifier les constantes dans `api/config/db.php`.
5. Ouvrir `http://localhost/mon_reseau_social/`.

## Configuration mail

Les emails d'activation et de reinitialisation utilisent PHPMailer avec SMTP Gmail dans :

- `api/auth/register.php`
- `api/auth/forgot_password.php`

Il faut un mot de passe d'application Gmail valide pour que l'envoi fonctionne.

## Module Siracide

Authentification :

- Inscription avec validation, hash du mot de passe et email HTML d'activation.
- Verification de compte par token.
- Connexion par email avec `sessionStorage` sur la cle `rss_user`.
- Deconnexion.
- Mot de passe oublie avec email HTML et token stocke dans `password_resets`.
- Reinitialisation du mot de passe par token.

Posts :

- Creation de post texte avec image optionnelle.
- Recuperation du fil avec informations auteur et compteurs.
- Like / dislike.
- Commentaire AJAX.
- Suppression par auteur.

## Identifiants de test

Creer un compte via l'interface, activer le compte avec le lien recu par email, puis se connecter.

## Routes principales

- `api/auth/register.php`
- `api/auth/login.php`
- `api/auth/logout.php`
- `api/auth/forgot_password.php`
- `api/auth/reset_password.php`
- `api/posts/create.php`
- `api/posts/feed.php`
- `api/posts/like.php`
- `api/posts/comment.php`
- `api/posts/delete.php`

## Notes

- Les requetes SQL du module Siracide utilisent PDO et des requetes preparees.
- La navigation est chargee dynamiquement depuis `index.html`.
- Les autres modules doivent completer leurs vues et scripts selon la roadmap.
