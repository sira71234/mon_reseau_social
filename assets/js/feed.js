function getCurrentUser() {
    const storedUser = sessionStorage.getItem('rss_user');
    return storedUser ? JSON.parse(storedUser) : null;
}

function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function initFeed() {
    const postForm = document.getElementById('postForm');
    const postsList = document.getElementById('postsList');
    const profileInfo = document.getElementById('profileInfo');

    if (profileInfo) {
        const user = getCurrentUser();
        profileInfo.innerHTML = user
            ? '<p><strong>Nom :</strong> ' + escapeHtml(user.nom) + '</p><p><strong>Prénom :</strong> ' + escapeHtml(user.prenom) + '</p><p><strong>Email :</strong> ' + escapeHtml(user.email) + '</p>'
            : '<p>Aucun utilisateur connecté.</p>';
    }

    if (!postForm || !postsList) {
        return;
    }

    postForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const user = getCurrentUser();
        const contentInput = document.getElementById('postContent');
        const content = contentInput.value.trim();
        const imageInput = document.getElementById('postImage');

        if (!user || !content) {
            return;
        }

        const requestOptions = {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: user.id, content })
        };

        if (imageInput && imageInput.files.length > 0) {
            const formData = new FormData();
            formData.append('user_id', user.id);
            formData.append('content', content);
            formData.append('image', imageInput.files[0]);
            requestOptions.headers = {};
            requestOptions.body = formData;
        }

        fetch('api/posts/create.php', {
            method: requestOptions.method,
            headers: requestOptions.headers,
            body: requestOptions.body
        })
        .then(res => res.json())
        .then(data => {
            const message = document.getElementById('message');
            if (data.success) {
                contentInput.value = '';
                if (imageInput) {
                    imageInput.value = '';
                }
                message.innerHTML = '<p style="color:green">' + data.message + '</p>';
                loadPosts();
            } else {
                message.innerHTML = '<p style="color:red">' + data.message + '</p>';
            }
        })
        .catch(() => {
            document.getElementById('message').innerHTML = '<p style="color:red">Impossible de publier pour le moment.</p>';
        });
    });

    loadPosts();
}

function loadPosts() {
    const postsList = document.getElementById('postsList');
    if (!postsList) {
        return;
    }

    postsList.innerHTML = '<div class="container"><p>Chargement...</p></div>';

    fetch('api/posts/feed.php')
        .then(res => res.json())
        .then(data => {
            if (!data.success || data.posts.length === 0) {
                postsList.innerHTML = '<div class="container"><p>Aucune publication pour le moment.</p></div>';
                return;
            }

            postsList.innerHTML = data.posts.map(renderPost).join('');
        })
        .catch(() => {
            postsList.innerHTML = '<div class="container"><p>Impossible de charger le fil.</p></div>';
        });
}

function renderPost(post) {
    const user = getCurrentUser();
    const canDelete = user && String(user.id) === String(post.user_id);
    const image = post.image ? '<img class="post-image" src="' + escapeHtml(post.image) + '" alt="Image du post">' : '';
    const deleteButton = canDelete
        ? '<button type="button" onclick="deletePost(' + Number(post.id) + ')">Supprimer</button>'
        : '';

    return `
        <article class="post">
            <div class="post-header">
                <img src="${escapeHtml(post.avatar || 'assets/images/default.png')}" alt="" class="avatar" onerror="this.style.visibility='hidden'">
                <div>
                    <strong>${escapeHtml(post.prenom + ' ' + post.nom)}</strong>
                    <small>${escapeHtml(post.created_at)}</small>
                </div>
            </div>
            <p>${escapeHtml(post.content)}</p>
            ${image}
            <small>${Number(post.likes_count || 0)} like(s) - ${Number(post.comments_count || 0)} commentaire(s)</small>
            <div class="post-actions">
                <button type="button" onclick="likePost(${Number(post.id)})">J'aime</button>
                <button type="button" onclick="likePost(${Number(post.id)}, 'dislike')">Je n'aime pas</button>
                ${deleteButton}
            </div>
            <form class="comment-form" onsubmit="addComment(event, ${Number(post.id)})">
                <input type="text" name="comment" placeholder="Ajouter un commentaire" required>
                <input type="submit" value="Commenter">
            </form>
        </article>
    `;
}

function likePost(postId, type = 'like') {
    const user = getCurrentUser();
    if (!user) {
        return;
    }

    fetch('api/posts/like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: user.id, post_id: postId, type })
    })
    .then(res => res.json())
    .then(() => loadPosts());
}

function addComment(event, postId) {
    event.preventDefault();

    const user = getCurrentUser();
    const input = event.target.elements.comment;
    const content = input.value.trim();

    if (!user || !content) {
        return;
    }

    fetch('api/posts/comment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: user.id, post_id: postId, content })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            input.value = '';
        }
    });
}

function deletePost(postId) {
    const user = getCurrentUser();
    if (!user) {
        return;
    }

    fetch('api/posts/delete.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: postId, user_id: user.id })
    })
    .then(res => res.json())
    .then(() => loadPosts());
}
