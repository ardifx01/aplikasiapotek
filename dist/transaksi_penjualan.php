<?php
include 'security.php';
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

$user_id = $_SESSION['user_id'] ?? 0;
$activeTab = isset($_POST['tab']) ? $_POST['tab'] : 'input';

// --- SIMPAN TRANSAKSI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi']=='simpan') {
    $cart = json_decode($_POST['cart'], true); // decode JSON
    $uang_diterima = isset($_POST['uang_diterima']) ? (float)$_POST['uang_diterima'] : 0;

    if(!empty($cart)){
        // simpan master transaksi
        $conn->query("INSERT INTO penjualan_master (tanggal, total, user_id, uang_diterima) VALUES (NOW(), 0, $user_id, $uang_diterima)");
        $penjualan_id = $conn->insert_id;
        $total_transaksi = 0;

        $stmt = $conn->prepare("INSERT INTO penjualan_detail (penjualan_id, kode_obat, nama_obat, jumlah, harga_satuan, total) VALUES (?,?,?,?,?,?)");
        foreach($cart as $item){
            $kode  = $item['kode_obat'];
            $nama  = $item['nama_obat'];
            $jumlah= (int)$item['jumlah'];
            $harga = (float)$item['harga'];
            $total = $jumlah * $harga;
            $stmt->bind_param("issidd", $penjualan_id, $kode, $nama, $jumlah, $harga, $total);
            $stmt->execute();

            // update stok obat
            $conn->query("UPDATE obat SET stok = stok - $jumlah WHERE kode_obat='$kode'");
            $total_transaksi += $total;
        }
        $stmt->close();

        // update total di master
        $conn->query("UPDATE penjualan_master SET total = $total_transaksi WHERE id=$penjualan_id");

        // return id penjualan untuk cetak struk
        echo $penjualan_id;
        exit;
    }
}

// ambil daftar obat untuk dropdown
$obat_list = mysqli_query($conn, "SELECT * FROM obat ORDER BY nama_obat ASC");

