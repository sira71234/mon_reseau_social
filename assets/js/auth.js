function initForms() {
    function showMessage(message, color) {
        color = color || 'red';
        var messageBox = document.getElementById('message');
        if (messageBox) {
            messageBox.innerHTML = '<p style="color:' + color + '">' + message + '</p>';
        }
    }

    function parseJsonResponse(res) {
        if (!res.ok) throw new Error('Erreur serveur');
        return res.json();
    }

    // LOGIN
    var loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;

            fetch('api/auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, password: password })
            })
            .then(parseJsonResponse)
            .then(function(data) {
                if (data.success) {
                    sessionStorage.setItem('rss_user', JSON.stringify(data.user));
                    document.getElementById('navbar').style.display = 'block';
                    loadView('vues/clients/feed.html');
                } else {
                    showMessage(data.message);
                }
            })
            .catch(function() { showMessage('Connexion impossible pour le moment.'); });
        });
    }

    // REGISTER
    var registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var username = document.getElementById('username').value;
            var surname = document.getElementById('surname').value;
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;
            var birthdate = document.getElementById('birthdate') ? document.getElementById('birthdate').value : '';
            var gender = document.querySelector('input[name="gender"]:checked') ? document.querySelector('input[name="gender"]:checked').value : '';
            var num = document.getElementById('num') ? document.getElementById('num').value : '';

            fetch('api/auth/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username: username, surname: surname, email: email, password: password, birthdate: birthdate, gender: gender, num: num })
            })
            .then(parseJsonResponse)
            .then(function(data) {
                if (data.success) {
                    showMessage(data.message, 'green');
                    registerForm.reset();
                } else {
                    showMessage(data.message);
                }
            })
            .catch(function() { showMessage('Inscription impossible pour le moment.'); });
        });
    }

    // RESET PASSWORD (forgot)
    var resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var email = document.getElementById('email').value;

            fetch('api/auth/forgot_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email })
            })
            .then(parseJsonResponse)
            .then(function(data) {
                if (data.success) {
                    showMessage(data.message, 'green');
                    resetForm.reset();
                } else {
                    showMessage(data.message);
                }
            })
            .catch(function() { showMessage('Demande impossible pour le moment.'); });
        });
    }

    // NEW PASSWORD
    var newPasswordForm = document.getElementById('newPasswordForm');
    if (newPasswordForm) {
        newPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var urlParams = new URLSearchParams(window.location.search);
            var token = urlParams.get('token');
            var password = document.getElementById('password').value;
            var confirm_password = document.getElementById('confirm_password').value;

            fetch('api/auth/reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: token, password: password, confirm_password: confirm_password })
            })
            .then(parseJsonResponse)
            .then(function(data) {
                if (data.success) {
                    showMessage(data.message, 'green');
                    setTimeout(function() { loadView('vues/clients/login.html'); }, 2000);
                } else {
                    showMessage(data.message);
                }
            })
            .catch(function() { showMessage('Modification impossible pour le moment.'); });
        });
    }

    // NAVIGATION LINKS
    var toRegister = document.getElementById('toRegister');
    if (toRegister) toRegister.addEventListener('click', function(e) { e.preventDefault(); loadView('vues/clients/register.html'); });

    var toLogin = document.getElementById('toLogin');
    if (toLogin) toLogin.addEventListener('click', function(e) { e.preventDefault(); loadView('vues/clients/login.html'); });

    var toReset = document.getElementById('toReset');
    if (toReset) toReset.addEventListener('click', function(e) { e.preventDefault(); loadView('vues/clients/reset_password.html'); });
}

function logout() {
    fetch('api/auth/logout.php')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        sessionStorage.removeItem('rss_user');
        document.getElementById('navbar').style.display = 'none';
        loadView('vues/clients/login.html');
    })
    .catch(function() {
        sessionStorage.removeItem('rss_user');
        document.getElementById('navbar').style.display = 'none';
        loadView('vues/clients/login.html');
    });
}
