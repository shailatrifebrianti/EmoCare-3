<?php
require_once __DIR__ . '/backend/config.php';
require_login();

// helper
function current_user_id(){ return (int)($_SESSION['user']['pengguna_id'] ?? 0); }
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function is_admin(mysqli $db): bool {
  $uid = current_user_id(); if ($uid<=0) return false;
  $st = $db->prepare("SELECT role FROM pengguna WHERE pengguna_id=?"); $st->bind_param('i',$uid);
  $st->execute(); $role = $st->get_result()->fetch_column(); $st->close();
  return $role === 'admin';
}
if (!is_admin($mysqli)) { http_response_code(403); die('Forbidden: Admin only.'); }

// ambil info akun
$uid = current_user_id();
$me = [
  'nama' => $_SESSION['user']['nama'] ?? 'Admin',
  'email'=> $_SESSION['user']['email'] ?? '-',
  'role' => 'admin',
  'created_at' => ''
];
$st = $mysqli->prepare("SELECT nama,email,role,COALESCE(DATE_FORMAT(created_at,'%Y-%m-%d %H:%i'), '') created_at FROM pengguna WHERE pengguna_id=?");
$st->bind_param('i',$uid); $st->execute(); $row=$st->get_result()->fetch_assoc(); $st->close();
if ($row) $me=$row;

