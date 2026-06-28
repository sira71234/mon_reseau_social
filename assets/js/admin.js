const API_BASE_URL = './api/';

// Vérifier que l'admin est connecté
function checkAdminAuth() {
    const admin = JSON.parse(sessionStorage.getItem('rss_admin'));
    if (!admin) {
        window.location.href = 'vues/back-office/login.html';
    }
    return admin;
}

// Déconnexion admin
function adminLogout() {
    sessionStorage.removeItem('rss_admin');
    window.location.href = 'vues/back-office/login.html';
}

// Afficher un message de succès ou erreur
function showMessage(elementId, message, type = 'success') {
    const el = document.getElementById(elementId);
    if (!el) return;
    el.className =`message ${type}`;
    el.textContent = message;
    setTimeout(() => {
        el.className = 'message';
        el.textContent = '';
    }, 3000);
}

// Formater une date en français
function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('fr-FR');
}

// Confirmer une action dangereuse
function confirmer(message) {
    return confirm(message);
}