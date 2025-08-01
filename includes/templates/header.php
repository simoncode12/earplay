<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TubeX - Tonton & Dapatkan Reward</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <div class="header-left">
            <a href="/index.php" class="logo">TubeX</a>
            <a href="/trending.php" class="header-link main-nav-link">Trending</a>
        </div>
        
        <div class="header-center">
            <form action="/search.php" method="GET" class="search-bar">
                <input type="text" name="q" placeholder="Search" required>
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <nav class="header-right">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="notification-icon" id="notification-icon">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="notification-badge" style="display: none;">0</span>
                </div>
                
                <a href="/library.php" class="header-link" title="My Library"><i class="fas fa-list-ul"></i></a>
                <a href="/profile.php" class="header-link" title="My Profile"><i class="fas fa-user-circle"></i></a>
                
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="/admin/dashboard.php" class="header-link" title="Admin Panel"><i class="fas fa-cog"></i></a>
                <?php elseif ($_SESSION['role'] === 'creator'): ?>
                    <a href="/creator/dashboard.php" class="header-link" title="Creator Studio"><i class="fas fa-video"></i></a>
                <?php endif; ?>
                
                <a href="/auth/logout.php" class="header-link" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
            <?php else: ?>
                <a href="/auth/login.php" class="btn btn-outline">Login</a>
                <a href="/auth/register.php" class="btn btn-primary">Daftar</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="notification-panel" id="notification-panel" style="display: none;">
        <div class="notification-panel-header">
            <h3>Notifikasi</h3>
            <a href="#" id="mark-all-read-btn">Tandai semua dibaca</a>
        </div>
        <div class="notification-list" id="notification-list">
            </div>
    </div>

    <main>