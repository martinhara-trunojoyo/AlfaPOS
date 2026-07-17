<?php
session_start();
require_once '../koneksi/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Ambil data supplier untuk dropdown
$sql_supplier = "SELECT id, nama_supplier FROM suppliers";
$res_supplier = mysqli_query($koneksi, $sql_supplier);

$message = "";
if (isset($_POST['submit'])) {
    $kode      = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $kategori  = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $hbeli     = $_POST['harga_beli'];
    $hjual     = $_POST['harga_jual'];
    $stok      = $_POST['stok'];
    $stok_min  = $_POST['stok_minimum'];
    $supplier  = $_POST['supplier_id'];

    $query = "INSERT INTO products (kode_barang, nama_barang, kategori, harga_beli, harga_jual, stok, stok_minimum, supplier_id) 
              VALUES ('$kode', '$nama', '$kategori', '$hbeli', '$hjual', '$stok', '$stok_min', '$supplier')";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php?status=success");
        exit();
    } else {
        $message = "Gagal menambah produk: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-produk">
        <div>
            <h2>Tambah Barang Baru</h2>
            <p>Masukkan detail produk dengan benar.</p>
        </div>
    </div>

    <div class="form-card">
        <form action="" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Kode Barang / Barcode</label>
                    <input type="text" name="kode_barang" placeholder="Contoh: 899123456..." required>
                </div>
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_barang" placeholder="Nama lengkap barang" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="Makanan">Makanan</option>
                        <option value="Minuman">Minuman</option>
                        <option value="Kebutuhan Pokok">Kebutuhan Pokok</option>
                        <option value="Kesehatan">Kesehatan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Supplier</label>
                    <select name="supplier_id">
                        <option value="">-- Pilih Supplier --</option>
                        <?php while($s = mysqli_fetch_assoc($res_supplier)): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo $s['nama_supplier']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Harga Beli (Rp)</label>
                    <input type="number" name="harga_beli" required>
                </div>
                <div class="form-group">
                    <label>Harga Jual (Rp)</label>
                    <input type="number" name="harga_jual" required>
                </div>
                <div class="form-group">
                    <label>Stok Awal</label>
                    <input type="number" name="stok" value="0" required>
                </div>
                <div class="form-group">
                    <label>Stok Minimum</label>
                    <input type="number" name="stok_minimum" value="10" required>
                </div>
            </div>

            <div class="form-footer">
                <a href="index.php" class="btn-batal">Batal</a>
                <button type="submit" name="submit" class="btn-simpan">Simpan Produk</button>
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
