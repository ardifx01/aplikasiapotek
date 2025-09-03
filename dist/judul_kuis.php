<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';

$notif = '';

// Proses Tambah
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_judul'])) {
  $judul = trim($_POST['judul']);
  $durasi = (int) $_POST['durasi'];

  $stmt = $conn->prepare("INSERT INTO judul_kuis (judul, durasi) VALUES (?, ?)");
  $stmt->bind_param("si", $judul, $durasi);

  if ($stmt->execute()) {
    $notif = "<script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Judul kuis berhasil ditambahkan!',
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

// Proses Hapus
if (isset($_GET['hapus'])) {
  $id_hapus = (int) $_GET['hapus'];
  $stmt = $conn->prepare("DELETE FROM judul_kuis WHERE id = ?");
  $stmt->bind_param("i", $id_hapus);
  if ($stmt->execute()) {
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          icon: 'success',
          title: 'Terhapus!',
          text: 'Judul kuis berhasil dihapus.',
          showConfirmButton: false,
          timer: 2000
        }).then(() => {
          window.location.href = 'judul_kuis.php';
        });
      });
    </script>";
  } else {
    echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
          icon: 'error',
          title: 'Gagal!',
          text: 'Tidak bisa menghapus data.',
          showConfirmButton: false,
          timer: 2000
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
  <title>Judul Kuis &mdash; SICONIC</title>

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
            <h1>Judul Kuis</h1>
            <div class="section-header-button ml-auto">
              <button class="btn btn-primary" data-toggle="modal" data-target="#modalTambah"><i class="fas fa-plus-circle"></i> Tambah Judul Kuis</button>
            </div>
          </div>

          <div class="section-body">
            <div class="card">
              <div class="card-header">
                <h4>Daftar Judul Kuis</h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered table-hover">
                    <thead class="thead-dark">
                      <tr>
                        <th width="5%">No</th>
                        <th>Judul</th>
                        <th>Durasi (menit)</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $no = 1;
                      $result = mysqli_query($conn, "SELECT * FROM judul_kuis ORDER BY created_at DESC");
                      if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                          echo "<tr>
                            <td>{$no}</td>
                            <td>" . htmlspecialchars($row['judul']) . "</td>
                            <td>{$row['durasi']}</td>
                            <td>{$row['created_at']}</td>
                            <td>
                              <a href='?hapus={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirmHapus(event)'><i class='fas fa-trash'></i> Hapus</a>
                            </td>
                          </tr>";
                          $no++;
                        }
                      } else {
                        echo "<tr><td colspan='5' class='text-center text-muted'>Belum ada judul kuis ditambahkan.</td></tr>";
                      }
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

        </section>
      </div>

      <!-- Modal Tambah -->
      <div class="modal fade" id="modalTambah" tabindex="-1" role="dialog" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <form method="POST">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Tambah Judul Kuis</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span>&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <div class="form-group">
                  <label>Judul</label>
                  <input type="text" name="judul" class="form-control" required>
                </div>
                <div class="form-group">
                  <label>Durasi (menit)</label>
                  <input type="number" name="durasi" class="form-control" required>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" name="tambah_judul" class="btn btn-success"><i class="fas fa-save"></i> Simpan</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- JS Scripts -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>

  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <?= $notif ?>

  <script>
    function confirmHapus(e) {
      e.preventDefault();
      const url = e.currentTarget.getAttribute('href');

      Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, hapus!'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = url;
        }
      });

      return false;
    }
  </script>

</body>
</html>
