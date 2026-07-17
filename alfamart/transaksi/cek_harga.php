<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Ambil data produk untuk pencarian manual
$query = "SELECT * FROM products ORDER BY nama_barang ASC";
$result = mysqli_query($koneksi, $query);
$produk = [];
while($row = mysqli_fetch_assoc($result)) {
    $produk[] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Harga - AlfaPOS</title>
    <link rel="stylesheet" href="../transaksi/style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .cek-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .hardware-alert {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            padding: 1.5rem;
            border-radius: 16px;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            color: #92400e;
        }
        .hardware-alert i { color: #f59e0b; }
        
        .price-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            display: none;
        }
        .price-card.active { display: block; }
        .price-card h1 { font-size: 3.5rem; color: var(--primary); font-weight: 800; margin: 1rem 0; }
        .price-card h2 { font-size: 1.5rem; color: #1e293b; }
        
        .search-area {
            background: white;
            padding: 2rem;
            border-radius: 24px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }
    </style>
</head>
<body style="overflow-y: auto; height: auto; background-color: #f8fafc;">
    <div class="cek-container">
        <div class="header-opname" style="margin-bottom: 2rem;">
            <div>
                <h2>Cek Harga Barang</h2>
                <p style="color: #64748b;">Gunakan pemindaian otomatis atau cari produk secara manual.</p>
            </div>
        </div>

        <!-- Fake Hardware Error -->
        <div class="hardware-alert" id="hardwareAlert">
            <div style="background: #fef3c7; padding: 1rem; border-radius: 12px;">
                <i data-lucide="alert-triangle" size="28"></i>
            </div>
            <div>
                <h4 style="margin: 0; font-size: 1.1rem; font-weight: 800;">Hardware Not Detected</h4>
                <p style="margin: 0.2rem 0 0; font-size: 0.9rem; opacity: 0.8;">
                    Alat deteksi harga tidak terpasang (error atau rusak atau periksa kembali kabel nya).
                </p>
            </div>
            <button onclick="retryConnection()" style="margin-left: auto; background: #f59e0b; color: white; border: none; padding: 0.6rem 1rem; border-radius: 8px; font-weight: 700; cursor: pointer;">
                Coba Lagi
            </button>
        </div>

        <!-- Manual Search Area -->
        <div class="search-area">
            <div class="search-box" style="margin-bottom: 0;">
                <i data-lucide="search"></i>
                <input type="text" id="manualSearch" placeholder="Ketik nama barang atau scan barcode manual di sini..." onkeyup="searchPrice()">
            </div>
            <p style="margin-top: 1rem; font-size: 0.8rem; color: #94a3b8; text-align: center;">
                <i data-lucide="info" size="12" style="vertical-align: middle;"></i> Masukkan kode barang atau nama produk untuk melihat harga terbaru.
            </p>
        </div>

        <!-- Result Display -->
        <div class="price-card" id="priceCard">
            <p id="resKode" style="color: var(--accent); font-weight: 700; text-transform: uppercase; letter-spacing: 1px;"></p>
            <h2 id="resNama">Nama Produk</h2>
            <p style="color: #64748b; font-size: 0.9rem;">Harga Jual Alfamart:</p>
            <h1 id="resHarga">Rp 0</h1>
            <div style="display: flex; justify-content: center; gap: 1rem; margin-top: 1.5rem;">
                <span id="resKategori" style="background: #f1f5f9; padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700;"></span>
                <span id="resStok" style="background: #f1f5f9; padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.8rem; font-weight: 700;"></span>
            </div>
        </div>

        <div id="noResult" style="text-align: center; padding: 3rem; color: #94a3b8; display: none;">
            <i data-lucide="search-x" size="48"></i>
            <p>Produk tidak ditemukan. Pastikan kata kunci benar.</p>
        </div>

        <center style="margin-top: 3rem;">
            <a href="../dashboard/index.php" style="text-decoration: none; color: #64748b; display: inline-flex; align-items: center; gap: 0.5rem; font-weight: 700;">
                <i data-lucide="arrow-left"></i> Kembali ke Dashboard
            </a>
        </center>
    </div>

    <script>
        lucide.createIcons();
        const products = <?php echo json_encode($produk); ?>;

        function searchPrice() {
            const input = document.getElementById('manualSearch').value.toLowerCase();
            const card = document.getElementById('priceCard');
            const noRes = document.getElementById('noResult');

            if (input.length < 2) {
                card.classList.remove('active');
                noRes.style.display = 'none';
                return;
            }

            const found = products.find(p => 
                p.nama_barang.toLowerCase().includes(input) || 
                p.kode_barang.toLowerCase() === input
            );

            if (found) {
                document.getElementById('resKode').innerText = found.kode_barang;
                document.getElementById('resNama').innerText = found.nama_barang;
                document.getElementById('resHarga').innerText = 'Rp ' + parseInt(found.harga_jual).toLocaleString('id-ID');
                document.getElementById('resKategori').innerText = 'Kategori: ' + found.kategori;
                document.getElementById('resStok').innerText = 'Tersedia: ' + found.stok + ' Unit';
                
                card.classList.add('active');
                noRes.style.display = 'none';
            } else {
                card.classList.remove('active');
                noRes.style.display = 'block';
            }
        }

        function retryConnection() {
            Swal.fire({
                title: 'Menghubungkan Alat...',
                text: 'Mencoba mendeteksi sensor barcode Alfamart-X1...',
                timer: 2000,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading()
                }
            }).then(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Koneksi Gagal',
                    text: 'Alat deteksi tetap tidak terbaca. Periksa koneksi USB atau hubungi tim IT.',
                    confirmButtonColor: '#e11a22'
                });
            });
        }
    </script>
</body>
</html>
