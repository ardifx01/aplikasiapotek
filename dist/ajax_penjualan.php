<?php
session_start();
include 'security.php';
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$user_id = $_SESSION['user_id'] ?? 0;
if($user_id==0){ http_response_code(403); exit; }

$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$tgl_dari = $_GET['tgl_dari'] ?? date('Y-m-d');
$tgl_sampai = $_GET['tgl_sampai'] ?? date('Y-m-d');

$total_q = $conn->prepare("SELECT COUNT(*) as total FROM penjualan_detail d 
JOIN penjualan_master m ON d.penjualan_id = m.id 
WHERE DATE(m.tanggal) BETWEEN ? AND ?");
$total_q->bind_param("ss", $tgl_dari, $tgl_sampai);
$total_q->execute();
$total_q->bind_result($totalData);
$total_q->fetch();
$total_q->close();

$totalPage = ceil($totalData / $limit);

$data_q = $conn->prepare("SELECT d.*, m.tanggal, m.total as total_transaksi 
FROM penjualan_detail d 
JOIN penjualan_master m ON d.penjualan_id = m.id 
WHERE DATE(m.tanggal) BETWEEN ? AND ? 
ORDER BY m.tanggal DESC 
LIMIT ?, ?");
$data_q->bind_param("ssii", $tgl_dari, $tgl_sampai, $offset, $limit);
$data_q->execute();
$result = $data_q->get_result();

$rows = [];
while($r = $result->fetch_assoc()){
    $rows[] = $r;
}

echo json_encode([
    'data' => $rows,
    'totalPage' => $totalPage,
    'currentPage' => $page
]);