// csrf
if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(16));
$CSRF=$_SESSION['csrf'];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Profil Admin • EmoCare</title>
  <link rel="stylesheet" href="css/admin.css">
  <style>
    :root{
      --pink:#f472b6; --pink-100:#ffe4ef; --pink-200:#fce7f3; --pink-300:#f9a8d4; --pink-700:#be185d;
      --bg:#fff1f7; --card:#ffffff; --muted:#6b7280; --bd:#f3d1e1;
    }
    body{background:var(--bg);}
    .layout{max-width:1200px;margin:24px auto;padding:0 16px;display:grid;grid-template-columns:280px 1fr;gap:16px}
    @media(max-width:980px){.layout{grid-template-columns:1fr}}
    /* Sidebar */
    .side{background:var(--card);border:1px solid var(--bd);border-radius:16px;box-shadow:0 2px 10px rgba(244,114,182,.12);overflow:hidden}
    .side-hd{display:flex;align-items:center;gap:10px;padding:14px 16px;border-bottom:1px solid var(--bd);background:linear-gradient(180deg,var(--pink-200),#fff)}
    .appmark{font-weight:800;color:#111827}
    .me{padding:18px 16px;text-align:center}
    .avatar{width:84px;height:84px;border-radius:999px;background:linear-gradient(180deg,#fff,var(--pink-100));border:2px solid var(--pink-300);margin:0 auto 8px;display:grid;place-items:center;font-weight:800;color:var(--pink-700)}
    .me-name{font-weight:800;margin:4px 0}
    .me-role{display:inline-block;border:1px solid var(--pink-300);background:var(--pink-200);color:var(--pink-700);border-radius:999px;padding:2px 10px;font-size:12px}
    .me-meta{color:var(--muted);font-size:12px;margin-top:6px}
    .menu{border-top:1px solid var(--bd);padding:8px}
    .mitem{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:12px;color:#111827;text-decoration:none}
    .mitem.active,.mitem:hover{background:var(--pink-200)}
    /* Main */
    .main{display:flex;flex-direction:column;gap:16px}
    .topbar{display:flex;align-items:center;gap:8px}
    .topbar h1{font-size:22px;margin:0;font-weight:800}
    .topbar .right{margin-left:auto;display:flex;gap:8px}
    .btn{background:var(--pink);color:#fff;border:none;border-radius:12px;padding:10px 14px;font-weight:700;cursor:pointer}
    .btn.ghost{background:#fff;color:var(--pink-700);border:1px solid var(--pink-300)}
    /* Card + Tabs */
    .card{background:var(--card);border:1px solid var(--bd);border-radius:16px;box-shadow:0 2px 10px rgba(244,114,182,.12)}
    .card-hd{padding:12px 16px;border-bottom:1px solid var(--bd);background:linear-gradient(180deg,var(--pink-200),#fff);font-weight:800}
    .tabs{display:flex;gap:4px;padding:8px;border-bottom:1px solid var(--bd);background:#fff;border-radius:16px 16px 0 0}
    .tab{padding:10px 14px;border-radius:12px;cursor:pointer;color:#111827}
    .tab.active{background:var(--pink-200);border:1px solid var(--pink-300);color:#be185d}
    .panel{padding:16px}
    .muted{color:var(--muted);font-size:13px}
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    @media(max-width:800px){.grid-2{grid-template-columns:1fr}}
    .field label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px}
    .inp, textarea{width:100%;border:1px solid var(--bd);border-radius:12px;padding:10px 12px;font-size:14px;background:#fff}
    .section{border:1px dashed var(--bd);border-radius:12px;padding:12px;margin-top:10px}
    .kv{display:grid;grid-template-columns:140px 1fr;row-gap:8px}
    .kv .k{color:var(--muted)}
    .kv .v strong{font-weight:800}
    .pill{display:inline-block;border:1px solid var(--bd);border-radius:999px;padding:4px 8px;background:#fff;font-size:12px}
  </style>
</head>
<body>
  <div class="layout">
    <!-- Sidebar kiri -->
    <aside class="side">
      <div class="side-hd"><div class="appmark">EmoCare</div></div>
      <div class="me">
        <div class="avatar"><?= strtoupper(substr($me['nama'] ?? 'A',0,1)) ?></div>
        <div class="me-name"><?= h($me['nama']) ?></div>
        <div class="me-role"><?= h($me['role']) ?></div>
        <div class="me-meta"><?= h($me['email']) ?></div>
        <div class="me-meta">Dibuat: <?= h($me['created_at'] ?: '-') ?></div>
      </div>
      <nav class="menu">
        <a class="mitem active" href="admin_profile.php">Beranda Profil</a>
        <a class="mitem" href="admin.php">Dashboard Kuis</a>
        <a class="mitem" href="home.php">Home User</a>
      </nav>
    </aside>

    <!-- Konten kanan -->
    <main class="main">
      <div class="topbar">
        <h1>Profil Admin</h1>
        <div class="right">
          <a class="btn ghost" href="admin.php">Dashboard</a>
          <form action="backend/auth_logout.php" method="post" style="margin:0"><button class="btn ghost">Keluar</button></form>
        </div>
      </div>

      <section class="card">
        <div class="tabs" role="tablist" aria-label="Profil Admin Tabs">
          <button class="tab active" id="t1" data-tab="p1" role="tab" aria-selected="true">1. Profil</button>
          
          <button class="tab" id="t3" data-tab="p3" role="tab" aria-selected="false">3. Ganti Kata Sandi</button>
        </div>

        <!-- Panel 1: Profil -->
        <div class="panel" id="p1" role="tabpanel" aria-labelledby="t1">
          <div class="grid-2">
            <div class="section">
              <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                <h3 style="margin:0">Tentang Saya</h3>
                <button class="btn ghost" type="button" onclick="document.getElementById('about').focus()">Ganti Tentang Saya</button>
              </div>
              <div class="field" style="margin-top:8px">
                <label>Deskripsi singkat</label>
                <textarea id="about" class="inp" rows="4" placeholder="Isi tentang saya…"></textarea>
              </div>
            </div>

            <div class="section">
              <div style="display:flex;justify-content:space-between;align-items:center;gap:8px">
                <h3 style="margin:0">Keahlian Saya</h3>
                <button class="btn ghost" type="button" onclick="addSkill()">Tambah Keahlian</button>
              </div>
              <div id="skills" style="margin-top:8px;display:flex;flex-wrap:wrap;gap:8px">
                <span class="pill">Manajemen</span>
                <span class="pill">Coping Support</span>
              </div>
            </div>
          </div>

          <div class="section" style="margin-top:12px">
            <h3 style="margin:0 0 10px">Detail Akun</h3>
            <div class="kv">
              <div class="k">Nama</div><div class="v"><strong><?= h($me['nama']) ?></strong></div>
              <div class="k">Email</div><div class="v"><?= h($me['email']) ?></div>
              <div class="k">Role</div><div class="v"><span class="pill"><?= h($me['role']) ?></span></div>
              <div class="k">Dibuat</div><div class="v"><?= h($me['created_at'] ?: '-') ?></div>
            </div>
          </div>
        </div>

        

        <!-- Panel 3: Ganti Password -->
        <div class="panel" id="p3" role="tabpanel" aria-labelledby="t3" hidden>
          <form action="backend/admin_change_password.php" method="post" class="grid-2">
            <input type="hidden" name="csrf" value="<?= h($CSRF) ?>">
            <div class="field">
              <label>Password saat ini</label>
              <input class="inp" type="password" name="current_password" required>
            </div>
            <div class="field">
              <label>Password baru</label>
              <input class="inp" type="password" name="new_password" required>
            </div>
            <div class="field">
              <label>Konfirmasi password baru</label>
              <input class="inp" type="password" name="confirm_password" required>
            </div>
            <div style="align-self:end">
              <button class="btn" type="submit">Simpan Password</button>
            </div>
          </form>
          <p class="muted" style="margin-top:8px">Pastikan password baru minimal 8 karakter dan kuat.</p>
        </div>
      </section>
    </main>
  </div>

  <script>
    // Tabs
    document.querySelectorAll('.tab').forEach(t=>{
      t.addEventListener('click',()=>{
        document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));
        document.querySelectorAll('.panel').forEach(p=>p.hidden=true);
        t.classList.add('active');
        document.getElementById(t.dataset.tab).hidden=false;
        document.querySelectorAll('.tab').forEach(x=>x.setAttribute('aria-selected', x===t ? 'true' : 'false'));
      });
    });

    // Skills dummy
    function addSkill(){
      const s = prompt('Nama keahlian?'); if(!s) return;
      const span = document.createElement('span'); span.className='pill'; span.textContent=s;
      document.getElementById('skills').appendChild(span);
    }
  </script>
</body>
</html>
