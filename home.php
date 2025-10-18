<?php
require_once __DIR__ . '/backend/config.php';
require_login();

$uid   = (int)$_SESSION['user']['pengguna_id'];
$nama  = $_SESSION['user']['nama'] ?? 'Pengguna';
$flash = '';

// === HANDLE POST (SIMPAN MOOD) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_POST['action'] ?? '') === 'create')) {
  $mood = (int)($_POST['mood_level'] ?? 0);
  $note = trim($_POST['catatan'] ?? '');

  if ($mood < 1 || $mood > 5) {
    $flash = 'Skala mood harus 1..5';
  } else {
    $stmt = $mysqli->prepare("INSERT INTO moodtracker (pengguna_id, tanggal, mood_level, catatan) VALUES (?, CURDATE(), ?, ?)");
    $stmt->bind_param('iis', $uid, $mood, $note);
    if ($stmt->execute()) {
      header('Location: home.php?saved=1'); // PRG: cegah resubmit saat refresh
      exit;
    } else {
      $flash = 'Gagal menyimpan: ' . $stmt->error;
    }
    $stmt->close();
  }
}

// === HANDLE POST (DELETE RIWAYAT TERPILIH) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
  $ids = array_map('intval', $_POST['delete_ids'] ?? []);
  $ids = array_values(array_filter($ids));
  if ($ids) {
    // siapkan placeholder IN (?,?,...)
    $in = implode(',', array_fill(0, count($ids), '?'));
    $sql = "DELETE FROM moodtracker WHERE pengguna_id=? AND mood_id IN ($in)";
    $stmt = $mysqli->prepare($sql);
    $types = 'i' . str_repeat('i', count($ids));
    $params = array_merge([$uid], $ids);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();

    // PRG agar tidak resubmit saat refresh + tampilkan notifikasi
    header('Location: home.php?deleted=1#top');
    exit;
  } else {
    $flash = 'Pilih minimal satu baris untuk dihapus.';
  }
}

// === HANDLE POST (EDIT/UPDATE RIWAYAT TERPILIH) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
  $id    = (int)($_POST['mood_id'] ?? 0);
  $mood  = (int)($_POST['mood_level'] ?? 0);
  $note  = trim($_POST['catatan'] ?? '');

  if ($id <= 0) {
    $flash = 'Data tidak valid.';
  } elseif ($mood < 1 || $mood > 5) {
    $flash = 'Skala mood harus 1..5';
  } else {
    $sql  = "UPDATE moodtracker SET mood_level=?, catatan=? WHERE mood_id=? AND pengguna_id=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('isii', $mood, $note, $id, $uid);
    $stmt->execute();
    $stmt->close();
    header('Location: home.php?updated=1#top'); // PRG + notifikasi
    exit;
  }
}

// === AMBIL RIWAYAT + PENCARIAN SEDERHANA (?s=...) ===
$items = [];
$s = trim($_GET['s'] ?? '');

$conds = ['pengguna_id = ?'];
$types = 'i';
$vals  = [$uid];

if ($s !== '') {
  // cari di tanggal (YYYY-MM-DD), catatan, atau angka mood
  $conds[] = '(tanggal LIKE ? OR catatan LIKE ? OR CAST(mood_level AS CHAR) LIKE ?)';
  $types  .= 'sss';
  $like = '%'.$s.'%';
  $vals[] = $like;  // tanggal
  $vals[] = $like;  // catatan
  $vals[] = $like;  // mood (1..5) sebagai teks
}

$sql = "SELECT mood_id, tanggal, mood_level, catatan
        FROM moodtracker
        WHERE ".implode(' AND ', $conds)."
        ORDER BY tanggal DESC, mood_id DESC";

$q = $mysqli->prepare($sql);
$q->bind_param($types, ...$vals);
$q->execute();
$res = $q->get_result();
while ($row = $res->fetch_assoc()) { $items[] = $row; }
$q->close();

