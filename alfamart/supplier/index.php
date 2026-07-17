<?php
session_start();
require_once '../koneksi/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Query ambil data supplier
$query = "SELECT * FROM suppliers ORDER BY nama_supplier ASC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Supplier - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-supplier">
        <div>
            <h2>Data Supplier</h2>
            <p style="color: #64748b;">Kelola daftar penyedia barang gerai Alfamart Anda.</p>
        </div>
        <a href="tambah.php" class="btn-tambah">
            <i data-lucide="plus-circle"></i>
            Tambah Supplier
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Supplier</th>
                    <th>Nama PIC</th>
                    <th>No. Telp</th>
                    <th>Email</th>
                    <th>Alamat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result)): 
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><strong style="color: var(--primary);"><?php echo $row['nama_supplier']; ?></strong></td>
                    <td><span class="pic-badge"><?php echo $row['nama_pic'] ?? '-'; ?></span></td>
                    <td><?php echo $row['no_telp'] ?? '-'; ?></td>
                    <td><a href="mailto:<?php echo $row['email']; ?>" style="color: var(--accent); text-decoration: none;"><?php echo $row['email'] ?? '-'; ?></a></td>
                    <td><small><?php echo $row['alamat'] ?? '-'; ?></small></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i data-lucide="edit-3"></i></a>
                        <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus supplier ini?')"><i data-lucide="trash-2"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem;">Belum ada data supplier.</td>
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

        // Notifikasi Sukses (jika ada param status)
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');

        if (status === 'success') {
            Swal.fire('Berhasil!', 'Supplier baru telah ditambahkan.', 'success');
        } else if (status === 'updated') {
            Swal.fire('Berhasil!', 'Data supplier telah diperbarui.', 'success');
        } else if (status === 'deleted') {
            Swal.fire('Dihapus!', 'Supplier telah dihapus dari sistem.', 'success');
        } else if (status === 'error_relation') {
            Swal.fire('Gagal Menghapus', 'Supplier tidak bisa dihapus karena masih ada produk yang terhubung dengan supplier ini.', 'error');
        }
    </script>
</body>
</html>
