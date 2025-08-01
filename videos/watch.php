<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Cek status login pengguna
$is_user_logged_in = isset($_SESSION['user_id']);
$user_id = $is_user_logged_in ? $_SESSION['user_id'] : null;

// Ambil ID video dari URL dan pastikan ada
$youtube_id = $_GET['v'] ?? null;
if (!$youtube_id) {
    die("Error: Video tidak ditemukan atau URL tidak valid.");
}

// Ambil data video utama, termasuk status monetisasi kreator dan Zone ID
$stmt = $pdo->prepare(
    "SELECT v.*, u.id as uploader_id, u.username as uploader_name, u.monetization_status, u.ad_zone_id 
     FROM videos v 
     JOIN users u ON v.uploader_id = u.id 
     WHERE v.youtube_id = ?"
);
$stmt->execute([$youtube_id]);
$video = $stmt->fetch();
if (!$video) {
    die("Error: Video ini tidak ada di database kami.");
}
$video_id = $video['id'];
$ad_campaign_id = $video['ad_campaign_id'];

// Inisialisasi variabel interaksi
$user_interaction = null;
$is_subscribed = false;

// Jika pengguna sudah login, ambil data interaksi mereka
if ($is_user_logged_in) {
    $interaction_stmt = $pdo->prepare("SELECT interaction_type FROM video_interactions WHERE user_id = ? AND video_id = ?");
    $interaction_stmt->execute([$user_id, $video_id]);
    $user_interaction = $interaction_stmt->fetchColumn();
    
    $subscribe_stmt = $pdo->prepare("SELECT id FROM subscriptions WHERE subscriber_id = ? AND creator_id = ?");
    $subscribe_stmt->execute([$user_id, $video['uploader_id']]);
    $is_subscribed = $subscribe_stmt->fetch() ? true : false;
}

// Tambah jumlah tayangan (berjalan untuk semua pengunjung)
$pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?")->execute([$video_id]);

// --- LOGIKA IKLAN BARU YANG MENGGUNAKAN PROXY ---
$vast_tag_url = '';
$zone_id_to_use = null;

// Prioritaskan Zone ID unik milik kreator jika ada
if (!empty($video['ad_zone_id'])) {
    $zone_id_to_use = $video['ad_zone_id'];
} else {
    // Jika tidak ada, gunakan Zone ID default dari pengaturan
    $zone_id_to_use = get_setting('default_ad_zone_id', $pdo);
}

// Jika ada Zone ID yang bisa digunakan, buat URL ke PROXY kita
if (!empty($zone_id_to_use)) {
    $vast_tag_url = "/api/vast_proxy.php?zone_id=" . urlencode($zone_id_to_use);
}


// Ambil satu kampanye banner display yang aktif secara acak
$stmt_banner = $pdo->query(
    "SELECT vast_tag FROM ad_campaigns 
     WHERE campaign_type = 'banner_display' AND is_active = 1 
     ORDER BY RAND() 
     LIMIT 1"
);
$banner_ad_code = $stmt_banner->fetchColumn();

// Ambil data video terkait
$related_videos_stmt = $pdo->prepare(
    "SELECT v.youtube_id, v.title, v.duration, v.views, u.id as uploader_id, u.username as uploader_name 
     FROM videos v 
     JOIN users u ON v.uploader_id = u.id 
     WHERE v.id != ? 
     ORDER BY v.created_at DESC 
     LIMIT 8"
);
$related_videos_stmt->execute([$video_id]);
$related_videos = $related_videos_stmt->fetchAll();

// Ambil data komentar dan data pendukung lainnya
$comments = $pdo->query("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.video_id = {$video_id} ORDER BY c.created_at DESC")->fetchAll();
$reward_rate = get_setting('reward_rate_per_second', $pdo);

// Sertakan header halaman
include '../includes/templates/header.php';
?>

