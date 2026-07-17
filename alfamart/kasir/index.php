<?php
session_start();
require_once '../koneksi/koneksi.php';

// Proteksi: Hanya Kepala Toko yang bisa mengelola kasir
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kepala_toko') {
    header("Location: ../dashboard/index.php");
    exit();
}

// Ambil data kasir
$query = "SELECT * FROM users WHERE role = 'kasir' ORDER BY created_at DESC";
$result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Kasir - AlfaPOS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header-kasir">
        <div>
            <h2>Data Petugas Kasir</h2>
            <p style="color: #64748b;">Kelola hak akses dan akun kasir gerai Alfamart Anda.</p>
        </div>
        <a href="tambah.php" class="btn-tambah">
            <i data-lucide="user-plus"></i>
            Tambah Kasir Baru
        </a>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                while($row = mysqli_fetch_assoc($result)): 
                    $status_class = $row['status_aktif'] ? 'status-aktif' : 'status-nonaktif';
                    $status_text = $row['status_aktif'] ? 'Aktif' : 'Non-Aktif';
                ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><strong style="color: var(--accent);"><?php echo $row['username']; ?></strong></td>
                    <td><strong><?php echo $row['nama_lengkap']; ?></strong></td>
                    <td><?php echo $row['email'] ?: '-'; ?></td>
                    <td>
                        <span class="badge-status <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </span>
                    </td>
                    <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    <td>
                        <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn-edit"><i data-lucide="edit"></i></a>
                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                        <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn-hapus" onclick="return confirm('Yakin ingin menghapus kasir ini?')"><i data-lucide="trash-2"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                
                <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem;">Belum ada data kasir.</td>
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
        if (urlParams.get('status') === 'success') Swal.fire('Berhasil!', 'Data kasir telah disimpan.', 'success');
        if (urlParams.get('status') === 'deleted') Swal.fire('Dihapus!', 'Kasir telah dihapus.', 'success');
    </script>
</body>
</html>
