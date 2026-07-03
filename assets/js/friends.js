function initFriends() {
    if (document.getElementById('profilInfoForm')) {
        initProfilPage();
    }
    if (document.getElementById('friendsList')) {
        initAmisPage();
    }
}

// ================= PAGE "MON PROFIL" =================

function initProfilPage() {
    var user = getCurrentUser();
    if (!user) return;

    loadProfilData(user.id, true);

    var infoForm = document.getElementById('profilInfoForm');
    if (infoForm) {
        infoForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitProfilInfo(user.id);
        });
    }

    var passwordForm = document.getElementById('passwordForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function (e) {
            e.preventDefault();
            submitPasswordChange(user.id);
        });
    }
}

function defaultAvatar() {
    // Petit avatar gris par défaut, généré directement en JS (pas de fichier requis)
    var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100">' +
        '<rect width="100" height="100" fill="#cccccc"/>' +
        '<text x="50" y="62" font-size="40" text-anchor="middle" fill="#ffffff">?</text></svg>';
    return 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
}

function loadProfilData(userId, fillForm) {
    fetch('api/friends/profile.php?id=' + userId + '&viewer_id=' + userId)
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.success) return;
            var u = data.user;

            var avatarEl = document.getElementById('profilAvatar');
            if (avatarEl) avatarEl.src = u.avatar ? u.avatar : defaultAvatar();

            var nomEl = document.getElementById('profilNomComplet');
            if (nomEl) nomEl.textContent = (u.surname || '') + ' ' + (u.username || '');

            var emailEl = document.getElementById('profilEmail');
            if (emailEl) emailEl.textContent = u.email || '';

            var nbAmisEl = document.getElementById('profilNbAmis');
            if (nbAmisEl) nbAmisEl.textContent = u.friends_count;

            var nbPostsEl = document.getElementById('profilNbPosts');
            if (nbPostsEl) nbPostsEl.textContent = u.posts_count;

            if (fillForm) {
                var usernameInput = document.getElementById('editUsername');
                var surnameInput = document.getElementById('editSurname');
                var birthdateInput = document.getElementById('editBirthdate');
                var numInput = document.getElementById('editNum');

                if (usernameInput) usernameInput.value = u.username || '';
                if (surnameInput) surnameInput.value = u.surname || '';
                if (birthdateInput) birthdateInput.value = u.birthdate || '';
                if (numInput) numInput.value = u.num || '';

                var genderRadio = document.querySelector('input[name="editGender"][value="' + u.gender + '"]');
                if (genderRadio) genderRadio.checked = true;
            }
        })
        .catch(function () {});
}

function submitProfilInfo(userId) {
    var msg = document.getElementById('profilMessage');
    var genderChecked = document.querySelector('input[name="editGender"]:checked');
    var avatarInput = document.getElementById('avatarInput');

    var formData = new FormData();
    formData.append('user_id', userId);
    formData.append('username', document.getElementById('editUsername').value.trim());
    formData.append('surname', document.getElementById('editSurname').value.trim());
    formData.append('birthdate', document.getElementById('editBirthdate').value);
    formData.append('gender', genderChecked ? genderChecked.value : '');
    formData.append('num', document.getElementById('editNum').value.trim());

    if (avatarInput && avatarInput.files.length > 0) {
        formData.append('avatar', avatarInput.files[0]);
    }

    fetch('api/friends/profile_edit.php', { method: 'POST', body: formData })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                if (msg) msg.innerHTML = '<p style="color:green">' + data.message + '</p>';

                // On met à jour la session avec les nouvelles infos (utilisées dans le fil d'actualité, etc.)
                var stored = getCurrentUser();
                if (stored && data.user) {
                    stored.username = data.user.username;
                    stored.surname = data.user.surname;
                    stored.avatar = data.user.avatar;
                    sessionStorage.setItem('rss_user', JSON.stringify(stored));
                }

                loadProfilData(userId, false);
                if (avatarInput) avatarInput.value = '';
            } else {
                if (msg) msg.innerHTML = '<p style="color:red">' + data.message + '</p>';
            }
        })
        .catch(function () {
            if (msg) msg.innerHTML = '<p style="color:red">Impossible de mettre à jour le profil.</p>';
        });
}

function submitPasswordChange(userId) {
    var msg = document.getElementById('passwordMessage');
    var currentPassword = document.getElementById('currentPassword').value;
    var newPassword = document.getElementById('newPassword').value;
    var confirmPassword = document.getElementById('confirmPassword').value;

    fetch('api/friends/change_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            user_id: userId,
            current_password: currentPassword,
            new_password: newPassword,
            confirm_password: confirmPassword
        })
    })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (msg) msg.innerHTML = '<p style="color:' + (data.success ? 'green' : 'red') + '">' + data.message + '</p>';
            if (data.success) document.getElementById('passwordForm').reset();
        })
        .catch(function () {
            if (msg) msg.innerHTML = '<p style="color:red">Impossible de changer le mot de passe.</p>';
        });
}

// ================= PAGE "AMIS" =================

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
