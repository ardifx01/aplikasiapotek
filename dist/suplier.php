<?php
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$user_id = $_SESSION['user_id'] ?? 0;
$current_file = basename(__FILE__);

// Fungsi flash message
function set_flash($msg){
    $_SESSION['flash_message'] = $msg;
}

// Fungsi set tab aktif
function set_active_tab($tab){
    $_SESSION['active_tab'] = $tab; // 'input' atau 'data'
}

// Proses Simpan / Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi    = $_POST['aksi'] ?? '';
    $id      = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $nama    = trim($_POST['nama'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $email   = trim($_POST['email'] ?? '');

    if(empty($nama)){
        set_flash("❌ Nama supplier wajib diisi.");
        set_active_tab('input');
        header("Location: suplier.php");
        exit;
    }

    if($aksi === 'tambah'){
        $stmt = $conn->prepare("INSERT INTO supplier (nama, alamat, telepon, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $alamat, $telepon, $email);
        $msg = "✅ Supplier berhasil ditambahkan.";
    } elseif($aksi === 'edit'){
        $stmt = $conn->prepare("UPDATE supplier SET nama=?, alamat=?, telepon=?, email=? WHERE id=?");
        $stmt->bind_param("ssssi", $nama, $alamat, $telepon, $email, $id);
        $msg = "✅ Supplier berhasil diperbarui.";
    } else {
        $stmt = null;
    }

    if($stmt){
        if($stmt->execute()){
            set_flash($msg);
            set_active_tab('data'); // setelah simpan/edit, pindah ke tab data
        } else {
            set_flash("❌ Gagal: " . $stmt->error);
            set_active_tab('input');
        }
        $stmt->close();
    }

    header("Location: suplier.php");
    exit;
}

// Hapus
if (isset($_GET['hapus'])){
    $id = (int) $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM supplier WHERE id=?");
    $stmt->bind_param("i", $id);
    if($stmt->execute()){
        set_flash("✅ Supplier berhasil dihapus.");
        set_active_tab('data');
    } else {
        set_flash("❌ Gagal hapus: " . $stmt->error);
        set_active_tab('data');
    }
    $stmt->close();
    header("Location: suplier.php");
    exit;
}

// Ambil data supplier
$data_supplier = $conn->query("SELECT * FROM supplier ORDER BY nama ASC");

// Ambil tab aktif
$activeTab = $_SESSION['active_tab'] ?? 'input';
unset($_SESSION['active_tab']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manajemen Supplier &mdash; Apotek-KU</title>
<link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css" />
<link rel="stylesheet" href="assets/css/style.css" />
<link rel="stylesheet" href="assets/css/components.css" />
<style>
#notif-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    display: none;
    min-width: 250px;
}
.table-nowrap td,
.table-nowrap th {
    white-space: nowrap;
}
.table thead th {
    background-color: #000 !important;
    color: #fff !important;
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

        <?php if(isset($_SESSION['flash_message'])): ?>
            <div id="notif-toast" class="alert alert-info">
                <?= $_SESSION['flash_message'] ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
              <h4>Manajemen Supplier</h4>
            </div>
            <div class="card-body">
              <ul class="nav nav-tabs" id="supplierTab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link <?= $activeTab=='input'?'active':'' ?>" id="input-tab" data-toggle="tab" href="#input" role="tab">Input Supplier</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $activeTab=='data'?'active':'' ?>" id="data-tab" data-toggle="tab" href="#data" role="tab">Data Supplier</a>
                </li>
              </ul>

              <div class="tab-content mt-3">
                <div class="tab-pane fade <?= $activeTab=='input'?'show active':'' ?>" id="input" role="tabpanel">
                  <form method="POST">
                    <input type="hidden" name="aksi" value="tambah">
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label>Nama Supplier</label>
                        <input type="text" name="nama" class="form-control" required>
                      </div>
                      <div class="form-group col-md-6">
                        <label>Alamat</label>
                        <input type="text" name="alamat" class="form-control">
                      </div>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label>Telepon</label>
                        <input type="text" name="telepon" class="form-control">
                      </div>
                      <div class="form-group col-md-6">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control">
                      </div>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                  </form>
                </div>

                <div class="tab-pane fade <?= $activeTab=='data'?'show active':'' ?>" id="data" role="tabpanel">
                  <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped table-nowrap">
                      <thead class="thead-dark">
                        <tr>
                          <th>No</th>
                          <th>Nama</th>
                          <th>Alamat</th>
                          <th>Telepon</th>
                          <th>Email</th>
                          <th>Aksi</th>
                        </tr>
                      </thead>
                      <tbody>
                      <?php $no=1; while($row=$data_supplier->fetch_assoc()): ?>
                        <tr>
                          <td><?= $no++; ?></td>
                          <td><?= htmlspecialchars($row['nama']) ?></td>
                          <td><?= htmlspecialchars($row['alamat']) ?></td>
                          <td><?= htmlspecialchars($row['telepon']) ?></td>
                          <td><?= htmlspecialchars($row['email']) ?></td>
                          <td>
                            <button type="button" class="btn btn-warning btn-sm btn-edit"
                              data-id="<?= $row['id'] ?>"
                              data-nama="<?= htmlspecialchars($row['nama']) ?>"
                              data-alamat="<?= htmlspecialchars($row['alamat']) ?>"
                              data-telepon="<?= htmlspecialchars($row['telepon']) ?>"
                              data-email="<?= htmlspecialchars($row['email']) ?>">
                              <i class="fas fa-edit"></i>
                            </button>
                            <a href="suplier.php?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus supplier ini?');"><i class="fas fa-trash"></i></a>
                          </td>
                        </tr>
                      <?php endwhile; ?>
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

    $(document).on("click", ".btn-edit", function(){
        var id = $(this).data("id");
        var nama = $(this).data("nama");
        var alamat = $(this).data("alamat");
        var telepon = $(this).data("telepon");
        var email = $(this).data("email");

        var form = '<form method="POST">'+
            '<input type="hidden" name="aksi" value="edit">'+
            '<input type="hidden" name="id" value="'+id+'">'+
            '<div class="form-group"><label>Nama</label><input type="text" name="nama" class="form-control" value="'+nama+'" required></div>'+
            '<div class="form-group"><label>Alamat</label><input type="text" name="alamat" class="form-control" value="'+alamat+'"></div>'+
            '<div class="form-group"><label>Telepon</label><input type="text" name="telepon" class="form-control" value="'+telepon+'"></div>'+
            '<div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" value="'+email+'"></div>'+
            '<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>'+
            '<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>'+
            '</form>';

        $('#modalEdit .modal-content').html(form);
        $('#modalEdit').modal('show');
    });
});
</script>

<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <!-- Isi form akan di-generate JS -->
    </div>
  </div>
</div>

</body>
</html>
