<?php
header('Content-Type: application/json');
session_start();

require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Ambil data input
$video_id = filter_input(INPUT_POST, 'video_id', FILTER_VALIDATE_INT);
$new_watched_seconds = filter_input(INPUT_POST, 'watched_seconds', FILTER_VALIDATE_INT);

// Tentukan apakah penonton adalah pengguna yang terdaftar
$is_user_logged_in = isset($_SESSION['user_id']);
$user_id = $is_user_logged_in ? $_SESSION['user_id'] : null;
$session_id = session_id(); // ID unik untuk setiap pengunjung

// Jika request adalah GET (untuk melanjutkan tontonan pengguna login)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $is_user_logged_in) {
    // ... (Logika GET request tetap sama seperti sebelumnya)
    exit();
}

// Jika request adalah POST, proses progres tontonan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$video_id || $new_watched_seconds === false) { exit(); }

    try {
        // --- PERBAIKAN UTAMA: CATAT AKTIVITAS LIVE DI LUAR TRANSAKSI ---
        // Perintah ini sekarang berjalan secara independen untuk memastikan "denyut jantung" selalu tercatat.
        $live_stmt = $pdo->prepare(
            "INSERT INTO live_sessions (session_id, video_id) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE last_update = NOW()"
        );
        $live_stmt->execute([$session_id, $video_id]);

        // Memulai transaksi HANYA untuk logika reward dan statistik
        $pdo->beginTransaction();

        $db_watched_seconds = 0;
        if ($is_user_logged_in) {
            $stmt = $pdo->prepare("SELECT watched_seconds FROM watch_stats WHERE user_id = ? AND video_id = ?");
            $stmt->execute([$user_id, $video_id]);
            $db_watched_seconds = (int)$stmt->fetchColumn();
        } else {
            $last_watched_key = "video_{$video_id}_watched";
            $db_watched_seconds = (int)($_SESSION[$last_watched_key] ?? 0);
        }

        $seconds_to_reward = $new_watched_seconds - $db_watched_seconds;

        if ($seconds_to_reward > 0) {
            // ... (sisa logika untuk menghitung penghasilan kreator dan reward penonton tetap sama seperti sebelumnya) ...
            $stmt_creator = $pdo->prepare("SELECT u.id as uploader_id, u.monetization_status FROM videos v JOIN users u ON v.uploader_id = u.id WHERE v.id = ?");
            $stmt_creator->execute([$video_id]);
            $creator = $stmt_creator->fetch();
            $reward_rate = (float)get_setting('reward_rate_per_second', $pdo, 0.0000001);
            
            $is_monetized_with_ads = ($creator && $creator['monetization_status'] === 'approved');
            $is_self_watch = ($is_user_logged_in && $user_id == $creator['uploader_id']);
            
            if ($creator && !$is_monetized_with_ads && !$is_self_watch) {
                $creator_earned_amount = $seconds_to_reward * $reward_rate;
                $pdo->prepare("UPDATE users SET creator_earnings = creator_earnings + ? WHERE id = ?")->execute([$creator_earned_amount, $creator['uploader_id']]);
                $pdo->prepare("UPDATE videos SET earnings = earnings + ? WHERE id = ?")->execute([$creator_earned_amount, $video_id]);
            }

            if ($is_user_logged_in) {
                $viewer_earned_amount = $seconds_to_reward * $reward_rate;
                $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$viewer_earned_amount, $user_id]);
                
                $stat_stmt = $pdo->prepare("INSERT INTO watch_stats (user_id, video_id, watched_seconds) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE watched_seconds = ?");
                $stat_stmt->execute([$user_id, $video_id, $new_watched_seconds, $new_watched_seconds]);
            } else {
                $_SESSION["video_{$video_id}_watched"] = $new_watched_seconds;
            }
        }
        
        $pdo->commit();
        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        // Jika terjadi error pada logika reward, batalkan transaksinya
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Watch Time Update Error: ". $e->getMessage());
        http_response_code(500);
        echo json_encode(['status' => 'error']);
    }
    exit();
}