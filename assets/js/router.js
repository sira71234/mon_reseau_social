function loadView(url) {
    var app = document.getElementById('app');
    app.innerHTML = '<p style="text-align:center;padding:20px">Chargement...</p>';
    
    if (typeof arreterChat === 'function') arreterChat();

    fetch(url)
    .then(function(res) {
        if (!res.ok) throw new Error('Vue introuvable');
        return res.text();
    })
    .then(function(html) {
        app.innerHTML = html;
        if (typeof initForms === 'function') initForms();
        if (typeof initFeed === 'function') initFeed();
        if (typeof initFriends === 'function') initFriends();
        if (typeof initChat === 'function') initChat();
        if (typeof initProfil === 'function') initProfil();
    })
    .catch(function() {
        app.innerHTML = '<div class="container"><h1>Page introuvable</h1></div>';
    });
}

var params = new URLSearchParams(window.location.search);
var user = sessionStorage.getItem('rss_user');

if (params.has('token')) {
    loadView('vues/clients/new_password.html');
    document.getElementById('navbar').style.display = 'none';
} else if (!user) {
    loadView('vues/clients/login.html');
    document.getElementById('navbar').style.display = 'none';
} else {
    loadView('vues/clients/feed.html');
    document.getElementById('navbar').style.display = 'block';
}
