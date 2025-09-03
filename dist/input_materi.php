<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';

$notif = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $judul = trim($_POST['judul']);
  $deskripsi = trim($_POST['deskripsi']);
  $refrensi_url = trim($_POST['refrensi_url']);
  $user_input = $_SESSION['user_id'];

  // Proses dokumen
  $nama_file = '';
  if (isset($_FILES['dokumen']) && $_FILES['dokumen']['error'] == 0) {
    $nama_sumber = $_FILES['dokumen']['name'];
    $tmp = $_FILES['dokumen']['tmp_name'];
    $nama_file = uniqid() . '_' . basename($nama_sumber);
    $target = "uploads/" . $nama_file;

    if (!move_uploaded_file($tmp, $target)) {
      $notif = "<script>
        document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
            icon: 'error',
            title: 'Gagal Upload',
            text: 'File dokumen gagal diunggah!',
            showConfirmButton: false,
            timer: 3000
          });
        });
      </script>";
    }
  }

  // Proses video
  $nama_video = '';
  if (isset($_FILES['video']) && $_FILES['video']['error'] == 0) {
    $video_sumber = $_FILES['video']['name'];
    $video_tmp = $_FILES['video']['tmp_name'];
    $nama_video = uniqid() . '_' . basename($video_sumber);
    $target_video = "uploads/" . $nama_video;

    if (!move_uploaded_file($video_tmp, $target_video)) {
      $notif = "<script>
        document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
            icon: 'error',
            title: 'Gagal Upload',
            text: 'File video gagal diunggah!',
            showConfirmButton: false,
            timer: 3000
          });
        });
      </script>";
    }
  }

  // Simpan ke database
  $stmt = $conn->prepare("INSERT INTO materi (judul, deskripsi, refrensi_url, dokumen, video, user_input) VALUES (?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("sssssi", $judul, $deskripsi, $refrensi_url, $nama_file, $nama_video, $user_input);

  if ($stmt->execute()) {
    $notif = "<script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Materi berhasil disimpan!',
          showConfirmButton: false,
          timer: 2500
        });
      });
    </script>";
  } else {
    $notif = "<script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: 'Terjadi kesalahan saat menyimpan data.',
          showConfirmButton: false,
          timer: 3000
        });
      });
    </script>";
  }

  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Input Materi &mdash; SICONIC</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">

      <?php include 'navbar.php'; ?>
      <?php include 'sidebar.php'; ?>

      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Input Materi</h1>
          </div>

          <div class="section-body">
            <div class="card">
              <div class="card-header">
                <h4>Form Tambah Materi</h4>
              </div>
              <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                  <div class="form-group">
                    <label>Judul Materi</label>
                    <input type="text" name="judul" class="form-control" required>
                  </div>

                  <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="4"></textarea>
                  </div>

                  <div class="form-group">
                    <label>Refrensi URL</label>
                    <input type="url" name="refrensi_url" class="form-control" placeholder="https://contoh.com/referensi">
                  </div>

                  <div class="form-group">
                    <label>Upload Video (mp4/webm)</label>
                    <input type="file" name="video" class="form-control-file" accept="video/*">
                  </div>

                  <div class="form-group">
                    <label>Upload Dokumen (PDF/Word)</label>
                    <input type="file" name="dokumen" class="form-control-file" accept=".pdf,.doc,.docx">
                  </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <!-- General JS Scripts -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>

  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <?= $notif ?>
</body>
</html>
