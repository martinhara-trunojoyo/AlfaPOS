<?php
session_start();
require_once '../koneksi/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$query = "SELECT so.*, p.nama_barang, p.kode_barang 
          FROM stok_opname so 
          JOIN products p ON so.product_id = p.id 
          WHERE so.id = '$id'";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: index.php");
    exit();
}

// Proses Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = $_POST['tanggal'];
    $stok_fisik_baru = $_POST['stok_fisik'];
    $stok_sistem = $data['stok_sistem'];
    $selisih_baru = $stok_fisik_baru - $stok_sistem;
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    
    $product_id = $data['product_id'];
    $stok_fisik_lama = $data['stok_fisik'];
    $diff_fisik = $stok_fisik_baru - $stok_fisik_lama;

    mysqli_begin_transaction($koneksi);

    try {
        // 1. Update ke tabel stok_opname
        $sql_update_so = "UPDATE stok_opname SET 
                          tanggal = '$tanggal', 
                          stok_fisik = '$stok_fisik_baru', 
                          selisih = '$selisih_baru', 
                          keterangan = '$keterangan' 
                          WHERE id = '$id'";
        mysqli_query($koneksi, $sql_update_so);

        // 2. Update stok di tabel products (sesuaikan dengan perubahan fisik)
        $sql_update_p = "UPDATE products SET stok = stok + ($diff_fisik) WHERE id = '$product_id'";
        mysqli_query($koneksi, $sql_update_p);

        mysqli_commit($koneksi);
        header("Location: index.php?status=updated");
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
    <title>Edit Stok Opname - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="header-opname">
        <div>
            <h2>Edit Stok Opname</h2>
            <p style="color: #64748b;">Koreksi data penyesuaian stok untuk <strong><?php echo $data['nama_barang']; ?></strong>.</p>
        </div>
    </div>

    <div class="form-card">
        <form action="" method="POST">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Produk</label>
                    <input type="text" value="<?php echo $data['kode_barang'] . ' - ' . $data['nama_barang']; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="tanggal">Tanggal Opname</label>
                    <input type="date" name="tanggal" id="tanggal" value="<?php echo $data['tanggal']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="stok_sistem">Stok Sistem (Saat itu)</label>
                    <input type="number" id="stok_sistem" value="<?php echo $data['stok_sistem']; ?>" readonly>
                </div>

                <div class="form-group">
                    <label for="stok_fisik">Stok Fisik Baru</label>
                    <input type="number" name="stok_fisik" id="stok_fisik" value="<?php echo $data['stok_fisik']; ?>" required min="0" oninput="calculateSelisih()">
                </div>

                <div class="form-group">
                    <label for="selisih">Selisih Baru</label>
                    <input type="number" id="selisih" value="<?php echo $data['selisih']; ?>" readonly>
                </div>

                <div class="form-group full-width">
                    <label for="keterangan">Keterangan / Alasan</label>
                    <textarea name="keterangan" id="keterangan" rows="3"><?php echo $data['keterangan']; ?></textarea>
                </div>
            </div>

            <div class="form-footer">
                <a href="index.php" class="btn-batal">Batal</a>
                <button type="submit" class="btn-simpan">Update Penyesuaian</button>
            </div>
        </form>
    </div>

    <script>
        lucide.createIcons();

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
        
        // Init color
        calculateSelisih();
    </script>
</body>
</html>
