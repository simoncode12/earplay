<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Halaman ini khusus untuk pengguna yang sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php?error=login_required');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil semua playlist yang dibuat oleh pengguna ini
// Kita juga mengambil thumbnail dari video pertama di setiap playlist sebagai cover
$stmt = $pdo->prepare(
    "SELECT 
        p.id, p.title, COUNT(pv.video_id) as video_count,
        (SELECT v.youtube_id FROM playlist_videos pv_thumb 
         JOIN videos v ON pv_thumb.video_id = v.id 
         WHERE pv_thumb.playlist_id = p.id 
         ORDER BY pv_thumb.added_at ASC LIMIT 1) as cover_youtube_id
     FROM playlists p
     LEFT JOIN playlist_videos pv ON p.id = pv.playlist_id
     WHERE p.user_id = ?
     GROUP BY p.id
     ORDER BY p.created_at DESC"
);
$stmt->execute([$user_id]);
$playlists = $stmt->fetchAll();

include 'includes/templates/header.php';
?>

<div class="main-container">
    <h1 class="page-title">My Library</h1>
    <p style="color: var(--text-secondary); margin-top: -15px; margin-bottom: 30px;">Koleksi playlist yang telah Anda buat.</p>

    <div class="playlist-grid">
        <?php if (count($playlists) > 0): ?>
            <?php foreach ($playlists as $playlist): ?>
                <a href="playlist.php?id=<?= $playlist['id'] ?>" class="playlist-card">
                    <div class="playlist-card-thumbnail">
                        <img src="https://i.ytimg.com/vi/<?= htmlspecialchars($playlist['cover_youtube_id'] ?: 'default') ?>/mqdefault.jpg" alt="Playlist Cover">
                        <div class="playlist-card-overlay">
                            <span><i class="fas fa-play"></i> <?= $playlist['video_count'] ?> videos</span>
                        </div>
                    </div>
                    <div class="playlist-card-info">
                        <h3><?= htmlspecialchars($playlist['title']) ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-content-message" style="grid-column: 1 / -1;">
                <i class="fas fa-list-ul"></i>
                <h3>Anda Belum Membuat Playlist</h3>
                <p>Simpan video ke playlist untuk mengumpulkannya di sini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>