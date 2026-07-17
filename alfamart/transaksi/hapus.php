<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Hapus data transaksi (Detail akan terhapus otomatis karena CASCADE)
    $query = "DELETE FROM transaksi WHERE id = '$id'";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php?status=deleted");
    } else {
        header("Location: index.php?status=error");
    }
} else {
    header("Location: index.php");
}
exit();
?>
