<?php
session_start();
require_once '../koneksi/koneksi.php';

// Proteksi halaman
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Hapus data stok opname
    $query = "DELETE FROM stok_opname WHERE id = '$id'";
    
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
