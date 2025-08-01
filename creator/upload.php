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

// Definisikan API Key YouTube Anda
define('YOUTUBE_API_KEY', 'AIzaSyDLulaJ1eKOY2Ly-MdpkxG2smQF2KbkB1E'); // Ganti dengan API Key Anda

// Inisialisasi variabel
$error = '';
$video_data = null;

// Langkah 1: Ambil data dari YouTube saat URL di-submit
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
            $error = "Failed to fetch video data. Please check the URL and your API Key.";
        }
    } else {
        $error = "Invalid YouTube URL provided.";
    }
}

// Langkah 2: Simpan video ke database setelah dikonfirmasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_video'])) {
    if (!empty($_POST['youtube_id']) && !empty($_POST['title'])) {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO videos (youtube_id, title, description, thumbnail_url, duration, uploader_id) VALUES (?, ?, ?, ?, ?, ?)"
            );
            // uploader_id diambil dari session, bukan dari form
            $stmt->execute([
                $_POST['youtube_id'], 
                $_POST['title'], 
                $_POST['description'], 
                $_POST['thumbnail_url'], 
                (int)$_POST['duration'], 
                $creator_id 
            ]);
            $new_video_id = $pdo->lastInsertId();

            // Panggil fungsi untuk membuat notifikasi bagi subscriber
            create_new_video_notifications($creator_id, $new_video_id, $_POST['title'], $pdo);

            $_SESSION['creator_message'] = "Video '".htmlspecialchars($_POST['title'])."' has been successfully added!";
            header("Location: content.php");
            exit();
        } catch (PDOException $e) {
            $error = ($e->errorInfo[1] == 1062) ? "Error: This video is already on the platform." : "Database Error: " . $e->getMessage();
        }
    } else {
        $error = "Title is required to save the video.";
        $video_data = $_POST;
    }
}

// Menggunakan template header khusus kreator
include '../includes/templates/header_creator.php';
?>

<h1>Upload Video</h1>
<p style="color: var(--text-secondary); margin-top: -15px; margin-bottom: 30px;">Add a new video to your channel by providing its YouTube URL.</p>

<?php
if ($error) { echo '<div class="panel-info error" style="margin-bottom: 24px;">' . htmlspecialchars($error) . '</div>'; }
?>

<div class="creator-section">
    <h2>Step 1: Fetch Video Data</h2>
    <div class="content">
        <form method="POST">
            <label for="youtube_url">YouTube Video URL</label>
            <input type="text" id="youtube_url" name="youtube_url" placeholder="e.g., https://www.youtube.com/watch?v=xxxxxxxxxxx" required>
            <button type="submit" name="fetch_video" class="btn btn-apply-monetization">Fetch Video</button>
        </form>
    </div>
</div>
    
<?php if ($video_data): ?>
<div class="creator-section">
    <h2>Step 2: Confirm Details and Save</h2>
    <div class="content">
        <form method="POST">
            <input type="hidden" name="youtube_id" value="<?= htmlspecialchars($video_data['youtube_id']) ?>">
            <input type="hidden" name="thumbnail_url" value="<?= htmlspecialchars($video_data['thumbnail_url']) ?>">
            <input type="hidden" name="duration" value="<?= $video_data['duration'] ?>">
            
            <img src="<?= htmlspecialchars($video_data['thumbnail_url']) ?>" alt="Thumbnail" style="max-width: 320px; border-radius: 8px; margin-bottom: 20px;">
            
            <label for="title">Video Title</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($video_data['title']) ?>" required>
            
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="8"><?= htmlspecialchars($video_data['description']) ?></textarea>

            <div style="text-align: right; margin-top: 20px;">
                <button type="submit" name="save_video" class="btn btn-apply-monetization">Add Video to Channel</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php
// Menggunakan template footer khusus kreator
include '../includes/templates/footer_creator.php';
?>