<?php
session_start();
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Pastikan hanya pengguna yang login yang bisa mengakses
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php?error=login_required');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Ambil data pengguna saat ini
$stmt = $pdo->prepare("SELECT username, email, balance, role, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Logika untuk mengubah password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt_pass = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt_pass->execute([$user_id]);
    $hashed_password = $stmt_pass->fetchColumn();

    if (!password_verify($current_password, $hashed_password)) {
        $error = "Password Anda saat ini salah.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Konfirmasi password baru tidak cocok.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password baru minimal harus 6 karakter.";
    } else {
        $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        if ($update_stmt->execute([$new_hashed_password, $user_id])) {
            $message = "Password Anda berhasil diperbarui!";
        } else {
            $error = "Gagal memperbarui password.";
        }
    }
}

include 'includes/templates/header.php';
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar"><?= strtoupper(substr($user['username'], 0, 1)) ?></div>
        <div class="profile-header-info">
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            <p><?= htmlspecialchars($user['email']) ?></p>
        </div>
    </div>

    <?php if ($message): ?> <div class="alert success"><?= htmlspecialchars($message) ?></div> <?php endif; ?>
    <?php if ($error): ?> <div class="alert error"><?= htmlspecialchars($error) ?></div> <?php endif; ?>

    <div class="profile-grid">
        <div class="profile-section">
            <h2>Informasi Akun</h2>
            <div class="info-item">
                <span class="info-label">Peran Akun</span>
                <span class="info-value role-badge role-<?= htmlspecialchars($user['role']) ?>"><?= ucfirst($user['role']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Tanggal Bergabung</span>
                <span class="info-value"><?= date('d F Y', strtotime($user['created_at'])) ?></span>
            </div>
        </div>
        <div class="profile-section">
            <h2>Saldo Reward Saya</h2>
            <p class="balance-amount">$<?= number_format((float)$user['balance'], 8) ?></p>
            <small>Total saldo yang diperoleh dari menonton video.</small>
        </div>
    </div>
    

    <div class="profile-section">
        <h2>Ganti Password</h2>
        <form method="POST">
            <input type="hidden" name="change_password" value="1">
            <div class="form-group">
                <label for="current_password">Password Saat Ini</label>
                <input type="password" name="current_password" id="current_password" required>
            </div>
            <div class="form-group">
                <label for="new_password">Password Baru</label>
                <input type="password" name="new_password" id="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
            <button type="submit" class="btn-primary">Perbarui Password</button>
        </form>
    </div>
</div>

<?php include 'includes/templates/footer.php'; ?>