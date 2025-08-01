<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Query sekarang mengambil durasi video dan ID kreator
$stmt = $pdo->query(
    "SELECT v.youtube_id, v.title, v.views, v.created_at, v.duration, u.id as uploader_id, u.username as uploader_name 
     FROM videos v 
     JOIN users u ON v.uploader_id = u.id 
     ORDER BY v.created_at DESC"
);
$videos = $stmt->fetchAll();

include 'includes/templates/header.php';
?>

<div class="main-container">
    <h1 class="page-title">Video Terbaru</h1>
    <div class="video-grid">
        <?php if (count($videos) > 0): ?>
            <?php foreach ($videos as $video): ?>
                <div class="video-card-new">
                    <a href="videos/watch.php?v=<?= htmlspecialchars($video['youtube_id']) ?>" class="thumbnail-container">
                        <img src="https://i.ytimg.com/vi/<?= htmlspecialchars($video['youtube_id']) ?>/mqdefault.jpg" alt="Thumbnail" class="video-thumbnail">
                        <span class="duration-badge"><?= format_duration($video['duration']) ?></span>
                    </a>
                    <div class="video-details">
                        <a href="channel.php?id=<?= $video['uploader_id'] ?>" class="creator-avatar-small">
                            <?= strtoupper(substr($video['uploader_name'], 0, 1)) ?>
                        </a>
                        <div class="video-metadata">
                            <a href="videos/watch.php?v=<?= htmlspecialchars($video['youtube_id']) ?>" class="video-title-link" title="<?= htmlspecialchars($video['title']) ?>">
                                <?= htmlspecialchars($video['title']) ?>
                            </a>
                            <a href="channel.php?id=<?= $video['uploader_id'] ?>" class="creator-name-link">
                                <?= htmlspecialchars($video['uploader_name']) ?>
                            </a>
                            <p class="video-stats-card"><?= number_format($video['views']) ?> views • <?= date('d M Y', strtotime($video['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="grid-column: 1 / -1;">Belum ada video yang diunggah.</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>