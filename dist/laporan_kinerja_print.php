<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$cari = isset($_GET['cari']) ? $_GET['cari'] : '';

$query = "
  SELECT k.*, u.nama,
    (SELECT AVG(rating) FROM feedback_kinerja f WHERE f.kinerja_id = k.id) AS avg_rating,
    (SELECT komentar FROM feedback_kinerja f WHERE f.kinerja_id = k.id ORDER BY tanggal_feedback DESC LIMIT 1) AS last_comment
  FROM kinerja_petugas k
  LEFT JOIN users u ON k.user_input = u.id
";

if (!empty($cari)) {
  $safe_cari = $conn->real_escape_string($cari);
  $query .= " WHERE u.nama LIKE '%$safe_cari%'";
}

$query .= " ORDER BY k.tanggal DESC, u.nama ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Laporan Kinerja</title>
  <style>
    body { font-family: Arial, sans-serif; font-size: 13px; color: #000; }
    h2, h4 { text-align: center; margin: 0; }
    .header { margin-bottom: 10px; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    table, th, td { border: 1px solid black; }
    th, td { padding: 6px; text-align: left; vertical-align: top; }
    th { background-color: #f0f0f0; text-align: center; }
    .text-center { text-align: center; }
    .text-muted { color: #777; }
    @media print {
      .no-print { display: none; }
    }
  </style>
</head>
<body onload="window.print()">
  <div class="header">
    <h2>Laporan Kinerja</h2>
    <?php if ($cari): ?>
      <h4>Filter: <?= htmlspecialchars($cari) ?></h4>
    <?php endif; ?>
  </div>

  <table>
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Pengguna</th>
        <th>Tanggal</th>
        <th>Kegiatan</th>
        <th>Progress</th>
        <th>Catatan</th>
        <th>Rating</th>
        <th>Feedback</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php $no = 1; ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
            $avg = round($row['avg_rating'], 1);
            if ($avg > 0) {
              if ($avg >= 4.5) $ket = "Sangat Baik";
              elseif ($avg >= 3.5) $ket = "Baik";
              elseif ($avg >= 2.5) $ket = "Cukup";
              elseif ($avg >= 1.5) $ket = "Kurang";
              else $ket = "Sangat Kurang";
              $rating_display = "$avg / 5 ($ket)";
            } else {
              $rating_display = "-";
            }
          ?>
          <tr>
            <td class="text-center"><?= $no++; ?></td>
            <td><?= htmlspecialchars($row['nama']); ?></td>
            <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal'])); ?></td>
            <td><?= htmlspecialchars($row['kegiatan']); ?></td>
            <td class="text-center"><?= htmlspecialchars($row['progres']); ?></td>
            <td><?= htmlspecialchars($row['catatan']); ?></td>
            <td class="text-center"><?= $rating_display ?></td>
            <td><?= htmlspecialchars($row['last_comment'] ?? '-'); ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr>
          <td colspan="8" class="text-center text-muted">Tidak ada data yang dapat ditampilkan.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

  <br><br>
  <div style="width: 100%; text-align: right;">
    <p>Dicetak oleh: <strong><?= htmlspecialchars($_SESSION['nama']) ?? 'Admin' ?></strong></p>
    <p><?= date('d-m-Y H:i') ?></p>
  </div>
</body>
</html>
