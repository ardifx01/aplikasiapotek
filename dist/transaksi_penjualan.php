<?php
session_start();  
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// DEBUG MySQL
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$user_id = $_SESSION['user_id'] ?? 0;
$nama_petugas = $_SESSION['nama_user'] ?? '';
if($user_id==0 || empty($nama_petugas)){
    http_response_code(403);
    echo "Session user tidak valid!";
    exit;
}

// --- SIMPAN TRANSAKSI ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'simpan') {
    $cart = json_decode($_POST['cart'] ?? '[]', true);
    if(!$cart || !is_array($cart)){
        http_response_code(400);
        echo "Keranjang kosong atau format tidak valid!";
        exit;
    }

    $uang_diterima = floatval(str_replace([',','Rp','-'], '', $_POST['uang_diterima'] ?? 0));

    $total_transaksi = 0;
    foreach($cart as $item){
        $total_transaksi += floatval($item['harga']) * intval($item['jumlah']);
    }

    if($uang_diterima < $total_transaksi){
        http_response_code(400);
        echo "Uang diterima tidak boleh kurang dari total belanja!";
        exit;
    }

    try {
        $conn->begin_transaction();

        $stmt_master = $conn->prepare("INSERT INTO penjualan_master (tanggal, total, user_id, nama_petugas, uang_diterima) VALUES (NOW(), ?, ?, ?, ?)");
        $stmt_master->bind_param("disd", $total_transaksi, $user_id, $nama_petugas, $uang_diterima);
        $stmt_master->execute();
        $penjualan_id = $stmt_master->insert_id;
        $stmt_master->close();

        $stmt_detail = $conn->prepare("INSERT INTO penjualan_detail (penjualan_id, kode_obat, nama_obat, jumlah, harga_satuan, total) VALUES (?,?,?,?,?,?)");
        $stmt_update_stok = $conn->prepare("UPDATE obat SET stok = stok - ? WHERE kode_obat = ?");

        foreach($cart as $item){
            $kode = $item['kode_obat'];
            $nama = $item['nama_obat'];
            $jumlah = intval($item['jumlah']);
            $harga = floatval($item['harga']);
            $total = $jumlah * $harga;

            $cek_stok = $conn->prepare("SELECT stok FROM obat WHERE kode_obat=? FOR UPDATE");
            $cek_stok->bind_param("s", $kode);
            $cek_stok->execute();
            $cek_stok->bind_result($stok_saat_ini);
            $cek_stok->fetch();
            $cek_stok->close();

            if($jumlah > $stok_saat_ini){
                $conn->rollback();
                http_response_code(400);
                echo "Stok obat '$nama' tidak cukup! Stok saat ini: $stok_saat_ini";
                exit;
            }

            $stmt_detail->bind_param("issidd", $penjualan_id, $kode, $nama, $jumlah, $harga, $total);
            $stmt_detail->execute();

            $stmt_update_stok->bind_param("is", $jumlah, $kode);
            $stmt_update_stok->execute();
        }

        $stmt_detail->close();
        $stmt_update_stok->close();
        $conn->commit();

        echo $penjualan_id;
        exit;
    } catch(Exception $e){
        $conn->rollback();
        http_response_code(500);
        echo "Gagal menyimpan transaksi: ".$e->getMessage();
        exit;
    }
}

// Ambil daftar obat
$obat_list = mysqli_query($conn, "SELECT * FROM obat ORDER BY nama_obat ASC");
?>
<!DOCTYPE html>
<html lang="id">
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
            <div class="card-header d-flex justify-content-between">
              <h4>Transaksi Penjualan Obat</h4>
              <button class="btn btn-primary" data-toggle="modal" data-target="#modalPenjualan"><i class="fas fa-plus"></i> Input Penjualan</button>
            </div>
            <div class="card-body">

              <div class="row mb-3">
                  <div class="col-md-3">
                    <label>Dari Tanggal:</label>
                    <input type="date" id="tgl_dari" class="form-control" value="<?= date('Y-m-d') ?>">
                  </div>
                  <div class="col-md-3">
                    <label>Sampai Tanggal:</label>
                    <input type="date" id="tgl_sampai" class="form-control" value="<?= date('Y-m-d') ?>">
                  </div>
                  <div class="col-md-2 align-self-end">
                    <button id="filter_tanggal" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                  </div>
              </div>

              <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped table-nowrap" id="tabel_penjualan">
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
                  <tbody></tbody>
                </table>
                <nav>
                  <ul class="pagination justify-content-center" id="pagination"></ul>
                </nav>
              </div>

            </div>
        </div>

        </div>
      </section>
    </div>
  </div>
</div>

