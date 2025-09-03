<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';
function formatTanggalIndonesia($datetime) {
  date_default_timezone_set('Asia/Jakarta');
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Data Materi &mdash; SICONIC</title>

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
            <h1>Data Materi</h1>
          </div>

          <div class="section-body">
            <div class="card">
              <div class="card-header">
                <h4>Daftar Materi Pelatihan</h4>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-striped table-bordered table-hover">
                   <thead class="thead-dark">
  <tr>
    <th width="5%">No</th>
    <th>Judul</th>
    <th>User Input</th>
    <th>Waktu Input</th>
    <th>Aksi</th>
  </tr>
</thead>
<tbody>
<?php
$no = 1;
$query = "SELECT materi.*, users.nama 
          FROM materi 
          LEFT JOIN users ON materi.user_input = users.id 
          ORDER BY waktu_input DESC";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
  while ($row = mysqli_fetch_assoc($result)) {
    $id_materi = $row['id'];
    $judul = htmlspecialchars($row['judul']);
    $nama_user = !empty($row['nama']) ? htmlspecialchars($row['nama']) : '<span class="text-muted">Tidak diketahui</span>';
    $waktu_input = formatTanggalIndonesia($row['waktu_input']);


    echo "<tr>";
    echo "<td>{$no}</td>";
    echo "<td>{$judul}</td>";
    echo "<td>{$nama_user}</td>";
    echo "<td>{$waktu_input}</td>";
    echo "<td>
            <a href='detail_materi.php?id={$id_materi}' class='btn btn-sm btn-primary' title='Lihat Materi'>
              <i class='fas fa-eye'></i> Lihat
            </a>
          </td>";
    echo "</tr>";
    $no++;
  }
} else {
  echo "<tr><td colspan='5' class='text-center text-muted'>Belum ada materi yang ditambahkan.</td></tr>";
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
