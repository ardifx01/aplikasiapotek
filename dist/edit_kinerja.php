<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil ID dan validasi
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Ambil data kinerja
$stmt = $conn->prepare("SELECT * FROM kinerja_petugas WHERE id = ? AND user_input = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
  echo "<script>alert('Data tidak ditemukan'); window.location.href='input_kinerja.php';</script>";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Kinerja - SICONIC</title>
  <meta content="width=device-width, initial-scale=1" name="viewport">
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
          <div class="section-header">
            <h1>Edit Kinerja</h1>
          </div>

          <div class="section-body">
            <div class="card">
              <div class="card-header"><h4>Form Edit Kinerja</h4></div>
              <div class="card-body">
                <form action="proses_edit_kinerja.php" method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="id" value="<?= $data['id']; ?>">
                  <input type="hidden" name="user_id" value="<?= $user_id; ?>">
                  <input type="hidden" name="bukti_lama" value="<?= $data['bukti']; ?>">

                  <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" class="form-control" name="tanggal" value="<?= $data['tanggal']; ?>" required>
                  </div>

                  <div class="form-group">
                    <label for="kegiatan">Deskripsi Kegiatan</label>
                    <textarea class="form-control" name="kegiatan" rows="4" required><?= htmlspecialchars($data['kegiatan']); ?></textarea>
                  </div>

                  <div class="form-group">
                    <label for="progres">Progress / Status</label>
                    <select class="form-control" name="progres" required>
                      <option value="">-- Pilih Progress --</option>
                      <option value="Belum Dimulai" <?= $data['progres'] == 'Belum Dimulai' ? 'selected' : ''; ?>>Belum Dimulai</option>
                      <option value="Dalam Proses" <?= $data['progres'] == 'Dalam Proses' ? 'selected' : ''; ?>>Dalam Proses</option>
                      <option value="Selesai" <?= $data['progres'] == 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                      <option value="Ditunda" <?= $data['progres'] == 'Ditunda' ? 'selected' : ''; ?>>Ditunda</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label for="catatan">Catatan Tambahan</label>
                    <textarea class="form-control" name="catatan" rows="3"><?= htmlspecialchars($data['catatan']); ?></textarea>
                  </div>

                  <div class="form-group">
                    <label for="bukti">Upload Bukti Baru (Opsional)</label>
                    <input type="file" class="form-control-file" name="bukti" accept=".jpg,.jpeg,.png,.pdf,.docx,.xlsx">
                    <?php if ($data['bukti']): ?>
                      <small class="form-text text-muted">Bukti saat ini: <a href="uploads/<?= $data['bukti']; ?>" target="_blank"><?= $data['bukti']; ?></a></small>
                    <?php endif; ?>
                  </div>

                  <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Perubahan</button>
                  <a href="input_kinerja.php" class="btn btn-secondary">Batal</a>
                </form>
              </div>
            </div>
          </div>
        </section>
      </div>

      <?php include 'footer.php'; ?>
    </div>
  </div>

  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
