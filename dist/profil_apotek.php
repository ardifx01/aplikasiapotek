<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Tambah Profil Apotek
if (isset($_POST['tambah'])) {
    $nama         = mysqli_real_escape_string($conn, $_POST['nama']);
    $nama_apoteker= mysqli_real_escape_string($conn, $_POST['nama_apoteker']);
    $no_sip       = mysqli_real_escape_string($conn, $_POST['no_sip']);
    $alamat       = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kota         = mysqli_real_escape_string($conn, $_POST['kota']);
    $provinsi     = mysqli_real_escape_string($conn, $_POST['provinsi']);
    $kontak       = mysqli_real_escape_string($conn, $_POST['kontak']);
    $email        = mysqli_real_escape_string($conn, $_POST['email']);

    // Upload logo
    $logo = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logo = 'logo_'.time().'.'.$ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], 'uploads/'.$logo);
    }

    $sql = "INSERT INTO profil_apotek (nama, nama_apoteker, no_sip, alamat, kota, provinsi, kontak, email, logo, created_at) 
            VALUES ('$nama','$nama_apoteker','$no_sip','$alamat','$kota','$provinsi','$kontak','$email','$logo',NOW())";
    mysqli_query($conn, $sql) or die("Gagal tambah profil: " . mysqli_error($conn));
    header("Location: profil_apotek.php?success=1");
    exit;
}

// Edit Profil Apotek
if (isset($_POST['edit'])) {
    $id           = intval($_POST['id']);
    $nama         = mysqli_real_escape_string($conn, $_POST['nama']);
    $nama_apoteker= mysqli_real_escape_string($conn, $_POST['nama_apoteker']);
    $no_sip       = mysqli_real_escape_string($conn, $_POST['no_sip']);
    $alamat       = mysqli_real_escape_string($conn, $_POST['alamat']);
    $kota         = mysqli_real_escape_string($conn, $_POST['kota']);
    $provinsi     = mysqli_real_escape_string($conn, $_POST['provinsi']);
    $kontak       = mysqli_real_escape_string($conn, $_POST['kontak']);
    $email        = mysqli_real_escape_string($conn, $_POST['email']);

    $logo_sql = '';
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == 0) {
        $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
        $logo = 'logo_'.time().'.'.$ext;
        move_uploaded_file($_FILES['logo']['tmp_name'], 'uploads/'.$logo);
        $logo_sql = ", logo='$logo'";
    }

    $sql = "UPDATE profil_apotek SET 
            nama='$nama', nama_apoteker='$nama_apoteker', no_sip='$no_sip',
            alamat='$alamat', kota='$kota', provinsi='$provinsi', 
            kontak='$kontak', email='$email' $logo_sql
            WHERE id=$id";
    mysqli_query($conn, $sql) or die("Gagal edit profil: " . mysqli_error($conn));
    header("Location: profil_apotek.php?updated=1");
    exit;
}

