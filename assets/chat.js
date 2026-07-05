/*C'est la page de discussion*/
let intervalChat = null;

function initChat(){
const zoneMessages=document.getElementById("zone-messages");
const inputMessage=document.getElementById("input-message");
const btnEnvoyer=document.getElementById("btn-envoyer");
const sidebar=document.getElementById("sidebar");
let conversationsActive =null;
function chargerConversations(){
    fetch("../../api/chat/conversations.php?utilisateur_id=1")
    .then(reponse => reponse.json())
    .then(donnes => {donnes.conversations.forEach(conversations=>{
        sidebar.innerHTML += `<div class="conversations">${conversations.expediteur_id}</div>`
    })})
};
function chargerMessages(destinataire_id){
    fetch(`../../api/chat/get_messages.php?utilisateur_id=1&destinataire_id=${destinataire_id}`)
    .then(reponse=> reponse.json())
    .then(donnes => {donnes.messages.forEach(messages=>{
        zoneMessages.innerHTML += `<div class="${messages.expediteur_id == 1 ? 
            "message-envoye": "message-recu"}">${messages.contenu}</div>`
    })})
};
function envoyerMessages(){
    fetch("../../api/chat/send.php?destinataire_id=1",{
        method: "POST", 
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({
            expediteur_id:1,
            destinataire_id:1,
            contenu: inputMessage.value
        })})
    .then(reponse => reponse.json())
    .then(donnees => {
    inputMessage.value = "";
    chargerMessages(1);
})};
chargerConversations();
chargerMessages(1);

intervalChat = setInterval(()=>chargerMessages(1),3000);
btnEnvoyer.addEventListener("click", envoyerMessages);
}

function arreterChat(){
    if (intervalChat) {
        clearInterval(intervalChat);
        intervalChat = null;
    }
}