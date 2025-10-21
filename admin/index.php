<?php
$pageTitle = 'Dashboard';
ob_start();
require_once __DIR__ . '/../backend/config.php';

// contoh statistik ringan (silakan sesuaikan nama tabelmu)
$quizCount = (int) ($mysqli->query("SELECT COUNT(*) c FROM quiz")->fetch_assoc()['c'] ?? 0);
$userCount = (int) ($mysqli->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'] ?? 0);
$adminCount= (int) ($mysqli->query("SELECT COUNT(*) c FROM users WHERE role='admin'")->fetch_assoc()['c'] ?? 0);
?>
<div class="ec-card">
  <div class="ec-title">Ringkasan</div>
  <div class="ec-grid">
    <div class="ec-card">
      <div class="ec-title">Total Kuis</div>
      <div class="badge"><?= $quizCount ?></div>
      <div class="note">Jumlah pertanyaan aktif & nonaktif</div>
    </div>
    <div class="ec-card">
      <div class="ec-title">Pengguna</div>
      <div class="badge"><?= $userCount ?></div>
      <div class="note">Termasuk admin & user biasa</div>
    </div>
    <div class="ec-card">
      <div class="ec-title">Admin</div>
      <div class="badge"><?= $adminCount ?></div>
      <div class="note">Akun dengan role admin</div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