<div class="watch-layout-container">

    <div class="watch-content-col">
        
        <div class="video-player-container">
            <div id="plays"></div> 
            <?php if ($is_user_logged_in): ?>
            <div id="reward-overlay"> 
                <span id="status">▶️ Menunggu...</span> | ⏱️ <span id="seconds">0</span>s | 💰 <span id="usd">$0.0000000</span>
            </div>
            <?php endif; ?>
        </div>

        <div class="video-primary-info">
            <h1 class="video-title-main"><?= htmlspecialchars($video['title']) ?></h1>
        </div>

        <div class="video-secondary-info">
            <div class="uploader-info-box">
                <a href="/channel.php?id=<?= $video['uploader_id'] ?>" class="uploader-avatar-link">
                    <div class="uploader-avatar"><?= strtoupper(substr($video['uploader_name'], 0, 1)) ?></div>
                </a>
                <div class="uploader-details">
                    <a href="/channel.php?id=<?= $video['uploader_id'] ?>" class="uploader-name-link"><?= htmlspecialchars($video['uploader_name']) ?></a>
                    <span><?= get_subscriber_count($video['uploader_id'], $pdo) ?> subscribers</span>
                </div>
                <?php if ($is_user_logged_in && $user_id != $video['uploader_id']): ?>
                    <button id="subscribe-btn" class="subscribe-btn <?= $is_subscribed ? 'subscribed' : '' ?>" data-creator-id="<?= $video['uploader_id'] ?>">
                        <?= $is_subscribed ? '✓ Subscribed' : 'Subscribe' ?>
                    </button>
                <?php endif; ?>
            </div>

            <div class="main-actions-group">
                <?php if ($is_user_logged_in): ?>
                    <div class="like-dislike-group">
                        <button id="like-btn" class="action-btn <?= $user_interaction === 'like' ? 'active' : '' ?>" data-video-id="<?= $video['id'] ?>"><i class="fas fa-thumbs-up"></i> <span id="like-count"><?= $video['likes'] ?></span></button>
                        <button id="dislike-btn" class="action-btn <?= $user_interaction === 'dislike' ? 'active' : '' ?>" data-video-id="<?= $video['id'] ?>"><i class="fas fa-thumbs-down"></i></button>
                    </div>
                    <button id="save-to-playlist-btn" class="action-btn"><i class="fas fa-plus-square"></i> Simpan</button>
                <?php endif; ?>
            </div>
        </div>

        <div class="video-description-box expandable">
            <div class="description-meta">
                <strong><i class="fas fa-eye"></i> <?= number_format($video['views']) ?> views</strong>
                <strong><i class="fas fa-calendar-alt"></i> <?= date('d M Y', strtotime($video['created_at'])) ?></strong>
                <strong><i class="fas fa-clock"></i> <?= format_duration($video['duration']) ?></strong>
            </div>
            <p class="description-text"><?= nl2br(htmlspecialchars($video['description'])) ?></p>
            <button class="expand-btn">Show more</button>
        </div>

        <div class="comments-section">
            <h3 class="section-title"><?= count($comments) ?> Comments</h3>
            <?php if ($is_user_logged_in): ?>
                <form id="comment-form" class="comment-form-new">
                    <div class="comment-form-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                    <textarea name="comment_text" placeholder="Add a comment..." required></textarea>
                    <button type="submit">Comment</button>
                </form>
            <?php else: ?>
                <div class="comment-login-prompt"><a href="/auth/login.php">Login</a> to add a comment.</div>
            <?php endif; ?>
            
            <div class="comments-list">
                <?php foreach ($comments as $comment): ?>
                    <div class="comment-item">
                        <div class="comment-avatar"><?= strtoupper(substr($comment['username'], 0, 1)) ?></div>
                        <div class="comment-content">
                            <span class="comment-author"><?= htmlspecialchars($comment['username']) ?> <span class="comment-date">• <?= date('d M Y', strtotime($comment['created_at'])) ?></span></span>
                            <p class="comment-body"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="watch-sidebar-col">
        
        <div class="sidebar-ad-slot">
            <?php if ($banner_ad_code): ?>
                <?= $banner_ad_code ?>
            <?php else: ?>
                <span>AREA IKLAN</span>
            <?php endif; ?>
        </div>

        <h3 class="section-title" style="margin-top: 24px;">Video Berikutnya</h3>
        <div class="related-videos-list-new">
            <?php foreach ($related_videos as $related_video): ?>
            <div class="related-video-item">
                <a href="watch.php?v=<?= htmlspecialchars($related_video['youtube_id']) ?>" class="related-thumbnail-container">
                    <img src="https://i.ytimg.com/vi/<?= htmlspecialchars($related_video['youtube_id']) ?>/mqdefault.jpg" alt="Thumbnail" class="related-thumbnail">
                    <span class="duration-badge-sidebar"><?= format_duration($related_video['duration']) ?></span>
                </a>
                <div class="related-video-details">
                    <a href="watch.php?v=<?= htmlspecialchars($related_video['youtube_id']) ?>" class="related-video-title-link">
                        <?= htmlspecialchars($related_video['title']) ?>
                    </a>
                    <a href="/channel.php?id=<?= $related_video['uploader_id'] ?>" class="related-creator-link">
                        <?= htmlspecialchars($related_video['uploader_name']) ?>
                    </a>
                    <p class="related-video-stats"><?= number_format($related_video['views']) ?> views</p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="playlist-modal-overlay" id="playlist-modal-overlay" style="display: none;">
    <div class="playlist-modal">
        <div class="playlist-modal-header">
            <h3>Simpan ke...</h3>
            <button id="close-playlist-modal-btn">&times;</button>
        </div>
        <div class="playlist-modal-body" id="playlist-modal-body"></div>
        <div class="playlist-modal-footer">
            <input type="text" id="new-playlist-title" placeholder="Buat playlist baru...">
            <button id="create-playlist-btn">Buat</button>
        </div>
    </div>
