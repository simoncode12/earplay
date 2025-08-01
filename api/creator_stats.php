<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Pastikan hanya kreator yang login yang bisa mengakses data ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    http_response_code(403);
    exit(json_encode(['error' => 'Authentication required.']));
}

$creator_id = $_SESSION['user_id'];
$reward_rate = (float)get_setting('reward_rate_per_second', $pdo, 0.0000001);

// Query untuk mengambil total detik tonton untuk SETIAP video milik kreator
$stmt = $pdo->prepare(
    "SELECT v.id, COALESCE(SUM(ws.watched_seconds), 0) as total_watched_seconds
     FROM videos v
     LEFT JOIN watch_stats ws ON v.id = ws.video_id
     WHERE v.uploader_id = ?
     GROUP BY v.id"
);
$stmt->execute([$creator_id]);
$videos_stats = $stmt->fetchAll();

// Siapkan array untuk menampung data penghasilan
$earnings_data = [];

foreach ($videos_stats as $stat) {
    // Hitung penghasilan berdasarkan total detik tonton
    $earnings = $stat['total_watched_seconds'] * $reward_rate;
    // Format angka dan tambahkan ke array dengan key video_id
    $earnings_data[$stat['id']] = number_format($earnings, 8);
}

// Kembalikan data dalam format JSON
echo json_encode($earnings_data);