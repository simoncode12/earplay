<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses
check_admin_auth();

// Logika untuk menandai kreator telah dibayar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payout_creator_id'])) {
    $creator_id = (int)$_POST['payout_creator_id'];
    $payout_amount = (float)$_POST['payout_amount'];
    
    if ($payout_amount > 0) {
        $pdo->beginTransaction();
        try {
            // 1. Reset saldo penghasilan kreator ke 0
            $stmt_reset = $pdo->prepare("UPDATE users SET creator_earnings = 0 WHERE id = ?");
            $stmt_reset->execute([$creator_id]);

            // 2. Simpan catatan pembayaran ke tabel 'payouts'
            $stmt_log = $pdo->prepare("INSERT INTO payouts (creator_id, amount, notes) VALUES (?, ?, ?)");
            $stmt_log->execute([$creator_id, $payout_amount, 'Manual payout by admin']);

            $pdo->commit();
            $_SESSION['message'] = "Pembayaran untuk kreator berhasil dicatat.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error_message'] = "Gagal mencatat pembayaran: " . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = "Jumlah pembayaran harus lebih dari nol.";
    }
    
    header("Location: payouts.php");
    exit();
}

// Ambil semua kreator yang memiliki saldo penghasilan lebih dari 0
$creators_with_earnings = $pdo->query(
    "SELECT id, username, email, creator_earnings
     FROM users 
     WHERE role = 'creator' AND creator_earnings > 0
     ORDER BY creator_earnings DESC"
)->fetchAll();

// Menggunakan template header admin
include '../includes/templates/header_admin.php';
?>

<h1>Keuangan & Pembayaran</h1>

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
        <h2>Kreator yang Menunggu Pembayaran</h2>
    </div>
    <div class="content">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Email (untuk Pembayaran)</th>
                    <th style="text-align: right;">Jumlah Penghasilan</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($creators_with_earnings) > 0): ?>
                    <?php foreach ($creators_with_earnings as $creator): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($creator['username']) ?></strong></td>
                        <td><?= htmlspecialchars($creator['email']) ?></td>
                        <td style="text-align: right; font-weight: 500;">$<?= number_format((float)$creator['creator_earnings'], 8) ?></td>
                        <td class="action-buttons" style="text-align: right;">
                            <form method="POST" onsubmit="return confirm('Anda yakin sudah membayar kreator ini sebesar $<?= number_format((float)$creator['creator_earnings'], 8) ?>? Aksi ini akan mereset saldo mereka.');" style="display:inline;">
                                <input type="hidden" name="payout_creator_id" value="<?= $creator['id'] ?>">
                                <input type="hidden" name="payout_amount" value="<?= (float)$creator['creator_earnings'] ?>">
                                <button type="submit" class="btn btn-approve">Tandai Sudah Dibayar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding: 20px;">Saat ini tidak ada kreator yang memiliki penghasilan untuk dibayarkan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Menggunakan template footer admin
include '../includes/templates/footer_admin.php';
?>