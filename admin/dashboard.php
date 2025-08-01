<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses halaman ini
check_admin_auth();

// Mengambil data statistik utama untuk ditampilkan di dashboard
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_videos = $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn();
$total_creators = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'creator'")->fetchColumn();
$pending_monetization = $pdo->query("SELECT COUNT(*) FROM users WHERE monetization_status = 'pending'")->fetchColumn();

// Menggunakan template header khusus admin yang baru
include '../includes/templates/header_admin.php';
?>

<h1>Dashboard</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background-color: #e5f1ff;">
            <i class="fas fa-users" style="color: #0066ff;"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">Total Pengguna</span>
            <span class="stat-value"><?= number_format($total_users) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background-color: #fff0e5;">
            <i class="fas fa-user-check" style="color: #ff8c00;"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">Total Kreator</span>
            <span class="stat-value"><?= number_format($total_creators) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background-color: #e5fff0;">
            <i class="fas fa-video" style="color: #00cc66;"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">Total Video</span>
            <span class="stat-value"><?= number_format($total_videos) ?></span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background-color: #fce5e5;">
            <i class="fas fa-hourglass-half" style="color: #cc0000;"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">Pengajuan Pending</span>
            <span class="stat-value"><?= number_format($pending_monetization) ?></span>
        </div>
    </div>
</div>

<div class="admin-section">
    <h2>Aksi Cepat</h2>
    <div class="content">
        <div class="quick-actions">
            <a href="import_youtube.php" class="action-box">
                <i class="fas fa-upload"></i>
                <span>Import Video Baru</span>
            </a>
            <a href="monetization.php" class="action-box">
                <i class="fas fa-hand-holding-dollar"></i>
                <span>Tinjau Monetisasi</span>
            </a>
            <a href="creators.php" class="action-box">
                <i class="fas fa-users-cog"></i>
                <span>Kelola Kreator</span>
            </a>
            <a href="settings.php" class="action-box">
                <i class="fas fa-cog"></i>
                <span>Buka Pengaturan</span>
            </a>
        </div>
    </div>
</div>


<style>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
}
.stat-card {
    background-color: #ffffff;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-icon-wrapper {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-shrink: 0;
}
.stat-icon-wrapper i {
    font-size: 20px;
}
.stat-info .stat-title {
    color: var(--text-secondary);
    font-size: 0.9em;
    display: block;
}
.stat-info .stat-value {
    font-size: 1.5em;
    font-weight: 700;
    color: var(--text-primary);
}
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}
.action-box {
    background-color: #f9f9f9;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    text-decoration: none;
    color: var(--text-primary);
    transition: transform 0.2s, box-shadow 0.2s;
}
.action-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.action-box i {
    font-size: 24px;
    color: var(--primary-color);
    margin-bottom: 12px;
    display: block;
}
.action-box span {
    font-weight: 500;
}
</style>

<?php
// Menggunakan template footer khusus admin
include '../includes/templates/footer_admin.php';
?>