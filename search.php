<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Ambil dan bersihkan kata kunci pencarian dari URL
$query = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_STRING);
$videos = [];
$creators = [];

if ($query && !empty(trim($query))) {
    // Cari video yang judul atau deskripsinya cocok
    $stmt_videos = $pdo->prepare(
        "SELECT v.youtube_id, v.title, v.views, v.created_at, v.duration, u.id as uploader_id, u.username as uploader_name 
         FROM videos v 
         JOIN users u ON v.uploader_id = u.id 
         WHERE v.title LIKE ? OR v.description LIKE ?
         ORDER BY v.views DESC"
    );
    $search_term = '%' . $query . '%';
    $stmt_videos->execute([$search_term, $search_term]);
    $videos = $stmt_videos->fetchAll();

    // Cari kreator yang namanya cocok
    $stmt_creators = $pdo->prepare(
        "SELECT id, username FROM users WHERE role = 'creator' AND username LIKE ?"
    );
    $stmt_creators->execute([$search_term]);
    $creators = $stmt_creators->fetchAll();
}

include 'includes/templates/header.php';
?>

<div class="main-container">
    <?php if ($query && !empty(trim($query))): ?>
        <h1 class="page-title">Hasil Pencarian untuk: "<?= htmlspecialchars($query) ?>"</h1>

        <?php if (empty($videos) && empty($creators)): ?>
            <p>Tidak ada hasil yang ditemukan untuk pencarian Anda.</p>
        <?php endif; ?>

        <?php if (!empty($creators)): ?>
            <div class="search-section">
                <h2>Kreator</h2>
                <?php foreach ($creators as $creator): ?>
                    <a href="channel.php?id=<?= $creator['id'] ?>" class="creator-result-card">
                        <div class="creator-avatar-search"><?= strtoupper(substr($creator['username'], 0, 1)) ?></div>
                        <div class="creator-info-search">
                            <h3><?= htmlspecialchars($creator['username']) ?></h3>
                            <span><?= get_subscriber_count($creator['id'], $pdo) ?> subscribers</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($videos)): ?>
            <div class="search-section">
                <h2>Video</h2>
                <div class="video-grid">
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
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <h1 class="page-title">Silakan masukkan kata kunci untuk memulai pencarian.</h1>
    <?php endif; ?>
</div>

<?php include 'includes/templates/footer.php'; ?>