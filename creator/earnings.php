<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Pastikan hanya kreator yang bisa mengakses
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'creator') {
    header('Location: /auth/login.php?error=creator_only');
    exit();
}

$creator_id = $_SESSION['user_id'];

// Ambil saldo penghasilan saat ini
$stmt_user = $pdo->prepare("SELECT creator_earnings FROM users WHERE id = ?");
$stmt_user->execute([$creator_id]);
$current_earnings = $stmt_user->fetchColumn();

// Ambil riwayat pembayaran (payouts) untuk kreator ini
$stmt_payouts = $pdo->prepare(
    "SELECT amount, payout_date, notes FROM payouts WHERE creator_id = ? ORDER BY payout_date DESC"
);
$stmt_payouts->execute([$creator_id]);
$payout_history = $stmt_payouts->fetchAll();

// Menggunakan template header khusus kreator
include '../includes/templates/header_creator.php';
?>

<h1>Earnings</h1>

<div class="creator-section">
    <h2>Current Balance</h2>
    <div class="content">
        <p class="balance-amount-creator">$<?= number_format((float)$current_earnings, 8) ?></p>
        <p style="color: var(--text-secondary);">This is your current estimated earnings from the pay-per-second model. Payouts are processed monthly by the admin.</p>
        
        <?php if ((float)$current_earnings > 0): ?>
            <button class="btn btn-apply-monetization" disabled>Request Payout (Coming Soon)</button>
        <?php else: ?>
            <button class="btn btn-apply-monetization" disabled>Request Payout</button>
        <?php endif; ?>
    </div>
</div>

<div class="creator-section">
    <h2>Payout History</h2>
    <div class="content">
        <table class="creator-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($payout_history) > 0): ?>
                    <?php foreach ($payout_history as $payout): ?>
                        <tr>
                            <td><?= date('M d, Y', strtotime($payout['payout_date'])) ?></td>
                            <td style="text-align: right; font-weight: 500; color: #34a853;">
                                +$<?= number_format((float)$payout['amount'], 8) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" style="text-align: center; padding: 20px;">You have no payout history yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.balance-amount-creator {
    font-size: 2.5em;
    font-weight: 700;
    margin: 0 0 10px 0;
    color: #34a853;
}
.creator-table {
    width: 100%;
    border-collapse: collapse;
}
.creator-table th, .creator-table td {
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
    text-align: left;
}
.creator-table tr:last-child td {
    border-bottom: none;
}
.creator-table th {
    color: var(--text-secondary);
    font-weight: 500;
}
</style>

<?php 
// Menggunakan template footer khusus kreator
include '../includes/templates/footer_creator.php'; 
?>