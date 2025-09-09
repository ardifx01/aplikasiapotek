<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// --- HANDLE SIMPAN / UPDATE OBAT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {
    $aksi       = $_POST['aksi'];
    $id         = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $kode_obat  = mysqli_real_escape_string($conn, $_POST['kode_obat']);
    $nama_obat  = mysqli_real_escape_string($conn, $_POST['nama_obat']);
    $kategori   = mysqli_real_escape_string($conn, $_POST['kategori']);
    $satuan     = mysqli_real_escape_string($conn, $_POST['satuan']);
    $harga_beli = (int) $_POST['harga_beli'];
    $harga_jual = (int) $_POST['harga_jual'];
    $stok       = (int) $_POST['stok'];
    $tanggal_kadaluwarsa = $_POST['tanggal_kadaluwarsa'] ?: null;
    $jatuh_tempo         = $_POST['jatuh_tempo'] ?: null;

    if ($aksi === 'tambah') {
        $stmt = $conn->prepare("INSERT INTO obat 
            (kode_obat, nama_obat, kategori, satuan, harga_beli, harga_jual, stok, tanggal_kadaluwarsa, jatuh_tempo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        // Perbaikan tipe param: satuan = string
        $stmt->bind_param("ssssiiiss", $kode_obat, $nama_obat, $kategori, $satuan, $harga_beli, $harga_jual, $stok, $tanggal_kadaluwarsa, $jatuh_tempo);
        $stmt->execute();
        $stmt->close();
    } elseif ($aksi === 'edit') {
        $stmt = $conn->prepare("UPDATE obat SET 
            kode_obat=?, nama_obat=?, kategori=?, satuan=?, harga_beli=?, harga_jual=?, stok=?, tanggal_kadaluwarsa=?, jatuh_tempo=? 
            WHERE id=?");
        // Perbaikan tipe param: satuan = string
        $stmt->bind_param("ssssiiissi", $kode_obat, $nama_obat, $kategori, $satuan, $harga_beli, $harga_jual, $stok, $tanggal_kadaluwarsa, $jatuh_tempo, $id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: data_obat.php");
    exit;
}

// --- HANDLE HAPUS OBAT ---
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM obat WHERE id='$id'");
    header("Location: data_obat.php");
    exit;
}

// --- GENERATE KODE OBAT OTOMATIS ---
$result = mysqli_query($conn, "SELECT kode_obat FROM obat ORDER BY id DESC LIMIT 1");
$last = mysqli_fetch_assoc($result);
$lastKode = $last ? intval(substr($last['kode_obat'], 3)) : 0;
$kode_otomatis = "OBT" . str_pad($lastKode + 1, 4, "0", STR_PAD_LEFT);

// --- AMBIL DATA OBAT ---
$obat = mysqli_query($conn, "SELECT * FROM obat ORDER BY nama_obat ASC");

// --- AMBIL MASTER KATEGORI DAN SATUAN ---
$kategori_list = mysqli_query($conn, "SELECT nama_kategori FROM master_kategori ORDER BY nama_kategori ASC");
$satuan_list   = mysqli_query($conn, "SELECT nama_satuan FROM master_satuan ORDER BY nama_satuan ASC");
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport" />
<title>Stok Obat &mdash; Apotek-KU</title>
<link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css" />
<link rel="stylesheet" href="assets/css/style.css" />
<link rel="stylesheet" href="assets/css/components.css" />
<style>
.form-horizontal .form-group {
    display: flex;
    align-items: center;
    margin-bottom: 10px;
}
.form-horizontal .form-group label {
    width: 140px;
    margin-bottom: 0;
    font-weight: 500;
}
.form-horizontal .form-group .input-group {
    flex: 1;
}

.bg-darkblue {
    background-color: #001f3f !important;
    color: #fff;
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
        <div class="section-body">

          <div class="card">
           <div class="card-header d-flex justify-content-between align-items-center">
  <h4>
    Stok Obat 
    <i class="fas fa-question-circle text-danger" style="cursor:pointer;" data-toggle="modal" data-target="#modalInfoWarna"></i>
  </h4>
  <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambah">
    <i class="fas fa-plus"></i> Tambah Obat Baru
  </button>
</div>

            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-bordered table-sm table-hover">
                <thead class="thead-light">
  <tr>
    <th class="text-center">No</th>
    <th>Kode Obat</th>
    <th>Nama Obat</th>
    <th>Kategori</th>
    <th>Satuan</th>
    <th>Harga Beli</th>
    <th>Harga Jual</th>
    <th>Stok Obat</th>
    <th>Tanggal Kadaluwarsa</th>
    <th>Jatuh Tempo</th>
    <th>Aksi</th>
  </tr>
</thead>
<tbody>
<?php
$no = 1;
$today = date('Y-m-d'); // tanggal hari ini
while ($row = mysqli_fetch_assoc($obat)) {
    // --- Warna Kadaluwarsa ---
    $expiredClass = '';
    if (!empty($row['tanggal_kadaluwarsa'])) {
        $expDate = $row['tanggal_kadaluwarsa'];
        $diff = (strtotime($expDate) - strtotime($today)) / (60*60*24); // selisih hari
        if ($diff < 0) {
            $expiredClass = 'bg-danger text-white'; // kadaluwarsa
        } elseif ($diff <= 7) {
            $expiredClass = 'bg-warning'; // hampir kadaluwarsa
        }
    }

   $jatuhTempoClass = '';
if (!empty($row['jatuh_tempo'])) {
    $jtDate = $row['jatuh_tempo'];
    $diffJT = (strtotime($jtDate) - strtotime($today)) / (60*60*24); // selisih hari

 if ($diffJT < 0) {
    $jatuhTempoClass = 'bg-primary text-white'; // biru dongker
} elseif ($diffJT <= 7) {
    $jatuhTempoClass = 'bg-info text-white'; // mendekati jatuh tempo
} else {
    $jatuhTempoClass = 'bg-success text-white'; // masih aman
}

}

// --- Print row ---
echo "<tr>
<td class='text-center'>".$no++."</td>
<td>".$row['kode_obat']."</td>
<td>".$row['nama_obat']."</td>
<td>".$row['kategori']."</td>
<td>".$row['satuan']."</td>
<td>Rp ".number_format($row['harga_beli'],0,',','.')."</td>
<td>Rp ".number_format($row['harga_jual'],0,',','.')."</td>
<td>".$row['stok']."</td>
<td class='$expiredClass'>".($row['tanggal_kadaluwarsa'] ? date('d-m-Y', strtotime($row['tanggal_kadaluwarsa'])) : '-')."</td>
<td class='$jatuhTempoClass'>".($row['jatuh_tempo'] ? date('d-m-Y', strtotime($row['jatuh_tempo'])) : '-')."</td>
<td>
 <button class='btn btn-warning btn-sm btn-edit'
    data-id='".$row['id']."'
    data-kode='".$row['kode_obat']."'
    data-nama='".$row['nama_obat']."'
    data-kategori='".$row['kategori']."'
    data-satuan='".$row['satuan']."'
    data-beli='".$row['harga_beli']."'
    data-jual='".$row['harga_jual']."'
    data-stok='".$row['stok']."'
    data-kadaluwarsa='".$row['tanggal_kadaluwarsa']."'
    data-jatuhtempo='".$row['jatuh_tempo']."'>
    <i class='fas fa-edit'></i>
</button>

  <a href='data_obat.php?hapus=".$row['id']."' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin hapus obat ini?');\">
    <i class='fas fa-trash'></i>
  </a>
</td>
</tr>";

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

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <form method="POST" class="modal-content form-horizontal">
      <input type="hidden" name="aksi" value="tambah">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-plus"></i> Tambah Obat</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Kode Obat</label>
          <input type="text" name="kode_obat" class="form-control" value="<?= $kode_otomatis ?>" readonly>
        </div>
        <div class="form-group">
          <label>Nama Obat</label>
          <input type="text" name="nama_obat" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Kategori</label>
          <select name="kategori" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <?php 
            mysqli_data_seek($kategori_list,0);
            while($kat = mysqli_fetch_assoc($kategori_list)) { ?>
              <option value="<?= $kat['nama_kategori'] ?>"><?= $kat['nama_kategori'] ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <label>Satuan</label>
          <select name="satuan" class="form-control" required>
            <option value="">-- Pilih Satuan --</option>
            <?php 
            mysqli_data_seek($satuan_list,0);
            while($sat = mysqli_fetch_assoc($satuan_list)) { ?>
              <option value="<?= $sat['nama_satuan'] ?>"><?= $sat['nama_satuan'] ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <label>Harga Beli</label>
          <input type="number" name="harga_beli" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Harga Jual</label>
          <input type="number" name="harga_jual" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Stok Awal</label>
          <input type="number" name="stok" class="form-control" value="0" required>
        </div>
        <div class="form-group">
          <label>Tanggal Kadaluwarsa</label>
          <input type="date" name="tanggal_kadaluwarsa" class="form-control">
        </div>
        <div class="form-group">
          <label>Jatuh Tempo</label>
          <input type="date" name="jatuh_tempo" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <form method="POST" class="modal-content form-horizontal">
      <input type="hidden" name="aksi" value="edit">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Obat</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Kode Obat</label>
          <input type="text" name="kode_obat" id="edit-kode" class="form-control" readonly>
        </div>
        <div class="form-group">
          <label>Nama Obat</label>
          <input type="text" name="nama_obat" id="edit-nama" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Kategori</label>
          <select name="kategori" id="edit-kategori" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <?php 
            mysqli_data_seek($kategori_list,0);
            while($kat = mysqli_fetch_assoc($kategori_list)) { ?>
              <option value="<?= $kat['nama_kategori'] ?>"><?= $kat['nama_kategori'] ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <label>Satuan</label>
          <select name="satuan" id="edit-satuan" class="form-control" required>
            <option value="">-- Pilih Satuan --</option>
            <?php 
            mysqli_data_seek($satuan_list,0);
            while($sat = mysqli_fetch_assoc($satuan_list)) { ?>
              <option value="<?= $sat['nama_satuan'] ?>"><?= $sat['nama_satuan'] ?></option>
            <?php } ?>
          </select>
        </div>
        <div class="form-group">
          <label>Harga Beli</label>
          <input type="number" name="harga_beli" id="edit-beli" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Harga Jual</label>
          <input type="number" name="harga_jual" id="edit-jual" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Stok</label>
          <input type="number" name="stok" id="edit-stok" class="form-control" required>
        </div>
        <div class="form-group">
          <label>Tanggal Kadaluwarsa</label>
          <input type="date" name="tanggal_kadaluwarsa" id="edit-kadaluwarsa" class="form-control">
        </div>
        <div class="form-group">
          <label>Jatuh Tempo</label>
          <input type="date" name="jatuh_tempo" id="edit-jatuhtempo" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>


<!-- Modal Info Warna -->
<div class="modal fade" id="modalInfoWarna" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Arti Warna Pada Kolom Tabel</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
     <div class="modal-body">
  <ul>
    <li><span class="badge badge-danger">&nbsp;&nbsp;&nbsp;</span> Sudah kadaluwarsa</li>
    <li><span class="badge badge-warning">&nbsp;&nbsp;&nbsp;</span> Akan kadaluwarsa dalam 7 hari</li>
    <li><span class="badge badge-primary">&nbsp;&nbsp;&nbsp;</span> Jatuh tempo sudah lewat</li>

    <li><span class="badge badge-info">&nbsp;&nbsp;&nbsp;</span> Jatuh tempo mendekati (≤7 hari)</li>
    <li><span class="badge badge-success">&nbsp;&nbsp;&nbsp;</span> Jatuh tempo masih aman</li>
  </ul>
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
<script>
$(document).on("click", ".btn-edit", function () {
    $("#edit-id").val($(this).data("id"));
    $("#edit-kode").val($(this).data("kode"));
    $("#edit-nama").val($(this).data("nama"));
    $("#edit-kategori").val($(this).data("kategori"));
    $("#edit-satuan").val($(this).data("satuan"));
    $("#edit-beli").val($(this).data("beli"));
    $("#edit-jual").val($(this).data("jual"));
    $("#edit-stok").val($(this).data("stok"));
    $("#edit-kadaluwarsa").val($(this).data("kadaluwarsa"));
    $("#edit-jatuhtempo").val($(this).data("jatuhtempo"));
    $("#modalEdit").modal('show');
});
</script>
</body>
</html>
