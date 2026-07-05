<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
$expediteur_id = $_GET["expediteur_id"];
$destinataire_id = $_GET["destinataire_id"];
if (empty($expediteur_id) || (empty($destinataire_id))) {
    echo json_encode(["succes"=> false, "message" => "Identifiants manquants"]);
    exit();
};
require_once "../config/db.php";
$mes = $pdo->prepare("SELECT * FROM messages WHERE(expediteur_id= :expediteur_id, destinataire_id= :destinataire_id)
 OR (expediteur_id= :destinataire_id, destinataire_id= :expediteur_id) ORDER BY date_envoi ASC");

$mes->execute([
    "expediteur_id" =>$expediteur_id,
    "destinataire_id" =>$destinataire_id
]);
$messages = $mes->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["succes" => true, "messages" => $messages]);

