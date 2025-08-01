<?php

/**
 * Mengambil nilai pengaturan dari database.
 *
 * @param string $key Kunci pengaturan yang ingin diambil.
 * @param PDO $pdo Objek koneksi database.
 * @param mixed $default Nilai default yang akan dikembalikan jika kunci tidak ditemukan.
 * @return mixed Nilai pengaturan atau nilai default.
 */
function get_setting($key, $pdo, $default = null) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    
    return $value !== false ? $value : $default;
}

/**
 * Memperbarui atau membuat nilai pengaturan di database.
 * Menggunakan satu query "UPSERT" yang efisien untuk mencegah error.
 *
 * @param string $key Kunci pengaturan.
 * @param string $value Nilai baru untuk pengaturan.
 * @param PDO $pdo Objek koneksi database.
 * @return bool True jika berhasil, false jika gagal.
 */
function update_setting($key, $value, $pdo) {
    // Perintah ini akan melakukan INSERT jika key belum ada,
    // atau UPDATE jika key sudah ada, secara otomatis.
    $sql = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
    
    $stmt = $pdo->prepare($sql);
    
    return $stmt->execute([$key, $value]);
}

/**
 * Memeriksa apakah pengguna yang sedang login adalah admin.
 * Jika tidak, alihkan ke halaman login.
 */
function check_admin_auth() {
    // Pastikan session sudah dimulai sebelum mengakses $_SESSION
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Gunakan path absolut untuk pengalihan yang lebih andal
        header('Location: /auth/login.php?error=admin_required');
        exit();
    }
}

/**
 * Menghitung jumlah total subscriber dari seorang kreator.
 *
 * @param int $creator_id ID dari pengguna kreator.
 * @param PDO $pdo Objek koneksi database.
 * @return int Jumlah subscriber.
 */
function get_subscriber_count($creator_id, $pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE creator_id = ?");
    $stmt->execute([$creator_id]);
    return (int) $stmt->fetchColumn();
}
/**
 * Membuat notifikasi untuk semua subscriber seorang kreator
 * saat video baru diunggah.
 */
function create_new_video_notifications($creator_id, $video_id, $video_title, $pdo) {
    // 1. Dapatkan semua ID subscriber dari kreator ini
    $stmt_subscribers = $pdo->prepare("SELECT subscriber_id FROM subscriptions WHERE creator_id = ?");
    $stmt_subscribers->execute([$creator_id]);
    $subscribers = $stmt_subscribers->fetchAll(PDO::FETCH_COLUMN);

    if (empty($subscribers)) {
        return; // Tidak ada subscriber, tidak perlu buat notifikasi
    }

    // 2. Buat pesan notifikasi
    $creator_name = $pdo->query("SELECT username FROM users WHERE id = $creator_id")->fetchColumn();
    $message = htmlspecialchars($creator_name) . " has uploaded a new video: " . htmlspecialchars($video_title);

    // 3. Masukkan notifikasi untuk setiap subscriber
    $sql = "INSERT INTO notifications (user_id, type, related_id, message) VALUES (?, 'new_video', ?, ?)";
    $stmt_insert = $pdo->prepare($sql);

    foreach ($subscribers as $subscriber_id) {
        $stmt_insert->execute([$subscriber_id, $video_id, $message]);
    }
}
/**
 * Memformat total detik menjadi format Jam:Menit:Detik yang benar.
 *
 * @param int $seconds Total detik.
 * @return string Durasi dalam format H:i:s atau i:s.
 */
function format_duration($seconds) {
    if ($seconds <= 0) {
        return '0:00';
    }
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
    } else {
        return sprintf('%d:%02d', $minutes, $secs);
    }
}
