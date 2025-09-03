<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Daftar Kuis</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
            <h1>Daftar Judul Kuis</h1>
          </div>

          <div class="section-body">
            <div class="card">
              <div class="card-header">
                <h4>Judul Kuis dan Jumlah Soal</h4>
              </div>
              <div class="card-body table-responsive">
                <table class="table table-bordered">
                  <thead class="thead-light">
                    <tr>
                      <th>No</th>
                      <th>Judul Kuis</th>
                      <th>Jumlah Soal</th>
                      <th>Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $sql = "SELECT jk.id, jk.judul, COUNT(k.id) AS jumlah_soal 
                            FROM judul_kuis jk
                            LEFT JOIN kuis k ON jk.id = k.judul_id
                            GROUP BY jk.id, jk.judul
                            ORDER BY jk.created_at DESC";
                    $result = $conn->query($sql);
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                      echo "<tr>
                              <td>{$no}</td>
                              <td>{$row['judul']}</td>
                              <td>{$row['jumlah_soal']}</td>
                              <td>
                                <a href='rincian_kuis.php?judul_id={$row['id']}' class='btn btn-info btn-sm'>
                                  <i class='fas fa-eye'></i> Lihat Rincian Kuis
                                </a>
                              </td>
                            </tr>";
                      $no++;
                    }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>

  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/js/scripts.js"></script>
</body>
</html>
