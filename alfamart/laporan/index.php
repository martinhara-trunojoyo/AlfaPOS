<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

// Query Laporan
$query = "SELECT t.*, u.nama_lengkap 
          FROM transaksi t 
          JOIN users u ON t.user_id = u.id 
          WHERE DATE(t.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
          ORDER BY t.tanggal DESC";
$result = mysqli_query($koneksi, $query);

// Hitung Total Omzet dalam periode
$q_total = "SELECT SUM(total_bayar) as total FROM transaksi WHERE DATE(tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
$res_total = mysqli_query($koneksi, $q_total);
$data_total = mysqli_fetch_assoc($res_total);
$total_omzet = $data_total['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - AlfaPOS</title>
    <link rel="stylesheet" href="../stok_opname/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .filter-card {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            display: flex;
            align-items: flex-end;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        .summary-banner {
            background: linear-gradient(135deg, var(--primary), #c0141b);
            color: white;
            padding: 2rem;
            border-radius: 24px;
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-print {
            background: white;
            color: var(--primary);
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
        }
        .btn-print:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="header-opname">
        <div>
            <h2>Laporan Penjualan</h2>
            <p style="color: #64748b;">Pantau performa penjualan gerai Alfamart Anda.</p>
        </div>
    </div>

    <form method="GET" class="filter-card">
        <div class="form-group" style="margin: 0;">
            <label>Dari Tanggal</label>
            <input type="date" name="tgl_mulai" value="<?php echo $tgl_mulai; ?>">
        </div>
        <div class="form-group" style="margin: 0;">
            <label>Sampai Tanggal</label>
            <input type="date" name="tgl_selesai" value="<?php echo $tgl_selesai; ?>">
        </div>
        <button type="submit" class="btn-tambah" style="border: none; cursor: pointer;">
            <i data-lucide="filter"></i> Filter
        </button>
    </form>

    <div class="summary-banner">
        <div>
            <p style="opacity: 0.8; font-weight: 600;">Total Omzet Periode Ini</p>
            <h1 style="font-size: 2.5rem; font-weight: 800;">Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?></h1>
            <p style="margin-top: 0.5rem; font-size: 0.9rem; opacity: 0.9;">
                <i data-lucide="calendar" size="14" style="vertical-align: middle;"></i> 
                <?php echo date('d M Y', strtotime($tgl_mulai)); ?> - <?php echo date('d M Y', strtotime($tgl_selesai)); ?>
            </p>
        </div>
        <a href="cetak.php?tgl_mulai=<?php echo $tgl_mulai; ?>&tgl_selesai=<?php echo $tgl_selesai; ?>" target="_blank" class="btn-print">
            <i data-lucide="file-text"></i> Cetak PDF / Print
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. Transaksi</th>
                    <th>Tanggal & Waktu</th>
                    <th>Kasir</th>
                    <th>Total</th>
                    <th>Tunai</th>
                    <th>Kembali</th>
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
                    <td><span class="pic-badge"><?php echo $row['nama_lengkap']; ?></span></td>
                    <td><strong style="color: var(--primary);">Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></strong></td>
                    <td>Rp <?php echo number_format($row['bayar'], 0, ',', '.'); ?></td>
                    <td>Rp <?php echo number_format($row['kembali'], 0, ',', '.'); ?></td>
                </tr>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem;">Tidak ada transaksi pada periode ini.</td>
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
