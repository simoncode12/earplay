<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses halaman ini
check_admin_auth();

// Logika untuk menghapus kreator saat form di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_creator_id'])) {
    $creator_to_delete = (int)$_POST['delete_creator_id'];

    // Menghapus pengguna dengan peran 'creator'
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'creator'");
    $stmt->execute([$creator_to_delete]);
    
    $_SESSION['message'] = "Kreator berhasil dihapus.";
    header("Location: creators.php");
    exit();
}

// Ambil semua data kreator untuk ditampilkan di tabel
$creators = $pdo->query(
    "SELECT id, username, email, monetization_status, created_at 
     FROM users 
     WHERE role = 'creator' 
     ORDER BY created_at DESC"
)->fetchAll();

// Menggunakan template header khusus admin
include '../includes/templates/header_admin.php';
?>

<h1>Manajemen Kreator</h1>

<?php
// Menampilkan pesan notifikasi jika ada
if (isset($_SESSION['message'])) {
    echo '<div class="alert success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Daftar Semua Kreator</h2>
        <a href="creator_form.php" class="btn btn-primary">Tambah Kreator</a>
    </div>
    <div class="content">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Status Monetisasi</th>
                    <th>Tanggal Bergabung</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($creators) > 0): ?>
                    <?php foreach ($creators as $creator): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($creator['username']) ?></strong></td>
                        <td><?= htmlspecialchars($creator['email']) ?></td>
                        <td>
                            <span class="status-badge status-<?= htmlspecialchars($creator['monetization_status']) ?>">
                                <?= ucfirst(str_replace('_', ' ', $creator['monetization_status'])) ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($creator['created_at'])) ?></td>
                        <td class="action-buttons" style="text-align: right;">
                            <a href="creator_form.php?edit_id=<?= $creator['id'] ?>" class="btn-edit"><i class="fas fa-pencil-alt"></i></a>
                            <form method="POST" onsubmit="return confirm('PERINGATAN: Menghapus kreator ini tidak bisa diurungkan. Lanjutkan?');" style="display:inline;">
                                <input type="hidden" name="delete_creator_id" value="<?= $creator['id'] ?>">
                                <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 20px;">Belum ada kreator yang terdaftar di platform Anda.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 24px;
    border-bottom: 1px solid var(--border-color);
}
.section-header h2 {
    margin: 0;
    font-size: 1.2em;
}
.admin-table td strong {
    color: var(--text-primary);
}
.action-buttons .btn-edit, .action-buttons .btn-delete {
    background: none;
    border: 1px solid #ccc;
    color: var(--text-secondary);
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    justify-content: center;
    align-items: center;
    padding: 0;
}
.action-buttons .btn-edit:hover { background-color: #f0f0f0; }
.action-buttons .btn-delete:hover { background-color: #fee2e2; color: #991b1b; }

.status-badge {
    font-size: 0.8em;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 500;
    text-transform: uppercase;
}
.status-badge.status-approved { background-color: #dcfce7; color: #166534; }
.status-badge.status-pending { background-color: #fefce8; color: #854d0e; }
.status-badge.status-rejected { background-color: #fee2e2; color: #991b1b; }
.status-badge.status-not_applied { background-color: #f1f5f9; color: #475569; }
</style>

<?php
// Menggunakan template footer khusus admin
include '../includes/templates/footer_admin.php';
?>