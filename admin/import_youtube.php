<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses
check_admin_auth();

// Definisikan API Key YouTube Anda
define('YOUTUBE_API_KEY', '');

// Inisialisasi variabel
$error = '';
$message = '';
$video_data = null;

// Logika untuk mengambil data dari YouTube
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_video'])) {
    $youtube_url = trim($_POST['youtube_url']);
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $youtube_url, $match);
    $youtube_id = $match[1] ?? null;

    if ($youtube_id) {
        $api_url = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id={$youtube_id}&key=" . YOUTUBE_API_KEY;
        $response = @file_get_contents($api_url);
        $data = json_decode($response, true);
        if ($data && !empty($data['items'])) {
            $item = $data['items'][0];
            $interval = new DateInterval($item['contentDetails']['duration']);
            $duration_seconds = $interval->h * 3600 + $interval->i * 60 + $interval->s;
            $video_data = [
                'youtube_id'    => $youtube_id,
                'title'         => $item['snippet']['title'],
                'description'   => $item['snippet']['description'],
                'thumbnail_url' => $item['snippet']['thumbnails']['high']['url'],
                'duration'      => $duration_seconds
            ];
        } else {
            $error = "Gagal mengambil data video. Pastikan URL dan API Key benar.";
        }
    } else {
        $error = "URL YouTube tidak valid.";
    }
}

// Logika untuk menyimpan video ke database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_video'])) {
    $youtube_id = $_POST['youtube_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $thumbnail_url = $_POST['thumbnail_url'];
    $duration = (int)$_POST['duration'];
    $uploader_id = (int)$_POST['uploader_id'];

    if (!empty($youtube_id) && !empty($title) && !empty($uploader_id)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO videos (youtube_id, title, description, thumbnail_url, duration, uploader_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$youtube_id, $title, $description, $thumbnail_url, $duration, $uploader_id]);
            $_SESSION['message'] = "Video '".htmlspecialchars($title)."' berhasil diimpor!";
            header("Location: videos.php");
            exit();
        } catch (PDOException $e) {
            $error = ($e->errorInfo[1] == 1062) ? "Gagal: Video ini sudah ada di database." : "Error: " . $e->getMessage();
        }
    } else {
        $error = "Judul dan Kreator wajib diisi.";
        $video_data = $_POST;
    }
}

// Ambil daftar kreator untuk dropdown
$creators = $pdo->query("SELECT id, username FROM users WHERE role = 'creator'")->fetchAll();

// Menggunakan template header admin
include '../includes/templates/header_admin.php';
?>

<h1>Import Video</h1>
<p style="color: var(--text-secondary); margin-top: -15px; margin-bottom: 30px;">Impor video dari YouTube dengan cepat ke platform Anda.</p>

<?php
if (isset($_SESSION['message'])) { echo '<div class="alert success">' . htmlspecialchars($_SESSION['message']) . '</div>'; unset($_SESSION['message']); }
if ($error) { echo '<div class="alert error">' . htmlspecialchars($error) . '</div>'; }
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Langkah 1: Masukkan URL YouTube</h2>
    </div>
    <div class="content">
        <form method="POST">
            <label for="youtube_url">URL Video</label>
            <input type="text" id="youtube_url" name="youtube_url" placeholder="Contoh: https://www.youtube.com/watch?v=xxxxxxxxxxx" required>
            <button type="submit" name="fetch_video" class="btn btn-primary">Ambil Data Video</button>
        </form>
    </div>
</div>
    
<?php if ($video_data): ?>
<div class="admin-section">
    <div class="section-header">
        <h2>Langkah 2: Konfirmasi dan Edit Video</h2>
    </div>
    <div class="content">
        <form method="POST">
            <input type="hidden" name="youtube_id" value="<?= htmlspecialchars($video_data['youtube_id']) ?>">
            <input type="hidden" name="thumbnail_url" value="<?= htmlspecialchars($video_data['thumbnail_url']) ?>">
            <input type="hidden" name="duration" value="<?= $video_data['duration'] ?>">
            
            <img src="<?= htmlspecialchars($video_data['thumbnail_url']) ?>" alt="Thumbnail" style="max-width: 320px; border-radius: 8px; margin-bottom: 20px;">
            
            <label for="title">Judul Video</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($video_data['title']) ?>" required>
            
            <label for="description">Deskripsi</label>
            <textarea id="description" name="description" rows="8"><?= htmlspecialchars($video_data['description']) ?></textarea>

            <div class="ai-feature-box">
                <button type="button" id="generate-ai-btn" class="btn btn-outline">
                    <i class="fas fa-magic"></i> Generate dengan AI
                </button>
                <span id="ai-status"></span>
            </div>
            
            <label for="uploader_id">Pilih Kreator (Uploader)</label>
            <select id="uploader_id" name="uploader_id" required>
                <option value="">-- Pilih Kreator --</option>
                <?php foreach ($creators as $creator): ?>
                    <option value="<?= $creator['id'] ?>"><?= htmlspecialchars($creator['username']) ?></option>
                <?php endforeach; ?>
            </select>

            <div style="text-align: right; margin-top: 20px;">
                <button type="submit" name="save_video" class="btn btn-primary">Simpan Video ke Database</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<style>
.ai-feature-box { margin: -10px 0 20px 0; display: flex; align-items: center; gap: 15px; }
.ai-feature-box i { margin-right: 8px; }
#ai-status { font-size: 0.9em; color: var(--text-secondary); }
.alert.error { border-left: 4px solid var(--danger-color); background-color: #fef2f2; color: #991b1b; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const generateBtn = document.getElementById('generate-ai-btn');
    if (generateBtn) {
        generateBtn.addEventListener('click', async () => {
            const titleField = document.getElementById('title');
            const descField = document.getElementById('description');
            const aiStatus = document.getElementById('ai-status');

            aiStatus.innerHTML = '<i class="fas fa-spinner fa-spin"></i> AI sedang berpikir...';
            generateBtn.disabled = true;

            try {
                const response = await fetch('/api/generate_text.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ title: titleField.value, description: descField.value })
                });
                
                if (!response.ok) throw new Error('Gagal menghubungi API AI.');
                
                const result = await response.json();
                titleField.value = result.new_title;
                descField.value = result.new_description;
                aiStatus.innerHTML = '<i class="fas fa-check-circle" style="color: green;"></i> Berhasil!';
            } catch (error) {
                console.error("AI Error:", error);
                aiStatus.innerHTML = '<i class="fas fa-times-circle" style="color: red;"></i> Gagal.';
            } finally {
                generateBtn.disabled = false;
                setTimeout(() => { aiStatus.innerHTML = ''; }, 3000);
            }
        });
    }
});
</script>

<?php
// Menggunakan template footer admin
include '../includes/templates/footer_admin.php';
?>
