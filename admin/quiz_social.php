<?php
$pageTitle = 'Buat Kuis • Kecemasan Sosial';
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/helpers.php';
ensure_csrf();

$flash='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  check_csrf();
  try{
    $q  = trim($_POST['question'] ?? '');
    $o1 = trim($_POST['opt1'] ?? '');
    $o2 = trim($_POST['opt2'] ?? '');
    $o3 = trim($_POST['opt3'] ?? '');
    $o4 = trim($_POST['opt4'] ?? '');
    $active = !empty($_POST['active']) ? 1 : 0;
    if ($q==='' || $o1==='' || $o2==='' || $o3==='' || $o4==='') {
      throw new RuntimeException('Semua field wajib diisi.');
    }
    $imgPath = handle_question_image_upload('question_image');

    $stmt = $mysqli->prepare("INSERT INTO quiz (question, opt1, opt2, opt3, opt4, category, is_active, image_path, created_at)
                              VALUES (?, ?, ?, ?, ?, 'Kecemasan Sosial', ?, ?, NOW())");
    $stmt->bind_param('sssssis', $q, $o1, $o2, $o3, $o4, $active, $imgPath);
    if (!$stmt->execute()) throw new RuntimeException($stmt->error);
    $flash = 'Berhasil disimpan.';
  }catch(Throwable $e){
    $flash = 'Gagal: '.$e->getMessage();
  }
}

ob_start(); ?>
<div class="ec-card">
  <div class="ec-title">Buat Kuis (Kecemasan Sosial)</div>
  <?php if ($flash): ?><p class="note"><?= htmlspecialchars($flash) ?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
    <label class="label">Pertanyaan</label>
    <textarea class="textarea" name="question" rows="3" placeholder="Contoh: Saya sulit berbicara di depan banyak orang."></textarea>

    <div class="row" style="margin-top:10px">
      <div><label class="label">Opsi 1</label><input class="input" name="opt1" placeholder="Tidak Pernah"></div>
      <div><label class="label">Opsi 2</label><input class="input" name="opt2" placeholder="Jarang"></div>
      <div><label class="label">Opsi 3</label><input class="input" name="opt3" placeholder="Sering"></div>
      <div><label class="label">Opsi 4</label><input class="input" name="opt4" placeholder="Sangat Sering"></div>
    </div>

    <div style="margin-top:10px">
      <label class="label">Gambar Pertanyaan (opsional, max 2MB)</label>
      <input class="input" type="file" name="question_image" accept="image/*">
    </div>

    <div style="margin:12px 0">
      <label><input type="checkbox" name="active" value="1"> Aktifkan setelah disimpan</label>
    </div>

    <button class="ec-btn" type="submit">Simpan Kuis</button>
  </form>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
