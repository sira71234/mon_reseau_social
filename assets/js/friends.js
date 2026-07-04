
function initFriends() {
    // Appelé à chaque changement de vue par index.html 
    // On regarde quelle page est chargée pour appeler la bonne fonction d'init.
    if (document.getElementById('profilInfoForm') && typeof initProfilPage === 'function') {
        initProfilPage();
    }
    if (document.getElementById('friendsList')) {
        initAmisPage();
    }
}

// AMIS

function initAmisPage() {
    loadFriendsData();
}

function loadFriendsData() {
    var user = getCurrentUser();
    if (!user) return;

    fetch('api/friends/list.php?user_id=' + user.id)
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.success) return;
            renderRequests(data.requests);
            renderFriends(data.friends);
            renderOthers(data.others);
        })
        .catch(function () {
            var el = document.getElementById('friendsList');
            if (el) el.innerHTML = '<p>Impossible de charger les amis.</p>';
        });
}

function renderRequests(requests) {
    var el = document.getElementById('friendRequests');
    if (!el) return;

    if (!requests || requests.length === 0) {
        el.innerHTML = '<p>Aucune demande en attente.</p>';
        return;
    }

    el.innerHTML = requests.map(function (r) {
        return '<div class="friend-card" style="display:flex;align-items:center;gap:10px;margin-bottom:10px">' +
            '<img src="' + escapeHtml(r.avatar || '') + '" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;background:#eee">' +
            '<strong>' + escapeHtml(r.surname + ' ' + r.username) + '</strong>' +
            '<button onclick="respondRequest(' + Number(r.request_id) + ', \'accept\')">Accepter</button>' +
            '<button onclick="respondRequest(' + Number(r.request_id) + ', \'refuse\')">Refuser</button>' +
            '</div>';
    }).join('');
}

function renderFriends(friends) {
    var el = document.getElementById('friendsList');
    if (!el) return;

    if (!friends || friends.length === 0) {
        el.innerHTML = '<p>Vous n\'avez pas encore d\'amis.</p>';
        return;
    }

    el.innerHTML = friends.map(function (f) {
        return '<div class="friend-card" style="display:flex;align-items:center;gap:10px;margin-bottom:10px">' +
            '<img src="' + escapeHtml(f.avatar || '') + '" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;background:#eee">' +
            '<strong>' + escapeHtml(f.surname + ' ' + f.username) + '</strong>' +
            '<button onclick="voirProfil(' + Number(f.id) + ')">Voir le profil</button>' +
            '</div>';
    }).join('');
}

function renderOthers(others) {
    var el = document.getElementById('usersList');
    if (!el) return;

    if (!others || others.length === 0) {
        el.innerHTML = '<p>Aucun autre utilisateur pour le moment.</p>';
        return;
    }

    el.innerHTML = others.map(function (u) {
        var actionBtn = u.pending_sent
            ? '<button disabled>Invitation envoyée</button>'
            : '<button onclick="sendRequest(' + Number(u.id) + ')">Ajouter</button>';

        return '<div class="friend-card" style="display:flex;align-items:center;gap:10px;margin-bottom:10px">' +
            '<img src="' + escapeHtml(u.avatar || '') + '" alt="" style="width:40px;height:40px;border-radius:50%;object-fit:cover;background:#eee">' +
            '<strong>' + escapeHtml(u.surname + ' ' + u.username) + '</strong>' +
            '<button onclick="voirProfil(' + Number(u.id) + ')">Voir le profil</button>' +
            actionBtn +
            '</div>';
    }).join('');
}

function voirProfil(id) {
    var user = getCurrentUser();
    if (!user) return;

    var card = document.getElementById('userProfileCard');
    if (!card) return;

    card.innerHTML = '<p>Chargement du profil...</p>';

    fetch('api/friends/profile.php?id=' + id + '&viewer_id=' + user.id)
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.success) {
                card.innerHTML = '<p>Profil introuvable.</p>';
                return;
            }

            var u = data.user;
            var actionHtml = '';

            if (u.relation === 'friends') {
                actionHtml = '<span style="color:green">Déjà ami</span>';
            } else if (u.relation === 'pending_sent') {
                actionHtml = '<span>Invitation envoyée</span>';
            } else if (u.relation === 'pending_received') {
                actionHtml = '<span>Cette personne vous a envoyé une invitation, répondez dans "Demandes reçues".</span>';
            } else {
                actionHtml = '<button onclick="sendRequest(' + Number(u.id) + ')">Ajouter en ami</button>';
            }

            card.innerHTML = '<div style="border:1px solid #ddd;padding:15px;border-radius:8px;margin-bottom:15px">' +
                '<div style="display:flex;align-items:center;gap:15px">' +
                '<img src="' + escapeHtml(u.avatar || '') + '" alt="" style="width:60px;height:60px;border-radius:50%;object-fit:cover;background:#eee">' +
                '<div><strong>' + escapeHtml(u.surname + ' ' + u.username) + '</strong><br>' +
                '<small>' + Number(u.friends_count) + ' ami(s) &middot; ' + Number(u.posts_count) + ' publication(s)</small></div>' +
                '</div>' +
                '<div style="margin-top:10px">' + actionHtml + '</div>' +
                '<button onclick="document.getElementById(\'userProfileCard\').innerHTML=\'\'">Fermer</button>' +
                '</div>';
        })
        .catch(function () {
            card.innerHTML = '<p>Impossible de charger le profil.</p>';
        });
}

function sendRequest(receiverId) {
    var user = getCurrentUser();
    if (!user) return;

    fetch('api/friends/send.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: user.id, receiver_id: receiverId })
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            var msg = document.getElementById('message');
            if (msg) msg.innerHTML = '<p style="color:' + (data.success ? 'green' : 'red') + '">' + data.message + '</p>';
            if (data.success) loadFriendsData();
        })
        .catch(function () {
            var msg = document.getElementById('message');
            if (msg) msg.innerHTML = '<p style="color:red">Impossible d\'envoyer l\'invitation.</p>';
        });
}

function respondRequest(requestId, action) {
    var user = getCurrentUser();
    if (!user) return;

    fetch('api/friends/respond.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: user.id, request_id: requestId, action: action })
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            var msg = document.getElementById('message');
            if (msg) msg.innerHTML = '<p style="color:' + (data.success ? 'green' : 'red') + '">' + data.message + '</p>';
            if (data.success) loadFriendsData();
        })
        .catch(function () {
            var msg = document.getElementById('message');
            if (msg) msg.innerHTML = '<p style="color:red">Erreur lors du traitement.</p>';
        });
}