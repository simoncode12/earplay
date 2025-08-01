<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$playlist_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$playlist_id) {
    die("Playlist not found.");
}

// Ambil info playlist dan nama pemiliknya
$stmt_playlist = $pdo->prepare(
    "SELECT p.title, p.description, u.username 
     FROM playlists p 
     JOIN users u ON p.user_id = u.id 
     WHERE p.id = ?"
);
$stmt_playlist->execute([$playlist_id]);
$playlist = $stmt_playlist->fetch();

if (!$playlist) {
    die("This playlist does not exist.");
}

// Ambil semua video di dalam playlist ini
$stmt_videos = $pdo->prepare(
    "SELECT v.youtube_id, v.title, v.views, u.username as uploader_name
     FROM playlist_videos pv
     JOIN videos v ON pv.video_id = v.id
     JOIN users u ON v.uploader_id = u.id
     WHERE pv.playlist_id = ?
     ORDER BY pv.added_at ASC"
);
$stmt_videos->execute([$playlist_id]);
$videos = $stmt_videos->fetchAll();

include 'includes/templates/header.php';
?>

<div class="main-container">
    <div class="playlist-header">
        <div class="playlist-thumbnail-stack">
            <img src="https://i.ytimg.com/vi/<?= !empty($videos) ? htmlspecialchars($videos[0]['youtube_id']) : '' ?>/mqdefault.jpg" alt="Playlist Cover">
        </div>
        <div class="playlist-info">
            <h1 class="playlist-title"><?= htmlspecialchars($playlist['title']) ?></h1>
            <p class="playlist-creator">By <?= htmlspecialchars($playlist['username']) ?></p>
            <span class="playlist-stats"><?= count($videos) ?> videos</span>
            <p class="playlist-description"><?= htmlspecialchars($playlist['description']) ?></p>
        </div>
    </div>

    <div class="video-grid">
        <?php foreach ($videos as $video): ?>
            <a href="videos/watch.php?v=<?= htmlspecialchars($video['youtube_id']) ?>" class="video-card">
                <img src="https://i.ytimg.com/vi/<?= htmlspecialchars($video['youtube_id']) ?>/mqdefault.jpg" alt="Thumbnail" class="video-thumbnail">
                <div class="video-card-info">
                    <h3 class="video-card-title"><?= htmlspecialchars($video['title']) ?></h3>
                    <p class="video-card-uploader"><?= htmlspecialchars($video['uploader_name']) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>