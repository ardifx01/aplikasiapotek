<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$judul_id = isset($_GET['judul_id']) ? (int)$_GET['judul_id'] : 0;

if ($id > 0) {
  $stmt = $conn->prepare("DELETE FROM kuis WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $stmt->close();
}

header("Location: rincian_kuis.php?judul_id=$judul_id");
exit;
