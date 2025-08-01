<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses halaman ini
check_admin_auth();

// Menangani pembaruan pengaturan saat form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Daftar semua kunci pengaturan yang mungkin di-submit dari form ini
    $settings_keys = [
        'reward_rate_per_second',
        'min_subscribers_for_monetization',
        'auto_approve_monetization',
        'rtb_endpoint_url',
        'default_ad_zone_id'
    ];

    // Loop melalui setiap kunci dan update nilainya jika ada di form
    foreach ($settings_keys as $key) {
        if (isset($_POST[$key])) {
            update_setting($key, $_POST[$key], $pdo);
        }
    }

    // Set pesan sukses dan redirect untuk mencegah re-submit
    $_SESSION['message'] = "Pengaturan berhasil diperbarui!";
    header("Location: settings.php");
    exit();
}

// Mengambil nilai pengaturan saat ini untuk ditampilkan di form
$reward_rate = get_setting('reward_rate_per_second', $pdo, '0.0000001');
$min_subscribers = get_setting('min_subscribers_for_monetization', $pdo, 100);
$auto_approve = get_setting('auto_approve_monetization', $pdo, 'off');
$rtb_endpoint_url = get_setting('rtb_endpoint_url', $pdo, '');
$default_ad_zone_id = get_setting('default_ad_zone_id', $pdo, '');

// Menggunakan template header khusus admin
include '../includes/templates/header_admin.php';
?>

<h1>Pengaturan</h1>

<?php
// Menampilkan pesan sukses jika ada dari session
if (isset($_SESSION['message'])) {
    echo '<div class="alert success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
?>

<form method="POST">
    <div class="admin-section">
        <div class="section-header">
            <h2>Pengaturan Monetisasi & Insentif</h2>
        </div>
        <div class="content">
            <label for="min_subscribers">Syarat Minimal Subscriber untuk Monetisasi</label>
            <input type="number" id="min_subscribers" name="min_subscribers_for_monetization" value="<?= htmlspecialchars($min_subscribers) ?>">
            <small class="form-hint">Jumlah subscriber yang harus dimiliki kreator sebelum bisa mengajukan monetisasi.</small>

            <label for="auto_approve">Persetujuan Monetisasi Otomatis</label>
            <select id="auto_approve" name="auto_approve_monetization">
                <option value="on" <?= $auto_approve == 'on' ? 'selected' : '' ?>>Aktif</option>
                <option value="off" <?= $auto_approve == 'off' ? 'selected' : '' ?>>Nonaktif (Perlu Tinjauan Manual)</option>
            </select>
            <small class="form-hint">Jika aktif, pengajuan akan otomatis disetujui saat syarat subscriber terpenuhi.</small>

            <label for="reward_rate">Rate Reward per Detik</label>
            <input type="text" id="reward_rate" name="reward_rate_per_second" value="<?= htmlspecialchars($reward_rate) ?>">
            <small class="form-hint">Rate ini berlaku untuk reward penonton dan insentif bagi kreator yang belum dimonetisasi.</small>
        </div>
    </div>

    <div class="admin-section">
        <div class="section-header">
            <h2>Pengaturan Iklan RTB</h2>
        </div>
        <div class="content">
            <label for="rtb_endpoint_url">Endpoint URL Ad Server RTB</label>
            <input type="text" id="rtb_endpoint_url" name="rtb_endpoint_url" value="<?= htmlspecialchars($rtb_endpoint_url) ?>" placeholder="http://rtb.svradv.com/rtb-handler.php?key=...">
            <small class="form-hint">URL dari ad server Anda yang akan dihubungi oleh server ini.</small>

            <label for="default_ad_zone_id">Default Ad Zone ID</label>
            <input type="text" id="default_ad_zone_id" name="default_ad_zone_id" value="<?= htmlspecialchars($default_ad_zone_id) ?>">
            <small class="form-hint">Zone ID umum yang digunakan untuk video dari kreator yang belum memiliki Zone ID spesifik.</small>
        </div>
    </div>
    
    <div style="text-align: right;">
        <button type="submit" class="btn btn-primary">Simpan Semua Pengaturan</button>
    </div>
</form>

<style>
.form-hint {
    display: block;
    margin-top: -15px;
    margin-bottom: 20px;
    font-size: 0.85em;
    color: var(--text-secondary);
}
</style>

<?php
// Menggunakan template footer khusus admin
include '../includes/templates/footer_admin.php';
?>