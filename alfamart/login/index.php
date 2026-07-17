<?php
session_start();

$error = "";
if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AlfaPOS System</title>
    <link rel="stylesheet" href="style.css">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="login-container">
        <!-- Visual Side -->
        <div class="login-visual">
            <div class="visual-logo">
                <i data-lucide="shopping-cart"></i>
                Alfa<span>POS</span>
            </div>
            <div class="visual-content">
                <h2>Solusi Digital<br>Kasir Masa Depan</h2>
                <p>Sistem Point of Sales yang terintegrasi, cepat, dan handal untuk kemajuan setiap gerai.</p>
            </div>
            <div class="visual-footer">
                <p>&copy; 2026 PT Sumber Alfaria Trijaya Tbk.</p>
            </div>
        </div>

        <!-- Form Side -->
        <div class="login-form-side">
            <div class="form-header">
                <h3>Selamat Datang</h3>
                <p>Silakan masuk ke akun AlfaPOS Anda</p>
            </div>

            <form action="proses.php" method="POST">
                <div class="form-group">
                    <label for="username">Username / ID Pegawai</label>
                    <div class="input-container">
                        <input type="text" id="username" name="username" placeholder="Contoh: ALFA001" required autofocus>
                        <i data-lucide="user"></i>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-container">
                        <input type="password" id="password" name="password" placeholder="••••••••" required>
                        <i data-lucide="lock"></i>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Masuk Sekarang</button>
            </form>

            <div class="form-footer">
                <p>Ada kendala login? <a href="#">Hubungi Helpdesk</a></p>
                <a href="../index.html" class="back-btn">
                    <i data-lucide="arrow-left"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // Notifikasi Error Jika Login Gagal
        <?php if ($error): ?>
        Swal.fire({
            icon: 'error',
            title: 'Akses Ditolak',
            text: '<?php echo $error; ?>',
            confirmButtonColor: '#e11a22',
            iconColor: '#e11a22'
        });
        <?php endif; ?>
    </script>
</body>
</html>
