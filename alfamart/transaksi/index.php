<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Query ambil data transaksi
$query = "SELECT t.*, u.nama_lengkap 
          FROM transaksi t 
          JOIN users u ON t.user_id = u.id 
          ORDER BY t.tanggal DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - AlfaPOS</title>
    <link rel="stylesheet" href="../stok_opname/style.css"> <!-- Reusing table styles -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-opname">
        <div>
            <h2>Riwayat Transaksi (POS)</h2>
            <p style="color: #64748b;">Daftar seluruh transaksi penjualan tunai.</p>
        </div>
        <a href="tambah.php" class="btn-tambah">
            <i data-lucide="plus-circle"></i>
            Transaksi Baru (POS)
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Transaksi</th>
                    <th>Tanggal & Waktu</th>
                    <th>Total Bayar</th>
                    <th>Tunai</th>
                    <th>Kembali</th>
                    <th>Kasir</th>
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
                    <td><strong style="color: var(--accent);"><?php echo $row['nomor_transaksi']; ?></strong></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                    <td><strong style="color: var(--primary);">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></strong></td>
                    <td>Rp <?php echo number_format($row['bayar'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($row['kembali'], 0, ',', '.'); ?></td>
                    <td><span class="pic-badge"><?php echo $row['nama_lengkap']; ?></span></td>
                    <td>
                        <a href="detail.php?id=<?php echo $row['id']; ?>" class="btn-edit" title="Lihat Detail"><i data-lucide="eye"></i></a>
                        <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn-hapus" onclick="return confirm('Hapus riwayat transaksi ini?')"><i data-lucide="trash-2"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="8" style="text-align: center; padding: 3rem;">Belum ada riwayat transaksi.</td>
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
        if (urlParams.get('status') === 'deleted') {
            Swal.fire('Dihapus!', 'Riwayat transaksi telah dihapus.', 'success');
        }
    </script>
</body>
</html>
