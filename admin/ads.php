<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';
check_admin_auth();

// Logika untuk menambah/mengedit kampanye
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_campaign'])) {
    $name = $_POST['name'];
    $campaign_type = $_POST['campaign_type'];
    $ad_code = $_POST['ad_code']; // Dulu vast_tag, sekarang lebih umum
    $cpm_rate = (float)$_POST['cpm_rate'];
    
    $stmt = $pdo->prepare("INSERT INTO ad_campaigns (name, campaign_type, vast_tag, cpm_rate) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $campaign_type, $ad_code, $cpm_rate]);
    $_SESSION['message'] = "Kampanye iklan baru berhasil ditambahkan.";
    header("Location: ads.php");
    exit();
}

$campaigns = $pdo->query("SELECT * FROM ad_campaigns ORDER BY id DESC")->fetchAll();

include '../includes/templates/header_admin.php';
?>

<h1>Manajemen Iklan</h1>

<?php
if (isset($_SESSION['message'])) {
    echo '<div class="alert success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
?>

<div class="admin-grid">
    <div class="admin-section">
        <div class="section-header">
            <h2><i class="fas fa-plus-circle"></i> Tambah Kampanye Baru</h2>
        </div>
        <div class="content">
            <form method="POST">
                <input type="hidden" name="save_campaign" value="1">
                
                <label for="name">Nama Kampanye</label>
                <input type="text" id="name" name="name" required placeholder="Contoh: Iklan Banner Utama">

                <label for="campaign_type">Jenis Kampanye</label>
                <select name="campaign_type" id="campaign_type">
                    <option value="video">Iklan Video (VAST Tag)</option>
                    <option value="banner_display">Iklan Banner Display (di Samping)</option>
                </select>

                <label for="ad_code">Kode Iklan / VAST Tag</label>
                <textarea name="ad_code" id="ad_code" rows="5" required placeholder="Masukkan URL VAST Tag atau kode iklan HTML/JS"></textarea>

                <label for="cpm_rate">CPM Rate ($)</label>
                <input type="text" id="cpm_rate" name="cpm_rate" placeholder="Contoh: 1.50 (untuk $1.5 per 1000 tayangan)">
                
                <div style="text-align: right; margin-top: 10px;">
                    <button type="submit" class="btn btn-primary">Simpan Kampanye</button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-section">
        <div class="section-header">
            <h2><i class="fas fa-list-ul"></i> Daftar Kampanye Iklan</h2>
        </div>
        <div class="content">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama Kampanye</th>
                        <th>Jenis</th>
                        <th style="text-align: right;">CPM</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($campaigns) > 0): ?>
                        <?php foreach($campaigns as $campaign): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($campaign['name']) ?></strong></td>
                            <td>
                                <span class="role-badge type-<?= str_replace('_', '-', $campaign['campaign_type']) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $campaign['campaign_type'])) ?>
                                </span>
                            </td>
                            <td style="text-align: right;">$<?= htmlspecialchars(number_format((float)$campaign['cpm_rate'], 2)) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" style="text-align: center; padding: 20px;">Belum ada kampanye iklan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.admin-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 24px;
}
@media (min-width: 1200px) {
    .admin-grid {
        grid-template-columns: 1fr 2fr;
    }
}
.role-badge {
    font-size: 0.8em;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 500;
    text-transform: capitalize;
}
.role-badge.type-video { background-color: #e0e7ff; color: #3730a3; }
.role-badge.type-banner-display { background-color: #dcfce7; color: #166534; }
</style>

<?php include '../includes/templates/footer_admin.php'; ?>