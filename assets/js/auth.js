function initForms() {
    function showMessage(message, color = 'red') {
        const messageBox = document.getElementById('message');
        if (messageBox) {
            messageBox.innerHTML = '<p style="color:' + color + '">' + message + '</p>';
        }
    }

    function parseJsonResponse(res) {
        if (!res.ok) {
            throw new Error('Erreur serveur');
        }
        return res.json();
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            fetch('api/auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            })
            .then(parseJsonResponse)
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('rss_user', JSON.stringify(data.user));
                    document.getElementById('navbar').style.display = 'block';
                    loadView('vues/clients/feed.html');
                } else {
                    showMessage(data.message);
                }
            })
            .catch(() => showMessage('Connexion impossible pour le moment.'));
        });
    }

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const nom = document.getElementById('nom').value;
            const prenom = document.getElementById('prenom').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
        
            fetch('api/auth/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nom, prenom, email, password })
            })
            .then(parseJsonResponse)
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'green');
                    registerForm.reset();
                } else {
                    showMessage(data.message);
                }
            })
            .catch(() => showMessage('Inscription impossible pour le moment.'));
        });
    }  
    
    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;

            fetch('api/auth/forgot_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            })
            .then(parseJsonResponse)
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'green');
                    resetForm.reset();
                } else {
                    showMessage(data.message);
                }
            })
            .catch(() => showMessage('Demande impossible pour le moment.'));
        });
    }  
    
    const newPasswordForm = document.getElementById('newPasswordForm');
    if (newPasswordForm) {
        newPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');

            const password = document.getElementById('password').value;
            const confirm_password = document.getElementById('confirm_password').value;

            fetch('api/auth/reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token, password, confirm_password })
            })
            .then(parseJsonResponse)
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'green');
                    loadView('vues/clients/login.html');
                } else {
                    showMessage(data.message);
                }
            })
            .catch(() => showMessage('Modification impossible pour le moment.'));
        });
    }

    // Liens de navigation dans les vues
    const toRegister = document.getElementById('toRegister');
    if (toRegister) toRegister.addEventListener('click', () => loadView('vues/clients/register.html'));

    const toLogin = document.getElementById('toLogin');
    if (toLogin) toLogin.addEventListener('click', () => loadView('vues/clients/login.html'));

    const toReset = document.getElementById('toReset');
    if (toReset) toReset.addEventListener('click', () => loadView('vues/clients/reset_password.html'));
}

function logout() {
    fetch('api/auth/logout.php')
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            sessionStorage.removeItem('rss_user');
            document.getElementById('navbar').style.display = 'none';
            loadView('vues/clients/login.html');
        }
    })
    .catch(() => {
        sessionStorage.removeItem('rss_user');
        document.getElementById('navbar').style.display = 'none';
        loadView('vues/clients/login.html');
    });
}
