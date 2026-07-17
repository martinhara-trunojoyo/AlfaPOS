<?php
session_start();
require_once '../koneksi/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Ambil daftar produk untuk dropdown
$query_produk = "SELECT id, kode_barang, nama_barang, stok FROM products ORDER BY nama_barang ASC";
$result_produk = mysqli_query($koneksi, $query_produk);
$produk_list = [];
while($row = mysqli_fetch_assoc($result_produk)) {
    $produk_list[] = $row;
}

// Proses Simpan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = $_POST['product_id'];
    $tanggal = $_POST['tanggal'];
    $stok_sistem = $_POST['stok_sistem'];
    $stok_fisik = $_POST['stok_fisik'];
    $selisih = $stok_fisik - $stok_sistem;
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    mysqli_begin_transaction($koneksi);

    try {
        // 1. Insert ke tabel stok_opname
        $sql_insert = "INSERT INTO stok_opname (product_id, tanggal, stok_sistem, stok_fisik, selisih, keterangan) 
                       VALUES ('$product_id', '$tanggal', '$stok_sistem', '$stok_fisik', '$selisih', '$keterangan')";
        mysqli_query($koneksi, $sql_insert);

        // 2. Update stok di tabel products
        $sql_update = "UPDATE products SET stok = '$stok_fisik' WHERE id = '$product_id'";
        mysqli_query($koneksi, $sql_update);

        mysqli_commit($koneksi);
        header("Location: index.php?status=success");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        header("Location: index.php?status=error");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Stok Opname - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="header-opname">
        <div>
            <h2>Input Stok Opname</h2>
            <p style="color: #64748b;">Lakukan penyesuaian stok fisik barang.</p>
        </div>
    </div>

    <div class="form-card">
        <form action="" method="POST" id="opnameForm">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label for="product_id">Pilih Produk</label>
                    <select name="product_id" id="product_id" required onchange="updateStokSistem()">
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach($produk_list as $p): ?>
                            <option value="<?php echo $p['id']; ?>" data-stok="<?php echo $p['stok']; ?>">
                                <?php echo $p['kode_barang']; ?> - <?php echo $p['nama_barang']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tanggal">Tanggal Opname</label>
                    <input type="date" name="tanggal" id="tanggal" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="stok_sistem">Stok Sistem (Gudang)</label>
                    <input type="number" name="stok_sistem" id="stok_sistem" readonly value="0">
                </div>

                <div class="form-group">
                    <label for="stok_fisik">Stok Fisik (Hasil Hitung)</label>
                    <input type="number" name="stok_fisik" id="stok_fisik" required min="0" oninput="calculateSelisih()">
                </div>

                <div class="form-group">
                    <label for="selisih">Selisih</label>
                    <input type="number" id="selisih" readonly value="0">
                </div>

                <div class="form-group full-width">
                    <label for="keterangan">Keterangan / Alasan</label>
                    <textarea name="keterangan" id="keterangan" rows="3" placeholder="Contoh: Barang rusak, salah input sebelumnya, atau kadaluarsa..."></textarea>
                </div>
            </div>

            <div class="form-footer">
                <a href="index.php" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Simpan Penyesuaian</button>
            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();

        function updateStokSistem() {
            const select = document.getElementById('product_id');
            const selectedOption = select.options[select.selectedIndex];
            const stokSistem = selectedOption.getAttribute('data-stok') || 0;
            document.getElementById('stok_sistem').value = stokSistem;
            calculateSelisih();
        }

        function calculateSelisih() {
            const sistem = parseInt(document.getElementById('stok_sistem').value) || 0;
            const fisik = parseInt(document.getElementById('stok_fisik').value) || 0;
            const selisih = fisik - sistem;
            
            const selisihInput = document.getElementById('selisih');
            selisihInput.value = selisih;

            if (selisih > 0) {
                selisihInput.style.color = '#10b981';
                selisihInput.style.fontWeight = 'bold';
            } else if (selisih < 0) {
                selisihInput.style.color = '#ef4444';
                selisihInput.style.fontWeight = 'bold';
            } else {
                selisihInput.style.color = '#64748b';
                selisihInput.style.fontWeight = 'normal';
            }
        }
    </script>
</body>
</html>
