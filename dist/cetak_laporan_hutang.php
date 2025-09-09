<?php
require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;

include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil filter tanggal
$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-01');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');

// Ambil profil apotek
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM profil_apotek LIMIT 1"));

// Ambil semua hutang "Tempo" dalam periode
$sql = "SELECT sm.id, sm.tanggal_masuk, sm.jatuh_tempo, sm.total, s.nama AS nama_supplier,
        CASE 
            WHEN sm.jatuh_tempo < CURDATE() THEN 'Sudah Jatuh Tempo'
            ELSE 'Belum Jatuh Tempo'
        END AS status_hutang
        FROM stok_masuk sm
        LEFT JOIN supplier s ON sm.id_supplier = s.id
        WHERE sm.cara_bayar = 'Tempo'
        ORDER BY sm.jatuh_tempo ASC";
$result = mysqli_query($conn, $sql);

// Mulai konten HTML
$html = '
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Hutang Lengkap</title>
<style>
body { font-family: Arial, sans-serif; font-size: 12px; }
.table { width: 100%; border-collapse: collapse; margin-top: 10px; }
.table th, .table td { border: 1px solid #000; padding: 5px; text-align: center; }
.table th { background: #f0f0f0; }
.header { text-align: center; }
.header img { max-height: 80px; margin-bottom: 5px; }
.text-left { text-align: left; }
.text-right { text-align: right; }
.status-jatuh-tempo { font-weight: bold; }
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
<h3>Laporan Hutang Lengkap</h3>
<p>Periode: '.date("d-m-Y", strtotime($tgl_dari)).' s/d '.date("d-m-Y", strtotime($tgl_sampai)).'</p>
</div>

<table class="table">
<thead>
<tr>
<th>No</th>
<th>Supplier</th>
<th>Tanggal Masuk</th>
<th>Jatuh Tempo</th>
<th>Status</th>
<th>Total Hutang</th>
</tr>
</thead>
<tbody>';

$no = 1;
$grand_total = 0;
$total_sudah_jatuh_tempo = 0;
$total_belum_jatuh_tempo = 0;

if(mysqli_num_rows($result) > 0){
    while($row = mysqli_fetch_assoc($result)){
        $grand_total += $row['total'];
        if($row['status_hutang'] == 'Sudah Jatuh Tempo'){
            $total_sudah_jatuh_tempo += $row['total'];
        } else {
            $total_belum_jatuh_tempo += $row['total'];
        }

        $html .= '<tr>
            <td>'.$no.'</td>
            <td>'.htmlspecialchars($row['nama_supplier'] ?? '-').'</td>
            <td>'.date("d-m-Y", strtotime($row['tanggal_masuk'])).'</td>
            <td>'.date("d-m-Y", strtotime($row['jatuh_tempo'])).'</td>
            <td class="status-jatuh-tempo">'.htmlspecialchars($row['status_hutang']).'</td>
            <td class="text-right">Rp '.number_format($row['total'],0,',','.').'</td>
        </tr>';
        $no++;
    }

    // Total per kategori
    $html .= '<tr>
        <td colspan="5" class="text-right"><strong>Total Belum Jatuh Tempo</strong></td>
        <td class="text-right"><strong>Rp '.number_format($total_belum_jatuh_tempo,0,',','.').'</strong></td>
    </tr>';
    $html .= '<tr>
        <td colspan="5" class="text-right"><strong>Total Sudah Jatuh Tempo</strong></td>
        <td class="text-right"><strong>Rp '.number_format($total_sudah_jatuh_tempo,0,',','.').'</strong></td>
    </tr>';
    $html .= '<tr>
        <td colspan="5" class="text-right"><strong>Grand Total</strong></td>
        <td class="text-right"><strong>Rp '.number_format($grand_total,0,',','.').'</strong></td>
    </tr>';

}else{
    $html .= '<tr><td colspan="6" style="text-align:center;">Tidak ada data hutang</td></tr>';
}

$html .= '</tbody></table>
</body>
</html>';

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("laporan_hutang_lengkap_".date("Ymd_His").".pdf", ["Attachment" => false]);
exit;
?>
