<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "DELETE FROM suppliers WHERE id = $id";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: index.php?status=deleted");
    } else {
        // Cek jika gagal karena relasi (masih ada produk yang pakai supplier ini)
        if (mysqli_errno($koneksi) == 1451) {
            header("Location: index.php?status=error_relation");
        } else {
            echo "Gagal menghapus data: " . mysqli_error($koneksi);
        }
    }
} else {
    header("Location: index.php");
}
?>
