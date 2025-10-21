<?php
$pageTitle = 'Kuis Terbaru';
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/helpers.php';
ensure_csrf();

// Aksi toggle/hapus
$flash = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  check_csrf();
  $id = (int)($_POST['id'] ?? 0);
  if (!$id) { $flash='ID tidak valid'; }
  else {
    if (isset($_POST['toggle'])) {
      $stmt = $mysqli->prepare("UPDATE quiz SET is_active = 1 - is_active WHERE id=?");
      $stmt->bind_param('i',$id);
      $stmt->execute();
      $flash = 'Status diubah.';
    } elseif (isset($_POST['delete'])) {
      $stmt = $mysqli->prepare("DELETE FROM quiz WHERE id=?");
      $stmt->bind_param('i',$id);
      $stmt->execute();
      $flash = 'Berhasil dihapus.';
    }
  }
}

// Ambil data
$res = $mysqli->query("SELECT id,question,category,is_active,created_at,image_path FROM quiz ORDER BY id DESC LIMIT 50");

ob_start(); ?>
<div class="ec-card">
  <div class="ec-title">Kuis Terbaru</div>
  <?php if ($flash): ?><p class="note"><?= htmlspecialchars($flash) ?></p><?php endif; ?>
  <table class="table">
    <thead><tr>
      <th>ID</th><th>Pertanyaan</th><th>Kategori</th><th>Gambar</th><th>Aktif?</th><th>Dibuat</th><th>Aksi</th>
    </tr></thead>
    <tbody>
      <?php while($row = $res->fetch_assoc()): ?>
      <tr>
        <td><?= (int)$row['id'] ?></td>
        <td><?= htmlspecialchars($row['question']) ?></td>
        <td><span class="badge"><?= htmlspecialchars($row['category']) ?></span></td>
        <td>
          <?php if (!empty($row['image_path'])): ?>
            <img src="../<?= htmlspecialchars($row['image_path']) ?>" alt="" style="width:60px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #eee">
          <?php else: ?><span class="note">-</span><?php endif; ?>
        </td>
        <td><?= $row['is_active'] ? '<span class="badge">Aktif</span>' : '<span class="note">Nonaktif</span>' ?></td>
        <td><?= htmlspecialchars($row['created_at']) ?></td>
        <td class="actions">
          <form method="post" style="display:inline">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
            <button class="ec-btn" name="toggle" value="1" type="submit"><?= $row['is_active']?'Nonaktifkan':'Aktifkan' ?></button>
          </form>
          <form method="post" style="display:inline" onsubmit="return confirm('Hapus pertanyaan ini?')">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
            <button class="ec-btn danger" name="delete" value="1" type="submit">Hapus</button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
