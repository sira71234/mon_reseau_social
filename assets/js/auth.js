document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            fetch('api/auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    sessionStorage.setItem('user', JSON.stringify(data.user));
                    window.location.href = 'index.html';
                } else {
                    document.getElementById('message').innerHTML = '<p style="color:red">' + data.message + '</p>';
                }
            })
        });
    }

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const username = document.getElementById('username').value;
            const surname = document.getElementById('surname').value;
            const birthdate = document.getElementById('birthdate').value;
            const gender = document.querySelector('input[name="gender"]:checked')?.value || '';
            const email = document.getElementById('email').value;
            const num = document.getElementById('num').value;
            const password = document.getElementById('password').value;
        
            fetch('api/auth/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, surname, birthdate, gender, email, num, password })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('message').innerHTML = '<p style="color:green">' + data.message + '</p>';
                    registerForm.reset();
                } else {
                    document.getElementById('message').innerHTML = '<p style="color:red">' + data.message + '</p>';
                }
            })
        });
    }  
    
    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value;

            fetch('api/auth/reset_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('message').innerHTML = '<p style="color:green">' + data.message + '</p>';
                    resetForm.reset();
                } else {
                    document.getElementById('message').innerHTML = '<p style="color:red">' + data.message + '</p>';
                }
            })
        });
    }  
    
    const newPasswordForm = document.getElementById('newPassword');
    if (newPasswordForm) {
        newPasswordForm.addEventListener('submit',function(e){
            e.preventDefault();

            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');

            const password = document.getElementById('password').value;
            const confirm_password = document.getElementById('confirm_password').value;

            fetch('api/auth/new_password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token, password, confirm_password })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'login.html';
                } else {
                    document.getElementById('message').innerHTML = '<p style="color:red">' + data.message + '</p>';
                }
            })
        })
    }
}); 