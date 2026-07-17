<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

$id = $_GET['id'];
$query_p = "SELECT * FROM products WHERE id = $id";
$res_p = mysqli_query($koneksi, $query_p);
$data = mysqli_fetch_assoc($res_p);

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

    $query = "UPDATE products SET 
              kode_barang = '$kode', 
              nama_barang = '$nama', 
              kategori = '$kategori', 
              harga_beli = '$hbeli', 
              harga_jual = '$hjual', 
              stok = '$stok', 
              stok_minimum = '$stok_min', 
              supplier_id = '$supplier' 
              WHERE id = $id";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php?status=updated");
        exit();
    } else {
        $message = "Gagal mengubah produk: " . mysqli_error($koneksi);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-produk">
        <div>
            <h2>Edit Produk</h2>
            <p>Perbarui informasi barang <strong><?php echo $data['nama_barang']; ?></strong></p>
        </div>
    </div>

    <div class="form-card">
        <form action="" method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Kode Barang / Barcode</label>
                    <input type="text" name="kode_barang" value="<?php echo $data['kode_barang']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Nama Produk</label>
                    <input type="text" name="nama_barang" value="<?php echo $data['nama_barang']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" required>
                        <option value="Makanan" <?php echo ($data['kategori'] == 'Makanan' ? 'selected' : ''); ?>>Makanan</option>
                        <option value="Minuman" <?php echo ($data['kategori'] == 'Minuman' ? 'selected' : ''); ?>>Minuman</option>
                        <option value="Kebutuhan Pokok" <?php echo ($data['kategori'] == 'Kebutuhan Pokok' ? 'selected' : ''); ?>>Kebutuhan Pokok</option>
                        <option value="Kesehatan" <?php echo ($data['kategori'] == 'Kesehatan' ? 'selected' : ''); ?>>Kesehatan</option>
                        <option value="Lainnya" <?php echo ($data['kategori'] == 'Lainnya' ? 'selected' : ''); ?>>Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Supplier</label>
                    <select name="supplier_id">
                        <option value="">-- Pilih Supplier --</option>
                        <?php while($s = mysqli_fetch_assoc($res_supplier)): ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($data['supplier_id'] == $s['id'] ? 'selected' : ''); ?>>
                                <?php echo $s['nama_supplier']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Harga Beli (Rp)</label>
                    <input type="number" name="harga_beli" value="<?php echo $data['harga_beli']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Harga Jual (Rp)</label>
                    <input type="number" name="harga_jual" value="<?php echo $data['harga_jual']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Stok</label>
                    <input type="number" name="stok" value="<?php echo $data['stok']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Stok Minimum</label>
                    <input type="number" name="stok_minimum" value="<?php echo $data['stok_minimum']; ?>" required>
                </div>
            </div>

            <div class="form-footer">
                <a href="index.php" class="btn-batal">Batal</a>
                <button type="submit" name="submit" class="btn-simpan">Update Produk</button>
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
