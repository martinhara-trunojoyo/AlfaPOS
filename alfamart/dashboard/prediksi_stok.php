<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// 1. Persiapkan Data untuk Python (Export ke CSV)
$csv_file = 'C:/Users/T480/.gemini/antigravity/brain/f88c43d7-793a-4527-9fe0-e45b56508d0c/scratch/sales_history.csv';
$fp = fopen($csv_file, 'w');
fputcsv($fp, ['product_id', 'tanggal', 'jumlah']);

// Query ambil history per produk per hari
$query = "SELECT product_id, DATE(tanggal) as tgl, SUM(jumlah) as total_qty 
          FROM detail_transaksi dt 
          JOIN transaksi t ON dt.transaksi_id = t.id 
          GROUP BY product_id, tgl 
          ORDER BY tgl ASC";
$res = mysqli_query($koneksi, $query);

while ($row = mysqli_fetch_assoc($res)) {
    fputcsv($fp, [$row['product_id'], $row['tgl'], $row['total_qty']]);
}
fclose($fp);

// 2. Jalankan Script Python
$python_script = 'C:/Users/T480/.gemini/antigravity/brain/f88c43d7-793a-4527-9fe0-e45b56508d0c/scratch/forecast.py';
$command = "python $python_script 2>&1";
$output = shell_exec($command);

// Debugging jika error
$forecast_data = json_decode($output, true);
if (!$forecast_data) {
    $error_msg = "Gagal menjalankan AI Forecasting. Pastikan Python terinstal.\nOutput: " . $output;
}

// 3. Ambil data stok saat ini untuk perbandingan
$products_stok = [];
$res_p = mysqli_query($koneksi, "SELECT id, nama_barang, stok, kode_barang FROM products");
while($p = mysqli_fetch_assoc($res_p)) {
    $products_stok[$p['id']] = $p;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prediksi Stok AI - AlfaPOS</title>
    <link rel="stylesheet" href="../stok_opname/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .status-cukup { background: #dcfce7; color: #16a34a; }
        .status-kurang { background: #fee2e2; color: #dc2626; }
        .prediction-value { font-size: 1.2rem; font-weight: 800; color: var(--accent); }
    </style>
</head>
<body>
    <div class="header-opname">
        <div>
            <h2><i data-lucide="brain-circuit" style="vertical-align: middle; margin-right: 0.5rem;"></i> Prediksi Stok AI</h2>
            <p style="color: #64748b;">Analisis cerdas menggunakan Scikit-Learn untuk memprediksi kebutuhan stok esok hari.</p>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div style="background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 12px; margin-bottom: 2rem;">
            <strong>Error:</strong> <?php echo nl2br(htmlspecialchars($error_msg)); ?>
        </div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Stok Saat Ini</th>
                    <th>Prediksi Terjual (Esok)</th>
                    <th>Sisa Stok (Estimasi)</th>
                    <th>Rekomendasi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if ($forecast_data) {
                    foreach ($forecast_data as $pid => $prediction): 
                        if (!isset($products_stok[$pid])) continue;
                        $p = $products_stok[$pid];
                        $pred_val = round($prediction, 1);
                        $sisa = $p['stok'] - $pred_val;
                        $is_cukup = $sisa >= 0;
                ?>
                <tr>
                    <td>
                        <strong><?php echo $p['nama_barang']; ?></strong><br>
                        <small style="color: #94a3b8;"><?php echo $p['kode_barang']; ?></small>
                    </td>
                    <td><span style="font-weight: 700;"><?php echo $p['stok']; ?> unit</span></td>
                    <td><span class="prediction-value"><?php echo $pred_val; ?></span> <small>unit</small></td>
                    <td>
                        <span style="font-weight: 700; color: <?php echo $is_cukup ? '#16a34a' : '#dc2626'; ?>">
                            <?php echo max(0, round($sisa, 1)); ?> unit
                        </span>
                    </td>
                    <td>
                        <?php if ($is_cukup): ?>
                            <span class="status-badge status-cukup">Stok Aman</span>
                        <?php else: ?>
                            <span class="status-badge status-kurang">Segera Restock!</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php 
                    endforeach; 
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; padding:3rem;'>Menunggu data forecasting...</td></tr>";
                }
                ?>
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
