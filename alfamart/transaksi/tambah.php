<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// Ambil data produk
$query_produk = "SELECT * FROM products WHERE stok > 0 ORDER BY nama_barang ASC";
$res_produk = mysqli_query($koneksi, $query_produk);
$produk_data = [];
while($row = mysqli_fetch_assoc($res_produk)) {
    $produk_data[] = $row;
}

// Proses Simpan Transaksi (AJAX)
if (isset($_POST['proses_bayar'])) {
    $data = json_decode($_POST['data'], true);
    $bayar = $_POST['bayar'];
    $total = $_POST['total'];
    $kembali = $bayar - $total;
    $user_id = $_SESSION['user_id'];
    $nomor_trx = "TRX-" . date('YmdHis') . rand(10, 99);

    mysqli_begin_transaction($koneksi);

    try {
        // 1. Simpan Header Transaksi
        $sql_header = "INSERT INTO transaksi (nomor_transaksi, total_bayar, bayar, kembali, user_id) 
                       VALUES ('$nomor_trx', '$total', '$bayar', '$kembali', '$user_id')";
        mysqli_query($koneksi, $sql_header);
        $transaksi_id = mysqli_insert_id($koneksi);

        // 2. Simpan Detail & Update Stok
        foreach ($data as $item) {
            $pid = $item['id'];
            $qty = $item['qty'];
            $price = $item['price'];
            $sub = $qty * $price;

            $sql_detail = "INSERT INTO detail_transaksi (transaksi_id, product_id, jumlah, harga_satuan, subtotal) 
                           VALUES ('$transaksi_id', '$pid', '$qty', '$price', '$sub')";
            mysqli_query($koneksi, $sql_detail);

            $sql_stok = "UPDATE products SET stok = stok - $qty WHERE id = '$pid'";
            mysqli_query($koneksi, $sql_stok);
        }

        mysqli_commit($koneksi);
        echo json_encode(['status' => 'success', 'trx_id' => $transaksi_id, 'nomor_trx' => $nomor_trx]);
        exit();
    } catch (Exception $e) {
        mysqli_rollback($koneksi);
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir AlfaPOS - Transaksi Baru</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="pos-container">
        <!-- Left: Products -->
        <div class="product-panel">
            <div class="search-box">
                <i data-lucide="search"></i>
                <input type="text" id="productSearch" placeholder="Cari nama barang atau barcode..." onkeyup="filterProducts()">
            </div>
            
            <div class="product-list" id="productList">
                <?php foreach($produk_data as $p): ?>
                <div class="product-item" onclick="addToCart(<?php echo htmlspecialchars(json_encode($p)); ?>)">
                    <p style="font-size: 0.7rem; color: #64748b; font-weight: 700;"><?php echo $p['kode_barang']; ?></p>
                    <h4><?php echo $p['nama_barang']; ?></h4>
                    <p class="price">Rp <?php echo number_format($p['harga_jual'], 0, ',', '.'); ?></p>
                    <p style="font-size: 0.75rem; color: #94a3b8;">Stok: <?php echo $p['stok']; ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 1rem;">
                <a href="../dashboard/index.php" style="text-decoration: none; color: #64748b; font-weight: 700; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="arrow-left"></i> Kembali ke Dashboard
                </a>
            </div>
        </div>

        <!-- Right: Cart -->
        <div class="cart-panel">
            <div class="cart-header">
                <h3><i data-lucide="shopping-cart" style="color: var(--primary);"></i> Keranjang Belanja</h3>
                <span id="cartCount" class="badge" style="background: var(--primary); color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.8rem;">0</span>
            </div>

            <div class="cart-items" id="cartContainer">
                <!-- Cart items will be here -->
                <div style="text-align: center; padding: 3rem; color: #94a3b8;">
                    <i data-lucide="shopping-basket" size="48"></i>
                    <p>Keranjang masih kosong</p>
                </div>
            </div>

            <div class="cart-footer">
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span id="subtotalText">Rp 0</span>
                </div>
                <div class="summary-row">
                    <span>Pajak (0%)</span>
                    <span>Rp 0</span>
                </div>
                <div class="summary-row total">
                    <span>TOTAL</span>
                    <span id="totalText">Rp 0</span>
                </div>

                <div class="payment-section">
                    <div class="payment-input">
                        <label>Uang Tunai (F8)</label>
                        <input type="number" id="cashInput" placeholder="0" oninput="updateChange()">
                    </div>
                    <div class="change-display">
                        <p>Kembalian: <span id="changeText">Rp 0</span></p>
                    </div>
                    <button class="btn-pay" id="btnPay" disabled onclick="processPayment()">
                        <i data-lucide="check-circle"></i> BAYAR SEKARANG
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Receipt Preview -->
    <div class="modal-struk" id="modalStruk">
        <div class="struk-content">
            <div id="receiptPaper"></div>
            <div class="no-print" style="margin-top: 2rem; display: flex; flex-direction: column; gap: 0.8rem;">
                <!-- <button onclick="window.print()" class="btn-pay" style="background: var(--accent);">
                    <i data-lucide="printer"></i> CETAK STRUK (Modern)
                </button> -->
                <button onclick="printCLI()" class="btn-pay" style="background: #1e293b;">
                    <i data-lucide="terminal"></i> CETAK STRUK 
                </button>
                <button onclick="location.reload()" class="btn-pay" style="background: #64748b;">
                    <i data-lucide="refresh-cw"></i> TRANSAKSI BARU
                </button>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        let cart = [];
        let total = 0;
        let currentTrxData = null;

        function filterProducts() {
            let input = document.getElementById('productSearch').value.toLowerCase();
            let items = document.querySelectorAll('.product-item');
            items.forEach(item => {
                let text = item.innerText.toLowerCase();
                item.style.display = text.includes(input) ? '' : 'none';
            });
        }

        function addToCart(product) {
            let existing = cart.find(item => item.id === product.id);
            if (existing) {
                if (existing.qty < product.stok) {
                    existing.qty++;
                } else {
                    Swal.fire('Stok Terbatas', 'Stok tidak mencukupi', 'warning');
                    return;
                }
            } else {
                cart.push({
                    id: product.id,
                    kode: product.kode_barang,
                    name: product.nama_barang,
                    price: parseInt(product.harga_jual),
                    qty: 1,
                    stok: parseInt(product.stok)
                });
            }
            renderCart();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            renderCart();
        }

        function updateQty(index, val) {
            let qty = parseInt(val);
            if (qty > cart[index].stok) {
                Swal.fire('Stok Terbatas', 'Stok hanya ada ' + cart[index].stok, 'warning');
                cart[index].qty = cart[index].stok;
            } else if (qty < 1) {
                cart[index].qty = 1;
            } else {
                cart[index].qty = qty;
            }
            renderCart();
        }

        function renderCart() {
            const container = document.getElementById('cartContainer');
            if (cart.length === 0) {
                container.innerHTML = '<div style="text-align: center; padding: 3rem; color: #94a3b8;"><i data-lucide="shopping-basket" size="48"></i><p>Keranjang masih kosong</p></div>';
                lucide.createIcons();
                total = 0;
            } else {
                let html = '';
                total = 0;
                cart.forEach((item, index) => {
                    let sub = item.price * item.qty;
                    total += sub;
                    html += `
                        <div class="cart-item">
                            <div class="cart-item-info">
                                <p>${item.name}</p>
                                <span>Rp ${item.price.toLocaleString('id-ID')}</span>
                            </div>
                            <div class="cart-qty">
                                <input type="number" value="${item.qty}" onchange="updateQty(${index}, this.value)">
                            </div>
                            <div style="width: 80px; text-align: right; font-weight: 700;">
                                Rp ${sub.toLocaleString('id-ID')}
                            </div>
                            <button class="btn-remove" onclick="removeFromCart(${index})">
                                <i data-lucide="x-circle"></i>
                            </button>
                        </div>
                    `;
                });
                container.innerHTML = html;
                lucide.createIcons();
            }

            document.getElementById('cartCount').innerText = cart.length;
            document.getElementById('subtotalText').innerText = 'Rp ' + total.toLocaleString('id-ID');
            document.getElementById('totalText').innerText = 'Rp ' + total.toLocaleString('id-ID');
            updateChange();
        }

        function updateChange() {
            let cash = parseInt(document.getElementById('cashInput').value) || 0;
            let change = cash - total;
            document.getElementById('changeText').innerText = 'Rp ' + (change > 0 ? change.toLocaleString('id-ID') : 0);
            
            const btnPay = document.getElementById('btnPay');
            btnPay.disabled = (total === 0 || cash < total);
        }

        function processPayment() {
            let cash = parseInt(document.getElementById('cashInput').value);
            
            let formData = new FormData();
            formData.append('proses_bayar', true);
            formData.append('data', JSON.stringify(cart));
            formData.append('total', total);
            formData.append('bayar', cash);

            fetch('tambah.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    currentTrxData = result;
                    showReceipt(result);
                } else {
                    Swal.fire('Gagal', result.message, 'error');
                }
            });
        }

        function showReceipt(data) {
            const paper = document.getElementById('receiptPaper');
            paper.className = ''; // Reset class
            let itemsHtml = '';
            cart.forEach(item => {
                itemsHtml += `
                    <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 5px;">
                        <span>${item.name} x${item.qty}</span>
                        <span>Rp ${(item.price * item.qty).toLocaleString('id-ID')}</span>
                    </div>
                `;
            });

            let cash = parseInt(document.getElementById('cashInput').value);
            let change = cash - total;

            paper.innerHTML = `
                <div style="text-align: center; border-bottom: 2px dashed #000; padding-bottom: 1rem; margin-bottom: 1rem;">
                    <h2 style="color: var(--primary); margin: 0;">ALFAMART</h2>
                    <p style="font-size: 0.8rem; margin: 5px 0;">JL. Telang Raya, Kampus UTM, Kamal-Bangkalan</p>
                    <p style="font-size: 0.8rem; margin: 5px 0;">Belanja Puas, Harga Pas!</p>
                    <p style="font-size: 0.7rem; color: #64748b;">${data.nomor_trx}</p>
                </div>
                <div style="margin-bottom: 1rem; font-size: 0.8rem;">
                    <p>Tgl: ${new Date().toLocaleString('id-ID')}</p>
                    <p>Kasir: <?php echo $_SESSION['nama_lengkap']; ?></p>
                </div>
                <div style="border-bottom: 1px dashed #ccc; padding-bottom: 0.5rem; margin-bottom: 0.5rem;">
                    ${itemsHtml}
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: 800; font-size: 1.1rem; margin-top: 1rem;">
                    <span>TOTAL</span>
                    <span>Rp ${total.toLocaleString('id-ID')}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-top: 0.5rem;">
                    <span>Tunai</span>
                    <span>Rp ${cash.toLocaleString('id-ID')}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                    <span>Kembali</span>
                    <span>Rp ${change.toLocaleString('id-ID')}</span>
                </div>
                <div style="text-align: center; margin-top: 2rem; font-size: 0.75rem; border-top: 2px dashed #000; padding-top: 1rem;">
                    <p>Terima Kasih Telah Berbelanja</p>
                    <p>Kritik & Saran: 1500800</p>
                </div>
            `;

            document.getElementById('modalStruk').classList.add('active');
            lucide.createIcons();
        }

        function printCLI() {
            const paper = document.getElementById('receiptPaper');
            paper.className = 'cli-receipt';
            
            let cash = parseInt(document.getElementById('cashInput').value);
            let change = cash - total;
            
            let itemsTxt = '';
            cart.forEach(item => {
                let name = item.name.padEnd(20).substring(0, 20);
                let sub = (item.price * item.qty).toLocaleString('id-ID').padStart(10);
                itemsTxt += `<p>${name} x${item.qty} ${sub}</p>`;
            });

            paper.innerHTML = `
                <div style="text-align: center;">
                    <h2 style="margin:0;">ALFAMART</h2>
                    <p style="font-size: 0.8rem; margin: 5px 0;">JL. Telang Raya, Kampus UTM, Kamal-Bangkalan</p>
                    <p style="font-size: 0.8rem; margin: 5px 0;">BELANJA PUAS, HARGA PAS!</p>
                    <div class="cli-line"></div>
                </div>
                <p>NO: ${currentTrxData.nomor_trx}</p>
                <p>TGL: ${new Date().toLocaleDateString('id-ID')} ${new Date().toLocaleTimeString('id-ID')}</p>
                <p>KSR: <?php echo strtoupper($_SESSION['nama_lengkap']); ?></p>
                <div class="cli-line"></div>
                ${itemsTxt}
                <div class="cli-line"></div>
                <div style="display: flex; justify-content: space-between; font-weight: bold;">
                    <span>TOTAL</span>
                    <span>Rp ${total.toLocaleString('id-ID')}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>TUNAI</span>
                    <span>Rp ${cash.toLocaleString('id-ID')}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>KEMBALI</span>
                    <span>Rp ${change.toLocaleString('id-ID')}</span>
                </div>
                <div class="cli-line"></div>
                <div style="text-align: center; margin-top: 10px;">
                    <p>TERIMA KASIH</p>
                    <p>KRITIK & SARAN: 1500800</p>
                </div>
            `;
            
            window.print();
        }

        // Hotkeys
        window.addEventListener('keydown', (e) => {
            if (e.key === 'F8') {
                e.preventDefault();
                document.getElementById('cashInput').focus();
            }
        });
    </script>
</body>
</html>
