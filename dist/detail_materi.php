<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

function formatTanggalIndonesia($datetime) {
  $bulan = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  $timestamp = strtotime($datetime);
  $hari = date('d', $timestamp);
  $bulanNum = (int)date('m', $timestamp);
  $tahun = date('Y', $timestamp);
  $jam = date('H:i', $timestamp);
  return "{$hari} {$bulan[$bulanNum]} {$tahun}, {$jam} WIB";
}

// Validasi ID Materi
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo "ID materi tidak valid.";
  exit;
}

$id = (int)$_GET['id'];
$query = "SELECT materi.*, users.nama FROM materi 
          LEFT JOIN users ON materi.user_input = users.id 
          WHERE materi.id = $id LIMIT 1";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
  echo "Data materi tidak ditemukan.";
  exit;
}

$data = mysqli_fetch_assoc($result);
$judul     = htmlspecialchars($data['judul']);
$deskripsi = nl2br(htmlspecialchars($data['deskripsi']));
$user      = !empty($data['nama']) ? htmlspecialchars($data['nama']) : 'Tidak diketahui';
$waktu     = formatTanggalIndonesia($data['waktu_input']);
$dokumen   = $data['dokumen'];
$video     = $data['video'];
$url       = $data['refrensi_url'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Detail Materi - <?= $judul ?> &mdash; SICONIC</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <style>
  .btn-seragam {
    min-width: 160px;
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
            <h1>Detail Materi</h1>
            <div class="section-header-breadcrumb">
              <a href="materi.php" class="btn btn-sm btn-light">
                <i class="fas fa-arrow-left"></i> Kembali
              </a>
            </div>
          </div>

          <div class="section-body">
            <div class="card">
              <div class="card-header">
                <h4><?= $judul ?></h4>
              </div>
              <div class="card-body">

                <div class="mb-3">
                  <span class="badge badge-primary"><i class="fas fa-user"></i> <?= $user ?></span>
                  <span class="badge badge-secondary"><i class="fas fa-clock"></i> <?= $waktu ?></span>
                </div>

                <div class="mb-4">
                  <strong>Deskripsi:</strong>
                  <p><?= $deskripsi ?></p>
                </div>

                <table class="table table-bordered table-striped">
                  <thead class="thead-dark">
                    <tr>
                      <th width="30%">Tipe</th>
                      <th width="70%">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                   <tr>
  <td><i class="fas fa-link text-info"></i> Refrensi URL</td>
  <td>
    <?php if (!empty($url)): ?>
      <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="min-width: 160px;">
        <i class="fas fa-external-link-alt"></i> Kunjungi URL
      </a>
    <?php else: ?>
      <span class="text-muted">Tidak tersedia</span>
    <?php endif; ?>
  </td>
</tr>
<tr>
  <td><i class="fas fa-file-alt text-warning"></i> Dokumen</td>
  <td>
    <?php if (!empty($dokumen)): ?>
      <a href="uploads/<?= htmlspecialchars($dokumen) ?>" target="_blank" class="btn btn-sm btn-outline-warning" style="min-width: 160px;">
        <i class="fas fa-download"></i> Lihat Dokumen
      </a>
    <?php else: ?>
      <span class="text-muted">Tidak tersedia</span>
    <?php endif; ?>
  </td>
</tr>
<tr>
  <td><i class="fas fa-video text-danger"></i> Video</td>
  <td>
    <?php if (!empty($video)): ?>
      <a href="play_video.php?file=<?= urlencode($video) ?>" target="_blank" class="btn btn-sm btn-outline-danger" style="min-width: 160px;">
        <i class="fas fa-play-circle"></i> Lihat Video
      </a>
    <?php else: ?>
      <span class="text-muted">Tidak tersedia</span>
    <?php endif; ?>
  </td>
</tr>

                  </tbody>
                </table>

              </div>
            </div>
          </div>
        </section>
      </div>

      <?php include 'footer.php'; ?>
    </div>
  </div>

  <!-- General JS Scripts -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>

  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>
