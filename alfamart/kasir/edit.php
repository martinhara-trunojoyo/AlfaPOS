<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kepala_toko') {
    header("Location: ../dashboard/index.php");
    exit();
}

$id = $_GET['id'];
$res_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$id'");
$data = mysqli_fetch_assoc($res_user);

if (!$data) {
    header("Location: index.php");
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $status = $_POST['status_aktif'];
    
    $sql = "UPDATE users SET nama_lengkap = '$nama', email = '$email', status_aktif = '$status' WHERE id = '$id'";
    
    // Update password jika diisi
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "UPDATE users SET nama_lengkap = '$nama', email = '$email', status_aktif = '$status', password = '$password' WHERE id = '$id'";
    }
    
    if (mysqli_query($koneksi, $sql)) {
        header("Location: index.php?status=success");
        exit();
    } else {
        $message = "Gagal memperbarui kasir: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kasir - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-kasir">
        <div>
            <h2>Edit Petugas Kasir</h2>
            <p>Perbarui informasi akun <strong><?php echo $data['username']; ?></strong>.</p>
        </div>
    </div>

    <div class="form-card">
        <form action="" method="POST">
            <div class="form-group">
                <label>Username (Tetap)</label>
                <input type="text" value="<?php echo $data['username']; ?>" readonly style="background: #f1f5f9;">
            </div>
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" value="<?php echo $data['nama_lengkap']; ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo $data['email']; ?>">
            </div>
            <div class="form-group">
                <label for="password">Password Baru (Kosongkan jika tidak ingin diubah)</label>
                <input type="password" name="password" id="password" placeholder="Minta kasir segera ganti password ini">
            </div>
            <div class="form-group">
                <label for="status_aktif">Status Akun</label>
                <select name="status_aktif" id="status_aktif">
                    <option value="1" <?php echo $data['status_aktif'] ? 'selected' : ''; ?>>Aktif</option>
                    <option value="0" <?php echo !$data['status_aktif'] ? 'selected' : ''; ?>>Non-Aktif</option>
                </select>
            </div>

            <div class="form-footer">
                <a href="index.php" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Perbarui Data</button>
            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
