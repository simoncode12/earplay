<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Pastikan hanya admin yang bisa mengakses data ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Akses ditolak.']));
}

// Menyiapkan data untuk 7 hari terakhir
$labels = [];
$user_data = [];
$video_data = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('d M', strtotime($date));

    // Ambil jumlah pengguna baru pada tanggal tersebut
    $stmt_users = $pdo->prepare("SELECT COUNT(*) FROM users WHERE DATE(created_at) = ?");
    $stmt_users->execute([$date]);
    $user_data[] = $stmt_users->fetchColumn();

    // Ambil jumlah video baru pada tanggal tersebut
    $stmt_videos = $pdo->prepare("SELECT COUNT(*) FROM videos WHERE DATE(created_at) = ?");
    $stmt_videos->execute([$date]);
    $video_data[] = $stmt_videos->fetchColumn();
}

// Kembalikan data dalam format JSON
echo json_encode([
    'labels' => $labels,
    'users' => $user_data,
    'videos' => $video_data
]);