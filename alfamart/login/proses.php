<?php
session_start();
require_once '../koneksi/koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_input = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password   = $_POST['password'];

    if (empty($user_input) || empty($password)) {
        $_SESSION['error'] = "Username dan Password tidak boleh kosong!";
        header("Location: index.php");
        exit();
    }

    // Cek berdasarkan username ATAU id_pegawai
    $query = "SELECT * FROM users WHERE (username = '$user_input' OR id_pegawai = '$user_input') AND status_aktif = 1 LIMIT 1";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $user_data = mysqli_fetch_assoc($result);
        
        // Verifikasi password
        if (password_verify($password, $user_data['password']) || $password === $user_data['password']) {
            $_SESSION['user_id']      = $user_data['id'];
            $_SESSION['username']     = $user_data['username'];
            $_SESSION['nama_lengkap'] = $user_data['nama_lengkap'];
            $_SESSION['role']         = $user_data['role'];

            header("Location: ../dashboard/index.php");
            exit();
        } else {
            $_SESSION['error'] = "Password yang Anda masukkan salah!";
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Username atau ID Pegawai tidak ditemukan!";
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
