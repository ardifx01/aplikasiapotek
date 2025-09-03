<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

function formatTanggalIndonesia($tanggal) {
  $bulan = [
    1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  $tglObj = strtotime($tanggal);
  $tgl = date('j', $tglObj);
  $bln = $bulan[(int)date('n', $tglObj)];
  $thn = date('Y', $tglObj);
  return "$tgl $bln $thn";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Daftar Kuis &mdash; SICONIC</title>
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
            <h1>Daftar Kuis</h1>
          </div>

          <div class="section-body">
            <div class="card">
              <div class="card-header">
                <h4>List Kuis Tersedia</h4>
              </div>
              <div class="card-body table-responsive">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th>No</th>
                      <th>Judul Kuis</th>
                      <th>Jumlah Soal</th>
                      <th>Durasi (hari)</th>
                      <th>Waktu Kuis</th>
                      <th>Opsi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $user_id = $_SESSION['user_id'];
                    $query = "SELECT jk.id, jk.judul, jk.durasi, jk.mulai, COUNT(k.id) as jumlah_soal
                              FROM judul_kuis jk
                              LEFT JOIN kuis k ON jk.id = k.judul_id
                              GROUP BY jk.id, jk.judul, jk.durasi, jk.mulai
                              ORDER BY jk.created_at DESC";
                    $result = $conn->query($query);
                    $no = 1;
                    $today = new DateTime();

                    while ($row = $result->fetch_assoc()):
                      $kuis_id = $row['id'];
                      $durasi_hari = intval($row['durasi']);
                      $mulai_str = $row['mulai'];

                      $waktu_mulai = new DateTime($mulai_str);
                      $waktu_berakhir = (clone $waktu_mulai)->modify("+$durasi_hari days");

                      $kuis_sudah_lewat = $today > $waktu_berakhir;
                      $allow_start = $today >= $waktu_mulai;

                      // Cek apakah user sudah menjawab
                      $cek = $conn->prepare("SELECT COUNT(*) FROM jawaban_kuis WHERE user_id = ? AND judul_id = ?");
                      $cek->bind_param("ii", $user_id, $kuis_id);
                      $cek->execute();
                      $cek->bind_result($sudah_jawab);
                      $cek->fetch();
                      $cek->close();

                      // Hitung nilai jika sudah menjawab
                      $nilai = null;
                      if ($sudah_jawab > 0) {
                        $stmt = $conn->prepare("SELECT k.jawaban AS kunci, j.jawaban
                                                FROM kuis k
                                                JOIN jawaban_kuis j ON k.id = j.soal_id
                                                WHERE j.user_id = ? AND j.judul_id = ?");
                        $stmt->bind_param("ii", $user_id, $kuis_id);
                        $stmt->execute();
                        $res = $stmt->get_result();

                        $total = $benar = 0;
                        while ($r = $res->fetch_assoc()) {
                          $total++;
                          if (strtoupper(trim($r['jawaban'])) === strtoupper(trim($r['kunci']))) {
                            $benar++;
                          }
                        }
                        $nilai = $total > 0 ? round(($benar / $total) * 100, 2) : 0;
                      }
                    ?>
                    <tr>
                      <td><?= $no++ ?></td>
                      <td><?= htmlspecialchars($row['judul']) ?></td>
                      <td><?= $row['jumlah_soal'] ?></td>
                      <td><?= $durasi_hari ?> hari</td>
                      <td><?= $mulai_str ? formatTanggalIndonesia($mulai_str) : '-' ?></td>
                      <td>
                        <?php if ($sudah_jawab > 0): ?>
                          <span class="badge badge-secondary mb-1"><i class="fas fa-lock"></i> Sudah Diikuti</span>
                          <small class="d-block text-muted">Nilai: <strong><?= $nilai ?></strong></small>
                        <?php elseif (!$allow_start): ?>
                          <button class="btn btn-warning btn-sm" onclick="showWaitingAlert('<?= formatTanggalIndonesia($mulai_str) ?>')">
                            <i class="fas fa-clock"></i> Kuis Belum Dimulai
                          </button>
                        <?php elseif ($kuis_sudah_lewat): ?>
                          <button class="btn btn-danger btn-sm" onclick="showExpiredAlert('<?= formatTanggalIndonesia($waktu_berakhir->format('Y-m-d')) ?>')">
                            <i class="fas fa-times-circle"></i> Kuis Sudah Lewat
                          </button>
                        <?php else: ?>
                          <a href="mulai_kuis.php?id=<?= $kuis_id ?>" class="btn btn-success btn-sm" onclick="localStorage.removeItem('kuisStartTime')">
                            <i class="fas fa-play"></i> Mulai
                          </a>
                        <?php endif; ?>
                      </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($result->num_rows === 0): ?>
                      <tr><td colspan="6" class="text-center">Belum ada kuis tersedia.</td></tr>
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

  <!-- ===================== SCRIPT URUTAN WAJIB ===================== -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function showWaitingAlert(jadwal) {
      Swal.fire({
        icon: 'info',
        title: 'Kuis Belum Dibuka',
        text: 'Silakan menunggu hingga tanggal mulai: ' + jadwal,
        confirmButtonText: 'Oke'
      });
    }

    function showExpiredAlert(berakhir) {
      Swal.fire({
        icon: 'error',
        title: 'Kuis Sudah Lewat',
        text: 'Kuis ini sudah berakhir pada: ' + berakhir,
        confirmButtonText: 'Tutup'
      });
    }
  </script>

  <?php if (isset($_GET['score'])): ?>
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Jawaban Anda telah disimpan!',
        html: '<strong>Nilai Anda: <?= htmlspecialchars($_GET['score']) ?> </strong>',
        confirmButtonText: 'Oke',
      });
    </script>
  <?php endif; ?>
</body>
</html>
