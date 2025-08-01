<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Pastikan hanya kreator yang login yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    exit('Akses ditolak.');
}

$creator_id = $_SESSION['user_id'];

// Ambil pengaturan dari admin
$min_subscribers_needed = (int)get_setting('min_subscribers_for_monetization', $pdo, 100);
$auto_approve = get_setting('auto_approve_monetization', $pdo, 'off');
$subscriber_count = get_subscriber_count($creator_id, $pdo);

// Keamanan: Cek kembali apakah kreator benar-benar memenuhi syarat
if ($subscriber_count >= $min_subscribers_needed) {
    
    // Cek apakah persetujuan otomatis aktif
    if ($auto_approve === 'on') {
        // Langsung setujui monetisasi
        $new_status = 'approved';
    } else {
        // Ubah status menjadi 'pending' untuk ditinjau admin
        $new_status = 'pending';
    }

    $stmt = $pdo->prepare("UPDATE users SET monetization_status = ? WHERE id = ? AND monetization_status = 'not_applied'");
    $stmt->execute([$new_status, $creator_id]);

}

// Alihkan kembali ke dasbor kreator
header('Location: dashboard.php');
exit();