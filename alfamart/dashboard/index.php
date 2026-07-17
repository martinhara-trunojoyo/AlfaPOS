<?php
session_start();
require_once '../koneksi/koneksi.php';

// Proteksi halaman dashboard
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$nama = $_SESSION['nama_lengkap'];

// 1. Ambil Omzet Hari Ini
$q_omzet = "SELECT SUM(total_bayar) as omzet FROM transaksi WHERE DATE(tanggal) = CURDATE()";
if ($role != 'kepala_toko') $q_omzet .= " AND user_id = '$user_id'";
$res_omzet = mysqli_query($koneksi, $q_omzet);
$data_omzet = mysqli_fetch_assoc($res_omzet);
$omzet_hari_ini = $data_omzet['omzet'] ?? 0;

// 2. Total Transaksi Hari Ini
$q_trx = "SELECT COUNT(*) as total FROM transaksi WHERE DATE(tanggal) = CURDATE()";
if ($role != 'kepala_toko') $q_trx .= " AND user_id = '$user_id'";
$res_trx = mysqli_query($koneksi, $q_trx);
$data_trx = mysqli_fetch_assoc($res_trx);
$total_trx = $data_trx['total'] ?? 0;

// 3. Stok Menipis (Hanya untuk Kepala Toko)
$low_stock_count = 0;
if ($role == 'kepala_toko') {
    $q_low = "SELECT COUNT(*) as low_stock FROM products WHERE stok <= stok_minimum";
    $res_low = mysqli_query($koneksi, $q_low);
    $data_low = mysqli_fetch_assoc($res_low);
    $low_stock_count = $data_low['low_stock'] ?? 0;
}

// 4. Transaksi Terbaru
$q_recent = "SELECT t.*, u.nama_lengkap 
             FROM transaksi t 
             JOIN users u ON t.user_id = u.id ";
