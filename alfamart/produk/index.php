<?php
session_start();
require_once '../koneksi/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Query ambil data produk dan supplier
$query = "SELECT p.*, s.nama_supplier 
          FROM products p 
          LEFT JOIN suppliers s ON p.supplier_id = s.id 
          ORDER BY p.id DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Barang - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-produk">
        <div>
            <h2>Data Barang / Produk</h2>
            <p style="color: #64748b;">Kelola inventori stok gerai Alfamart Anda.</p>
        </div>
        <a href="tambah.php" class="btn-tambah">
            <i data-lucide="plus-circle"></i>
            Tambah Barang Baru
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Supplier</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result)): 
                    $stok_class = ($row['stok'] <= $row['stok_minimum']) ? 'stok-kritis' : 'stok-aman';
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><strong style="color: var(--accent);"><?php echo $row['kode_barang']; ?></strong></td>
                    <td><strong><?php echo $row['nama_barang']; ?></strong></td>
                    <td><?php echo $row['kategori']; ?></td>
                    <td>Rp <?php echo number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                    <td><span style="color: var(--primary); font-weight: 700;">Rp <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></span></td>
                    <td>
                        <span class="badge-stok <?php echo $stok_class; ?>">
                            <?php echo $row['stok']; ?>
                        </span>
                    </td>
                    <td><?php echo $row['nama_supplier'] ?? '-'; ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i data-lucide="edit-3"></i></a>
                        <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus produk ini?')"><i data-lucide="trash-2"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="9" style="text-align: center; padding: 3rem;">Belum ada data produk.</td>
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

        // Notifikasi Sukses
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'success') {
            Swal.fire('Berhasil!', 'Barang baru telah ditambahkan.', 'success');
        } else if (status === 'updated') {
            Swal.fire('Berhasil!', 'Data barang telah diperbarui.', 'success');
        } else if (status === 'deleted') {
            Swal.fire('Dihapus!', 'Barang telah dihapus dari sistem.', 'success');
        }
    </script>
</body>
</html>
