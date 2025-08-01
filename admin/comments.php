<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses
check_admin_auth();

// Logika untuk menghapus komentar saat form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $comment_to_delete = (int)$_POST['delete_comment_id'];
    
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_to_delete]);
    
    $_SESSION['message'] = "Komentar berhasil dihapus.";
    header("Location: comments.php");
    exit();
}

// Ambil semua data komentar, gabungkan dengan data pengguna dan video
$comments = $pdo->query(
    "SELECT c.id, c.comment_text, c.created_at, u.username, v.title as video_title
     FROM comments c
     JOIN users u ON c.user_id = u.id
     JOIN videos v ON c.video_id = v.id
     ORDER BY c.created_at DESC"
)->fetchAll();

// Menggunakan template header admin
include '../includes/templates/header_admin.php';
?>

<h1>Manajemen Komentar</h1>

<?php
// Menampilkan pesan notifikasi jika ada
if (isset($_SESSION['message'])) {
    echo '<div class="alert success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Semua Komentar di Platform</h2>
    </div>
    <div class="content">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Komentar</th>
                    <th>Pengguna</th>
                    <th>Di Video</th>
                    <th>Tanggal</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($comments) > 0): ?>
                    <?php foreach ($comments as $comment): ?>
                    <tr>
                        <td style="max-width: 400px;"><?= nl2br(htmlspecialchars($comment['comment_text'])) ?></td>
                        <td><strong><?= htmlspecialchars($comment['username']) ?></strong></td>
                        <td><?= htmlspecialchars($comment['video_title']) ?></td>
                        <td><?= date('d M Y, H:i', strtotime($comment['created_at'])) ?></td>
                        <td class="action-buttons" style="text-align: right;">
                            <form method="POST" onsubmit="return confirm('Anda yakin ingin menghapus komentar ini secara permanen?');" style="display:inline;">
                                <input type="hidden" name="delete_comment_id" value="<?= $comment['id'] ?>">
                                <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 20px;">Belum ada komentar di platform.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Menggunakan template footer admin
include '../includes/templates/footer_admin.php';
?>