// ambil data penjualan (gabungan master + detail)
$penjualan = mysqli_query($conn, "
    SELECT d.*, m.tanggal, m.total as total_transaksi
    FROM penjualan_detail d
    JOIN penjualan_master m ON d.penjualan_id = m.id
    ORDER BY m.tanggal DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Transaksi Penjualan &mdash; Apotek-KU</title>
<link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css" />
<link rel="stylesheet" href="assets/css/style.css" />
<link rel="stylesheet" href="assets/css/components.css" />
<style>
#notif-toast { position: fixed; top: 20px; right: 20px; z-index: 9999; display: none; min-width: 250px; }
.table thead th { background-color: #000 !important; color: #fff !important; }
</style>
</head>
<body>
<div id="app">
  <div class="main-wrapper main-wrapper-1">
    <?php include 'navbar.php'; ?>
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
      <section class="section">
        <div class="section-body">

        <?php if(isset($_SESSION['flash_message'])): ?>
            <div id="notif-toast" class="alert alert-info">
                <?= $_SESSION['flash_message'] ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
              <h4>Transaksi Penjualan Obat</h4>
            </div>
            <div class="card-body">
              <ul class="nav nav-tabs" id="penjualanTab" role="tablist">
                <li class="nav-item">
                  <a class="nav-link <?= $activeTab=='input'?'active':'' ?>" id="input-tab" data-toggle="tab" href="#input" role="tab">Input Penjualan</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link <?= $activeTab=='data'?'active':'' ?>" id="data-tab" data-toggle="tab" href="#data" role="tab">Data Penjualan</a>
                </li>
              </ul>

              <div class="tab-content mt-3">
                <!-- FORM INPUT PENJUALAN -->
                <div class="tab-pane fade <?= $activeTab=='input'?'show active':'' ?>" id="input" role="tabpanel">
                  <form id="form-penjualan">
                    <div class="form-row">
                      <div class="col-md-4">
                        <label>Obat</label>
                        <select id="kode_obat" class="form-control">
                          <option value="">-- Pilih Obat --</option>
                          <?php while($ob = mysqli_fetch_assoc($obat_list)): ?>
                            <option value="<?= $ob['kode_obat'] ?>" data-nama="<?= $ob['nama_obat'] ?>" data-harga="<?= $ob['harga_jual'] ?>" data-stok="<?= $ob['stok'] ?>">
                              <?= $ob['nama_obat'] ?> (Stok: <?= $ob['stok'] ?>)
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label>Jumlah</label>
                        <input type="number" id="jumlah" class="form-control" min="1" value="1">
                      </div>
                      <div class="col-md-3 align-self-end">
                        <button type="button" id="tambah_cart" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah</button>
                      </div>
                    </div>
                  </form>

                  <hr>
                  <h5>Keranjang Belanja</h5>
                  <table class="table table-bordered" id="tabel_cart">
                    <thead>
                      <tr>
                        <th>No</th>
                        <th>Kode Obat</th>
                        <th>Nama Obat</th>
                        <th>Harga Satuan</th>
                        <th>Jumlah</th>
                        <th>Total</th>
                        <th>Aksi</th>
                      </tr>
                    </thead>
                    <tbody></tbody>
                  </table>

                  <!-- KALKULATOR PEMBAYARAN -->
                  <div class="row mt-3">
                    <div class="col-md-4">
                      <h5>Total: Rp <span id="total_belanja">0</span></h5>
                    </div>
                    <div class="col-md-4">
                      <label>Uang Diterima</label>
                      <input type="number" id="uang_diterima" class="form-control" value="0">
                    </div>
                    <div class="col-md-4">
                      <h5>Kembalian: Rp <span id="kembalian">0</span></h5>
                    </div>
                  </div>

                  <button id="simpan_cetak" class="btn btn-success mt-2"><i class="fas fa-print"></i> Simpan & Cetak Struk</button>

                </div>

                <!-- DATA PENJUALAN -->
                <div class="tab-pane fade <?= $activeTab=='data'?'show active':'' ?>" id="data" role="tabpanel">
                  <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped table-nowrap">
                      <thead>
                        <tr>
                          <th>No</th>
                          <th>Tanggal</th>
                          <th>Kode Obat</th>
                          <th>Nama Obat</th>
                          <th>Jumlah</th>
                          <th>Harga Satuan</th>
                          <th>Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $no=1; while($row=mysqli_fetch_assoc($penjualan)): ?>
                        <tr>
                          <td><?= $no++ ?></td>
                          <td><?= date('d-m-Y H:i', strtotime($row['tanggal'])) ?></td>
                          <td><?= $row['kode_obat'] ?></td>
                          <td><?= $row['nama_obat'] ?></td>
                          <td><?= $row['jumlah'] ?></td>
                          <td>Rp <?= number_format($row['harga_satuan'],0,',','.') ?></td>
                          <td>Rp <?= number_format($row['total'],0,',','.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
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
<script>
let cart = [];

// Render keranjang
function renderCart(){
    let tbody = $('#tabel_cart tbody');
    tbody.empty();
    let totalBelanja = 0;

    cart.forEach((item,i)=>{
        totalBelanja += item.total;
        tbody.append(`<tr>
            <td>${i+1}</td>
            <td>${item.kode_obat}</td>
            <td>${item.nama_obat}</td>
            <td>Rp ${item.harga.toLocaleString('id-ID')}</td>
            <td>${item.jumlah}</td>
            <td>Rp ${item.total.toLocaleString('id-ID')}</td>
            <td><button class="btn btn-sm btn-danger" onclick="hapusCart(${i})">Hapus</button></td>
        </tr>`);
    });

    // simpan total belanja sebagai angka mentah
    $('#total_belanja').text(totalBelanja.toFixed(0));
    hitungKembalian();
}

// Hapus item dari keranjang
function hapusCart(index){
    cart.splice(index,1);
    renderCart();
}

// Tambah obat ke keranjang
$('#tambah_cart').click(function(){
    let kode = $('#kode_obat').val();
    let nama = $('#kode_obat option:selected').data('nama');
    let harga = parseFloat($('#kode_obat option:selected').data('harga'));
    let stok = parseInt($('#kode_obat option:selected').data('stok'));
    let jumlah = parseInt($('#jumlah').val());

    if(!kode){ alert('Pilih obat!'); return; }
    if(jumlah <=0){ alert('Jumlah minimal 1'); return; }
    if(jumlah > stok){ alert('Jumlah melebihi stok!'); return; }

    let total = harga * jumlah;
    cart.push({kode_obat:kode, nama_obat:nama, harga, jumlah, total});
    renderCart();
});

// Hitung kembalian
$('#uang_diterima').on('input', hitungKembalian);
function hitungKembalian(){
    let total = parseFloat($('#total_belanja').text()) || 0; // angka mentah
    let bayar = parseFloat($('#uang_diterima').val()) || 0;
    let kembali = bayar - total;
    if(kembali < 0) kembali = 0;
    $('#kembalian').text(kembali.toLocaleString('id-ID'));
}

// Simpan transaksi & cetak struk
$('#simpan_cetak').click(function(){
    if(cart.length==0){ alert('Keranjang kosong!'); return; }
    let uangDiterima = parseFloat($('#uang_diterima').val()) || 0;

    $.ajax({
        url: 'transaksi_penjualan.php',
        type: 'POST',
        data: { aksi: 'simpan', cart: JSON.stringify(cart), uang_diterima: uangDiterima },
        success: function(penjualanId){
            // buka struk di window baru
            window.open('cetak_struk.php?id='+penjualanId, '_blank');
            location.reload();
        },
        error: function(){
            alert('Terjadi kesalahan, coba lagi!');
        }
    });
});
</script>

</body>
</html>
