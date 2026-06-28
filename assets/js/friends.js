function initFriends() {
    var usersList = document.getElementById('usersList');
    var friendsList = document.getElementById('friendsList');
    var friendRequests = document.getElementById('friendRequests');

    if (!usersList && !friendsList && !friendRequests) return;

    loadUsers();
    loadFriends();
    loadFriendRequests();
}

function loadUsers() {
    var usersList = document.getElementById('usersList');
    if (!usersList) return;

    fetch('api/friends/list.php')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data.success || data.users.length === 0) {
            usersList.innerHTML = '<p>Aucun utilisateur.</p>';
            return;
        }
        var user = JSON.parse(sessionStorage.getItem('rss_user') || '{}');
        usersList.innerHTML = data.users
            .filter(function(u) { return u.id !== user.id; })
            .map(function(u) {
                return '<div style="display:flex;align-items:center;justify-content:space-between;padding:10px;border-bottom:1px solid #eee">' +
                    '<span>' + escapeHtmlFriends(u.surname + ' ' + u.username) + '</span>' +
                    '<button onclick="sendFriendRequest(' + u.id + ')">Ajouter</button></div>';
            }).join('');
    })
    .catch(function() { if (usersList) usersList.innerHTML = '<p>Erreur chargement.</p>'; });
}

function loadFriends() {
    var friendsList = document.getElementById('friendsList');
    if (!friendsList) return;

    var user = JSON.parse(sessionStorage.getItem('rss_user') || '{}');
    fetch('api/friends/list.php?type=friends&user_id=' + user.id)
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data.success || !data.friends || data.friends.length === 0) {
            friendsList.innerHTML = '<p>Aucun ami pour le moment.</p>';
            return;
        }
        friendsList.innerHTML = data.friends.map(function(f) {
            return '<div style="padding:10px;border-bottom:1px solid #eee">' + escapeHtmlFriends(f.surname + ' ' + f.username) + '</div>';
        }).join('');
    });
}

function loadFriendRequests() {
    var friendRequests = document.getElementById('friendRequests');
    if (!friendRequests) return;

    var user = JSON.parse(sessionStorage.getItem('rss_user') || '{}');
    fetch('api/friends/list.php?type=requests&user_id=' + user.id)
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data.success || !data.requests || data.requests.length === 0) {
            friendRequests.innerHTML = '<p>Aucune demande en attente.</p>';
            return;
        }
        friendRequests.innerHTML = data.requests.map(function(r) {
            return '<div style="display:flex;align-items:center;justify-content:space-between;padding:10px;border-bottom:1px solid #eee">' +
                '<span>' + escapeHtmlFriends(r.surname + ' ' + r.username) + '</span>' +
                '<div><button onclick="respondFriend(' + r.friend_id + ', \'accepted\')">Accepter</button> ' +
                '<button onclick="respondFriend(' + r.friend_id + ', \'refused\')">Refuser</button></div></div>';
        }).join('');
    });
}

function sendFriendRequest(receiverId) {
    var user = JSON.parse(sessionStorage.getItem('rss_user') || '{}');
    fetch('api/friends/send.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ sender_id: user.id, receiver_id: receiverId })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) {
        var msg = document.getElementById('message');
        if (msg) msg.innerHTML = '<p style="color:' + (data.success ? 'green' : 'red') + '">' + data.message + '</p>';
    });
}

function respondFriend(friendId, status) {
    var user = JSON.parse(sessionStorage.getItem('rss_user') || '{}');
    fetch('api/friends/respond.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: user.id, friend_id: friendId, status: status })
    })
    .then(function(res) { return res.json(); })
    .then(function() { loadFriendRequests(); loadFriends(); });
}

function escapeHtmlFriends(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}
