<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'koneksi.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit;
}

$user_id   = $_SESSION['user_id'];
$judul_id  = $_POST['judul_id'] ?? 0;
$jawaban   = $_POST['jawaban'] ?? [];

if (!$judul_id) {
  header("Location: kuis.php?score=0");
  exit;
}

// Ambil semua id soal untuk judul ini
$stmt = $conn->prepare("SELECT id FROM kuis WHERE judul_id = ?");
$stmt->bind_param("i", $judul_id);
$stmt->execute();
$hasil = $stmt->get_result();

while ($soal = $hasil->fetch_assoc()) {
  $soal_id = $soal['id'];
  $opsi = $jawaban[$soal_id] ?? null;

  $stmt2 = $conn->prepare("INSERT INTO jawaban_kuis (user_id, judul_id, soal_id, jawaban) VALUES (?, ?, ?, ?)");
  $stmt2->bind_param("iiis", $user_id, $judul_id, $soal_id, $opsi);
  $stmt2->execute();
}

// Hitung nilai akhir
$sql = "SELECT k.jawaban AS kunci, j.jawaban
        FROM kuis k
        JOIN jawaban_kuis j ON k.id = j.soal_id
        WHERE j.user_id = ? AND j.judul_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $judul_id);
$stmt->execute();
$result = $stmt->get_result();

$total = $benar = 0;
while ($row = $result->fetch_assoc()) {
  if (strtoupper($row['jawaban']) === strtoupper($row['kunci'])) {
    $benar++;
  }
  $total++;
}

$nilai = $total > 0 ? round(($benar / $total) * 100, 2) : 0;

// Simpan hasil ke tabel hasil_kuis
$stmt = $conn->prepare("INSERT INTO hasil_kuis (user_id, judul_id, nilai) VALUES (?, ?, ?)");
$stmt->bind_param("iid", $user_id, $judul_id, $nilai);
$stmt->execute();

// Ambil email user
$stmt = $conn->prepare("SELECT nama, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$nama_user = $user['nama'];
$email_user = $user['email'];

// Ambil nama kuis
$stmt = $conn->prepare("SELECT judul FROM judul_kuis WHERE id = ?");
$stmt->bind_param("i", $judul_id);
$stmt->execute();
$judul_row = $stmt->get_result()->fetch_assoc();
$nama_kuis = $judul_row['judul'] ?? 'Kuis';

// Ambil konfigurasi mail
$mailset = $conn->query("SELECT * FROM mail_settings LIMIT 1")->fetch_assoc();

// Kirim Email Hasil
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
  // Server settings
  $mail->isSMTP();
  $mail->Host       = $mailset['mail_host'];
  $mail->SMTPAuth   = true;
  $mail->Username   = $mailset['mail_username'];
  $mail->Password   = $mailset['mail_password'];
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
  $mail->Port       = $mailset['mail_port'];

  // Recipients
  $mail->setFrom($mailset['mail_from_email'], $mailset['mail_from_name']);
  $mail->addAddress($email_user, $nama_user);

  // Konten email
  $mail->isHTML(true);
  $mail->Subject = "Hasil Kuis Anda - $nama_kuis";
  $mail->Body    = "
    <h3>Halo, $nama_user</h3>
    <p>Terima kasih telah mengerjakan kuis <strong>$nama_kuis</strong>.</p>
    <p><strong>Skor Anda:</strong> $nilai</p>
    <p>Benar: $benar dari $total soal.</p>
    <br>
    <small>Email ini dikirim otomatis oleh sistem.</small>
  ";

  $mail->send();
} catch (Exception $e) {
  error_log("Gagal mengirim email: {$mail->ErrorInfo}");
}

// Redirect ke halaman kuis
header("Location: kuis.php?id=$judul_id&score=$nilai");
exit;
?>
