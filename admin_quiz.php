<?php
// admin_quiz.php — Kelola kuis per-kategori (psikometri)
require_once __DIR__ . '/backend/config.php';
require_login();

/* helpers */
function current_user_id(){ return (int)($_SESSION['user']['pengguna_id'] ?? 0); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function is_admin(mysqli $db): bool {
  $uid = current_user_id(); if ($uid <= 0) return false;
  $stmt = $db->prepare("SELECT role FROM pengguna WHERE pengguna_id=? LIMIT 1");
  $stmt->bind_param('i', $uid); $stmt->execute();
  $role = $stmt->get_result()->fetch_column(); $stmt->close();
  return $role === 'admin';
}
if (!is_admin($mysqli)) { http_response_code(403); die('Forbidden: Admin only.'); }

/* kategori dari URL */
$cat = $_GET['cat'] ?? 'self_esteem';
$CAT_LABEL = [
  'self_esteem'     => 'Self-Esteem',
  'social_anxiety'  => 'Kecemasan Sosial'
];
if (!isset($CAT_LABEL[$cat])) $cat = 'self_esteem';

/* CSRF */
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
$CSRF = $_SESSION['csrf'];

$flash = '';

/* CREATE (psikometri: tanpa jawaban benar) */
if ($_SERVER['REQUEST_METHOD']==='POST' && (($_POST['action']??'')==='create_quiz')) {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { http_response_code(400); die('Invalid CSRF token'); }
  $question = trim($_POST['question_text'] ?? '');
  $opts = [
    trim($_POST['option_1'] ?? ''),
    trim($_POST['option_2'] ?? ''),
    trim($_POST['option_3'] ?? ''),
    trim($_POST['option_4'] ?? '')
  ];
  $make_active = (int)($_POST['make_active'] ?? 0) === 1;

  $valid_opts = array_values(array_filter($opts, fn($x)=>$x!==''));
  if ($question==='' || count($valid_opts)<2) {
    $flash = 'Isi pertanyaan dan minimal 2 opsi.';
  } else {
    $mysqli->begin_transaction();
    try {
      $stmt = $mysqli->prepare("INSERT INTO quiz_questions (question_text, is_active, category) VALUES (?, ?, ?)");
      $act = $make_active ? 1 : 0;
      $stmt->bind_param('sis', $question, $act, $cat);
      $stmt->execute(); $qid = $stmt->insert_id; $stmt->close();

      $ins = $mysqli->prepare("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (?,?,0)");
      for ($i=0; $i<count($opts); $i++){
        if ($opts[$i]==='') continue;
        $ins->bind_param('is', $qid, $opts[$i]);
        $ins->execute();
      }
      $ins->close();

      $mysqli->commit();
      header('Location: admin_quiz.php?cat='.urlencode($cat).'&saved=1#quiz'); exit;
    } catch(Throwable $e){
      $mysqli->rollback(); $flash = 'Gagal menyimpan: '.$e->getMessage();
    }
  }
}

/* TOGGLE ACTIVE (boleh banyak aktif dalam kategori) */
if ($_SERVER['REQUEST_METHOD']==='POST' && (($_POST['action']??'')==='toggle_active')) {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { http_response_code(400); die('Invalid CSRF'); }
  $qid = (int)($_POST['question_id'] ?? 0);
  if ($qid>0) {
    $stmt = $mysqli->prepare("UPDATE quiz_questions SET is_active=IF(is_active=1,0,1) WHERE id=? AND category=?");
    $stmt->bind_param('is', $qid, $cat); $stmt->execute(); $stmt->close();
    header('Location: admin_quiz.php?cat='.urlencode($cat).'&updated=1#quiz'); exit;
  }
}

/* DELETE */
if ($_SERVER['REQUEST_METHOD']==='POST' && (($_POST['action']??'')==='delete_quiz')) {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { http_response_code(400); die('Invalid CSRF'); }
  $qid = (int)($_POST['question_id'] ?? 0);
  if ($qid>0){
    $mysqli->begin_transaction();
    try {
      $d1 = $mysqli->prepare("DELETE FROM quiz_options WHERE question_id=?");
      $d1->bind_param('i', $qid); $d1->execute(); $d1->close();
      $d2 = $mysqli->prepare("DELETE FROM quiz_questions WHERE id=? AND category=?");
      $d2->bind_param('is', $qid, $cat); $d2->execute(); $d2->close();
      $mysqli->commit();
      header('Location: admin_quiz.php?cat='.urlencode($cat).'&deleted=1#quiz'); exit;
    } catch(Throwable $e){
      $mysqli->rollback(); $flash = 'Gagal menghapus: '.$e->getMessage();
    }
  }
}

/* UPDATE */
if ($_SERVER['REQUEST_METHOD']==='POST' && (($_POST['action']??'')==='update_quiz')) {
  if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { http_response_code(400); die('Invalid CSRF'); }
  $qid = (int)($_POST['edit_id'] ?? 0);
  $qtext = trim($_POST['edit_question'] ?? '');
  $opts = [
    trim($_POST['edit_opt1'] ?? ''),
    trim($_POST['edit_opt2'] ?? ''),
    trim($_POST['edit_opt3'] ?? ''),
    trim($_POST['edit_opt4'] ?? ''),
  ];
  if ($qid<=0 || $qtext==='' || count(array_filter($opts))<2) {
    $flash = 'Isi edit pertanyaan + minimal 2 opsi.';
  } else {
    $mysqli->begin_transaction();
    try {
      $u = $mysqli->prepare("UPDATE quiz_questions SET question_text=? WHERE id=? AND category=?");
      $u->bind_param('sis', $qtext, $qid, $cat); $u->execute(); $u->close();

      $d = $mysqli->prepare("DELETE FROM quiz_options WHERE question_id=?");
      $d->bind_param('i', $qid); $d->execute(); $d->close();

      $ins = $mysqli->prepare("INSERT INTO quiz_options (question_id, option_text, is_correct) VALUES (?,?,0)");
      for ($i=0;$i<4;$i++){
        if ($opts[$i]==='') continue;
        $ins->bind_param('is', $qid, $opts[$i]);
        $ins->execute();
      }
      $ins->close();

      $mysqli->commit();
      header('Location: admin_quiz.php?cat='.urlencode($cat).'&updated=1#quiz'); exit;
    } catch(Throwable $e){
      $mysqli->rollback(); $flash='Gagal update: '.$e->getMessage();
    }
  }
}

/* load data */
$edit = null;
if (!empty($_GET['edit_id'])) {
  $eid = (int)$_GET['edit_id'];
  $s = $mysqli->prepare("SELECT id, question_text FROM quiz_questions WHERE id=? AND category=? LIMIT 1");
  $s->bind_param('is', $eid, $cat); $s->execute();
  $edit = $s->get_result()->fetch_assoc(); $s->close();
  if ($edit){
    $ops = $mysqli->prepare("SELECT id, option_text FROM quiz_options WHERE question_id=? ORDER BY id");
    $ops->bind_param('i', $eid); $ops->execute();
    $edit['options'] = $ops->get_result()->fetch_all(MYSQLI_ASSOC);
    $ops->close();
  }
}

$quizzes = [];
$q = $mysqli->prepare("SELECT id, question_text, is_active, DATE_FORMAT(created_at,'%Y-%m-%d %H:%i') AS created_at
                       FROM quiz_questions
                       WHERE category=?
                       ORDER BY id DESC LIMIT 50");
$q->bind_param('s', $cat); $q->execute();
if ($r = $q->get_result()) $quizzes = $r->fetch_all(MYSQLI_ASSOC);
$q->close();
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Admin • <?= h($CAT_LABEL[$cat]) ?> • EmoCare</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>.actions{display:flex;gap:6px;align-items:center}.actions form{display:inline}</style>
</head>
<body data-page="admin">
  <div class="wrap">
    <div class="hdr">
      <div>
        <h1>Admin Dashboard • <?= h($CAT_LABEL[$cat]) ?></h1>
        <span class="muted">Kelola kuis psikometri (tanpa jawaban benar/salah).</span>
      </div>
      <div class="right" style="display:flex;gap:8px;margin-left:auto">
        <a class="btn ghost" href="admin_self_esteem.php">Self-Esteem</a>
        <a class="btn ghost" href="admin_social_anxiety.php">Kecemasan Sosial</a>
        <a class="btn ghost" href="admin_profile.php">Profil</a>
        <a class="btn" href="home.php">Home</a>
      </div>
    </div>

    <?php if (!empty($_GET['saved'])): ?><div class="ok">Kuis tersimpan.</div><?php endif; ?>
    <?php if (!empty($_GET['updated'])): ?><div class="ok">Data diperbarui.</div><?php endif; ?>
    <?php if (!empty($_GET['deleted'])): ?><div class="ok">Kuis dihapus.</div><?php endif; ?>
    <?php if ($flash): ?><div class="warn"><?= h($flash) ?></div><?php endif; ?>

    <section class="card full" id="quiz">
      <div class="hd"><h2>Buat Kuis • <?= h($CAT_LABEL[$cat]) ?></h2></div>
      <div class="bd">
        <form method="post" class="row">
          <input type="hidden" name="action" value="create_quiz">
          <input type="hidden" name="csrf" value="<?= h($CSRF) ?>">

          <label>
            <div class="muted small">Pertanyaan</div>
            <textarea name="question_text" class="inp" rows="3" required placeholder="Contoh: Saya sering merasa tegang tanpa alasan yang jelas."></textarea>
          </label>

          <div class="row row-2">
            <label><div class="muted small">Opsi 1</div><input class="inp" name="option_1" required placeholder="Mis: Tidak Pernah"></label>
            <label><div class="muted small">Opsi 2</div><input class="inp" name="option_2" required placeholder="Mis: Jarang"></label>
            <label><div class="muted small">Opsi 3</div><input class="inp" name="option_3" placeholder="Mis: Sering"></label>
            <label><div class="muted small">Opsi 4</div><input class="inp" name="option_4" placeholder="Mis: Sangat Sering"></label>
          </div>

          <label style="display:flex; align-items:center; gap:8px;">
            <input type="checkbox" name="make_active" value="1">
            <span class="muted small">Aktifkan setelah disimpan</span>
          </label>

          <div><button class="btn" type="submit">Simpan Kuis</button></div>
        </form>

        <?php if ($edit): ?>
          <hr style="border:none;border-top:1px solid var(--bd);margin:16px 0">
          <div class="muted" style="margin:8px 0">Edit Kuis (ID: <?= (int)$edit['id'] ?>)</div>
          <form method="post" class="row">
            <input type="hidden" name="action" value="update_quiz">
            <input type="hidden" name="csrf" value="<?= h($CSRF) ?>">
            <input type="hidden" name="edit_id" value="<?= (int)$edit['id'] ?>">

            <label>
              <div class="muted small">Pertanyaan</div>
              <textarea name="edit_question" class="inp" rows="3" required><?= h($edit['question_text']) ?></textarea>
            </label>

            <?php
              $o1=$o2=$o3=$o4='';
              if (!empty($edit['options'])) {
                $optsA=array_values($edit['options']);
                if(isset($optsA[0])) $o1=$optsA[0]['option_text'];
                if(isset($optsA[1])) $o2=$optsA[1]['option_text'];
                if(isset($optsA[2])) $o3=$optsA[2]['option_text'];
                if(isset($optsA[3])) $o4=$optsA[3]['option_text'];
              }
            ?>
            <div class="row row-2">
              <label><div class="muted small">Opsi 1</div><input class="inp" name="edit_opt1" value="<?= h($o1) ?>" required></label>
              <label><div class="muted small">Opsi 2</div><input class="inp" name="edit_opt2" value="<?= h($o2) ?>" required></label>
              <label><div class="muted small">Opsi 3</div><input class="inp" name="edit_opt3" value="<?= h($o3) ?>"></label>
              <label><div class="muted small">Opsi 4</div><input class="inp" name="edit_opt4" value="<?= h($o4) ?>"></label>
            </div>

            <div><button class="btn" type="submit">Simpan Perubahan</button></div>
          </form>
        <?php endif; ?>
      </div>
    </section>

    <section class="card full">
      <div class="hd"><h2>Kuis Terbaru • <?= h($CAT_LABEL[$cat]) ?></h2></div>
      <div class="bd table-responsive">
        <?php if (empty($quizzes)): ?>
          <div class="muted">Belum ada kuis pada kategori ini.</div>
        <?php else: ?>
          <table class="ec-table">
            <thead>
              <tr>
                <th style="width:64px">ID</th>
                <th>Pertanyaan</th>
                <th style="width:120px">Aktif?</th>
                <th style="width:180px">Dibuat</th>
                <th style="width:260px">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($quizzes as $q): ?>
              <tr>
                <td><?= (int)$q['id'] ?></td>
                <td><?= h(mb_strimwidth($q['question_text'],0,180,'…','UTF-8')) ?></td>
                <td><?= $q['is_active'] ? '<span class="tag active">Aktif</span>' : '<span class="tag">Nonaktif</span>' ?></td>
                <td><?= h($q['created_at']) ?></td>
                <td class="actions">
                  <form method="post">
                    <input type="hidden" name="action" value="toggle_active">
                    <input type="hidden" name="csrf" value="<?= h($CSRF) ?>">
                    <input type="hidden" name="question_id" value="<?= (int)$q['id'] ?>">
                    <button class="btn"><?= $q['is_active'] ? 'Nonaktifkan' : 'Aktifkan' ?></button>
                  </form>
                  <a class="btn ghost" href="admin_quiz.php?cat=<?= urlencode($cat) ?>&edit_id=<?= (int)$q['id'] ?>#quiz">Edit</a>
                  <form method="post" onsubmit="return confirm('Hapus kuis ini?')">
                    <input type="hidden" name="action" value="delete_quiz">
                    <input type="hidden" name="csrf" value="<?= h($CSRF) ?>">
                    <input type="hidden" name="question_id" value="<?= (int)$q['id'] ?>">
                    <button class="btn ghost">Hapus</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </section>
  </div>
</body>
</html>
