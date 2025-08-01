<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses halaman ini
check_admin_auth();

// Inisialisasi variabel
$error = '';
$video_data = null;
$page_title = 'Edit Video';

// Pastikan ID video ada dan valid
if (!isset($_GET['edit_id']) || !ctype_digit($_GET['edit_id'])) {
    header("Location: videos.php");
    exit();
}

$video_id = (int)$_GET['edit_id'];

// Ambil daftar semua kreator untuk dropdown
$creators = $pdo->query("SELECT id, username FROM users WHERE role = 'creator'")->fetchAll();

// Ambil daftar semua kampanye iklan yang aktif untuk dropdown
$campaigns = $pdo->query("SELECT id, name FROM ad_campaigns WHERE is_active = 1")->fetchAll();


// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $uploader_id = (int)$_POST['uploader_id'];
    // Ambil ad_campaign_id, set ke NULL jika kosong
    $ad_campaign_id = !empty($_POST['ad_campaign_id']) ? (int)$_POST['ad_campaign_id'] : null;

    if (empty($title) || empty($uploader_id)) {
        $error = "Judul dan Kreator wajib diisi.";
        // Isi kembali data jika ada error
        $video_data = $_POST;
        $video_data['id'] = $video_id;
    } else {
        try {
            // Query UPDATE sekarang mencakup ad_campaign_id
            $stmt = $pdo->prepare(
                "UPDATE videos SET title = ?, description = ?, uploader_id = ?, ad_campaign_id = ? WHERE id = ?"
            );
            $stmt->execute([$title, $description, $uploader_id, $ad_campaign_id, $video_id]);
            
            $_SESSION['message'] = "Data video berhasil diperbarui.";
            header("Location: videos.php");
            exit();
        } catch (PDOException $e) {
            $error = "Terjadi kesalahan database: " . $e->getMessage();
            $video_data = $_POST;
            $video_data['id'] = $video_id;
        }
    }
} else {
    // Ambil data video yang akan diedit dari database
    $stmt = $pdo->prepare("SELECT id, title, description, uploader_id, ad_campaign_id FROM videos WHERE id = ?");
    $stmt->execute([$video_id]);
    $video_data = $stmt->fetch();
}

// Jika video tidak ditemukan, alihkan kembali
if (!$video_data) {
    header("Location: videos.php");
    exit();
}

// Menggunakan template header admin
include '../includes/templates/header_admin.php';
?>

<h1><?= htmlspecialchars($page_title) ?></h1>

<?php
if ($error) {
    echo '<div class="alert error">' . htmlspecialchars($error) . '</div>';
}
?>

<div class="admin-section">
    <div class="content">
        <form method="POST">
            <label for="title">Judul Video</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($video_data['title']) ?>" required>

            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="8"><?= htmlspecialchars($video_data['description']) ?></textarea>
            
            <label for="uploader_id">Kreator (Uploader)</label>
            <select id="uploader_id" name="uploader_id" required>
                <option value="">-- Pilih Kreator --</option>
                <?php foreach ($creators as $creator): ?>
                    <option value="<?= $creator['id'] ?>" <?= ($creator['id'] == $video_data['uploader_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($creator['username']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="ad_campaign_id">Kampanye Iklan</label>
            <select name="ad_campaign_id" id="ad_campaign_id">
                <option value="">-- Tanpa Iklan --</option>
                <?php foreach($campaigns as $campaign): ?>
                    <option value="<?= $campaign['id'] ?>" <?= ($video_data['ad_campaign_id'] == $campaign['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($campaign['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small class="form-hint">Pilih kampanye iklan yang akan tayang di video ini (hanya berlaku jika kreator sudah dimonetisasi).</small>


            <div style="text-align: right; margin-top: 20px;">
                <a href="videos.php" class="btn btn-outline" style="margin-right: 10px;">Batal</a>
                <button type="submit" class="btn btn-primary">Update Video</button>
            </div>
        </form>
    </div>
</div>

<style>
.form-hint {
    display: block;
    margin-top: -15px;
    margin-bottom: 20px;
    font-size: 0.85em;
    color: var(--text-secondary);
}
</style>

<?php
// Menggunakan template footer admin
include '../includes/templates/footer_admin.php';
?>