// Ambil data profil apotek
$result = mysqli_query($conn, "SELECT * FROM profil_apotek ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Profil Apotek &mdash; Apotek-KU</title>

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
                <h4>Profil Apotek</h4>
                <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalTambah">
                  <i class="fas fa-plus"></i> Tambah Profil
                </button>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                      <tr>
                        <th>#</th>
                        <th>Nama Apotek</th>
                        <th>Nama Apoteker</th>
                        <th>No SIP</th>
                        <th>Alamat</th>
                        <th>Kota</th>
                        <th>Provinsi</th>
                        <th>Kontak</th>
                        <th>Email</th>
                        <th>Logo</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): ?>
                          <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama']); ?></td>
                            <td><?= htmlspecialchars($row['nama_apoteker']); ?></td>
                            <td><?= htmlspecialchars($row['no_sip']); ?></td>
                            <td><?= htmlspecialchars($row['alamat']); ?></td>
                            <td><?= htmlspecialchars($row['kota']); ?></td>
                            <td><?= htmlspecialchars($row['provinsi']); ?></td>
                            <td><?= htmlspecialchars($row['kontak']); ?></td>
                            <td><?= htmlspecialchars($row['email']); ?></td>
                            <td>
                              <?php if($row['logo']): ?>
                                <img src="uploads/<?= $row['logo']; ?>" width="50" alt="Logo">
                              <?php else: ?>
                                -
                              <?php endif; ?>
                            </td>
                            <td>
                              <button 
                                class="btn btn-sm btn-warning btn-edit"
                                data-id="<?= $row['id']; ?>"
                                data-nama="<?= htmlspecialchars($row['nama']); ?>"
                                data-nama_apoteker="<?= htmlspecialchars($row['nama_apoteker']); ?>"
                                data-no_sip="<?= htmlspecialchars($row['no_sip']); ?>"
                                data-alamat="<?= htmlspecialchars($row['alamat']); ?>"
                                data-kota="<?= htmlspecialchars($row['kota']); ?>"
                                data-provinsi="<?= htmlspecialchars($row['provinsi']); ?>"
                                data-kontak="<?= htmlspecialchars($row['kontak']); ?>"
                                data-email="<?= htmlspecialchars($row['email']); ?>"
                                data-toggle="modal" 
                                data-target="#modalEdit">
                                <i class="fas fa-edit"></i>
                              </button>
                            </td>
                          </tr>
                        <?php endwhile; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="11" class="text-center">Tidak ada data profil</td>
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

  <!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Profil Apotek</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nama Apotek</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-clinic-medical"></i></span>
                </div>
                <input type="text" name="nama" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Alamat</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <input type="text" name="alamat" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Kota</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-city"></i></span>
                </div>
                <input type="text" name="kota" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Provinsi</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-flag"></i></span>
                </div>
                <input type="text" name="provinsi" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Kontak</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-phone"></i></span>
                </div>
                <input type="text" name="kontak" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Email</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" name="email" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Nama Apoteker</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-user-md"></i></span>
                </div>
                <input type="text" name="nama_apoteker" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>No SIP</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                </div>
                <input type="text" name="no_sip" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Logo</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-image"></i></span>
                </div>
                <input type="file" name="logo" class="form-control">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="tambah" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <form method="POST" enctype="multipart/form-data" class="modal-content">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-header">
        <h5 class="modal-title">Edit Profil Apotek</h5>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label>Nama Apotek</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-clinic-medical"></i></span>
                </div>
                <input type="text" name="nama" id="edit-nama" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Alamat</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                </div>
                <input type="text" name="alamat" id="edit-alamat" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Kota</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-city"></i></span>
                </div>
                <input type="text" name="kota" id="edit-kota" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Provinsi</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-flag"></i></span>
                </div>
                <input type="text" name="provinsi" id="edit-provinsi" class="form-control" required>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label>Kontak</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-phone"></i></span>
                </div>
                <input type="text" name="kontak" id="edit-kontak" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Email</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                </div>
                <input type="email" name="email" id="edit-email" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Nama Apoteker</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-user-md"></i></span>
                </div>
                <input type="text" name="nama_apoteker" id="edit-nama_apoteker" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>No SIP</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                </div>
                <input type="text" name="no_sip" id="edit-no_sip" class="form-control" required>
              </div>
            </div>
            <div class="form-group">
              <label>Logo (isi jika ingin ganti)</label>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-image"></i></span>
                </div>
                <input type="file" name="logo" class="form-control">
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="edit" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
      </div>
    </form>
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
    // Kirim data ke modal edit
    $(document).on("click", ".btn-edit", function () {
        $("#edit-id").val($(this).data("id"));
        $("#edit-nama").val($(this).data("nama"));
        $("#edit-alamat").val($(this).data("alamat"));
        $("#edit-kota").val($(this).data("kota"));
        $("#edit-provinsi").val($(this).data("provinsi"));
        $("#edit-kontak").val($(this).data("kontak"));
        $("#edit-email").val($(this).data("email"));
        $("#edit-nama_apoteker").val($(this).data("nama_apoteker"));
        $("#edit-no_sip").val($(this).data("no_sip"));
    });
</script>
</body>
</html>
