<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses
check_admin_auth();

$error = '';

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Validasi dasar
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "Semua kolom wajib diisi.";
    } elseif (strlen($password) < 6) {
        $error = "Password minimal harus 6 karakter.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (!in_array($role, ['user', 'creator', 'admin'])) {
        $error = "Peran (role) tidak valid.";
    } else {
        try {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password, $role]);
            
            $_SESSION['message'] = "Pengguna baru '" . htmlspecialchars($username) . "' berhasil ditambahkan.";
            header("Location: users.php");
            exit();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = "Gagal: Username atau email ini sudah terdaftar.";
            } else {
                $error = "Terjadi kesalahan database.";
            }
        }
    }
}

include '../includes/templates/header_admin.php';
?>

<h1>Tambah Pengguna Baru</h1>

<?php if ($error): ?>
    <div class="alert error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="admin-section">
    <div class="content">
        <form method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>

            <label for="role">Peran (Role)</label>
            <select id="role" name="role" required>
                <option value="user">User (Penonton)</option>
                <option value="creator">Creator</option>
                <option value="admin">Admin</option>
            </select>

            <div style="text-align: right; margin-top: 20px;">
                <a href="users.php" class="btn btn-outline" style="margin-right: 10px;">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/templates/footer_admin.php'; ?>