<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/db_connect.php';

// Pastikan pengguna sudah login untuk mengelola playlist
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => 'Login required.']));
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? $_GET['action'] ?? null;

try {
    // Aksi untuk mengambil semua playlist milik pengguna
    if ($action === 'get_playlists') {
        $video_id = (int)($_GET['video_id'] ?? 0);
        $stmt = $pdo->prepare(
            "SELECT p.id, p.title, 
                    (SELECT COUNT(*) FROM playlist_videos pv WHERE pv.playlist_id = p.id AND pv.video_id = ?) as is_video_in
             FROM playlists p 
             WHERE p.user_id = ?"
        );
        $stmt->execute([$video_id, $user_id]);
        echo json_encode($stmt->fetchAll());
        exit();
    }

    // Aksi untuk menambah atau menghapus video dari playlist
    if ($action === 'toggle_video') {
        $playlist_id = (int)$data['playlist_id'];
        $video_id = (int)$data['video_id'];

        // Cek apakah video sudah ada di playlist
        $check_stmt = $pdo->prepare("SELECT id FROM playlist_videos WHERE playlist_id = ? AND video_id = ?");
        $check_stmt->execute([$playlist_id, $video_id]);

        if ($check_stmt->fetch()) {
            // Jika sudah ada, hapus
            $pdo->prepare("DELETE FROM playlist_videos WHERE playlist_id = ? AND video_id = ?")->execute([$playlist_id, $video_id]);
        } else {
            // Jika belum ada, tambahkan
            $pdo->prepare("INSERT INTO playlist_videos (playlist_id, video_id) VALUES (?, ?)")->execute([$playlist_id, $video_id]);
        }
        echo json_encode(['status' => 'success']);
        exit();
    }

    // Aksi untuk membuat playlist baru
    if ($action === 'create_playlist') {
        $title = trim($data['title']);
        if (!empty($title)) {
            $stmt = $pdo->prepare("INSERT INTO playlists (user_id, title) VALUES (?, ?)");
            $stmt->execute([$user_id, $title]);
            echo json_encode(['status' => 'success', 'new_playlist_id' => $pdo->lastInsertId(), 'title' => $title]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Title cannot be empty.']);
        }
        exit();
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}