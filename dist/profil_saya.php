<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$user_id = $_SESSION['user_id'];
$notif = "";

// Ambil data pengguna
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $nama = trim($_POST['nama']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE users SET nama=?, email=?, password=? WHERE id=?");
    $update->bind_param("sssi", $nama, $email, $hashed_password, $user_id);
  } else {
    $update = $conn->prepare("UPDATE users SET nama=?, email=? WHERE id=?");
    $update->bind_param("ssi", $nama, $email, $user_id);
  }

  if ($update->execute()) {
    $notif = "Profil berhasil diperbarui.";
    // Refresh data
    $user['nama'] = $nama;
    $user['email'] = $email;
  } else {
    $notif = "Gagal memperbarui profil.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport" />
  <title>Profil Saya &mdash; SICONIC</title>

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
          <div class="section-header">
            <h1>Profil Saya</h1>
          </div>

          <div class="section-body">
            <div class="card">
              <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Edit Profil</h4>
              </div>
              <div class="card-body">
                <?php if (!empty($notif)): ?>
                  <div class="alert alert-info"><?= htmlspecialchars($notif); ?></div>
                <?php endif; ?>
                <form method="POST">
                  <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" class="form-control" value="<?= htmlspecialchars($user['nama']); ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="email">Email Aktif</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
                  </div>
                  <div class="form-group">
                    <label for="password">Password Baru (kosongkan jika tidak ingin mengganti)</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="********">
                  </div>
                  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </form>
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
