<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

// Ambil semua pengguna
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");

// Ambil semua menu
$menus = $conn->query("SELECT * FROM menu ORDER BY nama_menu ASC");
$menuList = $menus->fetch_all(MYSQLI_ASSOC);

// Simpan hak akses jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
  $user_id = $_POST['user_id'];
  $akses = isset($_POST['akses']) ? $_POST['akses'] : [];

  $conn->query("DELETE FROM hak_akses WHERE user_id = $user_id");

  foreach ($akses as $menu_id) {
    $conn->query("INSERT INTO hak_akses (user_id, menu_id) VALUES ($user_id, $menu_id)");
  }

  $notif = "Hak akses berhasil diperbarui.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
  <title>Hak Akses &mdash; SICONIC</title>

  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css" />
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css" />
  <link rel="stylesheet" href="assets/css/style.css" />
  <link rel="stylesheet" href="assets/css/components.css" />

  <style>
    .table-responsive-custom {
      overflow-x: auto;
    }
    th, td {
      white-space: nowrap;
      vertical-align: middle;
    }
    th.nama-col, td.nama-col {
      min-width: 220px;
      white-space: normal;
      text-align: left;
    }
    th.menu-col, td.menu-col {
      min-width: 120px;
    }
    .custom-checkbox {
      display: flex;
      justify-content: center;
    }
  </style>
</head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <?php include 'navbar.php'; ?>
      <?php include 'sidebar.php'; ?>

      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Hak Akses Pengguna</h1>
          </div>

          <div class="section-body">
            <?php if (!empty($notif)): ?>
              <div class="alert alert-success"><?= $notif ?></div>
            <?php endif; ?>

            <div class="card">
              <div class="card-body table-responsive-custom">
                <table class="table table-bordered table-sm text-center">
                  <thead class="thead-dark">
                    <tr>
                      <th class="nama-col">Nama</th>
                      <?php foreach ($menuList as $menu): ?>
                        <th class="menu-col"><?= htmlspecialchars($menu['nama_menu']) ?></th>
                      <?php endforeach; ?>
                      <th class="menu-col">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $users->data_seek(0);
                    while ($user = $users->fetch_assoc()):
                      $user_id = $user['id'];
                      $akses_result = $conn->query("SELECT menu_id FROM hak_akses WHERE user_id = $user_id");
                      $akses_user = [];
                      while ($a = $akses_result->fetch_assoc()) {
                        $akses_user[] = $a['menu_id'];
                      }
                    ?>
                      <tr>
                        <form method="POST">
                          <input type="hidden" name="user_id" value="<?= $user_id ?>">
                          <td class="nama-col">
                            <?= htmlspecialchars($user['nama']) ?><br>
                            <small class="text-muted"><?= htmlspecialchars($user['nrp']) ?></small>
                          </td>
                          <?php foreach ($menuList as $menu): ?>
                            <td class="menu-col">
                              <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input"
                                       id="akses_<?= $user_id . '_' . $menu['id'] ?>"
                                       name="akses[]"
                                       value="<?= $menu['id'] ?>"
                                       <?= in_array($menu['id'], $akses_user) ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="akses_<?= $user_id . '_' . $menu['id'] ?>"></label>
                              </div>
                            </td>
                          <?php endforeach; ?>
                          <td class="menu-col">
                            <button type="submit" class="btn btn-primary btn-sm">
                              <i class="fas fa-save"></i>
                            </button>
                          </td>
                        </form>
                      </tr>
                    <?php endwhile; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </section>
      </div>

      <?php include 'footer.php'; ?>
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
</body>
</html>
