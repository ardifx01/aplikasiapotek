<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';

$judul_id = isset($_GET['judul_id']) ? (int)$_GET['judul_id'] : 0;

// Ambil judul kuis
$stmt = $conn->prepare("SELECT judul FROM judul_kuis WHERE id = ?");
$stmt->bind_param("i", $judul_id);
$stmt->execute();
$stmt->bind_result($judul);
$stmt->fetch();
$stmt->close();

if (!$judul) {
  echo "Judul kuis tidak ditemukan.";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Rincian Kuis</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <style>
    .table-responsive {
      overflow-x: auto;
    }
    td {
      white-space: nowrap;
    }
  </style>
</head>
<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">

      <?php include 'navbar.php'; ?>
      <?php include 'sidebar.php'; ?>

      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Rincian Soal Kuis: <?= htmlspecialchars($judul) ?></h1>
          </div>

          <div class="section-body">
            <a href="lihat_kuis.php" class="btn btn-secondary mb-3">
              <i class="fas fa-arrow-left"></i> Kembali
            </a>

            <div class="card">
              <div class="card-header">
                <h4>Daftar Soal</h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered">
                    <thead class="thead-light">
                      <tr>
                        <th>No</th>
                        <th>Soal</th>
                        <th>A</th>
                        <th>B</th>
                        <th>C</th>
                        <th>D</th>
                        <th>Jawaban</th>
                        <th>Kunci</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php
                      $stmt = $conn->prepare("SELECT * FROM kuis WHERE judul_id = ? ORDER BY id ASC");
                      $stmt->bind_param("i", $judul_id);
                      $stmt->execute();
                      $result = $stmt->get_result();
                      $no = 1;
                      while ($row = $result->fetch_assoc()) {
                        $json = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['soal']}</td>
                                <td>{$row['pilihan_a']}</td>
                                <td>{$row['pilihan_b']}</td>
                                <td>{$row['pilihan_c']}</td>
                                <td>{$row['pilihan_d']}</td>
                                <td>{$row['jawaban']}</td>
                                <td>{$row['kunci']}</td>
                                <td>
                                  <button class='btn btn-warning btn-sm' onclick='openEditModal({$json})'>
                                    <i class='fas fa-edit'></i>
                                  </button>
                                  <a href='hapus_kuis.php?id={$row['id']}&judul_id={$judul_id}' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin ingin menghapus soal ini?')\">
                                    <i class='fas fa-trash'></i>
                                  </a>
                                </td>
                              </tr>";
                        $no++;
                      }
                      $stmt->close();
                      ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>
        </section>
      </div>
</div>
</div>
</div>
    </div>
  </div>
  </div>

  <!-- Modal Edit Soal -->
  <div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <form method="POST" action="update_kuis.php">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalEditLabel">Edit Soal</h5>
            <button type="button" class="close" data-dismiss="modal">
              <span>&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="edit-id">
            <input type="hidden" name="judul_id" value="<?= $judul_id ?>">
            <div class="form-group">
              <label>Soal</label>
              <textarea name="soal" id="edit-soal" class="form-control" required></textarea>
            </div>
            <div class="form-group">
              <label>Pilihan A</label>
              <input type="text" name="pilihan_a" id="edit-a" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Pilihan B</label>
              <input type="text" name="pilihan_b" id="edit-b" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Pilihan C</label>
              <input type="text" name="pilihan_c" id="edit-c" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Pilihan D</label>
              <input type="text" name="pilihan_d" id="edit-d" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Jawaban</label>
              <select name="jawaban" id="edit-jawaban" class="form-control" required>
                <option value="">-- Pilih --</option>
                <option value="A">A</option>
                <option value="B">B</option>
                <option value="C">C</option>
                <option value="D">D</option>
              </select>
            </div>
            <div class="form-group">
              <label>Kunci (Opsional)</label>
              <input type="text" name="kunci" id="edit-kunci" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- JS -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script>
    function openEditModal(data) {
      $('#edit-id').val(data.id);
      $('#edit-soal').val(data.soal);
      $('#edit-a').val(data.pilihan_a);
      $('#edit-b').val(data.pilihan_b);
      $('#edit-c').val(data.pilihan_c);
      $('#edit-d').val(data.pilihan_d);
      $('#edit-jawaban').val(data.jawaban);
      $('#edit-kunci').val(data.kunci);
      $('#modalEdit').modal('show');
    }
  </script>
</body>
</html>
