<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Rekap Nilai &mdash; SICONIC</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
</head>

<body>
<div id="app">
  <div class="main-wrapper main-wrapper-1">

    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
      <section class="section">
        <div class="section-header">
          <h1>Rekap Nilai Peserta</h1>
        </div>

        <div class="section-body">
          <div class="card">
            <div class="card-header">
              <h4>List Hasil Kuis</h4>
            </div>
            <div class="card-body table-responsive">
              <form method="GET" class="form-inline mb-3">
  <label for="judul_id" class="mr-2">Filter Kuis:</label>
  <select name="judul_id" id="judul_id" class="form-control mr-2">
    <option value="">-- Semua Kuis --</option>
    <?php
    $kuis_list = $conn->query("SELECT id, judul FROM judul_kuis ORDER BY created_at DESC");
    while ($k = $kuis_list->fetch_assoc()):
    ?>
      <option value="<?= $k['id'] ?>" <?= (isset($_GET['judul_id']) && $_GET['judul_id'] == $k['id']) ? 'selected' : '' ?>>
        <?= htmlspecialchars($k['judul']) ?>
      </option>
    <?php endwhile; ?>
  </select>
  <button type="submit" class="btn btn-primary">Tampilkan</button>

  <?php if (isset($_GET['judul_id']) && $_GET['judul_id'] !== ''): ?>
    <a href="rekap_nilai.php" class="btn btn-secondary ml-2">Reset</a>
  <?php endif; ?>

  <a href="rekap_nilai_cetak.php?judul_id=<?= isset($_GET['judul_id']) ? $_GET['judul_id'] : '' ?>" class="btn btn-success ml-2" target="_blank">
    <i class="fas fa-print"></i> Cetak
  </a>
</form>

              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Nama Peserta</th>
                    <th>Judul Kuis</th>
                    <th>Nilai</th>
                    <th>Waktu Submit</th>
                  </tr>
                </thead>
                <tbody>
<?php
$filter_judul_id = isset($_GET['judul_id']) ? intval($_GET['judul_id']) : null;

$query = "SELECT h.id, h.user_id, u.nama, h.judul_id, jk.judul, h.nilai, h.waktu_submit
          FROM hasil_kuis h
          JOIN users u ON h.user_id = u.id
          JOIN judul_kuis jk ON h.judul_id = jk.id";

if ($filter_judul_id) {
  $query .= " WHERE h.judul_id = $filter_judul_id";
}

$query .= " ORDER BY h.waktu_submit DESC";

$result = $conn->query($query);
$no = 1;
while ($row = $result->fetch_assoc()):
?>
  <tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($row['nama']) ?></td>
    <td><?= htmlspecialchars($row['judul']) ?></td>
    <td><span class="badge badge-info p-2"><?= $row['nilai'] ?></span></td>
    <td><?= date('d-m-Y H:i', strtotime($row['waktu_submit'])) ?></td>
  </tr>
<?php endwhile; ?>
<?php if ($result->num_rows === 0): ?>
  <tr>
    <td colspan="5" class="text-center">Belum ada hasil kuis yang tercatat.</td>
  </tr>
<?php endif; ?>
                </tbody>
              </table>
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
</body>
</html>
