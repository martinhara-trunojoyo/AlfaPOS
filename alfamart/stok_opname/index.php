<?php
session_start();
require_once '../koneksi/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Query ambil data stok opname
$query = "SELECT so.*, p.nama_barang, p.kode_barang 
          FROM stok_opname so 
          JOIN products p ON so.product_id = p.id 
          ORDER BY so.id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stok Opname - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-opname">
        <div>
            <h2>Riwayat Stok Opname</h2>
            <p style="color: #64748b;">Pantau penyesuaian stok barang gerai Alfamart Anda.</p>
        </div>
        <a href="tambah.php" class="btn-tambah">
            <i data-lucide="plus-circle"></i>
            Tambah Stok Opname
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Kode Barang</th>
                    <th>Nama Produk</th>
                    <th>Stok Sistem</th>
                    <th>Stok Fisik</th>
                    <th>Selisih</th>
                    <th>Keterangan</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result)): 
                    $selisih = $row['selisih'];
                    $selisih_class = 'selisih-zero';
                    if ($selisih > 0) $selisih_class = 'selisih-plus';
                    if ($selisih < 0) $selisih_class = 'selisih-minus';
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                    <td><strong style="color: var(--accent);"><?php echo $row['kode_barang']; ?></strong></td>
                    <td><strong><?php echo $row['nama_barang']; ?></strong></td>
                    <td><?php echo $row['stok_sistem']; ?></td>
                    <td><?php echo $row['stok_fisik']; ?></td>
                    <td>
                        <span class="<?php echo $selisih_class; ?>">
                            <?php echo ($selisih > 0 ? '+' : '') . $selisih; ?>
                        </span>
                    </td>
                    <td><?php echo $row['keterangan'] ?: '-'; ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i data-lucide="edit-3"></i></a>
                        <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus data opname ini?')"><i data-lucide="trash-2"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 3rem;">Belum ada data stok opname.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <center style="margin-top: 3rem;">
        <a href="../dashboard/index.php" style="text-decoration: none; color: #64748b; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 700;">
            <i data-lucide="arrow-left"></i> Kembali ke Dashboard
        </a>
    </center>

    <script>
        lucide.createIcons();

        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'success') {
            Swal.fire('Berhasil!', 'Stok opname telah dicatat dan stok barang diperbarui.', 'success');
        } else if (status === 'deleted') {
            Swal.fire('Dihapus!', 'Data stok opname telah dihapus.', 'success');
        } else if (status === 'error') {
            Swal.fire('Gagal!', 'Terjadi kesalahan pada sistem.', 'error');
        }
    </script>
</body>
</html>
