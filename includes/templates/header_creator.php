<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Studio - TubeX</title>
    <link rel="stylesheet" href="/assets/css/style_creator.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="creator-layout">
        <aside class="creator-sidebar">
            <div class="sidebar-logo-container">
                <a href="/creator/dashboard.php" class="sidebar-logo">TubeX Studio</a>
            </div>
            <nav class="sidebar-nav">
                <a href="/creator/dashboard.php"><i class="fas fa-tachometer-alt fa-fw"></i> <span>Dashboard</span></a>
                <a href="/creator/content.php"><i class="fas fa-play-circle fa-fw"></i> <span>Content</span></a>
                    <a href="/creator/comments.php"><i class="fas fa-comments fa-fw"></i> <span>Comments</span></a>
                <a href="/creator/analytics.php"><i class="fas fa-chart-line fa-fw"></i> <span>Analytics</span></a>
                <a href="/creator/earnings.php"><i class="fas fa-wallet fa-fw"></i> <span>Earnings</span></a>
                <a href="/creator/upload.php"><i class="fas fa-upload fa-fw"></i> <span>Upload</span></a>
            </nav>
            <div class="sidebar-footer">
                <a href="/index.php" class="btn btn-outline">Back to TubeX</a>
            </div>
        </aside>

        <div class="creator-main-view">
            <header class="creator-topbar">
                <div class="topbar-title">
                    Creator Studio
                </div>
                <div class="topbar-user">
                    <span><?= htmlspecialchars($_SESSION['username']) ?></span>
                    <div class="user-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                    <a href="/auth/logout.php" class="logout-link" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
                </div>
            </header>
            
            <main class="creator-content-area">