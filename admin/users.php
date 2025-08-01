<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses
check_admin_auth();

// Logika untuk menghapus pengguna
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $user_to_delete = (int)$_POST['delete_user_id'];
    if ($user_to_delete === $_SESSION['user_id']) {
        $_SESSION['error_message'] = "Anda tidak dapat menghapus akun Anda sendiri.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_to_delete]);
        $_SESSION['message'] = "Pengguna berhasil dihapus.";
    }
    header("Location: users.php");
    exit();
}

// Ambil semua data pengguna dari database
$users = $pdo->query(
    "SELECT id, username, email, role, balance, created_at
     FROM users
     ORDER BY created_at DESC"
)->fetchAll();

// Menggunakan template header admin
include '../includes/templates/header_admin.php';
?>

<h1>Manajemen Pengguna</h1>

<?php
// Menampilkan pesan notifikasi
if (isset($_SESSION['message'])) {
    echo '<div class="alert success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
if (isset($_SESSION['error_message'])) {
    echo '<div class="alert error">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
    unset($_SESSION['error_message']);
}
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Semua Pengguna Terdaftar</h2>
        <a href="user_add_form.php" class="btn btn-primary">Tambah Pengguna Baru</a> </div>
    <div class="content">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <span class="role-badge role-<?= htmlspecialchars($user['role']) ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </td>
                        <td class="action-buttons" style="text-align: right;">
                            <a href="user_form.php?edit_id=<?= $user['id'] ?>" class="btn-edit"><i class="fas fa-pencil-alt"></i></a>
                            
                            <form method="POST" onsubmit="return confirm('Anda yakin ingin menghapus pengguna ini? Semua data terkait akan hilang permanen.');" style="display:inline;">
                                <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                <button type="submit" class="btn-delete" <?= ($user['id'] === $_SESSION['user_id']) ? 'disabled' : '' ?>><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 20px;">Tidak ada pengguna yang terdaftar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.role-badge {
    font-size: 0.8em;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 500;
    text-transform: uppercase;
}
.role-badge.role-admin { background-color: #fee2e2; color: #991b1b; }
.role-badge.role-creator { background-color: #dcfce7; color: #166534; }
.role-badge.role-user { background-color: #e0e7ff; color: #3730a3; }
</style>

<?php
// Menggunakan template footer admin
include '../includes/templates/footer_admin.php';
?>