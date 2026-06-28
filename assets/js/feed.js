function getCurrentUser() {
    var stored = sessionStorage.getItem('rss_user');
    return stored ? JSON.parse(stored) : null;
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function initFeed() {
    var postForm = document.getElementById('postForm');
    var postsList = document.getElementById('postsList');
    var profileInfo = document.getElementById('profileInfo');

    if (profileInfo) {
        var user = getCurrentUser();
        if (user) {
            profileInfo.innerHTML = '<p><strong>' + escapeHtml(user.surname) + ' ' + escapeHtml(user.username) + '</strong></p><p>' + escapeHtml(user.email) + '</p>';
        }
    }

    if (!postForm || !postsList) return;

    postForm.addEventListener('submit', function(e) {
        e.preventDefault();

        var user = getCurrentUser();
        var content = document.getElementById('postContent').value.trim();
        var imageInput = document.getElementById('postImage');

        if (!user || !content) return;

        var body, headers;

        if (imageInput && imageInput.files.length > 0) {
            var formData = new FormData();
            formData.append('user_id', user.id);
            formData.append('content', content);
            formData.append('image', imageInput.files[0]);
            body = formData;
            headers = {};
        } else {
            body = JSON.stringify({ user_id: user.id, content: content });
            headers = { 'Content-Type': 'application/json' };
        }

        fetch('api/posts/create.php', { method: 'POST', headers: headers, body: body })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            var msg = document.getElementById('message');
            if (data.success) {
                document.getElementById('postContent').value = '';
                if (imageInput) imageInput.value = '';
                if (msg) msg.innerHTML = '<p style="color:green">' + data.message + '</p>';
                loadPosts();
            } else {
                if (msg) msg.innerHTML = '<p style="color:red">' + data.message + '</p>';
            }
        })
        .catch(function() {
            var msg = document.getElementById('message');
            if (msg) msg.innerHTML = '<p style="color:red">Impossible de publier.</p>';
        });
    });

    loadPosts();
}

function loadPosts() {
    var postsList = document.getElementById('postsList');
    if (!postsList) return;

    postsList.innerHTML = '<p>Chargement...</p>';

    fetch('api/posts/feed.php')
    .then(function(res) { return res.json(); })
    .then(function(data) {
        if (!data.success || data.posts.length === 0) {
            postsList.innerHTML = '<p>Aucune publication pour le moment.</p>';
            return;
        }
        postsList.innerHTML = data.posts.map(renderPost).join('');
    })
    .catch(function() {
        postsList.innerHTML = '<p>Impossible de charger le fil.</p>';
    });
}

function renderPost(post) {
    var user = getCurrentUser();
    var canDelete = user && String(user.id) === String(post.user_id);
    var image = post.image ? '<img src="' + escapeHtml(post.image) + '" alt="image" style="max-width:100%">' : '';
    var deleteBtn = canDelete ? '<button onclick="deletePost(' + Number(post.id) + ')">Supprimer</button>' : '';

    return '<article class="post" style="border:1px solid #ddd;padding:15px;margin:10px 0;border-radius:8px">' +
        '<div class="post-header" style="display:flex;align-items:center;gap:10px;margin-bottom:10px">' +
        '<strong>' + escapeHtml(post.surname + ' ' + post.username) + '</strong>' +
        '<small style="color:#999">' + escapeHtml(post.created_at) + '</small></div>' +
        '<p>' + escapeHtml(post.content) + '</p>' +
        image +
        '<div style="margin:10px 0;color:#666"><small>' + Number(post.likes_count || 0) + ' j\'aime · ' + Number(post.dislikes_count || 0) + ' je n\'aime pas · ' + Number(post.comments_count || 0) + ' commentaire(s)</small></div>' +
        '<div style="display:flex;gap:10px">' +
        '<button onclick="likePost(' + Number(post.id) + ', \'like\')">J\'aime</button>' +
        '<button onclick="likePost(' + Number(post.id) + ', \'dislike\')">Je n\'aime pas</button>' +
        deleteBtn + '</div>' +
        '<form onsubmit="addComment(event, ' + Number(post.id) + ')" style="margin-top:10px;display:flex;gap:10px">' +
        '<input type="text" name="comment" placeholder="Commenter..." required style="flex:1">' +
        '<button type="submit">Envoyer</button></form>' +
        '</article>';
}

function likePost(postId, type) {
    var user = getCurrentUser();
    if (!user) return;

    fetch('api/posts/like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: user.id, post_id: postId, type: type })
    })
    .then(function(res) { return res.json(); })
    .then(function() { loadPosts(); });
}

function addComment(event, postId) {
    event.preventDefault();
    var user = getCurrentUser();
    var input = event.target.elements.comment;
    var content = input.value.trim();
    if (!user || !content) return;

    fetch('api/posts/comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: user.id, post_id: postId, content: content })
    })
    .then(function(res) { return res.json(); })
    .then(function(data) { if (data.success) { input.value = ''; loadPosts(); } });
}

function deletePost(postId) {
    var user = getCurrentUser();
    if (!user) return;
    if (!confirm('Supprimer ce post ?')) return;

    fetch('api/posts/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: postId, user_id: user.id })
    })
    .then(function(res) { return res.json(); })
    .then(function() { loadPosts(); });
}
