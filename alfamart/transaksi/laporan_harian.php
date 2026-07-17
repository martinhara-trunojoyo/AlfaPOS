<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$nama_kasir = $_SESSION['nama_lengkap'];
$hari_ini = date('Y-m-d');

// 1. Ambil Statistik Hari Ini untuk Kasir ini
$q_stats = "SELECT COUNT(*) as total_trx, SUM(total_bayar) as total_omzet 
            FROM transaksi 
            WHERE user_id = '$user_id' AND DATE(tanggal) = '$hari_ini'";
$res_stats = mysqli_query($koneksi, $q_stats);
$stats = mysqli_fetch_assoc($res_stats);

// 2. Ambil Daftar Transaksi Hari Ini
$q_list = "SELECT * FROM transaksi 
           WHERE user_id = '$user_id' AND DATE(tanggal) = '$hari_ini' 
           ORDER BY tanggal DESC";
$res_list = mysqli_query($koneksi, $q_list);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Harian Kasir - AlfaPOS</title>
    <link rel="stylesheet" href="../stok_opname/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .summary-item {
            background: white;
            padding: 2rem;
            border-radius: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        .summary-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>
<body>
    <div class="header-opname">
        <div>
            <h2>Laporan Penjualan Harian</h2>
            <p style="color: #64748b;">Ringkasan aktivitas Anda hari ini, <strong><?php echo date('d F Y'); ?></strong>.</p>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-item">
            <div class="summary-icon" style="background: rgba(225, 26, 34, 0.1); color: var(--primary);">
                <i data-lucide="banknote" size="32"></i>
            </div>
            <div>
                <p style="color: #64748b; font-weight: 600; font-size: 0.9rem;">Omzet Saya Hari Ini</p>
                <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b;">Rp <?php echo number_format($stats['total_omzet'] ?? 0, 0, ',', '.'); ?></h2>
            </div>
        </div>
        <div class="summary-item">
            <div class="summary-icon" style="background: rgba(0, 85, 164, 0.1); color: var(--accent);">
                <i data-lucide="shopping-cart" size="32"></i>
            </div>
            <div>
                <p style="color: #64748b; font-weight: 600; font-size: 0.9rem;">Total Transaksi</p>
                <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b;"><?php echo $stats['total_trx']; ?> Struk</h2>
            </div>
        </div>
    </div>

    <div class="table-container">
        <div style="padding-bottom: 1.5rem; border-bottom: 1px solid #f1f5f9; margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="list" style="color: var(--primary);"></i> Daftar Penjualan Hari Ini
            </h3>
            <button onclick="window.print()" style="background: #f1f5f9; border: none; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; color: #475569;">
                <i data-lucide="printer" size="16"></i> Cetak Laporan
            </button>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>No. Transaksi</th>
                    <th>Total Belanja</th>
                    <th>Bayar (Tunai)</th>
                    <th>Kembali</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($res_list)): ?>
                <tr>
                    <td><?php echo date('H:i:s', strtotime($row['tanggal'])); ?></td>
                    <td><strong style="color: var(--accent);"><?php echo $row['nomor_transaksi']; ?></strong></td>
                    <td><strong style="color: var(--primary);">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></strong></td>
                    <td>Rp <?php echo number_format($row['bayar'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($row['kembali'], 0, ',', '.'); ?></td>
                    <td>
                        <a href="detail.php?id=<?php echo $row['id']; ?>" class="btn-edit" title="Lihat Detail"><i data-lucide="eye"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($res_list) == 0): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 3rem;">Anda belum memproses transaksi hari ini.</td>
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
    </script>
</body>
</html>
