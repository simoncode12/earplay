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

// Query untuk mengambil video-video terbaru HANYA dari kreator yang di-subscribe oleh pengguna
$stmt = $pdo->prepare(
    "SELECT v.youtube_id, v.title, v.views, v.created_at, u.username as uploader_name 
     FROM videos v 
     JOIN users u ON v.uploader_id = u.id 
     WHERE v.uploader_id IN (SELECT creator_id FROM subscriptions WHERE subscriber_id = ?)
     ORDER BY v.created_at DESC"
);
$stmt->execute([$user_id]);
$videos = $stmt->fetchAll();

include 'includes/templates/header.php';
?>

<div class="main-container">
    <h1 class="page-title">Subscriptions</h1>
    <p style="color: var(--text-secondary); margin-top: -15px; margin-bottom: 30px;">Video terbaru dari channel yang Anda ikuti.</p>

    <div class="video-grid">
        <?php if (count($videos) > 0): ?>
            <?php foreach ($videos as $video): ?>
                <a href="videos/watch.php?v=<?= htmlspecialchars($video['youtube_id']) ?>" class="video-card">
                    <img src="https://i.ytimg.com/vi/<?= htmlspecialchars($video['youtube_id']) ?>/mqdefault.jpg" alt="Thumbnail" class="video-thumbnail">
                    <div class="video-card-info">
                        <h3 class="video-card-title"><?= htmlspecialchars($video['title']) ?></h3>
                        <p class="video-card-uploader"><?= htmlspecialchars($video['uploader_name']) ?></p>
                        <span class="video-card-views"><?= number_format($video['views']) ?> views • <?= date('d M Y', strtotime($video['created_at'])) ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-content-message" style="grid-column: 1 / -1;">
                <i class="fas fa-video-slash"></i>
                <h3>Belum Ada Video Baru</h3>
                <p>Coba subscribe ke beberapa channel untuk melihat video terbaru mereka di sini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>