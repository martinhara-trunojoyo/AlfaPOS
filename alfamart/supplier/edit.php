<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

$id = $_GET['id'];
$query_s = "SELECT * FROM suppliers WHERE id = $id";
$res_s = mysqli_query($koneksi, $query_s);
$data = mysqli_fetch_assoc($res_s);

$message = "";
if (isset($_POST['submit'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_supplier']);
    $pic      = mysqli_real_escape_string($koneksi, $_POST['nama_pic']);
    $telp     = mysqli_real_escape_string($koneksi, $_POST['no_telp']);
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $alamat   = mysqli_real_escape_string($koneksi, $_POST['alamat']);

    $query = "UPDATE suppliers SET 
              nama_supplier = '$nama', 
              nama_pic = '$pic', 
              no_telp = '$telp', 
              email = '$email', 
              alamat = '$alamat' 
              WHERE id = $id";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php?status=updated");
        exit();
    } else {
        $message = "Gagal memperbarui supplier: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Supplier - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-supplier">
        <div>
            <h2>Edit Data Supplier</h2>
            <p>Perbarui informasi untuk <strong><?php echo $data['nama_supplier']; ?></strong></p>
        </div>
    </div>

    <div class="form-card">
        <form action="" method="POST">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Nama Perusahaan / Supplier</label>
                    <input type="text" name="nama_supplier" value="<?php echo $data['nama_supplier']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Nama PIC (Kontak Person)</label>
                    <input type="text" name="nama_pic" value="<?php echo $data['nama_pic']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Nomor Telepon</label>
                    <input type="text" name="no_telp" value="<?php echo $data['no_telp']; ?>" required>
                </div>
                <div class="form-group full-width">
                    <label>Email Perusahaan</label>
                    <input type="email" name="email" value="<?php echo $data['email']; ?>">
                </div>
                <div class="form-group full-width">
                    <label>Alamat Lengkap</label>
                    <textarea name="alamat" rows="3"><?php echo $data['alamat']; ?></textarea>
                </div>
            </div>

            <div class="form-footer">
                <a href="index.php" class="btn-batal">Batal</a>
                <button type="submit" name="submit" class="btn-simpan">Update Supplier</button>
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
