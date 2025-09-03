<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

echo "Script mulai_kuis.php berjalan<br>";

include 'koneksi.php';
date_default_timezone_set("Asia/Jakarta");

$judul_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? null;

if (!$judul_id || !$user_id) {
  echo "Judul kuis atau user tidak ditemukan.";
  exit;
}

$stmt = $conn->prepare("SELECT judul, durasi, mulai FROM judul_kuis WHERE id = ?");
$stmt->bind_param("i", $judul_id);
$stmt->execute();
$result = $stmt->get_result();
$judul_kuis = $result->fetch_assoc();

if (!$judul_kuis) {
  echo "Judul kuis tidak ditemukan.";
  exit;
}

$tanggal_mulai = date('Y-m-d', strtotime($judul_kuis['mulai']));
$tanggal_sekarang = date('Y-m-d');

if ($tanggal_sekarang < $tanggal_mulai) {
  echo "
  <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
  <script>
    Swal.fire({
      icon: 'warning',
      title: 'Belum Waktunya!',
      text: 'Kuis ini baru bisa diakses mulai tanggal: $tanggal_mulai',
      confirmButtonText: 'Kembali'
    }).then(() => {
      window.location.href = 'kuis.php';
    });
  </script>
  ";
  exit;
}

// Simpan waktu mulai kuis ke session jika belum ada
if (!isset($_SESSION['waktu_mulai_kuis'][$judul_id])) {
  $_SESSION['waktu_mulai_kuis'][$judul_id] = time();
}
$waktu_mulai = $_SESSION['waktu_mulai_kuis'][$judul_id];

// Hitung sisa waktu berdasarkan durasi
$durasi_detik = $judul_kuis['durasi'] * 60;
$waktu_berakhir = $waktu_mulai + $durasi_detik;
$now = time();
$sisa_detik = max(0, $waktu_berakhir - $now);

$sql_soal = "SELECT * FROM kuis WHERE judul_id = $judul_id ORDER BY id ASC";
$soal_result = $conn->query($sql_soal);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Mulai Kuis &mdash; SICONIC</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
          <h1>Mulai Kuis: <?= htmlspecialchars($judul_kuis['judul']) ?></h1>
          <div class="section-header-breadcrumb">
            <div class="breadcrumb-item">
              <i class="fas fa-clock text-warning"></i>
              <strong>Durasi Kuis:</strong> <?= $judul_kuis['durasi'] ?> menit
            </div>
          </div>
        </div>

        <div class="section-body">
          <form method="POST" action="simpan_jawaban.php" id="form-kuis">
            <input type="hidden" name="judul_id" value="<?= $judul_id ?>">

            <div class="alert alert-info text-center">
              <strong><i class="fas fa-hourglass-half"></i> Waktu Tersisa:</strong> 
              <span id="timer">Loading...</span>
            </div>

            <?php $no = 1; while ($soal = $soal_result->fetch_assoc()): ?>
              <div class="card">
                <div class="card-header"><h4>Soal <?= $no++ ?></h4></div>
                <div class="card-body">
                  <p><strong><?= htmlspecialchars($soal['soal']) ?></strong></p>
                  <?php foreach (['A','B','C','D'] as $opt): ?>
                    <div class="form-check">
                      <input type="radio" class="form-check-input"
                             name="jawaban[<?= $soal['id'] ?>]"
                             value="<?= $opt ?>"
                             id="<?= strtolower($opt) . $soal['id'] ?>">
                      <label class="form-check-label" for="<?= strtolower($opt) . $soal['id'] ?>">
                        <?= $opt ?>. <?= htmlspecialchars($soal['pilihan_' . strtolower($opt)]) ?>
                      </label>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endwhile; ?>

            <?php if ($soal_result->num_rows > 0): ?>
              <div class="text-center mt-4 mb-5">
                <button type="submit" class="btn btn-success">
                  <i class="fas fa-check"></i> Selesai dan Simpan Jawaban
                </button>
              </div>
            <?php else: ?>
              <div class="alert alert-warning">Belum ada soal untuk kuis ini.</div>
            <?php endif; ?>
          </form>
        </div>
      </section>
    </div>
  </div>
</div>

<script src="assets/modules/jquery.min.js"></script>
<script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/scripts.js"></script>

<script>
// Timer mundur berdasarkan sisa waktu
let remaining = <?= $sisa_detik ?>;
const timerEl = document.getElementById("timer");

function updateTimer() {
  if (remaining <= 0) {
    timerEl.textContent = "00:00";
    clearInterval(countdown);

    Swal.fire({
      icon: 'warning',
      title: 'Waktu Habis!',
      text: 'Jawaban akan disimpan otomatis.',
      showConfirmButton: false,
      timer: 2500,
      willClose: () => {
        document.getElementById("form-kuis").submit();
      }
    });
    return;
  }

  const minutes = Math.floor(remaining / 60);
  const seconds = remaining % 60;
  timerEl.textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
  remaining--;
}

const countdown = setInterval(updateTimer, 1000);
updateTimer();

document.getElementById("form-kuis").addEventListener("submit", () => {
  clearInterval(countdown);
});
</script>

</body>
</html>
