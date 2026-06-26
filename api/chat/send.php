<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
$donnees = json_decode(file_get_contents("php://input"), true);
if (empty($donnees["expediteur_id"]) || empty($donnees["destinataire_id"]) || empty($donnees["contenu"])) {
    echo json_encode(["succes" => false, "message" => "Données manquantes"]);
    exit();
};
require_once "../config/db_php";
$msg = $pdo->prepare("INSERT INTO messages(expediteur_id, destinataire_id, contenu, date_envoi VALUES 
(:expediteur_id, :destinataire_id, :contenu, NOW())");
$msg ->execute([
    "expediteur_id" => $donnees["expediteur_id"], 
    "destinataire_id" => $donnees["destinataire_id"], 
    "contenu" => $donnees["contenu"] 
]);
echo json_encode(["succes" => true, "message" => "Message envoyé avec succès"]);