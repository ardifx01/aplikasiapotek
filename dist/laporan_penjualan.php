<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil filter tanggal
$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');

// Query data penjualan master dan detail
$sql = "SELECT pm.id, pm.kode_penjualan, pm.tanggal, pm.nama_petugas, pm.total, pm.uang_diterima, u.nama AS petugas
        FROM penjualan_master pm
        LEFT JOIN users u ON pm.user_id = u.id
        WHERE pm.tanggal BETWEEN '$tgl_dari 00:00:00' AND '$tgl_sampai 23:59:59'
        ORDER BY pm.tanggal DESC";
$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport" />
  <title>Laporan Penjualan &mdash; Apotek-KU</title>

  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/components.css" />
</head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <?php include 'navbar.php'; ?>
      <?php include 'sidebar.php'; ?>

      <div class="main-content">
        <section class="section">
          <div class="section-body">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Laporan Penjualan</h4>
                <form class="form-inline" method="get">
                  <input type="date" name="tgl_dari" class="form-control form-control-sm mr-1" value="<?= $tgl_dari ?>">
                  <input type="date" name="tgl_sampai" class="form-control form-control-sm mr-1" value="<?= $tgl_sampai ?>">
                  <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                  <a href="cetak_laporan_penjualan.php?tgl_dari=<?= $tgl_dari ?>&tgl_sampai=<?= $tgl_sampai ?>" target="_blank" class="btn btn-success btn-sm"><i class="fas fa-print"></i> Cetak PDF</a>
                </form>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-sm table-hover">
                    <thead class="thead-dark">
                      <tr>
                        <th>#</th>
                        <th>Kode Penjualan</th>
                        <th>Tanggal</th>
                        <th>Petugas</th>
                        <th>Total</th>
                        <th>Uang Diterima</th>
                        <th>Kembalian</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $no = 1; $grand_total = 0; while ($row = mysqli_fetch_assoc($result)): ?>
                          <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['kode_penjualan']); ?></td>
                            <td><?= date("d-m-Y H:i", strtotime($row['tanggal'])); ?></td>
                            <td><?= htmlspecialchars($row['nama_petugas'] ?: $row['petugas']); ?></td>
                            <td>Rp <?= number_format($row['total'],0,',','.'); ?></td>
                            <td>Rp <?= number_format($row['uang_diterima'],0,',','.'); ?></td>
                            <td>Rp <?= number_format($row['uang_diterima'] - $row['total'],0,',','.'); ?></td>
                          </tr>
                          <?php $grand_total += $row['total']; ?>
                        <?php endwhile; ?>
                        <tr class="font-weight-bold">
                          <td colspan="4" class="text-right">Grand Total</td>
                          <td>Rp <?= number_format($grand_total,0,',','.'); ?></td>
                          <td colspan="2"></td>
                        </tr>
                      <?php else: ?>
                        <tr>
                          <td colspan="7" class="text-center">Tidak ada data penjualan</td>
                        </tr>
                      <?php endif; ?>
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

  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>
