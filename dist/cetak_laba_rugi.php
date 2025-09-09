<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';
require 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

date_default_timezone_set('Asia/Jakarta');

$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');

// Ambil data sama seperti halaman laporan
$total_penjualan = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(total) AS total_penjualan
    FROM penjualan_master
    WHERE tanggal BETWEEN '$tgl_dari 00:00:00' AND '$tgl_sampai 23:59:59'
"))['total_penjualan'] ?? 0;

$total_hpp = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(pd.jumlah * o.harga_beli) AS total_hpp
    FROM penjualan_detail pd
    LEFT JOIN obat o ON pd.kode_obat = o.kode_obat
    LEFT JOIN penjualan_master pm ON pd.penjualan_id = pm.id
    WHERE pm.tanggal BETWEEN '$tgl_dari 00:00:00' AND '$tgl_sampai 23:59:59'
"))['total_hpp'] ?? 0;

$laba_kotor = $total_penjualan - $total_hpp;

$total_operasional = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT SUM(jumlah) AS total_operasional
    FROM biaya_operasional
    WHERE tanggal BETWEEN '$tgl_dari' AND '$tgl_sampai'
"))['total_operasional'] ?? 0;

$laba_bersih = $laba_kotor - $total_operasional;

// Profil apotek
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM profil_apotek LIMIT 1"));

// HTML untuk PDF
$html = '
<html>
<head>
<meta charset="UTF-8">
<title>Laporan Laba Rugi</title>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
.table { width: 100%; border-collapse: collapse; margin-top: 20px; }
.table th, .table td { border: 1px solid #333; padding: 5px; }
.table th { background: #f2f2f2; }
.text-left { text-align: left; }
.text-right { text-align: right; }
.total-row { font-weight: bold; background: #e2f0d9; }
</style>
</head>
<body>
<div style="text-align:center;">
<h2>'.htmlspecialchars($profil['nama']).'</h2>
<p>'.htmlspecialchars($profil['alamat']).', '.htmlspecialchars($profil['kota']).', '.htmlspecialchars($profil['provinsi']).'</p>
<p>Kontak: '.htmlspecialchars($profil['kontak']).' | Email: '.htmlspecialchars($profil['email']).'</p>
<hr>
<h3>Laporan Laba Rugi</h3>
<p>Periode: '.date("d-m-Y", strtotime($tgl_dari)).' s/d '.date("d-m-Y", strtotime($tgl_sampai)).'</p>
</div>

<table class="table">
<tr><td class="text-left">Total Penjualan</td><td class="text-right">Rp '.number_format($total_penjualan,0,',','.').'</td></tr>
<tr><td class="text-left">Harga Pokok Penjualan (HPP)</td><td class="text-right">Rp '.number_format($total_hpp,0,',','.').'</td></tr>
<tr class="total-row"><td class="text-left">Laba Kotor</td><td class="text-right">Rp '.number_format($laba_kotor,0,',','.').'</td></tr>
<tr><td class="text-left">Biaya Operasional</td><td class="text-right">Rp '.number_format($total_operasional,0,',','.').'</td></tr>
<tr class="total-row"><td class="text-left">Laba Bersih</td><td class="text-right">Rp '.number_format($laba_bersih,0,',','.').'</td></tr>
</table>
</body>
</html>
';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_laba_rugi_".date("Ymd_His").".pdf", ["Attachment" => false]);
exit;
