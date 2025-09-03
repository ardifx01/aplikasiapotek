<?php
session_start();
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id'])) {
  echo "Akses ditolak!";
  exit;
}

// Ambil nama admin dari session
$admin_id = $_SESSION['user_id'];
$admin = $conn->query("SELECT nama FROM users WHERE id = $admin_id")->fetch_assoc();
$nama_admin = $admin ? $admin['nama'] : 'Admin';

// Ambil filter judul kuis jika ada
$filter_judul_id = isset($_GET['judul_id']) ? intval($_GET['judul_id']) : null;

// Query data hasil kuis
$query = "SELECT h.id, u.nama, jk.judul, h.nilai, h.waktu_submit
          FROM hasil_kuis h
          JOIN users u ON h.user_id = u.id
          JOIN judul_kuis jk ON h.judul_id = jk.id";

if ($filter_judul_id) {
  $query .= " WHERE h.judul_id = $filter_judul_id";
}

$query .= " ORDER BY h.waktu_submit DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Cetak Rekap Nilai</title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 14px; }
    h2, p { text-align: center; margin: 0; padding: 0; }
    table { border-collapse: collapse; width: 100%; margin-top: 20px; }
    th, td { border: 1px solid black; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .footer {
      margin-top: 30px;
      text-align: right;
      font-size: 13px;
    }
  </style>
</head>
<body onload="window.print()">
  <h2>REKAP NILAI PESERTA</h2>

  <?php if ($filter_judul_id): ?>
    <?php
      $judul = $conn->query("SELECT judul FROM judul_kuis WHERE id = $filter_judul_id")->fetch_assoc();
    ?>
    <p><strong>Kuis:</strong> <?= htmlspecialchars($judul['judul']) ?></p>
  <?php endif; ?>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Peserta</th>
        <th>Judul Kuis</th>
        <th>Nilai</th>
        <th>Waktu Submit</th>
      </tr>
    </thead>
    <tbody>
      <?php $no = 1; while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama']) ?></td>
          <td><?= htmlspecialchars($row['judul']) ?></td>
          <td><?= intval($row['nilai']) ?></td>
          <td><?= date('d-m-Y H:i', strtotime($row['waktu_submit'])) ?></td>
        </tr>
      <?php endwhile; ?>
      <?php if ($result->num_rows === 0): ?>
        <tr><td colspan="5" style="text-align:center;">Data tidak ditemukan.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>

  <div class="footer">
    <p>Dicetak oleh: <strong><?= htmlspecialchars($nama_admin) ?></strong></p>
    <p>Tanggal Cetak: <?= date('d-m-Y H:i') ?></p>
  </div>
</body>
</html>
