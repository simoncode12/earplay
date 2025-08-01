<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses
check_admin_auth();

// --- PENGAMBILAN DATA STATISTIK UTAMA (TETAP SAMA) ---
$total_watch_seconds = $pdo->query("SELECT SUM(watched_seconds) FROM watch_stats")->fetchColumn();
$total_watch_hours = $total_watch_seconds ? $total_watch_seconds / 3600 : 0;
$total_payouts = $pdo->query("SELECT SUM(amount) FROM payouts")->fetchColumn();

// Menggunakan template header admin
include '../includes/templates/header_admin.php';
?>

<h1>Analitik Platform</h1>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background-color: #e5f1ff;">
            <i class="fas fa-clock" style="color: #0066ff;"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">Total Jam Tonton</span>
            <span class="stat-value"><?= number_format($total_watch_hours, 2) ?> Jam</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon-wrapper" style="background-color: #e5fff0;">
            <i class="fas fa-money-bill-wave" style="color: #00cc66;"></i>
        </div>
        <div class="stat-info">
            <span class="stat-title">Total Dibayarkan</span>
            <span class="stat-value">$<?= number_format((float)$total_payouts, 2) ?></span>
        </div>
    </div>
</div>

<div class="admin-section">
    <div class="section-header">
        <h2>Pertumbuhan Platform (7 Hari Terakhir)</h2>
    </div>
    <div class="content">
        <canvas id="growthChart" style="max-height: 350px;"></canvas>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Ambil data dari API yang kita buat
        const response = await fetch('/api/analytics_data.php');
        const data = await response.json();

        const ctx = document.getElementById('growthChart').getContext('2d');
        
        new Chart(ctx, {
            type: 'line', // Jenis grafik: garis
            data: {
                labels: data.labels, // Label sumbu X (tanggal)
                datasets: [{
                    label: 'Pengguna Baru',
                    data: data.users, // Data pengguna baru
                    borderColor: '#0066ff',
                    backgroundColor: 'rgba(0, 102, 255, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Video Baru',
                    data: data.videos, // Data video baru
                    borderColor: '#00cc66',
                    backgroundColor: 'rgba(0, 204, 102, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                }
            }
        });
    } catch (error) {
        console.error('Gagal memuat data analitik:', error);
    }
});
</script>

<?php
// Menggunakan template footer admin
include '../includes/templates/footer_admin.php';
?>