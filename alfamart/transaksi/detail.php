<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];

// 1. Ambil Header Transaksi
$query_t = "SELECT t.*, u.nama_lengkap 
            FROM transaksi t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.id = '$id'";
$res_t = mysqli_query($koneksi, $query_t);
$trx = mysqli_fetch_assoc($res_t);

if (!$trx) {
    header("Location: index.php");
    exit();
}

// 2. Ambil Detail Barang
$query_d = "SELECT dt.*, p.nama_barang, p.kode_barang 
            FROM detail_transaksi dt 
            JOIN products p ON dt.product_id = p.id 
            WHERE dt.transaksi_id = '$id'";
$res_d = mysqli_query($koneksi, $query_d);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - AlfaPOS</title>
    <link rel="stylesheet" href="../stok_opname/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="header-opname">
        <div>
            <h2>Detail Transaksi</h2>
            <p style="color: #64748b;">Rincian belanja untuk nomor struk <strong><?php echo $trx['nomor_transaksi']; ?></strong></p>
        </div>
        <a href="index.php" class="btn-batal" style="background: #e2e8f0; color: #475569; display: flex; align-items: center; gap: 0.5rem; padding: 0.8rem 1.5rem; text-decoration: none; border-radius: 12px; font-weight: 700;">
            <i data-lucide="arrow-left"></i> Kembali
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 2rem; margin-bottom: 2rem;">
        <!-- Left: Items Table -->
        <div class="table-container">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="shopping-basket" style="color: var(--primary);"></i> Daftar Barang
            </h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Barang</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($item = mysqli_fetch_assoc($res_d)): 
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <strong><?php echo $item['nama_barang']; ?></strong><br>
                            <small style="color: #94a3b8;"><?php echo $item['kode_barang']; ?></small>
                        </td>
                        <td>Rp <?php echo number_format($item['harga_satuan'], 0, ',', '.'); ?></td>
                        <td><?php echo $item['jumlah']; ?></td>
                        <td><strong>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></strong></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Right: Summary Card -->
        <div class="form-card" style="margin: 0; max-width: 100%;">
            <h3 style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <i data-lucide="info" style="color: var(--accent);"></i> Informasi Pembayaran
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-weight: 600;">Nomor Struk</span>
                    <span style="font-weight: 800; color: var(--accent);"><?php echo $trx['nomor_transaksi']; ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-weight: 600;">Waktu Transaksi</span>
                    <span><?php echo date('d M Y, H:i', strtotime($trx['tanggal'])); ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding-bottom: 1rem; border-bottom: 1px solid #f1f5f9;">
                    <span style="color: #64748b; font-weight: 600;">Nama Kasir</span>
                    <span class="pic-badge"><?php echo $trx['nama_lengkap']; ?></span>
                </div>
                
                <div style="margin-top: 1.5rem; background: #f8fafc; padding: 1.5rem; border-radius: 16px;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem;">
                        <span style="font-weight: 600;">Total Belanja</span>
                        <span style="font-weight: 800; color: var(--primary); font-size: 1.2rem;">Rp <?php echo number_format($trx['total_bayar'], 0, ',', '.'); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem;">
                        <span style="font-weight: 600;">Tunai (Cash)</span>
                        <span>Rp <?php echo number_format($trx['bayar'], 0, ',', '.'); ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding-top: 0.8rem; border-top: 2px dashed #e2e8f0;">
                        <span style="font-weight: 600;">Kembalian</span>
                        <span style="font-weight: 800; color: #16a34a;">Rp <?php echo number_format($trx['kembali'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                    <button onclick="window.print()" style="flex: 1; padding: 1rem; background: var(--accent); color: white; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <i data-lucide="printer"></i> Cetak Ulang Struk
                    </button>
                    <a href="hapus.php?id=<?php echo $trx['id']; ?>" onclick="return confirm('Yakin ingin menghapus transaksi ini?')" style="flex: 0.5; padding: 1rem; background: #fee2e2; color: #dc2626; text-decoration: none; border-radius: 12px; font-weight: 700; text-align: center;">
                        <i data-lucide="trash-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
