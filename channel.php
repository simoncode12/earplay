<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Ambil ID kreator dari URL
$creator_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$creator_id) {
    die("Creator not found.");
}

// Ambil data profil kreator
$stmt_creator = $pdo->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'creator'");
$stmt_creator->execute([$creator_id]);
$creator = $stmt_creator->fetch();

if (!$creator) {
    die("This creator does not exist.");
}

// Ambil semua video milik kreator ini
$stmt_videos = $pdo->prepare("SELECT youtube_id, title, views FROM videos WHERE uploader_id = ? ORDER BY created_at DESC");
$stmt_videos->execute([$creator_id]);
$videos = $stmt_videos->fetchAll();

// Hitung jumlah subscriber
$subscriber_count = get_subscriber_count($creator_id, $pdo);

// Cek apakah pengguna yang sedang login sudah subscribe
$is_subscribed = false;
if (isset($_SESSION['user_id'])) {
    $stmt_sub = $pdo->prepare("SELECT id FROM subscriptions WHERE subscriber_id = ? AND creator_id = ?");
    $stmt_sub->execute([$_SESSION['user_id'], $creator_id]);
    $is_subscribed = $stmt_sub->fetch() ? true : false;
}

include 'includes/templates/header.php';
?>

<div class="channel-container">
    <div class="channel-header">
        <div class="channel-avatar"><?= strtoupper(substr($creator['username'], 0, 1)) ?></div>
        <div class="channel-info">
            <h1 class="channel-name"><?= htmlspecialchars($creator['username']) ?></h1>
            <span class="channel-stats"><?= number_format($subscriber_count) ?> subscribers • <?= count($videos) ?> videos</span>
        </div>
        <div class="channel-actions">
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $creator_id): ?>
                <button id="subscribe-btn-channel" class="subscribe-btn <?= $is_subscribed ? 'subscribed' : '' ?>" data-creator-id="<?= $creator['id'] ?>">
                    <?= $is_subscribed ? '✓ Subscribed' : 'Subscribe' ?>
                </button>
            <?php elseif (!isset($_SESSION['user_id'])): ?>
                 <a href="/auth/login.php" class="subscribe-btn">Subscribe</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="channel-content">
        <h2 class="section-title">Uploads</h2>
        <div class="video-grid">
            <?php if (count($videos) > 0): ?>
                <?php foreach ($videos as $video): ?>
                    <a href="videos/watch.php?v=<?= htmlspecialchars($video['youtube_id']) ?>" class="video-card">
                        <img src="https://i.ytimg.com/vi/<?= htmlspecialchars($video['youtube_id']) ?>/mqdefault.jpg" alt="Thumbnail" class="video-thumbnail">
                        <div class="video-card-info">
                            <h3 class="video-card-title"><?= htmlspecialchars($video['title']) ?></h3>
                            <span class="video-card-views"><?= number_format($video['views']) ?> views</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>This creator has not uploaded any videos yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const subscribeBtn = document.getElementById('subscribe-btn-channel');
    if (subscribeBtn) {
        subscribeBtn.addEventListener('click', async () => {
            const creatorId = subscribeBtn.dataset.creatorId;
            try {
                const response = await fetch('api/interact.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'subscribe', creator_id: creatorId })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    subscribeBtn.classList.toggle('subscribed');
                    subscribeBtn.textContent = subscribeBtn.classList.contains('subscribed') ? '✓ Subscribed' : 'Subscribe';
                    // Update subscriber count (opsional, butuh query tambahan)
                }
            } catch (error) {
                console.error('Subscribe action failed:', error);
            }
        });
    }
});
</script>


<?php include 'includes/templates/footer.php'; ?>