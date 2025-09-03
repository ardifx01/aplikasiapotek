<?php
session_start();
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $kinerja_id = $_POST['kinerja_id'] ?? null;
  $mentor_id = $_POST['mentor_id'] ?? null;
  $rating = $_POST['rating'] ?? null;
  $komentar = $_POST['komentar'] ?? '';

  if ($kinerja_id && $mentor_id && $rating) {
    $stmt = $conn->prepare("INSERT INTO feedback_kinerja (kinerja_id, mentor_id, rating, komentar) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $kinerja_id, $mentor_id, $rating, $komentar);
    if ($stmt->execute()) {
      header("Location: feedback_kinerja.php?status=sukses");
      exit;
    } else {
      die("Gagal menyimpan feedback: " . $stmt->error);
    }
  } else {
    die("Data tidak lengkap.");
  }
} else {
  header("Location: feedback_kinerja.php");
  exit;
}
