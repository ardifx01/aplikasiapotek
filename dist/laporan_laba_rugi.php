<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');

// Ambil total penjualan
$sql_penjualan = "SELECT SUM(total) AS total_penjualan
                  FROM penjualan_master
                  WHERE tanggal BETWEEN '$tgl_dari 00:00:00' AND '$tgl_sampai 23:59:59'";
$res_penjualan = mysqli_query($conn, $sql_penjualan);
$row_penjualan = mysqli_fetch_assoc($res_penjualan);
$total_penjualan = $row_penjualan['total_penjualan'] ?? 0;

// Ambil HPP (Harga Pokok Penjualan)
$sql_hpp = "SELECT SUM(pd.jumlah * o.harga_beli) AS total_hpp
            FROM penjualan_detail pd
            LEFT JOIN obat o ON pd.kode_obat = o.kode_obat
            LEFT JOIN penjualan_master pm ON pd.penjualan_id = pm.id
            WHERE pm.tanggal BETWEEN '$tgl_dari 00:00:00' AND '$tgl_sampai 23:59:59'";
$res_hpp = mysqli_query($conn, $sql_hpp);
$row_hpp = mysqli_fetch_assoc($res_hpp);
$total_hpp = $row_hpp['total_hpp'] ?? 0;

// Hitung Laba Kotor
$laba_kotor = $total_penjualan - $total_hpp;

// Ambil total biaya operasional
$sql_operasional = "SELECT SUM(jumlah) AS total_operasional
                    FROM biaya_operasional
                    WHERE tanggal BETWEEN '$tgl_dari' AND '$tgl_sampai'";
$res_operasional = mysqli_query($conn, $sql_operasional);
$row_operasional = mysqli_fetch_assoc($res_operasional);
$total_operasional = $row_operasional['total_operasional'] ?? 0;

// Hitung Laba Bersih
$laba_bersih = $laba_kotor - $total_operasional;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Laporan Laba Rugi &mdash; Apotek-KU</title>
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
        <div class="section-body">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h4>
                Laporan Laba Rugi
                <i class="fas fa-question-circle text-danger" style="cursor:pointer;" data-toggle="modal" data-target="#modalPenjelasan"></i>
              </h4>
              <form class="form-inline" method="get">
                <input type="date" name="tgl_dari" class="form-control form-control-sm mr-1" value="<?= $tgl_dari ?>">
                <input type="date" name="tgl_sampai" class="form-control form-control-sm mr-1" value="<?= $tgl_sampai ?>">
                <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                <a href="cetak_laba_rugi.php?tgl_dari=<?= $tgl_dari ?>&tgl_sampai=<?= $tgl_sampai ?>" target="_blank" class="btn btn-success btn-sm">
                  <i class="fas fa-print"></i> Cetak PDF
                </a>
              </form>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover">
                  <thead class="thead-dark">
                    <tr>
                      <th>Keterangan</th>
                      <th>Jumlah (Rp)</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Total Penjualan</td>
                      <td class="text-right"><?= number_format($total_penjualan,0,',','.'); ?></td>
                    </tr>
                    <tr>
                      <td>Harga Pokok Penjualan (HPP)</td>
                      <td class="text-right"><?= number_format($total_hpp,0,',','.'); ?></td>
                    </tr>
                    <tr class="font-weight-bold">
                      <td>Laba Kotor</td>
                      <td class="text-right"><?= number_format($laba_kotor,0,',','.'); ?></td>
                    </tr>
                    <tr>
                      <td>Biaya Operasional</td>
                      <td class="text-right"><?= number_format($total_operasional,0,',','.'); ?></td>
                    </tr>
                    <tr class="font-weight-bold">
                      <td>Laba Bersih</td>
                      <td class="text-right"><?= number_format($laba_bersih,0,',','.'); ?></td>
                    </tr>
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

<!-- Modal Penjelasan -->
<div class="modal fade" id="modalPenjelasan" tabindex="-1" role="dialog" aria-labelledby="modalPenjelasanLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPenjelasanLabel">Penjelasan Laporan Laba Rugi</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p><strong>Total Penjualan:</strong> Jumlah semua pendapatan dari penjualan obat selama periode tertentu.</p>
        <p><strong>Harga Pokok Penjualan (HPP):</strong> Total biaya pembelian obat yang dijual selama periode tersebut.</p>
        <p><strong>Laba Kotor:</strong> Selisih antara Total Penjualan dan HPP.</p>
        <p><strong>Biaya Operasional:</strong> Semua pengeluaran harian selain pembelian obat ke supplier.</p>
        <p><strong>Laba Bersih:</strong> Laba Kotor dikurangi Biaya Operasional, menunjukkan keuntungan bersih apotek.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
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
