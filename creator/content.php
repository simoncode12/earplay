<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Ensure only creators can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: /auth/login.php?error=creator_only');
    exit();
}

$creator_id = $_SESSION['user_id'];

// Get the creator's monetization status
$stmt_creator = $pdo->prepare("SELECT monetization_status FROM users WHERE id = ?");
$stmt_creator->execute([$creator_id]);
$creator_status = $stmt_creator->fetchColumn();

// Logic to handle video deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_video_id'])) {
    $video_to_delete = (int)$_POST['delete_video_id'];
    
    // Security: Ensure the creator can only delete their own video
    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ? AND uploader_id = ?");
    $stmt->execute([$video_to_delete, $creator_id]);
    
    $_SESSION['creator_message'] = "Video successfully deleted.";
    header("Location: content.php");
    exit();
}

// Query to get all video data, including live viewers and recorded earnings
$stmt_videos = $pdo->prepare(
    "SELECT v.id, v.youtube_id, v.thumbnail_url, v.title, v.views, v.created_at, v.earnings,
            (SELECT COUNT(*) FROM live_sessions WHERE video_id = v.id AND last_update > NOW() - INTERVAL 5 MINUTE) as live_viewers
     FROM videos v
     WHERE v.uploader_id = ?
     ORDER BY v.created_at DESC"
);
$stmt_videos->execute([$creator_id]);
$videos = $stmt_videos->fetchAll();

// Use the dedicated creator template header
include '../includes/templates/header_creator.php';
?>

<h1>Channel Content</h1>

<?php
// Display notification messages if they exist
if (isset($_SESSION['creator_message'])) {
    echo '<div class="panel-info success" style="margin-bottom: 24px;">' . htmlspecialchars($_SESSION['creator_message']) . '</div>';
    unset($_SESSION['creator_message']);
}
?>

<div class="creator-section">
    <div class="content video-list-container">
        <?php if (count($videos) > 0): ?>
            <?php foreach ($videos as $video): ?>
                <div class="video-card-item">
                    <a href="/videos/watch.php?v=<?= htmlspecialchars($video['youtube_id']) ?>" target="_blank">
                        <img src="<?= htmlspecialchars($video['thumbnail_url']) ?>" alt="Thumbnail" class="video-card-thumbnail">
                    </a>
                    <div class="video-card-details">
                        <h3 class="video-card-title"><?= htmlspecialchars($video['title']) ?></h3>
                        <div class="video-card-stats">
                            <span class="stat-item <?= $video['live_viewers'] > 0 ? 'live' : '' ?>">
                                <i class="fas fa-signal"></i> 
                                <?= $video['live_viewers'] > 0 ? $video['live_viewers'] . ' Watching' : 'No Live Viewers' ?>
                            </span>

                            <span class="stat-item"><i class="fas fa-eye"></i> <?= number_format($video['views']) ?> Views</span>

                            <span class="stat-item earning-source">
                                <?php if ($creator_status === 'approved'): ?>
                                    <i class="fas fa-dollar-sign"></i> From Ads (Est.)
                                <?php else: ?>
                                    <i class="fas fa-coins"></i> $<?= number_format((float)$video['earnings'], 8) ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    <div class="video-card-actions">
                        <a href="video_form.php?edit_id=<?= $video['id'] ?>" class="btn-icon" title="Edit Video">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this video?');" style="display:inline;">
                            <input type="hidden" name="delete_video_id" value="<?= $video['id'] ?>">
                            <button type="submit" class="btn-icon btn-icon-delete" title="Delete Video">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-content-message">
                <i class="fas fa-video-slash"></i>
                <h3>You haven't uploaded any videos yet</h3>
                <p>Start uploading your first piece of content for the world to see!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.video-list-container { padding: 8px; }
.video-card-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
    gap: 20px;
}
.video-card-item:last-child { border-bottom: none; }
.video-card-thumbnail { width: 160px; height: 90px; object-fit: cover; border-radius: 8px; flex-shrink: 0; }
.video-card-details { flex-grow: 1; }
.video-card-title { font-weight: 500; font-size: 1.1em; color: var(--text-primary); margin: 0 0 12px 0; }
.video-card-stats { display: flex; align-items: center; gap: 24px; font-size: 0.9em; color: var(--text-secondary); }
.stat-item { display: flex; align-items: center; gap: 6px; }
.stat-item.live { color: #34a853; font-weight: 500; }
.earning-source { color: #34a853; }
.video-card-actions { display: flex; gap: 8px; }
.btn-icon { background: none; border: none; color: var(--text-secondary); width: 40px; height: 40px; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; cursor: pointer; transition: background-color 0.2s; }
.btn-icon:hover { background-color: #f0f0f0; color: var(--text-primary); }
.btn-icon-delete:hover { background-color: #fce8e6; color: #ea4335; }
.no-content-message { text-align: center; padding: 60px 20px; color: var(--text-secondary); }
.no-content-message i { font-size: 48px; margin-bottom: 16px; }
.panel-info.success { background-color: #e6f4ea; color: #34a853; }
</style>

<?php 
// Use the dedicated creator template footer
include '../includes/templates/footer_creator.php'; 
?>