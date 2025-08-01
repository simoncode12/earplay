<?php
// api/track_ad.php
header('Content-Type: application/json');
session_start();
require_once '../includes/db_connect.php';

// Ambil data JSON yang dikirim dari JavaScript
$data = json_decode(file_get_contents('php://input'), true);
$video_id = (int)($data['video_id'] ?? 0);
$campaign_id = (int)($data['campaign_id'] ?? 0);
// Ambil pendapatan aktual dari pemutar video
$revenue = (float)($data['revenue'] ?? 0); 

// Lakukan pencatatan hanya jika semua data penting ada
if ($video_id > 0 && $revenue > 0) {
    // Ambil ID kreator dari video
    $stmt = $pdo->prepare("SELECT uploader_id FROM videos WHERE id = ?");
    $stmt->execute([$video_id]);
    $creator_id = $stmt->fetchColumn();

    if ($creator_id) {
        // Simpan catatan ke tabel impresi dengan pendapatan aktual
        $insert_stmt = $pdo->prepare(
            "INSERT INTO ad_impressions (video_id, creator_id, ad_campaign_id, revenue_generated) 
             VALUES (?, ?, ?, ?)"
        );
        // campaign_id mungkin 0 jika dari RTB, itu tidak masalah
        $insert_stmt->execute([$video_id, $creator_id, $campaign_id ?: null, $revenue]);
        
        echo json_encode(['status' => 'success']);
        exit();
    }
}

// Jika data tidak lengkap, kirim respons error
echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);