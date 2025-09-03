<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';

if (isset($_GET['id'])) {
  $id = (int) $_GET['id'];
  $query = "DELETE FROM obat WHERE id='$id'";
  if (mysqli_query($conn, $query)) {
    header("Location: data_obat.php?deleted=1");
  } else {
    header("Location: data_obat.php?error=1");
  }
}
?>
