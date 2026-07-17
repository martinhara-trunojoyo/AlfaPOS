<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

$message = "";
if (isset($_POST['submit'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_supplier']);
    $pic      = mysqli_real_escape_string($koneksi, $_POST['nama_pic']);
    $telp     = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $alamat   = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    $query = "INSERT INTO suppliers (nama_supplier, nama_pic, no_telp, email, alamat) 
              VALUES ('$nama', '$pic', '$telp', '$email', '$alamat')";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php?status=success");
        exit();
    } else {
        $message = "Gagal menambah supplier: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Supplier - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-supplier">
        <div>
            <h2>Tambah Supplier Baru</h2>
            <p>Masukkan data penyedia barang dengan lengkap.</p>
        </div>
    </div>

    <div class="form-card">
        <form action="" method="POST">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Nama Perusahaan / Supplier</label>
                    <input type="text" name="nama_supplier" placeholder="Contoh: PT. Sumber Makmur" required>
                </div>
                <div class="form-group">
                    <label>Nama PIC (Kontak Person)</label>
                    <input type="text" name="nama_pic" placeholder="Nama penanggung jawab" required>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="no_telp" placeholder="Contoh: 08123456789" required>
                </div>
                <div class="form-group full-width">
                    <label>Email Perusahaan</label>
                    <input type="email" name="email" placeholder="email@supplier.com">
                </div>
                <div class="form-group full-width">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" rows="3" placeholder="Alamat kantor atau gudang"></textarea>
                </div>
            </div>

            <div class="form-footer">
                <a href="index.php" class="btn-batal">Batal</a>
                <button type="submit" name="submit" class="btn-simpan">Simpan Supplier</button>
            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();
        <?php if ($message): ?>
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: '<?php echo $message; ?>'
        });
        <?php endif; ?>
    </script>
</body>
</html>
