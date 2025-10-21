<?php
$pageTitle = 'Daftar Akun Admin';
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/helpers.php';
ensure_csrf();

$res = $mysqli->query("SELECT id, nama, email, created_at FROM users WHERE role='admin' ORDER BY id DESC");

ob_start(); ?>
<div class="ec-card">
  <div class="ec-title">Akun Admin</div>
  <table class="table">
    <thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Dibuat</th></tr></thead>
    <tbody>
      <?php while($u=$res->fetch_assoc()): ?>
        <tr>
          <td><?= (int)$u['id'] ?></td>
          <td><?= htmlspecialchars($u['nama'] ?? '-') ?></td>
          <td><?= htmlspecialchars($u['email'] ?? '-') ?></td>
          <td><?= htmlspecialchars($u['created_at'] ?? '-') ?></td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
