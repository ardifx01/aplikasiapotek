<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';

$notif = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $judul_id = $_POST['judul_id'];
  $soal = trim($_POST['soal']);
  $pilihan_a = trim($_POST['pilihan_a']);
  $pilihan_b = trim($_POST['pilihan_b']);
  $pilihan_c = trim($_POST['pilihan_c']);
  $pilihan_d = trim($_POST['pilihan_d']);
  $jawaban = $_POST['jawaban'];
  $kunci = $_POST['kunci'] ?? null;

  // Simpan soal ke tabel kuis
  $stmt = $conn->prepare("INSERT INTO kuis (judul_id, soal, pilihan_a, pilihan_b, pilihan_c, pilihan_d, jawaban, kunci) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
  $stmt->bind_param("isssssss", $judul_id, $soal, $pilihan_a, $pilihan_b, $pilihan_c, $pilihan_d, $jawaban, $kunci);

  if ($stmt->execute()) {
    // Cek apakah user memasukkan tanggal mulai kuis (opsional tambahan)
if (!empty($_POST['mulai'])) {
  $mulai_date = $_POST['mulai'];
  $mulai = $mulai_date . ' 00:00:00'; // Set default jam 00:00:00
  $update = $conn->prepare("UPDATE judul_kuis SET mulai = ? WHERE id = ? AND (mulai IS NULL OR mulai = '')");
  $update->bind_param("si", $mulai, $judul_id);
  $update->execute();
  $update->close();
}


    $notif = "<script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Soal berhasil disimpan!',
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
  <title>Input Soal Kuis &mdash; SICONIC</title>

  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
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
            <h1>Input Soal Kuis</h1>
          </div>

          <div class="section-body">
            <div class="card">
              <div class="card-header">
                <h4>Form Tambah Soal</h4>
              </div>
              <div class="card-body">
                <form method="POST">
                  <div class="form-group">
                    <label>Judul Kuis</label>
                    <select name="judul_id" class="form-control" required onchange="setMulaiTanggal(this)">
                      <option value="">-- Pilih Judul Kuis --</option>
                      <?php
                      $res = $conn->query("SELECT id, judul, mulai FROM judul_kuis ORDER BY created_at DESC");
                      while ($row = $res->fetch_assoc()) {
                        $start = $row['mulai'] ? 'Sudah diset' : 'Belum diset';
                        echo "<option value='{$row['id']}' data-mulai='{$row['mulai']}'>{$row['judul']} - ($start)</option>";
                      }
                      ?>
                    </select>
                  </div>

               <div class="form-group" id="tanggalMulaiWrapper" style="display:none;">
  <label>Set Tanggal Mulai (jika belum diset)</label>
  <input type="date" name="mulai" class="form-control">
</div>


                  <div class="form-group">
                    <label>Soal</label>
                    <textarea name="soal" class="form-control" rows="4" required></textarea>
                  </div>

                  <div class="form-group">
                    <label>Pilihan A</label>
                    <input type="text" name="pilihan_a" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label>Pilihan B</label>
                    <input type="text" name="pilihan_b" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label>Pilihan C</label>
                    <input type="text" name="pilihan_c" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label>Pilihan D</label>
                    <input type="text" name="pilihan_d" class="form-control" required>
                  </div>

                  <div class="form-group">
                    <label>Jawaban Benar</label>
                    <select name="jawaban" class="form-control" required>
                      <option value="">-- Pilih Jawaban --</option>
                      <?php
                      $a = htmlspecialchars($_POST['pilihan_a'] ?? '');
                      $b = htmlspecialchars($_POST['pilihan_b'] ?? '');
                      $c = htmlspecialchars($_POST['pilihan_c'] ?? '');
                      $d = htmlspecialchars($_POST['pilihan_d'] ?? '');
                      ?>
                      <option value="A">A. <?= $a ?></option>
                      <option value="B">B. <?= $b ?></option>
                      <option value="C">C. <?= $c ?></option>
                      <option value="D">D. <?= $d ?></option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Kunci (Opsional)</label>
                    <input type="text" name="kunci" class="form-control" maxlength="1">
                  </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="judul_kuis.php" class="btn btn-secondary">Kembali</a>
                    <a href="lihat_kuis.php" class="btn btn-info"><i class="fas fa-eye"></i> Lihat Kuis</a>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script>
    function setMulaiTanggal(select) {
      const selected = select.options[select.selectedIndex];
      const mulai = selected.getAttribute('data-mulai');
      const wrapper = document.getElementById('tanggalMulaiWrapper');
      wrapper.style.display = (mulai === '' || mulai === null) ? 'block' : 'none';
    }
  </script>

  <?= $notif ?>
</body>
</html>