?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Home • EmoCare</title>

  <!-- Styles milikmu -->
  <link rel="stylesheet" href="css/styles.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <link rel="stylesheet" href="css/mood.css" />
  <style>.bg-hero{background:linear-gradient(135deg,#ffe0ea 0%,#e7dcff 50%,#dfe9ff 100%);}</style>
</head>
<body class="ec-body">
  <header class="ec-nav">
    <div class="ec-nav-inner">
      <div class="ec-brand">
        <span class="ec-brand-name" style="font-weight:500; font-style:normal; color:#6b7280;">EmoCare</span>
      </div>
      <nav class="ec-nav-links">
        <a href="#top">Beranda</a>
        <a href="#features">Fitur</a>
        <a href="#stats">Statistik</a>
      </nav>
      <form action="backend/auth_logout.php" method="post" style="margin:0">
        <button class="ec-btn-outline">Keluar</button>
      </form>
    </div>
  </header>

  <main id="top" class="ec-container">
    <!-- Greeting / Hero -->
    <section class="ec-card ec-hero" id="greetingCard">
      <div class="ec-hero-left">
        <div class="ec-hero-title">
          Selamat <span id="greetTimeWord">Pagi</span>, <span id="greetUsername"><?= htmlspecialchars($nama) ?></span>! 🙌🏻
        </div>
        <div class="ec-hero-sub">Gimana hari ini?</div>
      </div>
      <div class="ec-hero-right">
        <div class="ec-clock"><span id="greetClock"></span></div>
        <div class="ec-streak">🔥 <span id="greetStreak">0</span> hari beruntun</div>
      </div>
    </section>

    <!-- VIDEO QUOTES -->
    <section class="ec-card ec-video">
      <h3 class="ec-section-title">Tonton dulu Yuk😻</h3>
      <div class="ec-video-frame" id="video-frame">
        <iframe width="560" height="315"
          src="https://www.youtube.com/embed/WWloIAQpMcQ"
          title="Quotes"
          frameborder="0"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          referrerpolicy="strict-origin-when-cross-origin"
          allowfullscreen></iframe>
      </div>
    </section>

    <!-- Mood Tracker -->
    <section id="mood-tracker" class="ec-mood-grid">
      <!-- Form -->
      <article class="card ec-mood-card">
        <div class="card-bar">
          <div class="bar-dot"></div>
          <h3 class="bar-title">Mood Tracker</h3>
        </div>

        <?php if (!empty($_GET['saved'])): ?>
  <p class="form-success" role="status">Mood tersimpan!</p>
<?php elseif (!empty($_GET['updated'])): ?>
  <p class="form-success" role="status">Riwayat berhasil diperbarui.</p>
<?php elseif (!empty($_GET['deleted'])): ?>
  <p class="form-success" role="status">Riwayat terpilih berhasil dihapus.</p>
<?php elseif ($flash): ?>
  <p class="form-error" role="alert"><?= htmlspecialchars($flash) ?></p>
<?php endif; ?>



        <form action="home.php" method="POST" class="ec-form" autocomplete="off">
          <input type="hidden" name="action" value="create">
          <div class="form-row">
            <label>Skala Mood</label>
            <div class="scale-group" role="radiogroup" aria-label="Skala Mood">
              <label class="scale-pill"><input type="radio" name="mood_level" value="1" required>1. Senang Banget</label>
              <label class="scale-pill"><input type="radio" name="mood_level" value="2">2. Senang</label>
              <label class="scale-pill"><input type="radio" name="mood_level" value="3">3. Biasa</label>
              <label class="scale-pill"><input type="radio" name="mood_level" value="4">4. Cemas</label>
              <label class="scale-pill"><input type="radio" name="mood_level" value="5">5. Stress</label>
            </div>
            <small class="helper">Tanggal otomatis (hari ini).</small>
          </div>

          <div class="form-row">
            <label for="mood-note">Catatan</label>
            <textarea id="mood-note" name="catatan" rows="3" placeholder="Tulis catatan singkat…"></textarea>
          </div>

          <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <button type="reset" class="btn btn-ghost">Reset</button>
          </div>
        </form>
      </article>

      <!-- Riwayat -->
<article class="card ec-history-card">
  <div class="card-bar">
    <div class="bar-dot"></div>
    <h3 class="bar-title">Riwayat Mood</h3>
  </div>

  <!-- Toolbar kecil: Cari + Edit + Hapus -->
<div class="ec-toolbar" style="display:flex; align-items:center; gap:8px; width:100%; margin:12px 0;">
  <!-- Cari (GET) -> form melar -->
  <form action="home.php#top" method="get"
        style="display:flex; align-items:center; gap:8px; flex:1;">
    <input type="text" name="s" placeholder="Cari…"
           value="<?= htmlspecialchars($_GET['s'] ?? '') ?>"
           style="flex:1; min-width:0; height:36px;">
    <button type="submit" class="btn btn-primary">Cari</button>
  </form>

  <!-- Edit (aktif bila 1 baris terpilih) -->
  <button type="button" id="btnEdit" class="btn btn-ghost" disabled>Edit</button>

  <!-- Hapus (POST mengirim form tabel) -->
  <button type="submit" form="del-form" id="btnDelete" class="btn btn-ghost" disabled
          onclick="return confirm('Hapus baris yang dipilih?');">Hapus</button>
</div>

<!-- Panel Edit (hidden) -->
<form id="edit-form" action="home.php#top" method="post"
      style="display:none; border:1px dashed #e5e7eb; padding:10px; border-radius:10px; margin:-4px 0 12px;">
  <input type="hidden" name="action" value="edit">
  <input type="hidden" name="mood_id" id="edit-id">
  <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
    <label> Mood:
      <select name="mood_level" id="edit-mood" required>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
      </select>
    </label>
    <label style="flex:1; min-width:240px;">
      Catatan:
      <input type="text" name="catatan" id="edit-note" placeholder="Ubah catatan…" style="width:100%;">
    </label>
    <button type="submit" class="btn btn-primary">Simpan</button>
    <button type="button" id="edit-cancel" class="btn btn-ghost">Batal</button>
  </div>
</form>


  <!-- Form POST untuk hapus + tabel riwayat -->
  <form id="del-form" action="home.php#top" method="post">
    <input type="hidden" name="action" value="delete">
    <div class="table-wrapper">
      <table class="ec-table" aria-label="Tabel Riwayat Mood">
        <thead>
          <tr>
            <th style="width:42px;text-align:center;"><input type="checkbox" id="chkAll"></th>
            <th>No</th>
            <th>Tanggal</th>
            <th>Mood</th>
            <th>Catatan</th>
          </tr>
        </thead>
        <tbody id="tbody-history">
          <?php if (empty($items)): ?>
            <tr class="empty"><td colspan="5">Belum ada data.</td></tr>
          <?php else: ?>
            <?php foreach ($items as $i => $it): ?>
              <tr>
                <td style="text-align:center;">
                  <input
  type="checkbox"
  class="rowchk"
  name="delete_ids[]"
  value="<?= (int)$it['mood_id'] ?>"
  data-mood="<?= (int)$it['mood_level'] ?>"
  data-note="<?= htmlspecialchars($it['catatan'] ?? '', ENT_QUOTES) ?>"
>

                </td>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($it['tanggal']) ?></td>
                <td><?= (int)$it['mood_level'] ?></td>
                <td><?= nl2br(htmlspecialchars($it['catatan'] ?? '')) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </form>
</article>

    </section>

    <!-- Statistik dummy (opsional) -->
    <section id="stats" class="ec-card">
      <h2 class="ec-section-title">Statistik &amp; Progress</h2>
      <div class="ec-tiles">
        <div class="ec-tile"><div class="k">Total Aktivitas</div><div class="v" id="tileActivity"><?= count($items) ?></div></div>
        <div class="ec-tile"><div class="k">Streak Harian</div><div class="v" id="tileStreak">0 hari</div></div>
        <div class="ec-tile"><div class="k">Rata-rata Mood</div><div class="v" id="tileAvgMood">
          <?php
            if ($items) {
              $sum = 0; foreach ($items as $it) { $sum += (int)$it['mood_level']; }
              echo round($sum / count($items), 2) . '/5';
            } else echo '0/5';
          ?>
        </div></div>
        <div class="ec-tile"><div class="k">Kuis Selesai</div><div class="v" id="tileQuizDone">0</div></div>
      </div>
    </section>
  </main>

  <script>
    // Jam & ucapan sederhana
    const clock = document.getElementById('greetClock');
    const word  = document.getElementById('greetTimeWord');
    function tick(){
      const d=new Date();
      clock.textContent = d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
      const h=d.getHours();
      word.textContent = (h<11)?'Pagi':(h<15)?'Siang':(h<18)?'Sore':'Malam';
    }
    tick(); setInterval(tick, 30000);
  </script>

  <script>
(function(){
  const btnDelete = document.getElementById('btnDelete');
  const btnEdit   = document.getElementById('btnEdit');
  const chkAll    = document.getElementById('chkAll');

  const editForm  = document.getElementById('edit-form');
  const editId    = document.getElementById('edit-id');
  const editMood  = document.getElementById('edit-mood');
  const editNote  = document.getElementById('edit-note');
  const editCancel= document.getElementById('edit-cancel');

  function getCheckedRows(){
    return Array.from(document.querySelectorAll('.rowchk:checked'));
  }
  function refreshButtons(){
    const rows = getCheckedRows();
    if (btnDelete) btnDelete.disabled = rows.length === 0;  // hapus: minimal 1
    if (btnEdit)   btnEdit.disabled   = rows.length !== 1;  // edit: tepat 1
    if (rows.length !== 1 && editForm) editForm.style.display = 'none';
  }

  // Select-all
  chkAll?.addEventListener('change', () => {
    document.querySelectorAll('.rowchk').forEach(c => c.checked = chkAll.checked);
    refreshButtons();
  });

  // Centang baris
  document.addEventListener('change', (e) => {
    if (e.target && e.target.classList.contains('rowchk')) {
      if (!e.target.checked && chkAll) chkAll.checked = false;
      refreshButtons();
    }
  });

  // Tampilkan panel edit dengan data baris terpilih
  btnEdit?.addEventListener('click', () => {
    const rows = getCheckedRows();
    if (rows.length !== 1) return;
    const r = rows[0];
    editId.value   = r.value;
    editMood.value = r.dataset.mood || '';
    editNote.value = r.dataset.note || '';
    editForm.style.display = '';
    setTimeout(() => editNote?.focus(), 0);
  });

  // Batal edit
  editCancel?.addEventListener('click', () => {
    editForm.style.display = 'none';
  });

  // Set awal
  refreshButtons();
})();
</script>

<?php if (!empty($_GET['updated']) || !empty($_GET['deleted'])): ?>
<script>
(function () {
  const url = new URL(location.href);
  const hasUpdated = url.searchParams.has('updated');
  const hasDeleted = url.searchParams.has('deleted');

  // Satu alert untuk keduanya
  const msg = hasUpdated
    ? 'Riwayat berhasil diperbarui.'
    : 'Riwayat terpilih berhasil dihapus.';
  alert(msg);

  // Bersihkan query agar refresh tidak memunculkan alert lagi
  ['deleted','saved','updated'].forEach(k => url.searchParams.delete(k));
  const qs = url.searchParams.toString();
  history.replaceState(null, '', url.pathname + (qs ? '?' + qs : '') + url.hash);
})();
</script>
<?php endif; ?>

</body>
</html>
