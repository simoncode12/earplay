<?php
session_start();
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Memastikan hanya admin yang bisa mengakses halaman ini
check_admin_auth();

// Inisialisasi variabel
$error = '';
$creator_data = [
    'id' => null,
    'username' => '',
    'email' => '',
    'ad_zone_id' => ''
];
$is_edit_mode = false;
$page_title = 'Tambah Kreator Baru';

// Cek apakah ini mode Edit dengan mengambil ID dari URL
if (isset($_GET['edit_id']) && ctype_digit($_GET['edit_id'])) {
    $is_edit_mode = true;
    $page_title = 'Edit Data Kreator';
    $creator_id = (int)$_GET['edit_id'];
    
    // Ambil data kreator yang akan diedit, termasuk ad_zone_id
    $stmt = $pdo->prepare("SELECT id, username, email, ad_zone_id FROM users WHERE id = ? AND role = 'creator'");
    $stmt->execute([$creator_id]);
    $creator_data = $stmt->fetch();
    
    // Jika ID kreator tidak ditemukan, alihkan kembali
    if (!$creator_data) {
        header("Location: creators.php");
        exit();
    }
}

// Proses form saat disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $ad_zone_id = !empty($_POST['ad_zone_id']) ? (int)$_POST['ad_zone_id'] : null;

    // Validasi dasar
    if (empty($username) || empty($email)) {
        $error = "Username dan Email wajib diisi.";
    } elseif (!$is_edit_mode && empty($password)) {
        $error = "Password wajib diisi untuk kreator baru.";
    } elseif (strlen($password) > 0 && strlen($password) < 6) {
        $error = "Password minimal harus 6 karakter.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        try {
            if ($is_edit_mode) {
                // --- LOGIKA UNTUK UPDATE DATA KREATOR ---
                $creator_id_to_update = (int)$_POST['creator_id'];
                if (!empty($password)) {
                    // Jika password baru diisi, update semua termasuk password
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, ad_zone_id = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $hashed_password, $ad_zone_id, $creator_id_to_update]);
                } else {
                    // Jika password kosong, jangan update password
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, ad_zone_id = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $ad_zone_id, $creator_id_to_update]);
                }
                $_SESSION['message'] = "Data kreator '" . htmlspecialchars($username) . "' berhasil diperbarui.";
            } else {
                // --- LOGIKA UNTUK INSERT (TAMBAH KREATOR BARU) ---
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                // ad_zone_id juga disertakan saat membuat kreator baru
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, ad_zone_id) VALUES (?, ?, ?, 'creator', ?)");
                $stmt->execute([$username, $email, $hashed_password, $ad_zone_id]);
                $_SESSION['message'] = "Kreator baru '" . htmlspecialchars($username) . "' berhasil ditambahkan.";
            }
            // Redirect kembali ke halaman daftar kreator setelah sukses
            header("Location: creators.php");
            exit();
        } catch (PDOException $e) {
            // Tangani error jika username atau email sudah ada
            if ($e->errorInfo[1] == 1062) {
                $error = "Gagal: Username atau email ini sudah terdaftar.";
            } else {
                $error = "Terjadi kesalahan database: " . $e->getMessage();
            }
        }
    }
    // Jika ada error, isi kembali data yang sudah diinput oleh user
    $creator_data['username'] = $username;
    $creator_data['email'] = $email;
    $creator_data['ad_zone_id'] = $ad_zone_id;
}

// Menggunakan template header khusus admin
include '../includes/templates/header_admin.php';
?>

<h1><?= htmlspecialchars($page_title) ?></h1>

<?php
// Menampilkan pesan error jika ada
if ($error) {
    echo '<div class="alert error">' . htmlspecialchars($error) . '</div>';
}
?>

<div class="admin-section">
    <div class="content">
        <form method="POST">
            <?php if ($is_edit_mode): ?>
                <input type="hidden" name="creator_id" value="<?= $creator_data['id'] ?>">
            <?php endif; ?>

            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($creator_data['username']) ?>" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($creator_data['email']) ?>" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" <?= $is_edit_mode ? '' : 'required' ?>>
            <?php if ($is_edit_mode): ?>
                <small class="form-hint">Kosongkan jika Anda tidak ingin mengubah password.</small>
            <?php endif; ?>

            <label for="ad_zone_id">Ad Server Zone ID</label>
            <input type="number" id="ad_zone_id" name="ad_zone_id" value="<?= htmlspecialchars($creator_data['ad_zone_id'] ?? '') ?>">
            <small class="form-hint">Masukkan Zone ID dari ad server untuk kreator ini. Biarkan kosong jika belum ada.</small>

            <div style="text-align: right; margin-top: 20px;">
                <a href="creators.php" class="btn btn-outline" style="margin-right: 10px;">Batal</a>
                <button type="submit" class="btn btn-primary"><?= $is_edit_mode ? 'Update Kreator' : 'Simpan Kreator' ?></button>
            </div>
        </form>
    </div>
</div>

<style>
.form-hint {
    display: block;
    margin-top: -15px;
    margin-bottom: 20px;
    font-size: 0.85em;
    color: var(--text-secondary);
}
</style>

<?php
// Menggunakan template footer khusus admin
include '../includes/templates/footer_admin.php';
?>