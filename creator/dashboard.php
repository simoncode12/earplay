<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Pastikan hanya kreator yang bisa mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: /auth/login.php?error=creator_only');
    exit();
}

$creator_id = $_SESSION['user_id'];

// Ambil data kreator dari database
$stmt = $pdo->prepare("SELECT username, creator_earnings, monetization_status, revenue_share FROM users WHERE id = ?");
$stmt->execute([$creator_id]);
$creator = $stmt->fetch();

// Ambil data pendukung
$subscriber_count = get_subscriber_count($creator_id, $pdo);
$min_subscribers_needed = (int)get_setting('min_subscribers_for_monetization', $pdo, 100);
$progress_percentage = $min_subscribers_needed > 0 ? min(100, ($subscriber_count / $min_subscribers_needed) * 100) : 0;

// Logika untuk menampilkan pendapatan iklan
if ($creator['monetization_status'] === 'approved') {
    $revenue_share_decimal = (int)($creator['revenue_share'] ?? 55) / 100;
    $stmt_ad_rev = $pdo->prepare(
        "SELECT SUM(revenue_generated) * ? as total_ad_earnings 
         FROM ad_impressions WHERE creator_id = ?"
    );
    $stmt_ad_rev->execute([$revenue_share_decimal, $creator_id]);
    $ad_earnings = $stmt_ad_rev->fetchColumn();
}


// Menggunakan template header khusus kreator
include '../includes/templates/header_creator.php';
?>

<h1>Dashboard</h1>

<div class="stats-grid-creator">
    <?php if ($creator['monetization_status'] === 'approved'): ?>
        <div class="stat-card-creator">
            <h3>Estimasi Pendapatan Iklan</h3>
            <p class="stat-value">$<?= number_format((float)($ad_earnings ?? 0), 8) ?></p>
            <small style="color: var(--text-secondary);">Bagian Anda adalah <?= (int)($creator['revenue_share'] ?? 55) ?>% dari total.</small>
        </div>
        <div class="stat-card-creator">
            <h3>Insentif (Terkumpul Sebelumnya)</h3>
            <p class="stat-value">$<?= number_format((float)$creator['creator_earnings'], 8) ?></p>
        </div>
    <?php else: ?>
        <div class="stat-card-creator">
            <h3>Insentif Terkumpul (per Detik)</h3>
            <p class="stat-value">$<?= number_format((float)$creator['creator_earnings'], 8) ?></p>
        </div>
    <?php endif; ?>

    <div class="stat-card-creator">
        <h3>Subscribers</h3>
        <p class="stat-value"><?= number_format($subscriber_count) ?></p>
    </div>
</div>
    
<div class="analytics-link-box">
    <a href="analytics.php" class="btn-analytics">
        Lihat Analitik Channel Lengkap 
        <span style="font-size: 0.8em; vertical-align: middle;">→</span>
    </a>
</div>

<div class="monetization-panel">
    <h2>Monetisasi Channel</h2>

    <?php if ($creator['monetization_status'] === 'approved'): ?>
        <div class="panel-info success">Selamat! Channel Anda telah dimonetisasi dan sekarang menghasilkan pendapatan melalui iklan.</div>
    
    <?php elseif ($creator['monetization_status'] === 'pending'): ?>
        <div class="panel-info warning">Pengajuan Anda sedang ditinjau oleh tim kami. Anda akan diberitahu setelah prosesnya selesai.</div>

    <?php elseif ($creator['monetization_status'] === 'rejected'): ?>
        <div class="panel-info error">Pengajuan Anda sebelumnya ditolak. Silakan periksa kembali syarat & ketentuan dan coba lagi nanti.</div>
    
    <?php else: // Status 'not_applied' ?>
        <div class="monetization-requirement">
            <h3>Tinggal selangkah lagi untuk menghasilkan uang</h3>
            <p style="color: var(--text-secondary);">Penuhi persyaratan di bawah ini untuk dapat mengajukan permohonan monetisasi berbasis iklan.</p>
            
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: <?= $progress_percentage ?>%;"></div>
            </div>
            <p class="progress-label"><strong><?= number_format($subscriber_count) ?></strong> / <?= number_format($min_subscribers_needed) ?> subscribers</p>

            <?php if ($subscriber_count >= $min_subscribers_needed): ?>
                <form action="apply_monetization.php" method="POST" style="margin-top:20px;">
                    <button type="submit" class="btn btn-apply-monetization">Ajukan Sekarang</button>
                </form>
            <?php else: ?>
                <button class="btn btn-apply-monetization" disabled style="margin-top:20px;">Ajukan Sekarang</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.analytics-link-box {
    margin: -4px 0 24px 0;
}
.btn-analytics {
    display: inline-block;
    padding: 10px 16px;
    background-color: var(--bg-card);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
    border-radius: 4px;
    font-weight: 500;
    transition: background-color 0.2s;
    text-decoration: none;
}
.btn-analytics:hover {
    background-color: #f2f2f2;
}
</style>

<?php 
// Menggunakan template footer khusus kreator
include '../includes/templates/footer_creator.php'; 
?>