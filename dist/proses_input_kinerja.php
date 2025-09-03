<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil data dari form
$user_id  = $_POST['user_id'];
$tanggal  = $_POST['tanggal'];
$kegiatan = trim($_POST['kegiatan']);
$progres  = $_POST['progres'];
$catatan  = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';
$bukti    = null;

// Validasi upload file
$upload_dir = __DIR__ . '/images/kinerja/';
$upload_url = 'dist/images/kinerja/'; // Untuk disimpan ke database jika diperlukan
$max_file_size = 5 * 1024 * 1024; // 5MB
$allowed_ext = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'xlsx'];

// Pastikan folder ada
if (!is_dir($upload_dir)) {
  mkdir($upload_dir, 0755, true);
}

if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
  $file_tmp  = $_FILES['bukti']['tmp_name'];
  $file_name = basename($_FILES['bukti']['name']);
  $file_size = $_FILES['bukti']['size'];
  $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

  if (!in_array($file_ext, $allowed_ext)) {
    die("Format file tidak diperbolehkan. Hanya: jpg, jpeg, png, pdf, docx, xlsx.");
  }

  if ($file_size > $max_file_size) {
    die("Ukuran file terlalu besar. Maksimal 5MB.");
  }

  $new_file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file_name);
  $target_path = $upload_dir . $new_file_name;

  if (!move_uploaded_file($file_tmp, $target_path)) {
    die("Gagal mengupload file.");
  }

  $bukti = $new_file_name;
}

// Simpan ke database
$stmt = $conn->prepare("INSERT INTO kinerja_petugas (tanggal, kegiatan, progres, catatan, bukti, user_input) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssi", $tanggal, $kegiatan, $progres, $catatan, $bukti, $user_id);

if ($stmt->execute()) {
  header("Location: input_kinerja.php?status=sukses");
  exit;
} else {
  echo "Terjadi kesalahan saat menyimpan data: " . $stmt->error;
}
?>
