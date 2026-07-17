<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

$tgl_mulai = $_GET['tgl_mulai'];
$tgl_selesai = $_GET['tgl_selesai'];

$query = "SELECT t.*, u.nama_lengkap 
          FROM transaksi t 
          JOIN users u ON t.user_id = u.id 
          WHERE DATE(t.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
          ORDER BY t.tanggal ASC";
$result = mysqli_query($koneksi, $query);

$q_total = "SELECT SUM(total_bayar) as total FROM transaksi WHERE DATE(tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'";
$res_total = mysqli_query($koneksi, $q_total);
$data_total = mysqli_fetch_assoc($res_total);
$total_omzet = $data_total['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Penjualan</title>
    <style>
        body { font-family: 'Arial', sans-serif; color: #333; padding: 20px; }
        .header { text-align: center; border-bottom: 3px solid #e11a22; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #e11a22; margin: 0; text-transform: uppercase; }
        .info { margin-bottom: 20px; display: flex; justify-content: space-between; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; font-size: 12px; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .total-row { font-weight: bold; background-color: #eee; }
        .footer { margin-top: 50px; text-align: right; font-size: 12px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h1>ALFAMART - LAPORAN PENJUALAN</h1>
        <p>Jl. Merdeka No. 123, Jakarta | Telp: (021) 12345678</p>
    </div>

    <div class="info">
        <div>
            <p><strong>Periode:</strong> <?php echo date('d/m/Y', strtotime($tgl_mulai)); ?> s/d <?php echo date('d/m/Y', strtotime($tgl_selesai)); ?></p>
            <p><strong>Dicetak oleh:</strong> <?php echo $_SESSION['nama_lengkap']; ?></p>
        </div>
        <div style="text-align: right;">
            <p><strong>Waktu Cetak:</strong> <?php echo date('d/m/Y H:i'); ?></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>No. Transaksi</th>
                <th>Tanggal</th>
                <th>Kasir</th>
                <th>Tunai</th>
                <th>Kembali</th>
                <th>Total Bayar</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($row = mysqli_fetch_assoc($result)): 
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo $row['nomor_transaksi']; ?></td>
                <td><?php echo date('d/m/Y H:i', strtotime($row['tanggal'])); ?></td>
                <td><?php echo $row['nama_lengkap']; ?></td>
                <td>Rp <?php echo number_format($row['bayar'], 0, ',', '.'); ?></td>
                <td>Rp <?php echo number_format($row['kembali'], 0, ',', '.'); ?></td>
                <td><strong>Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></strong></td>
            </tr>
            <?php endwhile; ?>
            <tr class="total-row">
                <td colspan="6" style="text-align: right;">TOTAL OMZET</td>
                <td>Rp <?php echo number_format($total_omzet, 0, ',', '.'); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>Jakarta, <?php echo date('d F Y'); ?></p>
        <br><br><br>
        <p>( <?php echo $_SESSION['nama_lengkap']; ?> )</p>
        <p>Manager Toko</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Tutup Halaman</button>
    </div>
</body>
</html>
