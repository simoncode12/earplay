<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Studio - TubeX</title>
    <link rel="stylesheet" href="/assets/css/style_admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-logo-container">
                <a href="/admin/dashboard.php" class="sidebar-logo">TubeX</a>
            </div>
            <nav class="sidebar-nav">
                <a href="/admin/dashboard.php"><i class="fas fa-tachometer-alt fa-fw"></i> <span>Dashboard</span></a>
                <a href="/admin/analytics.php"><i class="fas fa-chart-line fa-fw"></i> <span>Analitik</span></a>
                <a href="/admin/users.php"><i class="fas fa-users-cog fa-fw"></i> <span>Pengguna</span></a>
                <a href="/admin/videos.php"><i class="fas fa-play-circle fa-fw"></i> <span>Konten</span></a>
                <a href="/admin/comments.php"><i class="fas fa-comments fa-fw"></i> <span>Komentar</span></a>
                <a href="/admin/creators.php"><i class="fas fa-user-check fa-fw"></i> <span>Kreator</span></a>
                <a href="/admin/monetization.php"><i class="fas fa-hand-holding-dollar fa-fw"></i> <span>Monetisasi</span></a>
                <a href="/admin/payouts.php"><i class="fas fa-file-invoice-dollar fa-fw"></i> <span>Pembayaran</span></a>
                <a href="/admin/ads.php"><i class="fas fa-ad fa-fw"></i> <span>Iklan</span></a> <a href="/admin/settings.php"><i class="fas fa-cog fa-fw"></i> <span>Pengaturan</span></a>
            </nav>
        </aside>

        <div class="admin-main-view">
            <header class="admin-topbar">
                <div class="topbar-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Cari...">
                </div>
                <div class="topbar-user">
                    <a href="/index.php" class="btn btn-outline">Lihat Situs</a>
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                </div>
            </header>
            
            <main class="admin-content-area">