<?php
session_start();
include 'koneksi.php'; // koneksi database

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik      = mysqli_real_escape_string($conn, $_POST['nik']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // cek user berdasarkan NIK
    $sql   = "SELECT * FROM users WHERE nik='$nik' LIMIT 1";
    $query = mysqli_query($conn, $sql);

    if ($query && mysqli_num_rows($query) == 1) {
        $user = mysqli_fetch_assoc($query);

        // verifikasi password
        if (password_verify($password, $user['password'])) {
            // buat session login
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['nama_user'] = $user['nama'];  // ubah dari 'nama' jadi 'nama_user'
            $_SESSION['nik']       = $user['nik'];
            $_SESSION['email']     = $user['email'];

            // redirect ke dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            echo "<script>alert('Password salah!'); window.location.href='index.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('NIK tidak ditemukan!'); window.location.href='index.php';</script>";
        exit;
    }
} else {
    header("Location: index.php");
    exit;
}
