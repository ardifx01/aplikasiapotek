<?php
session_start();
include 'koneksi.php'; // file koneksi ke database

// Cek apakah data dikirim lewat POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nik      = mysqli_real_escape_string($conn, $_POST['nik']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $no_hp    = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Validasi sederhana
    if (empty($nik) || empty($nama) || empty($no_hp) || empty($email) || empty($password)) {
        echo "<script>alert('Semua field wajib diisi!'); window.location.href='login.php';</script>";
        exit;
    }

    // Cek apakah email atau NIK sudah terdaftar
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' OR nik='$nik'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Email atau NIK sudah terdaftar!'); window.location.href='login.php';</script>";
        exit;
    }

    // Enkripsi password
    $hashPassword = password_hash($password, PASSWORD_BCRYPT);

    // Simpan ke database
    $query = "INSERT INTO users (nik, nama, no_hp, email, password) 
              VALUES ('$nik', '$nama', '$no_hp', '$email', '$hashPassword')";

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Pendaftaran berhasil, silakan login!'); window.location.href='login.php';</script>";
    } else {
        echo "<script>alert('Pendaftaran gagal, coba lagi!'); window.location.href='login.php';</script>";
    }
} else {
    header("Location: login.php");
    exit;
}