<!-- MODAL INPUT PENJUALAN -->
<div class="modal fade" id="modalPenjualan" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-shopping-cart"></i> Input Penjualan Obat</h5>
        <button type="button" class="close text-white" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">

        <div class="card mb-3 shadow-sm">
          <div class="card-body">
            <div class="form-row align-items-end">
              <div class="col-md-6">
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
              <div class="col-md-3">
                <button type="button" id="tambah_cart" class="btn btn-success btn-block">Tambah</button>
              </div>
            </div>
          </div>
        </div>

        <!-- Keranjang Belanja -->
        <div class="card mb-3 shadow-sm">
          <div class="card-header bg-info text-white">Keranjang Belanja</div>
          <div class="card-body p-0">
            <table class="table table-hover table-bordered mb-0" id="tabel_cart">
              <thead class="thead-dark">
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
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-body">
            <div class="row text-center">
              <div class="col-md-4">
                <h5>Total</h5>
                <p class="h4" id="total_belanja">Rp 0,-</p>
              </div>
              <div class="col-md-4">
                <label>Uang Diterima</label>
                <input type="text" id="uang_diterima" class="form-control" value="0">
              </div>
              <div class="col-md-4">
                <h5>Kembalian</h5>
                <p class="h4" id="kembalian">Rp 0,-</p>
              </div>
            </div>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button id="simpan_cetak" class="btn btn-success">Simpan & Cetak</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
      </div>
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

function formatRupiah(nilai){
    return 'Rp ' + Number(nilai).toLocaleString('id-ID') + ',-';
}

function renderCart(){
    let tbody = $('#tabel_cart tbody'); tbody.empty();
    let totalBelanja = 0;
    cart.forEach((item, idx)=>{
        totalBelanja += item.total;
        tbody.append(`<tr>
            <td>${idx+1}</td>
            <td>${item.kode_obat}</td>
            <td>${item.nama_obat}</td>
            <td>${formatRupiah(item.harga)}</td>
            <td>${item.jumlah}</td>
            <td>${formatRupiah(item.total)}</td>
            <td><button class="btn btn-danger btn-sm hapus_cart" data-index="${idx}">Hapus</button></td>
        </tr>`);
    });
    $('#total_belanja').text(formatRupiah(totalBelanja));
    updateKembalian();
}

function updateKembalian(){
    let uang = parseFloat($('#uang_diterima').val().replace(/[^0-9.-]+/g,"")) || 0;
    let totalBelanja = cart.reduce((a,b)=>a+b.total,0);
    $('#kembalian').text(formatRupiah(uang - totalBelanja > 0 ? uang - totalBelanja : 0));
}

$(document).on('click', '.hapus_cart', function(){
    cart.splice($(this).data('index'), 1);
    renderCart();
});

$('#tambah_cart').click(function(){
    let kode = $('#kode_obat').val();
    if(!kode) return alert('Silakan pilih obat!');
    let nama = $('#kode_obat option:selected').data('nama');
    let harga = parseFloat($('#kode_obat option:selected').data('harga'));
    let stok = parseInt($('#kode_obat option:selected').data('stok'));
    let jumlah = parseInt($('#jumlah').val());
    if(jumlah<1) return alert('Jumlah minimal 1!');
    if(jumlah>stok) return alert('Jumlah melebihi stok!');
    let exist = cart.find(i=>i.kode_obat===kode);
    if(exist){ exist.jumlah+=jumlah; exist.total=exist.jumlah*exist.harga; }
    else{ cart.push({kode_obat:kode,nama_obat:nama,harga,jumlah,total:jumlah*harga}); }
    renderCart();
});

$('#uang_diterima').on('input', updateKembalian);

$('#simpan_cetak').click(function(){
    if(cart.length===0){ alert('Keranjang kosong!'); return; }
    let uang_diterima = parseFloat($('#uang_diterima').val().replace(/[^0-9.-]+/g,"")) || 0;
    let totalBelanja = cart.reduce((a,b)=>a+b.total,0);
    if(uang_diterima < totalBelanja){ alert('Uang diterima kurang!'); return; }

    $.post('transaksi_penjualan.php', {aksi:'simpan', cart:JSON.stringify(cart), uang_diterima}, function(res){
        alert('Transaksi berhasil! ID: '+res);
        cart=[]; renderCart();
        $('#uang_diterima').val('0'); $('#kembalian').text('Rp 0,-'); $('#modalPenjualan').modal('hide');
        loadData();
    }).fail(function(xhr){ alert('Gagal simpan: '+xhr.responseText); });
});

// Load data
function loadData(page=1){
    let tglDari=$('#tgl_dari').val();
    let tglSampai=$('#tgl_sampai').val();
    $.get('ajax_penjualan.php',{tgl_dari:tglDari,tgl_sampai:tglSampai,page},function(res){
        let obj=JSON.parse(res); let tbody=$('#tabel_penjualan tbody'); tbody.empty();
        let no=(obj.currentPage-1)*10+1;
        obj.data.forEach(r=>{
            tbody.append(`<tr>
                <td>${no++}</td>
                <td>${r.tanggal}</td>
                <td>${r.kode_obat}</td>
                <td>${r.nama_obat}</td>
                <td>${r.jumlah}</td>
                <td>${formatRupiah(r.harga_satuan)}</td>
                <td>${formatRupiah(r.total)}</td>
            </tr>`);
        });
        let pag=$('#pagination'); pag.empty();
        for(let i=1;i<=obj.totalPage;i++){
            let active=i==obj.currentPage?'active':'';
            pag.append(`<li class="page-item ${active}"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`);
        }
    });
}

$(document).on('click','#pagination a',function(e){ e.preventDefault(); loadData($(this).data('page')); });
$('#filter_tanggal').click(()=>loadData(1));
$(document).ready(()=>loadData());
</script>
</body>
</html>
