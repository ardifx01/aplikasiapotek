<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include 'koneksi.php';

// Ambil data profil apotek terbaru
$profil = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama, kontak FROM profil_apotek ORDER BY id DESC LIMIT 1"));
$nama_apotek = $profil['nama'] ?? 'Apotek-KU';
$kontak_apotek = $profil['kontak'] ?? '-';

// Ambil data pengguna yang login dari tabel users
$nama_pengguna = 'Pengguna';
if (isset($_SESSION['user_id'])) {
    $user_id = intval($_SESSION['user_id']);
    $user_q = mysqli_query($conn, "SELECT nama FROM users WHERE id=$user_id LIMIT 1");
    if ($user_row = mysqli_fetch_assoc($user_q)) {
        $nama_pengguna = $user_row['nama'];
    }
}
?>

<div class="navbar-bg"></div>
<nav class="navbar navbar-expand-lg main-navbar">
  <ul class="navbar-nav mr-auto">
    <li>
      <a href="#" data-toggle="sidebar" class="nav-link nav-link-lg">
        <i class="fas fa-bars"></i>
      </a>
    </li>
  </ul>

  <ul class="navbar-nav navbar-right align-items-center ml-auto">
    <!-- Jam Digital -->
    <li class="nav-item mr-3 d-flex align-items-center text-white">
      <i class="far fa-clock mr-1"></i>
      <span id="digital-clock" class="font-weight-bold"></span>
      <span class="ml-1 font-weight-bold">WIB</span>
    </li>

    <!-- Nama Apotek -->
    <li class="nav-item mr-3 d-flex align-items-center text-white">
      <i class="fas fa-clinic-medical mr-1"></i>
      <strong><?= htmlspecialchars($nama_apotek) ?></strong>
    </li>

    <!-- Kontak Apotek -->
    <li class="nav-item mr-3 d-flex align-items-center text-white">
      <i class="fas fa-phone mr-1"></i>
      <span><?= htmlspecialchars($kontak_apotek) ?></span>
    </li>

    <!-- Nama Pengguna -->
    <li class="nav-item mr-3 d-flex align-items-center text-white">
      <i class="far fa-user-circle mr-1"></i>
      <strong><?= htmlspecialchars($nama_pengguna) ?></strong>
    </li>

    <!-- Tombol Logout -->
    <li class="nav-item">
      <a href="logout.php" class="nav-link btn btn-sm btn-danger text-white font-weight-bold">
        <i class="fas fa-sign-out-alt mr-1"></i> Keluar / Logout
      </a>
    </li>
  </ul>
</nav>

<script>
// Jam Digital Lengkap + WIB
function updateClock() {
    const now = new Date();
    const days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

    const dayName = days[now.getDay()];
    const date = now.getDate();
    const month = months[now.getMonth()];
    const year = now.getFullYear();

    const hours = String(now.getHours()).padStart(2,'0');
    const minutes = String(now.getMinutes()).padStart(2,'0');
    const seconds = String(now.getSeconds()).padStart(2,'0');

    const formattedTime = `${dayName}, ${date} ${month} ${year} - ${hours}:${minutes}:${seconds}`;
    document.getElementById('digital-clock').textContent = formattedTime;
}

setInterval(updateClock, 1000);
updateClock();
</script>
