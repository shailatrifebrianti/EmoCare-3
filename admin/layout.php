<?php
// admin/layout.php
require_once __DIR__ . '/../backend/config.php';
require_once __DIR__ . '/../backend/helpers.php';
ensure_csrf();

// auth minimal (silakan sesuaikan logikanya)
if (empty($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
  header('Location: ../admin_login.php');
  exit;
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title><?= htmlspecialchars($pageTitle ?? 'Admin • EmoCare') ?></title>
  <link rel="stylesheet" href="./admin.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body class="ec-admin">
  <?php include __DIR__ . '/../partials/admin_sidebar.php'; ?>
  <main class="ec-main">
    <?php include __DIR__ . '/../partials/admin_topbar.php'; ?>
    <div class="ec-container">
      <?= $content ?? '' ?>
    </div>
  </main>
</body>
</html>
