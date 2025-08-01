<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Pastikan hanya kreator yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: /auth/login.php?error=creator_only');
    exit();
}

$creator_id = $_SESSION['user_id'];
$error = '';

// Pastikan ID video ada dan valid
if (!isset($_GET['edit_id']) || !ctype_digit($_GET['edit_id'])) {
    header("Location: content.php");
    exit();
}
$video_id = (int)$_GET['edit_id'];

// Ambil data video untuk memastikan video ini milik kreator yang sedang login
$stmt = $pdo->prepare("SELECT id, title, description FROM videos WHERE id = ? AND uploader_id = ?");
$stmt->execute([$video_id, $creator_id]);
$video_data = $stmt->fetch();

// Jika video tidak ditemukan atau bukan milik kreator ini, alihkan
if (!$video_data) {
    header("Location: content.php");
    exit();
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (empty($title)) {
        $error = "Judul video tidak boleh kosong.";
        $video_data['title'] = $title;
        $video_data['description'] = $description;
    } else {
        try {
            $update_stmt = $pdo->prepare("UPDATE videos SET title = ?, description = ? WHERE id = ? AND uploader_id = ?");
            $update_stmt->execute([$title, $description, $video_id, $creator_id]);
            
            $_SESSION['creator_message'] = "Video berhasil diperbarui.";
            header("Location: content.php");
            exit();
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan saat menyimpan data.";
        }
    }
}

// Gunakan template header khusus kreator
include '../includes/templates/header_creator.php';
?>

<h1>Edit Video</h1>

<?php if ($error): ?>
    <div class="panel-info error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="creator-section">
    <div class="content">
        <form method="POST">
            <label for="title">Judul Video</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($video_data['title']) ?>" required>

            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="10"><?= htmlspecialchars($video_data['description']) ?></textarea>
            
            <div style="text-align: right; margin-top: 20px;">
                <a href="content.php" class="btn btn-outline" style="margin-right: 10px;">Batal</a>
                <button type="submit" class="btn btn-apply-monetization">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<?php 
// Gunakan template footer khusus kreator
include '../includes/templates/footer_creator.php'; 
?>