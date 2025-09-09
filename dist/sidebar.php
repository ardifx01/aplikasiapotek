<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// Ambil data profil apotek terbaru
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM profil_apotek ORDER BY id DESC LIMIT 1"));
$nama_apotek = $profil['nama'] ?? 'Apotek-KU';
?>

<div class="main-sidebar sidebar-style-2">
  <aside id="sidebar-wrapper">
    <!-- Sidebar Brand -->
    <div class="sidebar-brand">
      <a href="dashboard.php"><?= htmlspecialchars($nama_apotek) ?></a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
      <a href="dashboard.php"><?= strtoupper(substr($nama_apotek,0,2)) ?></a>
    </div>

    <ul class="sidebar-menu">
      <!-- Dashboard -->
      <li class="menu-header">Dashboard</li>
      <li class="<?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
        <a href="dashboard.php" class="nav-link"><i class="fas fa-home"></i> <span>Dashboard</span></a>
      </li>

      <!-- Master Data -->
      <li class="menu-header">Master Data</li>
      <li class="dropdown">
        <a href="#" class="nav-link has-dropdown">
          <i class="fas fa-pills"></i> <span>Stok Obat</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="data_obat.php"><i class="fas fa-list"></i> Daftar Obat</a></li>
          <li><a class="nav-link" href="suplier.php"><i class="fas fa-truck"></i> Suplier</a></li>
          <li><a class="nav-link" href="stok_obat.php"><i class="fas fa-dolly"></i> Obat Masuk</a></li>
        </ul>
      </li>

      <!-- Penjualan -->
      <li class="menu-header">Transaksi</li>
      <li class="dropdown">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-shopping-cart"></i> <span>Penjualan</span></a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="transaksi_penjualan.php">Transaksi Penjualan</a></li>
          <li><a class="nav-link" href="operasional.php">Biaya Operasional</a></li>
        </ul>
      </li>

      <!-- Pelaporan -->
      <li class="menu-header">Pelaporan</li>
      <li class="dropdown">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-file-alt"></i> <span>Laporan</span></a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="laporan_penjualan.php">Laporan Penjualan</a></li>
          <li><a class="nav-link" href="laporan_hutang.php">Laporan Hutang</a></li>
          <li><a class="nav-link" href="laporan_laba_rugi.php">Laporan Laba Rugi</a></li>
        </ul>
      </li>

      <!-- Setting -->
      <li class="menu-header">Setting</li>
      <li class="dropdown">
        <a href="#" class="nav-link has-dropdown">
          <i class="fas fa-cogs"></i> <span>Pengaturan</span>
        </a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="pengguna.php"><i class="fas fa-user"></i> Pengguna</a></li>
          <li><a class="nav-link" href="profil_apotek.php"><i class="fas fa-hospital"></i> Profil Apotek</a></li>
        </ul>
      </li>

      <!-- Kontak Admin -->
      <div class="mt-4 mb-4 p-3 hide-sidebar-mini">
        <a href="https://wa.me/6282177846209" target="_blank" 
           class="btn btn-primary btn-lg btn-block d-flex align-items-center justify-content-start">
          <i class="fab fa-whatsapp fa-2x mr-3"></i>
          <div class="text-left">
            <strong>Info Admin :</strong><br>0821 7784 6209
          </div>
        </a>
      </div>
  </aside>
</div>
