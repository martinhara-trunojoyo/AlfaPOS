<?php
/**
 * Konfigurasi Koneksi Database AlfaPOS
 */

$host = "localhost";
$user = "root";
$pass = "";
$db   = "alfamart";

// Melakukan koneksi ke database
$koneksi = mysqli_connect($host, $user, $pass, $db);

// Mengecek apakah koneksi berhasil atau tidak
if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
} else {
    // Anda bisa mengomentari baris di bawah ini jika tidak ingin menampilkan pesan sukses di setiap halaman
    // echo "Koneksi ke database berhasil!";
}
?>
