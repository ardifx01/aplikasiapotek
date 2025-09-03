<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id = $_POST['id'];
  $judul_id = $_POST['judul_id'];
  $soal = $_POST['soal'];
  $a = $_POST['pilihan_a'];
  $b = $_POST['pilihan_b'];
  $c = $_POST['pilihan_c'];
  $d = $_POST['pilihan_d'];
  $jawaban = $_POST['jawaban'];
  $kunci = $_POST['kunci'];

  $stmt = $conn->prepare("UPDATE kuis SET soal=?, pilihan_a=?, pilihan_b=?, pilihan_c=?, pilihan_d=?, jawaban=?, kunci=? WHERE id=?");
  $stmt->bind_param("sssssssi", $soal, $a, $b, $c, $d, $jawaban, $kunci, $id);
  $stmt->execute();
  $stmt->close();

  header("Location: rincian_kuis.php?judul_id=$judul_id");
  exit;
}
