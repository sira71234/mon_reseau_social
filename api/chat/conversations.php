<?php
header("Content-Type: application/json");
header("Acess-Control-Allow-Origin: *");
$utilisateur_id=$_GET["utilisateur_id"];
if (empty($utilisateur_id)) {
    echo json_encode(["succes"=>false, "message"=>"Identifiant manquant"]);
    exit();
};
require_once "../config/db.php";
$stmt=$pdo->prepare("SELECT DISTINCT * FROM messages WHERE expediteur_id= :utilisateur_id OR
 destinataire_id= :utilisateur_id");
$stmt->execute([
    "utilisateur_id"=>$utilisateur_id
]);
$conversations=$stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["succes"=>true, "conversations"=>$conversations]);