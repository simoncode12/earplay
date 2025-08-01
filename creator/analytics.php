<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Pastikan hanya kreator yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: /auth/login.php?error=creator_only');
    exit();
}

$creator_id = $_SESSION['user_id'];

// --- PENGAMBILAN DATA UNTUK ANALITIK KREATOR ---

// 1. Statistik Utama Kreator
$stmt_stats = $pdo->prepare(
    "SELECT 
        COUNT(*) as total_videos, 
        COALESCE(SUM(views), 0) as total_views, 
        (SELECT COALESCE(SUM(watched_seconds), 0) FROM watch_stats WHERE video_id IN (SELECT id FROM videos WHERE uploader_id = ?)) as total_seconds
     FROM videos WHERE uploader_id = ?"
);
$stmt_stats->execute([$creator_id, $creator_id]);
$stats = $stmt_stats->fetch();

$total_watch_hours = $stats['total_seconds'] ? $stats['total_seconds'] / 3600 : 0;
$subscriber_count = get_subscriber_count($creator_id, $pdo);

// 2. Video Terpopuler Milik Kreator (berdasarkan views)
$stmt_top_videos = $pdo->prepare(
    "SELECT title, views, created_at
     FROM videos
     WHERE uploader_id = ?
     ORDER BY views DESC
     LIMIT 5"
);
$stmt_top_videos->execute([$creator_id]);
$top_videos = $stmt_top_videos->fetchAll();
    
// Menggunakan template header khusus kreator
include '../includes/templates/header_creator.php';
?>

<h1>Analitik Channel</h1>

<div class="stats-grid-creator">
    <div class="stat-card-creator">
        <h3>Total Jam Tonton</h3>
        <p class="stat-value"><?= number_format($total_watch_hours, 2) ?></p>
    </div>
    <div class="stat-card-creator">
        <h3>Total Penayangan (Views)</h3>
        <p class="stat-value"><?= number_format((int)$stats['total_views']) ?></p>
    </div>
    <div class="stat-card-creator">
        <h3>Subscribers</h3>
        <p class="stat-value"><?= number_format($subscriber_count) ?></p>
    </div>
    <div class="stat-card-creator">
        <h3>Total Video</h3>
        <p class="stat-value"><?= number_format((int)$stats['total_videos']) ?></p>
    </div>
</div>

<div class="creator-section">
    <h2>Video Terpopuler Anda (Berdasarkan Penayangan)</h2>
    <div class="content">
        <table class="creator-table">
            <thead>
                <tr>
                    <th>Judul Video</th>
                    <th style="text-align: right;">Views</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($top_videos) > 0): ?>
                    <?php foreach ($top_videos as $video): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($video['title']) ?></strong>
                            <br>
                            <small style="color: #606060;">Dipublikasikan: <?= date('d M Y', strtotime($video['created_at'])) ?></small>
                        </td>
                        <td style="text-align: right; font-weight: 500;"><?= number_format($video['views']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center; padding: 20px;">Anda belum memiliki video untuk dianalisis.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.creator-table {
    width: 100%;
    border-collapse: collapse;
}
.creator-table th, .creator-table td {
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
    text-align: left;
}
.creator-table tr:last-child td {
    border-bottom: none;
}
.creator-table th {
    color: var(--text-secondary);
    font-weight: 500;
    font-size: 0.9em;
    text-transform: uppercase;
}
</style>

<?php 
// Menggunakan template footer khusus kreator
include '../includes/templates/footer_creator.php'; 
?>