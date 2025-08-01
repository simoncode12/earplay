<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses
check_admin_auth();

// Logika untuk menghapus video
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_video_id'])) {
    $video_to_delete = (int)$_POST['delete_video_id'];
    $stmt = $pdo->prepare("DELETE FROM videos WHERE id = ?");
    $stmt->execute([$video_to_delete]);
    $_SESSION['message'] = "Video berhasil dihapus.";
    header("Location: videos.php");
    exit();
}

// Ambil semua data video
$videos = $pdo->query(
    "SELECT v.id, v.youtube_id, v.thumbnail_url, v.title, v.views, v.created_at, u.username as uploader_name
     FROM videos v
     LEFT JOIN users u ON v.uploader_id = u.id
     ORDER BY v.created_at DESC"
)->fetchAll();

// Menggunakan template header admin
include '../includes/templates/header_admin.php';
?>

<h1>Manajemen Konten Video</h1>

<?php
if (isset($_SESSION['message'])) {
    echo '<div class="alert success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Semua Video di Platform</h2>
        <a href="import_youtube.php" class="btn btn-primary">Import Video Baru</a>
    </div>
    <div class="content">
        <table class="admin-table content-table">
            <thead>
                <tr>
                    <th>Thumbnail</th>
                    <th>Judul Video</th>
                    <th>Kreator</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($videos) > 0): ?>
                    <?php foreach ($videos as $video): ?>
                    <tr>
                        <td>
                            <img src="<?= htmlspecialchars($video['thumbnail_url']) ?>" alt="Thumbnail" class="content-thumbnail">
                        </td>
                        <td>
                            <a href="/videos/watch.php?v=<?= htmlspecialchars($video['youtube_id']) ?>" target="_blank" class="video-title-link">
                                <?= htmlspecialchars($video['title']) ?>
                            </a>
                            <small class="video-meta-info">Views: <?= number_format($video['views']) ?> | <?= date('d M Y', strtotime($video['created_at'])) ?></small>
                        </td>
                        <td><?= htmlspecialchars($video['uploader_name'] ?: 'N/A') ?></td>
                        <td class="action-buttons" style="text-align: right;">
                            <a href="video_form.php?edit_id=<?= $video['id'] ?>" class="btn-edit"><i class="fas fa-pencil-alt"></i></a>
                            
                            <form method="POST" onsubmit="return confirm('Anda yakin ingin menghapus video ini secara permanen?');" style="display:inline;">
                                <input type="hidden" name="delete_video_id" value="<?= $video['id'] ?>">
                                <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 20px;">Belum ada video di platform.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.content-thumbnail { width: 120px; height: 67px; object-fit: cover; border-radius: 4px; }
.video-title-link { font-weight: 500; color: var(--text-primary); text-decoration: none; }
.video-title-link:hover { text-decoration: underline; color: var(--primary-color); }
.video-meta-info { display: block; font-size: 0.85em; color: var(--text-secondary); margin-top: 4px; }
</style>

<?php
// Menggunakan template footer admin
include '../includes/templates/footer_admin.php';
?>