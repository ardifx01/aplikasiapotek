<?php
include 'security.php';
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$user_id = $_SESSION['user_id'] ?? 0;

// Flash message
function set_flash($msg){
    $_SESSION['flash_message'] = $msg;
}

// Set tab aktif
function set_active_tab($tab){
    $_SESSION['active_tab'] = $tab;
}

// Fungsi generate kode operasional
function generateKodeOperasional($conn){
    $tgl = date('Ymd');
    $result = $conn->query("SELECT kode_operasional FROM biaya_operasional WHERE kode_operasional LIKE 'BO-$tgl%' ORDER BY kode_operasional DESC LIMIT 1");
    $last = $result->fetch_assoc();
    if($last){
        $num = (int)substr($last['kode_operasional'], 11) + 1;
    } else {
        $num = 1;
    }
    return 'BO-'.$tgl.'-'.str_pad($num,3,'0',STR_PAD_LEFT);
}

// Proses Simpan Biaya Operasional
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi       = $_POST['aksi'] ?? '';
    $tanggal    = $_POST['tanggal'] ?? date('Y-m-d');
    $keterangan = trim($_POST['keterangan'] ?? '');
    $jumlah     = isset($_POST['jumlah']) ? (float) $_POST['jumlah'] : 0;
    $nota       = trim($_POST['nota'] ?? '');

    if($jumlah <= 0 || $keterangan==''){
        set_flash("❌ Semua field wajib diisi dengan benar.");
        set_active_tab('input');
        header("Location: operasional.php");
        exit;
    }

    $kode_operasional = generateKodeOperasional($conn);

    $stmt = $conn->prepare("INSERT INTO biaya_operasional (kode_operasional, tanggal, keterangan, jumlah, nota) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $kode_operasional, $tanggal, $keterangan, $jumlah, $nota);
    if($stmt->execute()){
        set_flash("✅ Biaya operasional berhasil ditambahkan.");
        set_active_tab('data');
    } else {
        set_flash("❌ Gagal: ".$stmt->error);
        set_active_tab('input');
    }
    $stmt->close();
    header("Location: operasional.php");
    exit;
}

// Ambil tab aktif
$activeTab = $_SESSION['active_tab'] ?? 'input';
unset($_SESSION['active_tab']);

// Ambil data operasional
$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');
$data_operasional = $conn->query("SELECT * FROM biaya_operasional WHERE tanggal BETWEEN '$tgl_dari' AND '$tgl_sampai' ORDER BY tanggal ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Biaya Operasional &mdash; Apotek-KU</title>
<link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css" />
<link rel="stylesheet" href="assets/css/style.css" />
<link rel="stylesheet" href="assets/css/components.css" />
<style>
#notif-toast { position: fixed; top: 20px; right: 20px; z-index: 9999; display: none; min-width: 250px; }
.table-nowrap td, .table-nowrap th { white-space: nowrap; }
.table thead th { background-color: #000 !important; color: #fff !important; }
</style>
</head>
<body>
<div id="app">
  <div class="main-wrapper main-wrapper-1">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
      <section class="section">
        <div class="section-body">

        <?php if(isset($_SESSION['flash_message'])): ?>
            <div id="notif-toast" class="alert alert-info">
                <?= $_SESSION['flash_message'] ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
              <h4>Biaya Operasional / Pengeluaran Harian</h4>
            </div>
            <div class="card-body">
              <ul class="nav nav-tabs" id="operasionalTab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link <?= $activeTab=='input'?'active':'' ?>" id="input-tab" data-toggle="tab" href="#input" role="tab">Input Biaya Operasional</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $activeTab=='data'?'active':'' ?>" id="data-tab" data-toggle="tab" href="#data" role="tab">Data Biaya Operasional</a>
                </li>
              </ul>

              <div class="tab-content mt-3">
                <!-- Form Input -->
                <div class="tab-pane fade <?= $activeTab=='input'?'show active':'' ?>" id="input" role="tabpanel">
                  <form method="POST">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group col-md-5">
                            <label>Keterangan</label>
                            <input type="text" name="keterangan" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Jumlah (Rp)</label>
                            <input type="number" name="jumlah" class="form-control" min="0" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>No. Nota</label>
                            <input type="text" name="nota" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                  </form>
                </div>

                <!-- Data -->
                <div class="tab-pane fade <?= $activeTab=='data'?'show active':'' ?>" id="data" role="tabpanel">
                  <form method="get" class="form-inline mb-2">
                      <input type="date" name="tgl_dari" class="form-control mr-2" value="<?= $tgl_dari ?>">
                      <input type="date" name="tgl_sampai" class="form-control mr-2" value="<?= $tgl_sampai ?>">
                      <button type="submit" class="btn btn-secondary">Filter</button>
                  </form>
                  <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped table-nowrap">
                      <thead class="thead-dark">
                        <tr>
                          <th>No</th>
                          <th>Kode</th>
                          <th>Tanggal</th>
                          <th>Keterangan</th>
                          <th>Nota</th>
                          <th>Jumlah (Rp)</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php 
                        $no=1; 
                        $total_operasional = 0;
                        while($row=$data_operasional->fetch_assoc()):
                            $total_operasional += $row['jumlah'];
                        ?>
                        <tr>
                          <td><?= $no++; ?></td>
                          <td><?= htmlspecialchars($row['kode_operasional']) ?></td>
                          <td><?= date('d-m-Y', strtotime($row['tanggal'])) ?></td>
                          <td><?= htmlspecialchars($row['keterangan']) ?></td>
                          <td><?= htmlspecialchars($row['nota'] ?: '-') ?></td>
                          <td class="text-right"><?= number_format($row['jumlah'],0,',','.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="font-weight-bold">
                          <td colspan="5" class="text-right">Total Biaya Operasional</td>
                          <td class="text-right"><?= number_format($total_operasional,0,',','.') ?></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

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
<script>
$(document).ready(function(){
    var toast = $('#notif-toast');
    if(toast.length){
        toast.fadeIn(300).delay(2000).fadeOut(500);
    }
});
</script>
</body>
</html>
