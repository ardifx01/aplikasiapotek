<?php
session_start();
include 'koneksi.php';

if (!isset($_POST['id'])) {
  header("Location: input_kinerja.php");
  exit;
}

$id = $_POST['id'];
$user_id = $_POST['user_id'];
$tanggal = $_POST['tanggal'];
$kegiatan = $_POST['kegiatan'];
$progres = $_POST['progres'];
$catatan = $_POST['catatan'];
$bukti_lama = $_POST['bukti_lama'] ?? '';

$nama_file = $bukti_lama; // default pakai bukti lama

// Cek apakah ada file baru yang diupload tanpa error
if (isset($_FILES['bukti']) && $_FILES['bukti']['error'] === UPLOAD_ERR_OK) {
    $file_tmp = $_FILES['bukti']['tmp_name'];
    $file_name = $_FILES['bukti']['name'];
    $file_size = $_FILES['bukti']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf', 'docx', 'xlsx'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (in_array($file_ext, $allowed_ext)) {
        if ($file_size <= $max_size) {
            $new_file_name = uniqid() . '.' . $file_ext;
            $upload_dir = 'uploads/';
            $upload_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Hapus bukti lama jika ada dan berbeda dari file baru
                if (!empty($bukti_lama) && file_exists($upload_dir . $bukti_lama) && $bukti_lama != $new_file_name) {
                    unlink($upload_dir . $bukti_lama);
                }
                $nama_file = $new_file_name;
            } else {
                // Gagal upload, tetap pakai bukti lama
                $nama_file = $bukti_lama;
            }
        } else {
            die("File terlalu besar. Maksimal 5MB.");
        }
    } else {
        die("Ekstensi file tidak diizinkan.");
    }
}

// Update database
$stmt = $conn->prepare("UPDATE kinerja_petugas SET tanggal=?, kegiatan=?, progres=?, catatan=?, bukti=? WHERE id=? AND user_input=?");
$stmt->bind_param("sssssii", $tanggal, $kegiatan, $progres, $catatan, $nama_file, $id, $user_id);

if ($stmt->execute()) {
    header("Location: input_kinerja.php?status=edit_sukses");
    exit;
} else {
    echo "Gagal menyimpan perubahan: " . $stmt->error;
}