</div>

<script src="https://content.jwplatform.com/libraries/IDzF9Zmk.js"></script>
<script type="text/javascript">jwplayer.key = "Rj7tDuYSCqZdNKNAqsjWoMDBdHhp1THggDFOUhr0Zj8=";</script>
<script src="../assets/js/reward_handler.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Data yang akan dikirim ke JavaScript
    const tubeXData = { 
        video_id: <?= json_encode($video_id) ?>, 
        youtube_id: <?= json_encode($video['youtube_id']) ?>, 
        user_id: <?= json_encode($user_id) ?>,
        reward_rate: <?= json_encode((float)$reward_rate) ?>,
        vast_tag: <?= json_encode($vast_tag_url) ?>,
        ad_campaign_id: <?= json_encode($ad_campaign_id) ?>
    };
    initTubeXPlayer(tubeXData);

    // Jalankan skrip interaksi HANYA jika pengguna sudah login
    <?php if ($is_user_logged_in): ?>
        const likeBtn = document.getElementById('like-btn');
        const dislikeBtn = document.getElementById('dislike-btn');
        const subscribeBtn = document.getElementById('subscribe-btn');
        const commentForm = document.getElementById('comment-form');
        const saveBtn = document.getElementById('save-to-playlist-btn');

        const handleInteraction = async (action, videoId, creatorId) => {
            try {
                const response = await fetch('../api/interact.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action, video_id: videoId, creator_id: creatorId })
                });
                const result = await response.json();
                if (result.status === 'success' && result.counts) {
                    if(document.getElementById('like-count')) document.getElementById('like-count').textContent = result.counts.likes;
                }
            } catch (error) {
                console.error('Interaction failed:', error);
            }
        };

        if(likeBtn) {
            likeBtn.addEventListener('click', () => {
                handleInteraction('like', likeBtn.dataset.videoId, null);
                likeBtn.classList.toggle('active');
                if(dislikeBtn) dislikeBtn.classList.remove('active');
            });
        }
        
        if(dislikeBtn) {
            dislikeBtn.addEventListener('click', () => {
                handleInteraction('dislike', dislikeBtn.dataset.videoId, null);
                dislikeBtn.classList.toggle('active');
                if(likeBtn) likeBtn.classList.remove('active');
            });
        }

        if(subscribeBtn) {
            subscribeBtn.addEventListener('click', () => {
                handleInteraction('subscribe', null, subscribeBtn.dataset.creatorId);
                subscribeBtn.classList.toggle('subscribed');
                subscribeBtn.textContent = subscribeBtn.classList.contains('subscribed') ? '✓ Subscribed' : 'Subscribe';
            });
        }

        if(commentForm) {
            commentForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const textArea = commentForm.querySelector('textarea');
                const commentText = textArea.value.trim();
                if (commentText === '') return;
                try {
                    const response = await fetch('../api/post_comment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ video_id: tubeXData.video_id, comment_text: commentText })
                    });
                    const result = await response.json();
                    if (result.status === 'success') {
                        textArea.value = '';
                        const commentsList = document.querySelector('.comments-list');
                        const newComment = document.createElement('div');
                        newComment.className = 'comment-item';
                        newComment.innerHTML = `<div class="comment-avatar">${result.comment.username.charAt(0).toUpperCase()}</div><div class="comment-content"><span class="comment-author">${result.comment.username} <span class="comment-date">• Baru saja</span></span><p class="comment-body">${result.comment.comment_text}</p></div>`;
                        commentsList.prepend(newComment);
                    } else {
                        alert('Gagal mengirim komentar: ' + result.message);
                    }
                } catch (error) {
                    console.error('Submit comment failed:', error);
                }
            });
        }

        const modalOverlay = document.getElementById('playlist-modal-overlay');
        const closeModalBtn = document.getElementById('close-playlist-modal-btn');
        const modalBody = document.getElementById('playlist-modal-body');
        const createPlaylistBtn = document.getElementById('create-playlist-btn');
        const newPlaylistTitleInput = document.getElementById('new-playlist-title');
        const currentVideoId = tubeXData.video_id;

        const loadPlaylists = async () => {
            modalBody.innerHTML = '<p>Loading...</p>';
            const response = await fetch(`/api/playlist_manager.php?action=get_playlists&video_id=${currentVideoId}`);
            const playlists = await response.json();
            modalBody.innerHTML = '';
            playlists.forEach(p => {
                const item = document.createElement('div');
                item.className = 'playlist-item';
                item.innerHTML = `<input type="checkbox" id="pl-${p.id}" ${p.is_video_in > 0 ? 'checked' : ''}> <label for="pl-${p.id}">${p.title}</label>`;
                item.querySelector('input').addEventListener('change', () => toggleVideoInPlaylist(p.id));
                modalBody.appendChild(item);
            });
        };
        const toggleVideoInPlaylist = async (playlistId) => {
            await fetch('/api/playlist_manager.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'toggle_video', playlist_id: playlistId, video_id: currentVideoId }) });
        };
        createPlaylistBtn.addEventListener('click', async () => {
            const title = newPlaylistTitleInput.value.trim();
            if (title) {
                const response = await fetch('/api/playlist_manager.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action: 'create_playlist', title: title }) });
                const result = await response.json();
                if (result.status === 'success') {
                    newPlaylistTitleInput.value = '';
                    await loadPlaylists();
                    await toggleVideoInPlaylist(result.new_playlist_id);
                    document.getElementById(`pl-${result.new_playlist_id}`).checked = true;
                }
            }
        });

        if(saveBtn) saveBtn.addEventListener('click', () => { modalOverlay.style.display = 'flex'; loadPlaylists(); });
        if(closeModalBtn) closeModalBtn.addEventListener('click', () => modalOverlay.style.display = 'none');
        if(modalOverlay) modalOverlay.addEventListener('click', (e) => { if (e.target === modalOverlay) { modalOverlay.style.display = 'none'; } });

    <?php endif; ?>

    // JavaScript untuk deskripsi interaktif
    const descBox = document.querySelector('.video-description-box');
    const expandBtn = document.querySelector('.expand-btn');
    if (descBox && expandBtn) {
        expandBtn.addEventListener('click', () => {
            descBox.classList.toggle('expanded');
            expandBtn.textContent = descBox.classList.contains('expanded') ? 'Show less' : 'Show more';
        });
    }
});
</script>

<?php 
// Sertakan footer halaman
include '../includes/templates/footer.php'; 
?>
