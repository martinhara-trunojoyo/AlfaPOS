<?php
session_start();
require_once '../koneksi/koneksi.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'kepala_toko') {
    header("Location: ../dashboard/index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Jangan izinkan hapus diri sendiri
    if ($id == $_SESSION['user_id']) {
        header("Location: index.php");
        exit();
    }
    
    $query = "DELETE FROM users WHERE id = '$id' AND role = 'kasir'";
    
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
