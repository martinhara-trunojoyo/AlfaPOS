<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kepala_toko') {
    header("Location: ../dashboard/index.php");
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $status = $_POST['status_aktif'];

    // Cek username unik
    $check = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username'");
    if (mysqli_num_rows($check) > 0) {
        $message = "Username sudah digunakan!";
    } else {
        $sql = "INSERT INTO users (username, password, nama_lengkap, email, role, status_aktif) 
                VALUES ('$username', '$password', '$nama', '$email', 'kasir', '$status')";
        
        if (mysqli_query($koneksi, $sql)) {
            header("Location: index.php?status=success");
            exit();
        } else {
            $message = "Gagal menambah kasir: " . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kasir - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-kasir">
        <div>
            <h2>Tambah Kasir Baru</h2>
            <p>Daftarkan petugas kasir baru untuk sistem AlfaPOS.</p>
        </div>
    </div>

    <div class="form-card">
        <form action="" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required placeholder="Contoh: kasir_budi">
            </div>
            <div class="form-group">
                <label for="nama_lengkap">Nama Lengkap</label>
                <input type="text" name="nama_lengkap" id="nama_lengkap" required placeholder="Nama asli petugas">
            </div>
            <div class="form-group">
                <label for="email">Email (Opsional)</label>
                <input type="email" name="email" id="email" placeholder="email@contoh.com">
            </div>
            <div class="form-group">
                <label for="password">Password Default</label>
                <input type="password" name="password" id="password" required placeholder="Masukkan password kuat">
            </div>
            <div class="form-group">
                <label for="status_aktif">Status Akun</label>
                <select name="status_aktif" id="status_aktif">
                    <option value="1">Aktif</option>
                    <option value="0">Non-Aktif</option>
                </select>
            </div>

            <div class="form-footer">
                <a href="index.php" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan Petugas</button>
            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();
        <?php if ($message): ?>
        Swal.fire('Gagal', '<?php echo $message; ?>', 'error');
        <?php endif; ?>
    </script>
</body>
</html>
