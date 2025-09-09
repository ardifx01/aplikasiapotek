<?php
include 'koneksi.php';

$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');

// Query hutang
$sql = "SELECT sm.id, sm.tanggal_masuk, o.kode_obat, o.nama_obat, sm.jumlah, sm.harga_beli, sm.total,
        sm.cara_bayar, sm.jatuh_tempo, sm.keterangan, s.nama AS nama_supplier
        FROM stok_masuk sm
        LEFT JOIN obat o ON sm.id_obat = o.id
        LEFT JOIN supplier s ON sm.id_supplier = s.id
        WHERE sm.cara_bayar = 'Tempo'
          AND sm.jatuh_tempo BETWEEN '$tgl_dari' AND '$tgl_sampai'
        ORDER BY sm.jatuh_tempo ASC";

$result = mysqli_query($conn, $sql);

// Debug: jika query error
if(!$result){
    die("Query Error: ".mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Laporan Hutang &mdash; Apotek-KU</title>
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
              <h4>Laporan Hutang</h4>
              <form class="form-inline" method="get">
                <input type="date" name="tgl_dari" class="form-control form-control-sm mr-1" value="<?= $tgl_dari ?>">
                <input type="date" name="tgl_sampai" class="form-control form-control-sm mr-1" value="<?= $tgl_sampai ?>">
                <button type="submit" class="btn btn-primary btn-sm mr-1"><i class="fas fa-filter"></i> Filter</button>
                <a href="cetak_laporan_hutang.php?tgl_dari=<?= $tgl_dari ?>&tgl_sampai=<?= $tgl_sampai ?>" target="_blank" class="btn btn-success btn-sm">
                  <i class="fas fa-print"></i> Cetak PDF
                </a>
              </form>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover">
                  <thead class="thead-dark">
                    <tr>
                      <th>#</th>
                      <th>Supplier</th>
                      <th>Tanggal Masuk</th>
                      <th>Jatuh Tempo</th>
                      <th>Total Hutang</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                      <?php $no=1; $grand_total=0; ?>
                      <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                          <td><?= $no++; ?></td>
                          <td><?= htmlspecialchars($row['nama_supplier'] ?? '-'); ?></td>
                          <td><?= date("d-m-Y", strtotime($row['tanggal_masuk'])); ?></td>
                          <td><?= date("d-m-Y", strtotime($row['jatuh_tempo'])); ?></td>
                          <td>Rp <?= number_format($row['total'],0,',','.'); ?></td>
                        </tr>
                        <?php $grand_total += $row['total']; ?>
                      <?php endwhile; ?>
                      <tr class="font-weight-bold">
                        <td colspan="4" class="text-right">Grand Total</td>
                        <td>Rp <?= number_format($grand_total,0,',','.'); ?></td>
                      </tr>
                    <?php else: ?>
                      <tr>
                        <td colspan="5" class="text-center">Tidak ada data hutang</td>
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
