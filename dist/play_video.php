<?php
if (!isset($_GET['file'])) {
  die("Video tidak ditemukan.");
}

$video = basename($_GET['file']); // Hindari path traversal
$path = "uploads/" . $video;

if (!file_exists($path)) {
  die("File video tidak ditemukan.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Putar Video</title>
</head>
<body style="margin:0; display:flex; justify-content:center; align-items:center; height:100vh; background-color:#000;">
  <video width="80%" height="auto" controls autoplay>
    <source src="<?= htmlspecialchars($path) ?>" type="video/mp4">
    Browser Anda tidak mendukung pemutar video.
  </video>
</body>
</html>