if ($role != 'kepala_toko') $q_recent .= " WHERE t.user_id = '$user_id' ";
$q_recent .= " ORDER BY t.tanggal DESC LIMIT 5";
$res_recent = mysqli_query($koneksi, $q_recent);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard <?php echo ($role == 'kepala_toko' ? 'Kepala Toko' : 'Kasir'); ?> - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-logo">
                <i data-lucide="shopping-cart"></i>
                <span>Alfa<span>POS</span></span>
            </div>
            
            <ul class="nav-menu">
                <span class="menu-label">Overview</span>
                <li class="nav-item">
                    <a href="#" class="nav-link active">
                        <i data-lucide="layout-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <?php if ($role == 'kepala_toko'): ?>
                <!-- Kelompok 2: Arus Barang -->
                <span class="menu-label">Arus Barang</span>
                <li class="nav-item">
                    <a href="../produk/index.php" class="nav-link">
                        <i data-lucide="package"></i>
                        <span>Manajemen Barang</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../supplier/index.php" class="nav-link">
                        <i data-lucide="truck"></i>
                        <span>Supplier</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../stok_opname/index.php" class="nav-link">
                        <i data-lucide="clipboard-list"></i>
                        <span>Stok Opname</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="prediksi_stok.php" class="nav-link" style="background: rgba(255, 235, 0, 0.1); border: 1px solid rgba(255, 235, 0, 0.2);">
                        <i data-lucide="brain-circuit" style="color: var(--secondary);"></i>
                        <span style="color: var(--secondary);">Prediksi Stok AI</span>
                    </a>
                </li>

                <!-- Kelompok 3: Arus Orang -->
                <span class="menu-label">Arus Orang</span>
                <li class="nav-item">
                    <a href="../kasir/index.php" class="nav-link">
                        <i data-lucide="users"></i>
                        <span>Manajemen Kasir</span>
                    </a>
                </li>

                <!-- Kelompok 4: Analitik & Sistem -->
                <span class="menu-label">Analitik & Sistem</span>
                <li class="nav-item">
                    <a href="../laporan/index.php" class="nav-link">
                        <i data-lucide="bar-chart-3"></i>
                        <span>Laporan Penjualan</span>
                    </a>
                </li>
                <?php else: ?>
                <!-- Menu Khusus Kasir (Tetap disederhanakan) -->
                <span class="menu-label">Operasional</span>
                <li class="nav-item">
                    <a href="../transaksi/tambah.php" class="nav-link">
                        <i data-lucide="shopping-bag"></i>
                        <span>Transaksi (POS)</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../transaksi/cek_harga.php" class="nav-link">
                        <i data-lucide="search"></i>
                        <span>Cek Harga</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../transaksi/index.php" class="nav-link">
                        <i data-lucide="history"></i>
                        <span>Riwayat Transaksi</span>
                    </a>
                </li>
                <span class="menu-label">Laporan</span>
                <li class="nav-item">
                    <a href="../transaksi/laporan_harian.php" class="nav-link">
                        <i data-lucide="file-text"></i>
                        <span>Laporan Harian</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <span class="menu-label">Konfigurasi</span>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i data-lucide="settings"></i>
                        <span>Pengaturan</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <a href="../login/logout.php" class="nav-link" style="color: #ffeb00;">
                    <i data-lucide="log-out"></i>
                    <span>Keluar Sistem</span>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <header class="top-bar">
                <div class="page-title">
                    <h2>Ringkasan Dashboard</h2>
                    <p>Selamat datang kembali, <strong><?php echo $nama; ?></strong></p>
                </div>
                <div class="top-bar-right">
                    <!-- Notification System -->
                    <div class="notification-wrapper">
                        <div class="notification-trigger" id="notifTrigger">
                            <i data-lucide="bell"></i>
                            <span class="notif-badge">3</span>
                        </div>
                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-header">Notifikasi Terbaru</div>
                            <div class="notif-list">
                                <div class="notif-item">
                                    <div class="notif-icon" style="background: #fee2e2; color: #ef4444;"><i data-lucide="alert-circle"></i></div>
                                    <div class="notif-text">
                                        <p>Stok <strong>Susu UHT</strong> sisa 5 unit!</p>
                                        <span>5 menit yang lalu</span>
                                    </div>
                                </div>
                                <div class="notif-item">
                                    <div class="notif-icon" style="background: #dcfce7; color: #22c55e;"><i data-lucide="check-circle"></i></div>
                                    <div class="notif-text">
                                        <p>Setoran Kasir #002 telah diverifikasi.</p>
                                        <span>1 jam yang lalu</span>
                                    </div>
                                </div>
                                <div class="notif-item">
                                    <div class="notif-icon" style="background: #e0f2fe; color: #0ea5e9;"><i data-lucide="info"></i></div>
                                    <div class="notif-text">
                                        <p>Update sistem AlfaPOS v2.4 tersedia.</p>
                                        <span>Kemarin</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="user-info">
                        <div class="info-text" style="text-align: right;">
                            <p style="font-weight: 700; margin: 0;"><?php echo $nama; ?></p>
                            <span class="role-badge"><?php echo ($role == 'kepala_toko' ? 'Kepala Toko' : 'Kasir'); ?></span>
                        </div>
                        <div class="avatar"><?php echo substr($nama, 0, 1); ?></div>
                    </div>
                </div>
            </header>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <?php if ($role == 'kepala_toko'): ?>
                <div class="stat-card" style="--border-color: #e11a22;">
                    <div class="stat-icon" style="background: rgba(225, 26, 34, 0.1); color: #e11a22;">
                        <i data-lucide="dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Omzet Hari Ini</h4>
                        <p>Rp <?php echo number_format($omzet_hari_ini, 0, ',', '.'); ?></p>
                    </div>
                </div>
                <div class="stat-card" style="--border-color: #0055a4;">
                    <div class="stat-icon" style="background: rgba(0, 85, 164, 0.1); color: #0055a4;">
                        <i data-lucide="shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Total Transaksi</h4>
                        <p><?php echo $total_trx; ?></p>
                    </div>
                </div>
                <div class="stat-card" style="--border-color: #ffeb00;">
                    <div class="stat-icon" style="background: rgba(255, 235, 0, 0.1); color: #a16207;">
                        <i data-lucide="alert-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Stok Menipis</h4>
                        <p><?php echo $low_stock_count; ?> Produk</p>
                    </div>
                </div>
                <?php else: ?>
                <div class="stat-card" style="--border-color: #e11a22;">
                    <div class="stat-icon" style="background: rgba(225, 26, 34, 0.1); color: #e11a22;">
                        <i data-lucide="banknote"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Penjualan Saya</h4>
                        <p>Rp <?php echo number_format($omzet_hari_ini, 0, ',', '.'); ?></p>
                    </div>
                </div>
                <div class="stat-card" style="--border-color: #0055a4;">
                    <div class="stat-icon" style="background: rgba(0, 85, 164, 0.1); color: #0055a4;">
                        <i data-lucide="credit-card"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Transaksi Kas</h4>
                        <p><?php echo $total_trx; ?></p>
                    </div>
                </div>
                <div class="stat-card" style="--border-color: #ffeb00;">
                    <div class="stat-icon" style="background: rgba(255, 235, 0, 0.1); color: #a16207;">
                        <i data-lucide="star"></i>
                    </div>
                    <div class="stat-info">
                        <h4>Poin Member</h4>
                        <p>Non-Aktif</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Recent Activity Table -->
            <div class="content-card">
                <div class="card-header">
                    <h3><?php echo ($role == 'kepala_toko' ? 'Transaksi Terbaru Seluruh Kasir' : 'Aktivitas Transaksi Terakhir Saya'); ?></h3>
                    <a href="../transaksi/index.php" style="padding: 0.5rem 1rem; background: #f1f5f9; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; text-decoration: none; color: inherit; font-size: 0.8rem;">Lihat Semua</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>No. Struk</th>
                            <th>Kasir</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($res_recent)): ?>
                        <tr>
                            <td><?php echo date('H:i:s', strtotime($row['tanggal'])); ?></td>
                            <td><strong style="color: var(--accent);"><?php echo $row['nomor_transaksi']; ?></strong></td>
                            <td><?php echo $row['nama_lengkap']; ?></td>
                            <td>Rp <?php echo number_format($row['total_bayar'], 0, ',', '.'); ?></td>
                            <td><span class="status-pill status-success">Selesai</span></td>
                            <td>
                                <a href="../transaksi/index.php" class="btn-action">
                                    <i data-lucide="eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        
                        <?php if (mysqli_num_rows($res_recent) == 0): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">Belum ada aktivitas transaksi hari ini.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Receipt -->
    <div class="modal-overlay" id="modalReceipt">
        <div class="receipt-paper">
            <div class="receipt-header">
                <i data-lucide="shopping-cart" style="color: #e11a22;"></i>
                <h3>ALFAMART</h3>
                <p>Jl. Merdeka No. 123, Jakarta</p>
            </div>
            <div class="receipt-info">
                <p>No. Struk: <span id="receiptNumber">#ALF-000000</span></p>
                <p>Kasir: <?php echo $nama; ?></p>
                <p>Tgl: <?php echo date('d/m/Y H:i'); ?></p>
            </div>
            <div class="receipt-divider"></div>
            <div class="receipt-items">
                <div class="item-row">
                    <span>Indomie Goreng x2</span>
                    <span>Rp 6.000</span>
                </div>
                <div class="item-row">
                    <span>Aqua 600ml x1</span>
                    <span>Rp 4.000</span>
                </div>
                <div class="item-row">
                    <span>Susu UHT 250ml x1</span>
                    <span>Rp 8.000</span>
                </div>
            </div>
            <div class="receipt-divider"></div>
            <div class="receipt-total">
                <span>TOTAL</span>
                <span id="receiptTotal">Rp 18.000</span>
            </div>
            <div class="receipt-footer">
                <p>Terima Kasih Atas Kunjungan Anda</p>
                <p>Belanja Puas, Harga Pas!</p>
                <button class="btn-close-receipt" onclick="closeReceipt()">Tutup</button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Notif Dropdown Toggle
        const notifTrigger = document.getElementById('notifTrigger');
        const notifDropdown = document.getElementById('notifDropdown');
        notifTrigger.addEventListener('click', () => {
            notifDropdown.classList.toggle('active');
        });

        // Receipt Modal Toggle
        const modalReceipt = document.getElementById('modalReceipt');
        function showReceipt(id) {
            document.getElementById('receiptNumber').innerText = id;
            modalReceipt.classList.add('active');
        }
        function closeReceipt() {
            modalReceipt.classList.remove('active');
        }

        // Close dropdown when clicking outside
        window.onclick = function(event) {
            if (!event.target.closest('.notification-wrapper')) {
                notifDropdown.classList.remove('active');
            }
            if (event.target == modalReceipt) {
                closeReceipt();
            }
        }
    </script>
</body>
</html>
