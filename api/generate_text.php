<?php
header('Content-Type: application/json');
session_start();
require_once '../includes/db_connect.php'; // Untuk keamanan di masa depan
require_once '../includes/functions.php'; // Untuk keamanan di masa depan

// Pastikan hanya admin yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit(json_encode(['error' => 'Akses ditolak.']));
}

// Ambil data JSON yang dikirim dari frontend
$input = json_decode(file_get_contents('php://input'), true);
$original_title = $input['title'] ?? '';
$original_desc = $input['description'] ?? '';

// --- SIMULASI PROSES AI ---
// Di aplikasi nyata, bagian ini akan memanggil API AI eksternal.
// Di sini, kita akan memanipulasi teks secara sederhana.

// 1. Membuat Judul Baru yang Unik
$title_prefixes = ["Review Jujur:", "Wajib Tonton!", "Terungkap!", "Rahasia di Balik", "Analisis Mendalam:"];
$random_prefix = $title_prefixes[array_rand($title_prefixes)];
$new_title = $random_prefix . " " . ucwords(strtolower($original_title));

// 2. Membuat Deskripsi Baru yang Lebih Rapi
$desc_intro = "Di video kali ini, kita akan mengupas tuntas tentang '" . $original_title . "'. Sebuah tayangan yang akan mengubah cara pandang Anda.";
$new_desc = $desc_intro . "\n\n" . "--- Rangkuman ---\n" . substr(str_replace("\n", " ", $original_desc), 0, 200) . "...\n\n" . "Jangan lupa untuk Like, Share, dan Subscribe untuk konten menarik lainnya!";

// Jeda simulasi untuk membuatnya terasa seperti proses AI
sleep(1); 

// Kembalikan hasil dalam format JSON
echo json_encode([
    'new_title' => $new_title,
    'new_description' => $new_desc
]);