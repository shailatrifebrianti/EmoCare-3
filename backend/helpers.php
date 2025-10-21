<?php
// backend/helpers.php
if (session_status() === PHP_SESSION_NONE) session_start();

/** CSRF */
function ensure_csrf() {
  if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
  }
}
function check_csrf() {
  if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    http_response_code(419);
    exit('Invalid CSRF token');
  }
}

/** Hardening sesi setelah login (panggil di proses login sukses) */
function harden_session() {
  if (function_exists('session_regenerate_id')) session_regenerate_id(true);
  ini_set('session.cookie_httponly', '1');
  ini_set('session.cookie_samesite', 'Lax');
  if (!empty($_SERVER['HTTPS'])) ini_set('session.cookie_secure', '1');
}

/** Upload gambar pertanyaan */
function handle_question_image_upload(string $field = 'question_image'): ?string {
  if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
    return null; // tidak ada file
  }
  $f = $_FILES[$field];
  if ($f['error'] !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Upload gagal (kode '.$f['error'].')');
  }
  $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime  = finfo_file($finfo, $f['tmp_name']);
  finfo_close($finfo);
  if (!isset($allowed[$mime])) throw new RuntimeException('Tipe file tidak didukung');
  if ($f['size'] > 2*1024*1024) throw new RuntimeException('Maksimal 2MB');

  $ext = $allowed[$mime];
  $name = 'q_'.date('Ymd_His').'_' . bin2hex(random_bytes(4)) . '.' . $ext;
  $destDir = __DIR__ . '/../uploads/questions';
  if (!is_dir($destDir)) mkdir($destDir, 0775, true);
  $dest = $destDir . '/' . $name;
  if (!move_uploaded_file($f['tmp_name'], $dest)) {
    throw new RuntimeException('Gagal menyimpan file');
  }
  // path relatif untuk ditampilkan
  return 'uploads/questions/' . $name;
}
