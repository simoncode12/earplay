<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses
check_admin_auth();

// Inisialisasi variabel
$error = '';
$user_data = null;
$page_title = 'Edit Pengguna';

// Pastikan ID pengguna ada dan valid
if (!isset($_GET['edit_id']) || !ctype_digit($_GET['edit_id'])) {
    header("Location: users.php");
    exit();
}

$user_id = (int)$_GET['edit_id'];

// Ambil data pengguna yang akan diedit
$stmt = $pdo->prepare("SELECT id, username, email, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

// Jika pengguna tidak ditemukan, alihkan kembali
if (!$user_data) {
    header("Location: users.php");
    exit();
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Validasi dasar
    if (empty($username) || empty($email)) {
        $error = "Username dan Email wajib diisi.";
    } elseif (!in_array($role, ['user', 'creator', 'admin'])) {
        $error = "Peran (role) tidak valid.";
    } else {
        try {
            // Jika password baru diisi, update passwordnya
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
                $stmt->execute([$username, $email, $role, $hashed_password, $user_id]);
            } else {
                // Jika password kosong, jangan update password
                $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$username, $email, $role, $user_id]);
            }
            $_SESSION['message'] = "Data pengguna '" . htmlspecialchars($username) . "' berhasil diperbarui.";
            header("Location: users.php");
            exit();
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = "Gagal: Username atau email ini sudah digunakan oleh akun lain.";
            } else {
                $error = "Terjadi kesalahan database: " . $e->getMessage();
            }
        }
    }
    // Jika ada error, isi kembali data yang sudah diinput
    $user_data['username'] = $username;
    $user_data['email'] = $email;
    $user_data['role'] = $role;
}

// Menggunakan template header admin
include '../includes/templates/header_admin.php';
?>

<h1><?= htmlspecialchars($page_title) ?></h1>

<?php
if ($error) {
    echo '<div class="alert error">' . htmlspecialchars($error) . '</div>';
}
?>

<div class="admin-section">
    <div class="content">
        <form method="POST">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($user_data['username']) ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>

            <label for="role">Peran (Role)</label>
            <select id="role" name="role">
                <option value="user" <?= $user_data['role'] === 'user' ? 'selected' : '' ?>>User (Penonton)</option>
                <option value="creator" <?= $user_data['role'] === 'creator' ? 'selected' : '' ?>>Creator</option>
                <option value="admin" <?= $user_data['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            
            <label for="password">Reset Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password baru">
            <small class="form-hint">Kosongkan jika Anda tidak ingin mengubah password pengguna ini.</small>

            <div style="text-align: right; margin-top: 20px;">
                <a href="users.php" class="btn btn-outline" style="margin-right: 10px;">Batal</a>
                <button type="submit" class="btn btn-primary">Update Pengguna</button>
            </div>
        </form>
    </div>
</div>

<?php
// Menggunakan template footer admin
include '../includes/templates/footer_admin.php';
?>