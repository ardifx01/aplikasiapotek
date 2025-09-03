<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport" />
  <title>Input Kinerja &mdash; SICONIC</title>

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
        <div class="section-header d-flex justify-content-between align-items-center">
          <h1>Daftar Kinerja Harian</h1>
          <button class="btn btn-primary" data-toggle="modal" data-target="#modalInputKinerja">
            <i class="fas fa-plus"></i> Tambah Kinerja
          </button>
        </div>

        <div class="section-body">
          <div class="card">
            <div class="card-body">
              <?php
              $stmt = $conn->prepare("SELECT * FROM kinerja_petugas WHERE user_input = ? ORDER BY tanggal DESC");
              $stmt->bind_param("i", $_SESSION['user_id']);
              $stmt->execute();
              $data = $stmt->get_result();
              ?>
              <div class="table-responsive">
                <table class="table table-bordered table-sm" id="tableKinerja">
                  <thead class="thead-dark">
                    <tr>
                       <th class="text-center">No</th>
                      <th>Tanggal</th>
                      <th>Kegiatan</th>
                      <th>Progress</th>
                      <th>Catatan</th>
                      <th>Bukti</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php $no = 1; while ($row = $data->fetch_assoc()): ?>
                      <tr 
                        data-id="<?= $row['id']; ?>"
                        data-tanggal="<?= $row['tanggal']; ?>"
                        data-kegiatan="<?= htmlspecialchars($row['kegiatan'], ENT_QUOTES); ?>"
                        data-progres="<?= $row['progres']; ?>"
                        data-catatan="<?= htmlspecialchars($row['catatan'], ENT_QUOTES); ?>"
                        data-bukti="<?= $row['bukti']; ?>"
                      >
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                        <td><?= htmlspecialchars($row['kegiatan']); ?></td>
                        <td><span class="badge badge-info"><?= $row['progres']; ?></span></td>
                        <td><?= htmlspecialchars($row['catatan']); ?></td>
                        <td>
                          <?php if (!empty($row['bukti'])): ?>
                            <a href="uploads/<?= $row['bukti']; ?>" target="_blank" class="btn btn-sm btn-primary mb-1">
                              <i class="fas fa-file"></i>
                            </a>
                          <?php else: ?>
                            <span class="text-muted d-block mb-1">-</span>
                          <?php endif; ?>
                        </td>
                        <td>
                          <button class="btn btn-sm btn-warning btn-edit-kinerja" data-toggle="modal" data-target="#modalEditKinerja">
                            <i class="fas fa-edit"></i> Edit
                          </button>
                        </td>
                      </tr>
                    <?php endwhile; ?>
                    <?php if ($data->num_rows === 0): ?>
                      <tr>
                        <td colspan="7" class="text-center text-muted">Belum ada kinerja tercatat.</td>
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

    <!-- Modal Input Kinerja -->
    <div class="modal fade" id="modalInputKinerja" tabindex="-1" role="dialog" aria-labelledby="modalInputKinerjaLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
          <form action="proses_input_kinerja.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>">
            <div class="modal-header">
              <h5 class="modal-title">Input Kinerja Harian</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="tanggal">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= date('Y-m-d') ?>" required>
              </div>

              <div class="form-group">
                <label for="kegiatan">Deskripsi Kegiatan</label>
                <textarea class="form-control" id="kegiatan" name="kegiatan" rows="4" required></textarea>
              </div>

              <div class="form-group">
                <label for="progres">Progress / Status</label>
                <select class="form-control" id="progres" name="progres" required>
                  <option value="">-- Pilih Progress --</option>
                  <option value="Belum Dimulai">Belum Dimulai</option>
                  <option value="Dalam Proses">Dalam Proses</option>
                  <option value="Selesai">Selesai</option>
                  <option value="Ditunda">Ditunda</option>
                </select>
              </div>

              <div class="form-group">
                <label for="catatan">Catatan Tambahan (Opsional)</label>
                <textarea class="form-control" id="catatan" name="catatan" rows="3"></textarea>
              </div>

              <div class="form-group">
                <label for="bukti">Upload Bukti Kegiatan (Opsional)</label>
                <input type="file" class="form-control-file" id="bukti" name="bukti" accept=".jpg,.jpeg,.png,.pdf,.docx,.xlsx">
                <small class="form-text text-muted">File gambar, PDF, DOCX atau Excel. Maksimal 5MB.</small>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Edit Kinerja -->
    <div class="modal fade" id="modalEditKinerja" tabindex="-1" role="dialog" aria-labelledby="modalEditKinerjaLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
          <form id="formEditKinerja" action="proses_edit_kinerja.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_id" />
            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>">
            <div class="modal-header">
              <h5 class="modal-title">Edit Kinerja Harian</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="form-group">
                <label for="edit_tanggal">Tanggal</label>
                <input type="date" class="form-control" id="edit_tanggal" name="tanggal" required />
              </div>

              <div class="form-group">
                <label for="edit_kegiatan">Deskripsi Kegiatan</label>
                <textarea class="form-control" id="edit_kegiatan" name="kegiatan" rows="4" required></textarea>
              </div>

              <div class="form-group">
                <label for="edit_progres">Progress / Status</label>
                <select class="form-control" id="edit_progres" name="progres" required>
                  <option value="">-- Pilih Progress --</option>
                  <option value="Belum Dimulai">Belum Dimulai</option>
                  <option value="Dalam Proses">Dalam Proses</option>
                  <option value="Selesai">Selesai</option>
                  <option value="Ditunda">Ditunda</option>
                </select>
              </div>

              <div class="form-group">
                <label for="edit_catatan">Catatan Tambahan (Opsional)</label>
                <textarea class="form-control" id="edit_catatan" name="catatan" rows="3"></textarea>
              </div>

              <div class="form-group">
                <label>Bukti Kegiatan Saat Ini:</label>
                <div id="buktiLamaContainer" class="mb-2"></div>
                <label for="edit_bukti">Upload Bukti Kegiatan Baru (Opsional)</label>
                <input type="file" class="form-control-file" id="edit_bukti" name="bukti" accept=".jpg,.jpeg,.png,.pdf,.docx,.xlsx" />
                <small class="form-text text-muted">File gambar, PDF, DOCX atau Excel. Maksimal 5MB.</small>
              </div>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
            </div>
          </form>
        </div>
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
$(document).ready(function(){
  $('.btn-edit-kinerja').on('click', function(){
    // Ambil data dari row
    var tr = $(this).closest('tr');
    var id = tr.data('id');
    var tanggal = tr.data('tanggal');
    var kegiatan = tr.data('kegiatan');
    var progres = tr.data('progres');
    var catatan = tr.data('catatan');
    var bukti = tr.data('bukti');

    // Isi form modal edit
    $('#edit_id').val(id);
    $('#edit_tanggal').val(tanggal);
    $('#edit_kegiatan').val(kegiatan);
    $('#edit_progres').val(progres);
    $('#edit_catatan').val(catatan);

    if(bukti){
      var fileLink = `<a href="uploads/${bukti}" target="_blank" class="btn btn-sm btn-primary"><i class="fas fa-file"></i> Lihat Bukti</a>`;
      $('#buktiLamaContainer').html(fileLink);
    } else {
      $('#buktiLamaContainer').html('<span class="text-muted">Tidak ada bukti</span>');
    }
  });
});
</script>

<?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Kinerja berhasil dicatat.',
      timer: 2000,
      showConfirmButton: false
    });
  </script>
<?php endif; ?>

</body>
</html>
