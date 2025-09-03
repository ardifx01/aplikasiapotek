<?php
include 'koneksi.php';

$id = intval($_GET['id']);
$master = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM penjualan_master WHERE id=$id"));
$detail = mysqli_query($conn, "SELECT * FROM penjualan_detail WHERE penjualan_id=$id");

?>
<!DOCTYPE html>
<html>
<head>
<title>Struk Penjualan</title>
<style>
body{font-family: monospace; font-size:14px;}
table{width:100%; border-collapse: collapse;}
td, th{padding:5px;}
</style>
</head>
<body onload="window.print()">
<h3>Apotek-KU</h3>
<p>Tanggal: <?= date('d-m-Y H:i', strtotime($master['tanggal'])) ?></p>
<table border="1">
<tr>
<th>Nama Obat</th><th>Jumlah</th><th>Harga</th><th>Total</th>
</tr>
<?php 
$total = 0;
while($row = mysqli_fetch_assoc($detail)){
    echo "<tr>
        <td>{$row['nama_obat']}</td>
        <td>{$row['jumlah']}</td>
        <td>Rp ".number_format($row['harga_satuan'],0,',','.')."</td>
        <td>Rp ".number_format($row['total'],0,',','.')."</td>
    </tr>";
    $total += $row['total'];
}
?>
<tr>
<td colspan="3"><b>Total</b></td>
<td><b>Rp <?= number_format($total,0,',','.') ?></b></td>
</tr>
</table>
<p>Terima Kasih</p>
</body>
</html>
