<?php
$menu = [
  ['href'=>'/admin/index.php','label'=>'Dashboard','icon'=>'🏠'],
  ['href'=>'/admin/quiz_selfesteem.php','label'=>'Buat Kuis • Self-Esteem','icon'=>'🧠'],
  ['href'=>'/admin/quiz_social.php','label'=>'Buat Kuis • Kecemasan Sosial','icon'=>'😰'],
  ['href'=>'/admin/quizzes.php','label'=>'Kuis Terbaru','icon'=>'📝'],
  ['href'=>'/admin/admin_accounts.php','label'=>'Daftar Akun Admin','icon'=>'🛡️'],
  ['href'=>'/admin/user_accounts.php','label'=>'Daftar Akun User','icon'=>'👤'],
];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<aside class="ec-sidebar">
  <div class="ec-brand">EmoCare Admin</div>
  <nav class="ec-nav">
    <?php foreach ($menu as $item): 
      $active = ($path === $item['href']) ? 'active' : '';
    ?>
    <a class="ec-link <?= $active ?>" href="<?= htmlspecialchars($item['href']) ?>">
      <span class="ec-ico"><?= $item['icon'] ?></span>
      <span><?= htmlspecialchars($item['label']) ?></span>
    </a>
    <?php endforeach; ?>
  </nav>
</aside>
