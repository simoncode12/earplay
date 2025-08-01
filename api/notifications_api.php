<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/db_connect.php';

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    exit(json_encode(['error' => 'Login required.']));
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? null;

// Aksi untuk menandai semua notifikasi sebagai sudah dibaca
if ($action === 'mark_all_read') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$user_id]);
    echo json_encode(['status' => 'success']);
    exit();
}

// Ambil 10 notifikasi terbaru yang belum dibaca
$stmt = $pdo->prepare(
    "SELECT id, message, is_read, created_at 
     FROM notifications 
     WHERE user_id = ? 
     ORDER BY created_at DESC 
     LIMIT 10"
);
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Hitung jumlah notifikasi yang belum dibaca
$unread_count = 0;
foreach ($notifications as $notification) {
    if (!$notification['is_read']) {
        $unread_count++;
    }
}

// Kembalikan data dalam format JSON
echo json_encode([
    'notifications' => $notifications,
    'unread_count' => $unread_count
]);