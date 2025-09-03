<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Simpan perubahan setting email
if (isset($_POST['simpan'])) {
  $mail_host        = $_POST['mail_host'];
  $mail_port        = $_POST['mail_port'];
  $mail_username    = $_POST['mail_username'];
  $mail_password    = $_POST['mail_password'];
  $mail_from_email  = $_POST['mail_from_email'];
  $mail_from_name   = $_POST['mail_from_name'];
  $base_url         = $_POST['base_url'];

  mysqli_query($conn, "DELETE FROM mail_settings");

  $query = "INSERT INTO mail_settings 
              (mail_host, mail_port, mail_username, mail_password, mail_from_email, mail_from_name, base_url) 
            VALUES 
              ('$mail_host', '$mail_port', '$mail_username', '$mail_password', '$mail_from_email', '$mail_from_name', '$base_url')";
  $simpan = mysqli_query($conn, $query);

  if ($simpan) {
    echo "<script>alert('Setting email berhasil disimpan'); window.location='mail_setting.php';</script>";
    exit;
  } else {
    echo "<script>alert('Gagal menyimpan setting email');</script>";
  }
}

$data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM mail_settings LIMIT 1"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport" />
  <title>Mail Setting &mdash; SICONIC</title>

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
            <h1>Pengaturan Mail SMTP</h1>
          </div>

          <div class="section-body">
            <div class="card">
              <div class="card-header">
                <h4>Form Setting Email</h4>
              </div>
              <div class="card-body">
                <form method="POST">
                  <div class="form-group">
                    <label>Mail Host</label>
                    <input type="text" name="mail_host" class="form-control" value="<?= htmlspecialchars($data['mail_host'] ?? '') ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Mail Port</label>
                    <input type="number" name="mail_port" class="form-control" value="<?= htmlspecialchars($data['mail_port'] ?? '') ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Mail Username</label>
                    <input type="text" name="mail_username" class="form-control" value="<?= htmlspecialchars($data['mail_username'] ?? '') ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Mail Password</label>
                    <input type="password" name="mail_password" class="form-control" value="<?= htmlspecialchars($data['mail_password'] ?? '') ?>" required>
                  </div>
                  <div class="form-group">
                    <label>From Email</label>
                    <input type="email" name="mail_from_email" class="form-control" value="<?= htmlspecialchars($data['mail_from_email'] ?? '') ?>" required>
                  </div>
                  <div class="form-group">
                    <label>From Name</label>
                    <input type="text" name="mail_from_name" class="form-control" value="<?= htmlspecialchars($data['mail_from_name'] ?? '') ?>" required>
                  </div>
                  <div class="form-group">
                    <label>Base URL</label>
                    <input type="text" name="base_url" class="form-control" value="<?= htmlspecialchars($data['base_url'] ?? '') ?>" required>
                  </div>
                  <button type="submit" name="simpan" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                  </button>
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
