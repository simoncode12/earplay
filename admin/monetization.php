<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses halaman ini
check_admin_auth();

// Logika untuk menyetujui atau menolak pengajuan monetisasi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creator_id']) && isset($_POST['action'])) {
    $creator_id = (int)$_POST['creator_id'];
    $action = $_POST['action'];

    // Tentukan status baru berdasarkan aksi dari admin
    if ($action === 'approve') {
        $new_status = 'approved';
        $_SESSION['message'] = "Monetisasi untuk kreator berhasil disetujui.";
    } elseif ($action === 'reject') {
        $new_status = 'rejected';
        $_SESSION['message'] = "Monetisasi untuk kreator berhasil ditolak.";
    } else {
        $new_status = null;
    }

    // Update status di database jika aksi valid
    if ($new_status) {
        $stmt = $pdo->prepare("UPDATE users SET monetization_status = ? WHERE id = ? AND role = 'creator'");
        $stmt->execute([$new_status, $creator_id]);
    }
    
    // Redirect untuk mencegah re-submit form saat refresh
    header("Location: monetization.php");
    exit();
}

// Ambil semua data kreator yang pengajuannya masih 'pending'
$requests = $pdo->query(
    "SELECT id, username, email, created_at FROM users 
     WHERE role = 'creator' AND monetization_status = 'pending' 
     ORDER BY created_at ASC"
)->fetchAll();

// Menggunakan template header khusus admin
include '../includes/templates/header_admin.php';
?>

<h1>Tinjau Pengajuan Monetisasi</h1>

<?php
// Menampilkan pesan notifikasi jika ada
if (isset($_SESSION['message'])) {
    echo '<div class="alert success">' . htmlspecialchars($_SESSION['message']) . '</div>';
    unset($_SESSION['message']);
}
?>

<div class="admin-section">
    <div class="section-header">
        <h2>Menunggu Persetujuan</h2>
    </div>
    <div class="content">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Jumlah Subscriber</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($requests) > 0): ?>
                    <?php foreach ($requests as $creator): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($creator['username']) ?></strong></td>
                        <td><?= htmlspecialchars($creator['email']) ?></td>
                        <td>
                            <?php
                            // Menghitung dan menampilkan jumlah subscriber
                            $sub_count = get_subscriber_count($creator['id'], $pdo);
                            echo number_format($sub_count);
                            ?>
                        </td>
                        <td class="action-buttons" style="text-align: right;">
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Anda yakin ingin MENYETUJUI monetisasi untuk kreator ini?');">
                                <input type="hidden" name="creator_id" value="<?= $creator['id'] ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-approve">Setujui</button>
                            </form>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Anda yakin ingin MENOLAK monetisasi untuk kreator ini?');">
                                <input type="hidden" name="creator_id" value="<?= $creator['id'] ?>">
                                <button type="submit" name="action" value="reject" class="btn btn-danger">Tolak</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 20px;">Tidak ada pengajuan monetisasi yang menunggu persetujuan.</td>
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
.content {
    padding: 0; /* Hapus padding default agar tabel pas */
}
.admin-table td strong {
    color: var(--text-primary);
    font-weight: 500;
}
.action-buttons .btn-approve, .action-buttons .btn-danger {
    text-transform: none;
    font-weight: 500;
    border-radius: 4px;
    padding: 8px 16px;
    font-size: 0.9em;
}
.btn-approve { background-color: #065fd4; color: white; }
.btn-approve:hover { background-color: #054ab2; }
.btn-danger { background-color: #f1f1f1; color: #606060; }
.btn-danger:hover { background-color: #e5e5e5; }
</style>


<?php
// Menggunakan template footer khusus admin
include '../includes/templates/footer_admin.php';
?>