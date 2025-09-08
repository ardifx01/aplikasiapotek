<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$user_id = $_SESSION['user_id'] ?? 0;
?>

<!-- sidebar.php -->
<div class="main-sidebar sidebar-style-2">
  <aside id="sidebar-wrapper">
    <div class="sidebar-brand">
      <a href="dashboard.php">Apotek - KU</a>
    </div>
    <div class="sidebar-brand sidebar-brand-sm">
      <a href="dashboard.php">AK</a>
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
    <li><a class="nav-link" href="kategori_obat.php"><i class="fas fa-tags"></i> Kategori Obat</a></li>
    <li><a class="nav-link" href="stok_obat.php"><i class="fas fa-dolly"></i> Obat Masuk</a></li>
  </ul>
</li>


      <!-- Penjualan -->
      <li class="menu-header">Transaksi</li>
      <li class="dropdown">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-shopping-cart"></i> <span>Penjualan</span></a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="transaksi_penjualan.php">Transaksi Penjualan</a></li>
          <li><a class="nav-link" href="riwayat_penjualan.php">Riwayat Penjualan</a></li>
        </ul>
      </li>

      <!-- Pelaporan -->
      <li class="menu-header">Pelaporan</li>
      <li class="dropdown">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-file-alt"></i> <span>Laporan</span></a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="laporan_penjualan.php">Laporan Penjualan</a></li>
          <li><a class="nav-link" href="laporan_stok.php">Laporan Stok Obat</a></li>
        </ul>
      </li>

      <!-- Setting -->
      <li class="menu-header">Setting</li>
      <li class="dropdown">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-cogs"></i> <span>Pengaturan</span></a>
        <ul class="dropdown-menu">
          <li><a class="nav-link" href="pengguna.php">Pengguna</a></li>
          <li><a class="nav-link" href="profil_apotek.php">Profil Apotek</a></li>
        </ul>
      </li>
    </ul>

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
