
// Appelée depuis friends.js (initFriends) quand la page profil est chargée.
function initProfilPage() {
    var user = getCurrentUser();
    if (!user) {
        var msg = document.getElementById('profilMessage');
        if (msg) msg.innerHTML = '<p style="color:red">Vous devez être connecté pour voir votre profil.</p>';
        return;
    }

    // 1) Affichage immédiat avec les infos déjà connues (stockées à la connexion)
    //    -> l'en-tête n'est plus vide en attendant la réponse du serveur.
    renderProfilHeader(user);

    // 2) Puis on va chercher les infos à jour en base (compteurs, date de naissance, etc.)
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

// Remplit uniquement l'en-tête (photo, nom, email) à partir d'un objet utilisateur simple.
function renderProfilHeader(u) {
    var avatarEl = document.getElementById('profilAvatar');
    if (avatarEl) avatarEl.src = u.avatar ? u.avatar : defaultAvatar();

    var nomEl = document.getElementById('profilNomComplet');
    if (nomEl) nomEl.textContent = (u.surname || '') + ' ' + (u.username || '');

    var emailEl = document.getElementById('profilEmail');
    if (emailEl) emailEl.textContent = u.email || '';
}

// ================= AFFICHAGE / MASQUAGE DES SECTIONS PAR BOUTON =================

function toggleProfilSection(sectionId, buttonId) {
    var section = document.getElementById(sectionId);
    var button = document.getElementById(buttonId);
    if (!section) return;

    var estVisible = section.style.display === 'block';

    // On ferme les autres sections pour n'en garder qu'une ouverte à la fois
    var toutesLesSections = document.querySelectorAll('.profil-section');
    toutesLesSections.forEach(function (s) {
        s.style.display = 'none';
    });
    var tousLesBoutons = document.querySelectorAll('.profil-actions button');
    tousLesBoutons.forEach(function (b) {
        b.classList.remove('actif');
    });

    if (!estVisible) {
        section.style.display = 'block';
        if (button) button.classList.add('actif');
    }
}

// ================= AFFICHAGE DES INFOS DU PROFIL =================

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

            renderProfilHeader(u);

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
        .catch(function () {
            // Si le serveur ne répond pas, l'en-tête reste quand même visible
            // grâce à renderProfilHeader() déjà appelé avec les infos de session.
        });
}

// ================= SOUMISSION DU FORMULAIRE "MES INFORMATIONS" =================

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

                // L'en-tête change tout de suite, sans attendre un nouvel appel serveur
                renderProfilHeader(data.user);

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

// ================= SOUMISSION DU FORMULAIRE "MOT DE PASSE" =================

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