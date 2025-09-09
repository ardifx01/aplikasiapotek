<?php
require_once 'dompdf/autoload.inc.php'; // Sesuaikan jika folder dompdf di sini
use Dompdf\Dompdf;

include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil filter tanggal
$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');

// Ambil profil apotek
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM profil_apotek LIMIT 1"));

// Ambil data penjualan
$sql = "SELECT pm.id, pm.kode_penjualan, pm.tanggal, pm.nama_petugas, pm.total, pm.uang_diterima, u.nama AS petugas
        FROM penjualan_master pm
        LEFT JOIN users u ON pm.user_id = u.id
        WHERE pm.tanggal BETWEEN '$tgl_dari 00:00:00' AND '$tgl_sampai 23:59:59'
        ORDER BY pm.tanggal ASC";
$result = mysqli_query($conn, $sql);

// Mulai konten HTML
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Penjualan</title>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
.table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.table th, .table td { border: 1px solid #000; padding: 5px; text-align: center; }
.table th { background: #f0f0f0; }
.header { text-align: center; }
.header img { max-height: 80px; margin-bottom: 5px; }
.text-left { text-align: left; }
.text-right { text-align: right; }
</style>
</head>
<body>
<div class="header">';
if($profil['logo']) $html .= '<img src="'.$profil['logo'].'" alt="Logo">';
$html .= '
<h2>'.htmlspecialchars($profil['nama']).'</h2>
<p>'.htmlspecialchars($profil['alamat']).', '.htmlspecialchars($profil['kota']).', '.htmlspecialchars($profil['provinsi']).'</p>
<p>Kontak: '.htmlspecialchars($profil['kontak']).' | Email: '.htmlspecialchars($profil['email']).'</p>
<hr>
<h3>Laporan Penjualan</h3>
<p>Periode: '.date("d-m-Y", strtotime($tgl_dari)).' s/d '.date("d-m-Y", strtotime($tgl_sampai)).'</p>
</div>

<table class="table">
<thead>
<tr>
<th>No</th>
<th>Kode Penjualan</th>
<th>Tanggal</th>
<th>Petugas</th>
<th>Total</th>
<th>Uang Diterima</th>
<th>Kembalian</th>
</tr>
</thead>
<tbody>';

$no = 1;
$grand_total = 0;
while($row = mysqli_fetch_assoc($result)){
    $kembalian = $row['uang_diterima'] - $row['total'];
    $grand_total += $row['total'];
    $html .= '<tr>
        <td>'.$no.'</td>
        <td>'.htmlspecialchars($row['kode_penjualan']).'</td>
        <td>'.date("d-m-Y H:i", strtotime($row['tanggal'])).'</td>
        <td>'.htmlspecialchars($row['nama_petugas'] ?: $row['petugas']).'</td>
        <td class="text-right">Rp '.number_format($row['total'],0,',','.').'</td>
        <td class="text-right">Rp '.number_format($row['uang_diterima'],0,',','.').'</td>
        <td class="text-right">Rp '.number_format($kembalian,0,',','.').'</td>
    </tr>';
    $no++;
}

$html .= '<tr>
    <td colspan="4" class="text-right"><strong>Grand Total</strong></td>
    <td class="text-right"><strong>Rp '.number_format($grand_total,0,',','.').'</strong></td>
    <td colspan="2"></td>
</tr>';

$html .= '</tbody></table>
</body>
</html>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_penjualan_".date("Ymd_His").".pdf", ["Attachment" => false]);

