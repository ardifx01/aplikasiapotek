<?php
include 'koneksi.php';
require_once 'dompdf/autoload.inc.php'; // path dompdf

use Dompdf\Dompdf;

$id = $_GET['id'] ?? 0;
$id = (int)$id;

if(!$id){ echo "ID transaksi tidak valid!"; exit; }

// --- Ambil data master transaksi ---
$master = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM penjualan_master WHERE id=$id"));
if(!$master){ echo "Transaksi tidak ditemukan!"; exit; }

// --- Ambil detail transaksi ---
$details = mysqli_query($conn, "SELECT * FROM penjualan_detail WHERE penjualan_id={$master['id']}");

// --- Ambil data profil apotek ---
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM profil_apotek LIMIT 1"));

// --- Mulai HTML nota ---
$grand_total = 0;
$html = '
<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
.header { text-align: center; margin-bottom: 10px; }
.header img { max-width: 100px; margin-bottom: 5px; }
.header h2 { margin: 0; font-size: 18px; }
.header p { margin: 2px 0; font-size: 10px; }
.table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.table th, .table td { border: 1px solid #000; padding: 5px; text-align: left; }
.table th { background-color: #f2f2f2; }
.total { text-align: right; margin-top: 10px; }
.footer { text-align: center; margin-top: 15px; font-size: 10px; }
</style>
<div class="header">';
if(!empty($profil['logo']) && file_exists($profil['logo'])){
    $html .= '<img src="'.$profil['logo'].'" alt="Logo">';
}
$html .= '<h2>'.$profil['nama'].'</h2>
<p>'.$profil['alamat'].', '.$profil['kota'].' - '.$profil['provinsi'].'</p>
<p>Kontak: '.$profil['kontak'].' | Email: '.$profil['email'].'</p>
<hr>
<h4>Nota Penjualan Obat</h4>
<p>No: '.$master['kode_penjualan'].'</p>
<p>Tanggal: '.date('d-m-Y H:i', strtotime($master['tanggal'])).'</p>
<p>Petugas: '.$master['nama_petugas'].'</p>
</div>

<table class="table">
<thead>
<tr>
<th>No</th>
<th>Nama Obat</th>
<th>Jumlah</th>
<th>Harga</th>
<th>Total</th>
</tr>
</thead>
<tbody>';
$no = 1;
while($row = mysqli_fetch_assoc($details)){
    $total = $row['total'];
    $grand_total += $total;
    $html .= '<tr>
        <td>'.$no.'</td>
        <td>'.$row['nama_obat'].'</td>
        <td>'.$row['jumlah'].'</td>
        <td>Rp '.number_format($row['harga_satuan'],0,',','.').'</td>
        <td>Rp '.number_format($total,0,',','.').'</td>
    </tr>';
    $no++;
}
$html .= '</tbody></table>';

$html .= '
<div class="total">
<p>Total: Rp '.number_format($grand_total,0,',','.').'</p>
<p>Uang Diterima: Rp '.number_format($master['uang_diterima'],0,',','.').'</p>
<p>Kembalian: Rp '.number_format($master['uang_diterima']-$grand_total,0,',','.').'</p>
</div>

<div class="footer">
<p>Apoteker: '.$profil['nama_apoteker'].' | No. SIP: '.$profil['no_sip'].'</p>
<p>Terima Kasih atas kunjungan Anda!</p>
</div>';

// --- Generate PDF ---
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A5', 'portrait'); // bisa juga 'A4'
$dompdf->render();

// Simpan PDF ke folder nota_pdf
$folder = __DIR__ . '/nota_pdf';
if(!is_dir($folder)) mkdir($folder, 0777, true);
$filename = $folder.'/Nota_'.$master['kode_penjualan'].'.pdf';
file_put_contents($filename, $dompdf->output());

// Tampilkan PDF di browser
$dompdf->stream('Nota_'.$master['kode_penjualan'].'.pdf', ["Attachment" => false]);
