<?php
// ============================================================
//  لوحة التحكم — الثانوية التأهيلية عمرو بن العاص
//  admin.php  |  يعمل مع أي استضافة PHP 7+
// ============================================================

session_start();

// ——— كلمة سر بسيطة (غيّرها!) ———
define('ADMIN_PASS', 'amrou2026');
define('NEWS_FILE',  __DIR__ . '/news.json');
define('UPLOADS_DIR', __DIR__ . '/imgs/');

// ——— تسجيل الدخول / الخروج ———
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_pass'])) {
    if ($_POST['login_pass'] === ADMIN_PASS) {
        $_SESSION['logged'] = true;
        header('Location: admin.php'); exit;
    }
    $login_error = 'كلمة السر غير صحيحة';
}
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php'); exit;
}

$logged = !empty($_SESSION['logged']);

// ——— قراءة / كتابة JSON ———
function readNews(): array {
    if (!file_exists(NEWS_FILE)) file_put_contents(NEWS_FILE, '[]');
    return json_decode(file_get_contents(NEWS_FILE), true) ?: [];
}
function writeNews(array $data): void {
    file_put_contents(NEWS_FILE, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}
function nextId(array $data): int {
    return $data ? max(array_column($data, 'id')) + 1 : 1;
}

// ——— رفع الصورة ———
function handleUpload(): string {
    if (empty($_FILES['image']['tmp_name'])) return '';
    $ext  = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext, $allowed)) return '';
    if (!is_dir(UPLOADS_DIR)) mkdir(UPLOADS_DIR, 0755, true);
    $name = 'news_' . time() . '_' . rand(100,999) . '.' . $ext;
    move_uploaded_file($_FILES['image']['tmp_name'], UPLOADS_DIR . $name);
    return 'imgs/' . $name;
}

// ——— AJAX API (JSON) ———
if ($logged && isset($_GET['api'])) {
    header('Content-Type: application/json; charset=utf-8');
    $news = readNews();

    switch ($_GET['api']) {

        case 'list':
            echo json_encode(['ok'=>true,'data'=>array_reverse($news)]);
            break;

        case 'save':   // إضافة أو تعديل
            $id    = (int)($_POST['id'] ?? 0);
            $image = handleUpload();
            if (!$image) $image = trim($_POST['image_url'] ?? '');

            $item = [
                'id'         => $id ?: nextId($news),
                'title'      => trim($_POST['title']    ?? ''),
                'category'   => trim($_POST['category'] ?? ''),
                'date'       => trim($_POST['date']     ?? ''),
                'excerpt'    => trim($_POST['excerpt']  ?? ''),
                'content'    => trim($_POST['content']  ?? ''),
                'image'      => $image,
                'status'     => ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published',
                'created_at' => date('Y-m-d'),
            ];

            if (!$item['title']) { echo json_encode(['ok'=>false,'msg'=>'العنوان مطلوب']); break; }

            if ($id) {
                $idx = array_search($id, array_column($news,'id'));
                if ($idx !== false) {
                    // حافظ على الصورة القديمة إن لم ترفع صورة جديدة
                    if (!$image) $item['image'] = $news[$idx]['image'] ?? '';
                    $item['created_at'] = $news[$idx]['created_at'] ?? date('Y-m-d');
                    $news[$idx] = $item;
                }
            } else {
                $news[] = $item;
            }
            writeNews($news);
            echo json_encode(['ok'=>true,'id'=>$item['id'],'msg'=>$id?'تم التعديل':'تمت الإضافة']);
            break;

        case 'delete':
            $id   = (int)($_POST['id'] ?? 0);
            $news = array_values(array_filter($news, fn($n) => $n['id'] !== $id));
            writeNews($news);
            echo json_encode(['ok'=>true]);
            break;

        case 'toggle':
            $id  = (int)($_POST['id'] ?? 0);
            $idx = array_search($id, array_column($news,'id'));
            if ($idx !== false) {
                $news[$idx]['status'] = $news[$idx]['status'] === 'published' ? 'draft' : 'published';
                writeNews($news);
                echo json_encode(['ok'=>true,'status'=>$news[$idx]['status']]);
            }
            break;

        case 'get':
            $id  = (int)($_GET['id'] ?? 0);
            $idx = array_search($id, array_column($news,'id'));
            echo $idx !== false
                ? json_encode(['ok'=>true,'data'=>$news[$idx]])
                : json_encode(['ok'=>false]);
            break;

        case 'stats':
            $total = count($news);
            $pub   = count(array_filter($news, fn($n)=>$n['status']==='published'));
            echo json_encode(['ok'=>true,'total'=>$total,'published'=>$pub,'draft'=>$total-$pub]);
            break;
    }
    exit;
}

// ——— HTML ———
?><!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>لوحة التحكم — عمرو بن العاص</title>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Noto+Kufi+Arabic:wght@300;400;600;700&display=swap" rel="stylesheet"/>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --r:#B5202C;--r2:#8B1520;--g:#C9A84C;--g2:#E8C97A;
  --k:#0E0B08;--s1:#181210;--s2:#211A16;--s3:#2C2420;
  --bd:rgba(201,168,76,.15);--bd2:rgba(201,168,76,.3);
  --tx:rgba(255,255,255,.85);--tx2:rgba(255,255,255,.45);
  --green:#2ECC71;--blue:#3498DB;--orange:#E67E22;
  --sw:260px;
}
html{scroll-behavior:smooth}
body{font-family:'Noto Kufi Arabic',sans-serif;background:var(--k);color:var(--tx);min-height:100vh}
::-webkit-scrollbar{width:5px}::-webkit-scrollbar-track{background:#080604}::-webkit-scrollbar-thumb{background:var(--g);border-radius:3px}

/* LOGIN */
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;background:radial-gradient(ellipse at 50% 0%,rgba(181,32,44,.15),transparent 60%)}
.login-box{background:var(--s1);border:1px solid var(--bd2);border-radius:18px;padding:42px 40px;width:360px;text-align:center;box-shadow:0 24px 80px rgba(0,0,0,.6)}
.login-logo{width:60px;height:60px;background:var(--r);border:2px solid var(--g);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 20px;box-shadow:0 6px 20px rgba(181,32,44,.4)}
.login-title{font-family:'Amiri',serif;font-size:22px;color:#fff;margin-bottom:6px}
.login-sub{font-size:12px;color:var(--tx2);margin-bottom:28px;letter-spacing:.5px}
.login-input{width:100%;background:var(--s3);border:1px solid rgba(201,168,76,.2);border-radius:10px;padding:13px 16px;color:var(--tx);font-size:15px;outline:none;text-align:center;letter-spacing:4px;font-family:monospace;transition:border-color .2s}
.login-input:focus{border-color:var(--g)}
.login-btn{width:100%;margin-top:14px;background:var(--g);color:var(--k);border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:700;cursor:pointer;font-family:'Noto Kufi Arabic',sans-serif;transition:all .2s}
.login-btn:hover{background:var(--g2)}
.login-err{color:#e77;font-size:13px;margin-top:10px}

/* LAYOUT */
.sidebar{position:fixed;top:0;right:0;width:var(--sw);height:100vh;background:var(--s1);border-left:1px solid var(--bd);display:flex;flex-direction:column;z-index:100}
.sh-head{padding:22px 18px 18px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:12px}
.sh-logo{width:40px;height:40px;background:var(--r);border:2px solid var(--g);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:18px;box-shadow:0 4px 14px rgba(181,32,44,.4);flex-shrink:0}
.sh-title{font-family:'Amiri',serif;font-size:13px;color:var(--g);font-weight:700;line-height:1.3}
.sh-sub{font-size:10px;color:var(--tx2);margin-top:1px}
.nav-sec{padding:16px 14px 6px;font-size:10px;letter-spacing:3px;text-transform:uppercase;color:rgba(201,168,76,.38);font-weight:600}
.nav-a{display:flex;align-items:center;gap:11px;padding:10px 15px;margin:1px 8px;border-radius:8px;cursor:pointer;color:var(--tx2);font-size:13px;transition:all .2s;text-decoration:none;border:1px solid transparent}
.nav-a:hover{background:rgba(201,168,76,.06);color:var(--tx)}
.nav-a.active{background:linear-gradient(135deg,rgba(181,32,44,.18),rgba(201,168,76,.08));color:var(--g);border-color:var(--bd)}
.nav-a .ni{font-size:15px;width:20px;text-align:center;flex-shrink:0}
.nav-badge{margin-right:auto;background:var(--r);color:#fff;font-size:10px;padding:1px 7px;border-radius:10px;font-weight:700}
.sh-foot{margin-top:auto;padding:14px;border-top:1px solid var(--bd);font-size:10px;color:var(--tx2);text-align:center;line-height:1.6}

.main{margin-right:var(--sw);min-height:100vh;display:flex;flex-direction:column}
.topbar{background:var(--s1);border-bottom:1px solid var(--bd);padding:0 28px;height:62px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:50}
.topbar-t{font-family:'Amiri',serif;font-size:19px;color:#fff}
.topbar-t span{color:var(--g)}
.topbar-acts{display:flex;gap:10px;align-items:center}

/* BUTTONS */
.btn{display:inline-flex;align-items:center;gap:7px;padding:9px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;font-family:'Noto Kufi Arabic',sans-serif;transition:all .2s;text-decoration:none}
.btn-gold{background:var(--g);color:var(--k)}.btn-gold:hover{background:var(--g2);transform:translateY(-1px);box-shadow:0 6px 20px rgba(201,168,76,.28)}
.btn-outline{background:transparent;color:var(--tx);border:1px solid var(--bd2)}.btn-outline:hover{background:rgba(201,168,76,.06);border-color:var(--g)}
.btn-red{background:var(--r);color:#fff}.btn-red:hover{background:var(--r2)}
.btn-sm{padding:6px 13px;font-size:12px;border-radius:6px}

/* PAGES */
.page{padding:28px;display:none;flex:1}
.page.active{display:block}

/* STATS */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:26px}
.stat-card{background:var(--s2);border:1px solid var(--bd);border-radius:12px;padding:18px 20px;position:relative;overflow:hidden;transition:all .25s}
.stat-card:hover{border-color:var(--bd2);transform:translateY(-2px)}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--cc,var(--g))}
.stat-ico{font-size:26px;margin-bottom:8px}
.stat-v{font-family:'Amiri',serif;font-size:34px;color:var(--cc,var(--g));line-height:1;font-weight:700}
.stat-l{font-size:11px;color:var(--tx2);margin-top:4px}

/* FORM */
.form-card{background:var(--s2);border:1px solid var(--bd);border-radius:14px;padding:26px;margin-bottom:24px}
.form-card-title{font-family:'Amiri',serif;font-size:18px;color:var(--g);margin-bottom:20px;padding-bottom:14px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:9px}
.fg{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.fg-full{grid-column:1/-1}
.fl{font-size:12px;color:var(--tx2);margin-bottom:6px;font-weight:600;letter-spacing:.3px}
.fi,.fs,.ft{background:var(--s3);border:1px solid rgba(201,168,76,.18);border-radius:8px;padding:10px 14px;color:var(--tx);font-family:'Noto Kufi Arabic',sans-serif;font-size:14px;transition:border-color .2s;outline:none;width:100%}
.fi:focus,.fs:focus,.ft:focus{border-color:var(--g);box-shadow:0 0 0 3px rgba(201,168,76,.08)}
.ft{resize:vertical;min-height:100px;line-height:1.7}
option{background:#1C1410}
.form-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:18px;padding-top:18px;border-top:1px solid var(--bd)}

/* UPLOAD ZONE */
.upload-zone{border:2px dashed rgba(201,168,76,.22);border-radius:10px;padding:18px;text-align:center;cursor:pointer;transition:all .2s;font-size:13px;color:var(--tx2)}
.upload-zone:hover,.upload-zone.drag{border-color:var(--g);background:rgba(201,168,76,.04);color:var(--g)}
.upload-zone input{display:none}
.upload-preview{width:100%;max-height:140px;object-fit:cover;border-radius:8px;margin-top:10px;display:none}

/* TABLE */
.tbl-wrap{background:var(--s2);border:1px solid var(--bd);border-radius:14px;overflow:hidden}
.tbl-toolbar{display:flex;align-items:center;gap:10px;padding:14px 18px;border-bottom:1px solid var(--bd)}
.search{flex:1;background:var(--s3);border:1px solid rgba(201,168,76,.14);border-radius:8px;padding:8px 14px;color:var(--tx);font-size:13px;outline:none;font-family:'Noto Kufi Arabic',sans-serif}
.search:focus{border-color:var(--g)}
.chip{padding:6px 14px;border-radius:20px;border:1px solid var(--bd);background:transparent;color:var(--tx2);font-size:12px;cursor:pointer;transition:all .2s;font-family:'Noto Kufi Arabic',sans-serif}
.chip:hover,.chip.on{background:rgba(201,168,76,.1);border-color:var(--g);color:var(--g)}
table{width:100%;border-collapse:collapse}
thead th{padding:11px 16px;font-size:10px;letter-spacing:2px;text-transform:uppercase;color:var(--tx2);text-align:right;background:var(--s3);border-bottom:1px solid var(--bd);font-weight:600}
tbody tr{border-bottom:1px solid rgba(201,168,76,.06);transition:background .15s}
tbody tr:hover{background:rgba(201,168,76,.03)}
tbody tr:last-child{border-bottom:none}
td{padding:13px 16px;font-size:13px;vertical-align:middle}
.thumb{width:50px;height:38px;border-radius:6px;object-fit:cover;background:var(--s3);border:1px solid var(--bd);font-size:20px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0}
.thumb img{width:100%;height:100%;object-fit:cover}
.news-cell{display:flex;align-items:center;gap:11px}
.news-cell-text .t1{font-weight:600;color:#fff;font-size:13px;line-height:1.4}
.news-cell-text .t2{font-size:11px;color:var(--tx2);margin-top:1px}
.tag{display:inline-flex;align-items:center;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700}
.tag-1{background:rgba(201,168,76,.12);color:var(--g);border:1px solid rgba(201,168,76,.25)}
.tag-2{background:rgba(52,152,219,.12);color:#3498DB;border:1px solid rgba(52,152,219,.25)}
.tag-3{background:rgba(46,204,113,.12);color:#2ECC71;border:1px solid rgba(46,204,113,.25)}
.tag-4{background:rgba(181,32,44,.12);color:#e77;border:1px solid rgba(181,32,44,.25)}
.tag-5{background:rgba(255,255,255,.07);color:rgba(255,255,255,.45);border:1px solid rgba(255,255,255,.1)}
.dot-pub{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--green);box-shadow:0 0 5px rgba(46,204,113,.5);margin-left:5px}
.dot-dft{display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--orange);margin-left:5px}
.acts{display:flex;gap:6px}
.act{width:30px;height:30px;border-radius:7px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:14px;transition:all .2s}
.act-e{background:rgba(52,152,219,.12);color:#3498DB}.act-e:hover{background:rgba(52,152,219,.25)}
.act-d{background:rgba(181,32,44,.1);color:#e77}.act-d:hover{background:rgba(181,32,44,.22)}
.act-t{background:rgba(46,204,113,.1);color:var(--green)}.act-t:hover{background:rgba(46,204,113,.22)}

/* MODALS */
.overlay{display:none;position:fixed;inset:0;z-index:200;background:rgba(6,3,1,.88);backdrop-filter:blur(8px);align-items:center;justify-content:center;padding:20px}
.overlay.open{display:flex}
.modal{background:var(--s2);border:1px solid var(--bd2);border-radius:16px;padding:28px;max-width:440px;width:100%;position:relative;animation:popIn .28s ease}
@keyframes popIn{from{opacity:0;transform:scale(.95) translateY(14px)}to{opacity:1;transform:none}}
.modal-close{position:absolute;top:12px;left:12px;background:rgba(255,255,255,.07);border:none;color:var(--tx2);width:32px;height:32px;border-radius:7px;cursor:pointer;font-size:15px;display:flex;align-items:center;justify-content:center;transition:.2s}
.modal-close:hover{color:#fff;background:var(--r)}
.modal-icon{font-size:40px;margin-bottom:12px;text-align:center}
.modal-title{font-family:'Amiri',serif;font-size:19px;color:#fff;text-align:center;margin-bottom:7px}
.modal-sub{font-size:13px;color:var(--tx2);text-align:center;margin-bottom:22px;line-height:1.6}
.modal-acts{display:flex;gap:10px;justify-content:center}

/* PREVIEW OVERLAY */
.prev-overlay{display:none;position:fixed;inset:0;z-index:300;background:rgba(6,3,1,.92);backdrop-filter:blur(10px);align-items:center;justify-content:center;padding:16px;overflow-y:auto}
.prev-overlay.open{display:flex}
.prev-box{background:var(--s2);border:1px solid var(--bd2);border-radius:16px;max-width:600px;width:100%;position:relative;animation:popIn .3s ease;overflow:hidden}
.prev-img{width:100%;height:220px;object-fit:cover}
.prev-img-ph{width:100%;height:200px;background:var(--s3);display:flex;align-items:center;justify-content:center;font-size:56px}
.prev-body{padding:24px}
.prev-meta{display:flex;align-items:center;gap:8px;margin-bottom:12px}
.prev-title{font-family:'Amiri',serif;font-size:22px;color:#fff;line-height:1.4;margin-bottom:10px}
.prev-divider{width:38px;height:2px;background:linear-gradient(90deg,var(--r),var(--g));border-radius:2px;margin-bottom:14px}
.prev-text{font-size:14px;color:rgba(255,255,255,.6);line-height:1.9;white-space:pre-wrap}
.prev-close-btn{position:absolute;top:12px;left:12px;background:rgba(0,0,0,.55);border:none;color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;font-size:17px;display:flex;align-items:center;justify-content:center;transition:.2s;backdrop-filter:blur(4px)}
.prev-close-btn:hover{background:var(--r)}

/* TOAST */
.toasts{position:fixed;bottom:20px;left:20px;z-index:999;display:flex;flex-direction:column;gap:9px}
.toast{background:var(--s2);border:1px solid var(--bd2);border-radius:10px;padding:13px 17px;font-size:13px;display:flex;align-items:center;gap:9px;box-shadow:0 8px 28px rgba(0,0,0,.5);animation:slideUp .3s ease;min-width:240px}
@keyframes slideUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
.toast.ok{border-color:rgba(46,204,113,.35)}
.toast.err{border-color:rgba(181,32,44,.35)}

/* RESPONSIVE */
@media(max-width:900px){
  .sidebar{transform:translateX(100%);transition:transform .3s}
  .sidebar.open{transform:translateX(0)}
  .main{margin-right:0}
  .stats-row{grid-template-columns:1fr 1fr}
  .fg{grid-template-columns:1fr}.fg-full{grid-column:1}
  .page{padding:16px}
  .topbar{padding:0 14px}
}
@media(max-width:540px){
  .stats-row{grid-template-columns:1fr}
  table{font-size:12px}
}
.mob-btn{display:none;background:var(--s2);border:1px solid var(--bd);border-radius:8px;width:36px;height:36px;align-items:center;justify-content:center;cursor:pointer;font-size:18px}
@media(max-width:900px){.mob-btn{display:flex}}
.help{background:rgba(201,168,76,.05);border:1px solid rgba(201,168,76,.13);border-radius:9px;padding:11px 14px;font-size:12px;color:rgba(255,255,255,.4);line-height:1.7;display:flex;gap:9px;align-items:flex-start;margin-top:8px}
.help .hi{flex-shrink:0;font-size:15px}
</style>
</head>
<body>

<?php if (!$logged): ?>
<!-- ===== LOGIN ===== -->
<div class="login-wrap">
  <div class="login-box">
    <div class="login-logo">🏫</div>
    <div class="login-title">الثانوية التأهيلية عمرو بن العاص</div>
    <div class="login-sub">لوحة التحكم الإدارية</div>
    <form method="POST">
      <input class="login-input" type="password" name="login_pass" placeholder="••••••••" autofocus/>
      <button class="login-btn" type="submit">دخول ←</button>
      <?php if(!empty($login_error)):?>
        <div class="login-err">⚠️ <?= htmlspecialchars($login_error) ?></div>
      <?php endif;?>
    </form>
  </div>
</div>

<?php else: ?>
<!-- ===== DASHBOARD ===== -->

<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
  <div class="sh-head">
    <div class="sh-logo">🏫</div>
    <div>
      <div class="sh-title">عمرو بن العاص</div>
      <div class="sh-sub">لوحة التحكم</div>
    </div>
  </div>
  <div class="nav-sec">القائمة</div>
  <a class="nav-a active" onclick="showPage('home',this)" href="#">
    <span class="ni">🏠</span> الرئيسية
  </a>
  <a class="nav-a" onclick="showPage('manage',this)" href="#">
    <span class="ni">📰</span> الأخبار
    <span class="nav-badge" id="nb">0</span>
  </a>
  <a class="nav-a" onclick="showPage('add',this);prepareAdd()" href="#">
    <span class="ni">➕</span> خبر جديد
  </a>
  <div class="nav-sec">الموقع</div>
  <a class="nav-a" href="index.php" target="_blank">
    <span class="ni">🌐</span> عرض الموقع
  </a>
  <a class="nav-a" href="?logout" onclick="return confirm('تسجيل الخروج؟')">
    <span class="ni">🚪</span> خروج
  </a>
  <div class="sh-foot">
    الثانوية التأهيلية عمرو بن العاص<br/>
    <span style="color:rgba(201,168,76,.35)">لوحة التحكم v2.0</span>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div style="display:flex;align-items:center;gap:12px">
      <button class="mob-btn" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
      <div class="topbar-t" id="top-title">لوحة <span>التحكم</span></div>
    </div>
    <div class="topbar-acts">
      <button class="btn btn-outline btn-sm" onclick="showPage('manage',null);setNav('manage')">📋 الأخبار</button>
      <button class="btn btn-gold btn-sm" onclick="showPage('add',null);setNav('add');prepareAdd()">✚ خبر جديد</button>
    </div>
  </div>

  <!-- PAGE: HOME -->
  <div class="page active" id="pg-home">
    <div class="stats-row">
      <div class="stat-card" style="--cc:#C9A84C"><div class="stat-ico">📰</div><div class="stat-v" id="st-total">—</div><div class="stat-l">إجمالي الأخبار</div></div>
      <div class="stat-card" style="--cc:#2ECC71"><div class="stat-ico">✅</div><div class="stat-v" id="st-pub">—</div><div class="stat-l">منشورة</div></div>
      <div class="stat-card" style="--cc:#E67E22"><div class="stat-ico">📝</div><div class="stat-v" id="st-dft">—</div><div class="stat-l">مسودات</div></div>
      <div class="stat-card" style="--cc:#3498DB"><div class="stat-ico">🌐</div><div class="stat-v">PHP</div><div class="stat-l">النظام</div></div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px">
      <div class="form-card" style="margin:0">
        <div class="form-card-title">🕐 آخر الأخبار المضافة</div>
        <div id="recent-list" style="display:flex;flex-direction:column;gap:10px"></div>
      </div>
      <div class="form-card" style="margin:0">
        <div class="form-card-title">🚀 إجراءات سريعة</div>
        <div style="display:flex;flex-direction:column;gap:10px">
          <button class="btn btn-gold" onclick="showPage('add',null);setNav('add');prepareAdd()" style="justify-content:center;padding:13px">✚ إضافة خبر جديد</button>
          <button class="btn btn-outline" onclick="showPage('manage',null);setNav('manage')" style="justify-content:center;padding:13px">📋 إدارة جميع الأخبار</button>
          <a class="btn btn-outline" href="index.php" target="_blank" style="justify-content:center;padding:13px">🌐 معاينة الموقع</a>
        </div>
        <div class="help" style="margin-top:16px">
          <span class="hi">✅</span>
          <span>الأخبار تظهر على الموقع <strong style="color:var(--g)">فوراً</strong> عند إضافتها أو تعديلها — بدون أي نسخ أو لصق!</span>
        </div>
      </div>
    </div>
  </div>

  <!-- PAGE: ADD / EDIT -->
  <div class="page" id="pg-add">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
      <h2 id="add-title" style="font-family:'Amiri',serif;color:#fff;font-size:22px">➕ إضافة خبر جديد</h2>
    </div>
    <div class="form-card">
      <div class="form-card-title" id="form-section-title">📰 بيانات الخبر</div>
      <input type="hidden" id="f-id" value=""/>
      <div class="fg">
        <div class="fg-full">
          <div class="fl">عنوان الخبر *</div>
          <input class="fi" id="f-title" type="text" placeholder="أدخل عنوان الخبر..." maxlength="100"/>
        </div>
        <div>
          <div class="fl">التصنيف *</div>
          <select class="fs" id="f-cat">
            <option value="">— اختر —</option>
            <option>تميز</option><option>وعي رقمي</option><option>رياضة ذهنية</option>
            <option>تربوي</option><option>ثقافي</option><option>رياضي</option>
            <option>إداري</option><option>أخرى</option>
          </select>
        </div>
        <div>
          <div class="fl">التاريخ *</div>
          <input class="fi" id="f-date" type="text" placeholder="مثال: أبريل ٢٠٢٦"/>
        </div>
        <div class="fg-full">
          <div class="fl">مقتطف الخبر (يظهر في البطاقة)</div>
          <textarea class="ft" id="f-excerpt" rows="3" placeholder="ملخص قصير..." maxlength="280"></textarea>
        </div>
        <div class="fg-full">
          <div class="fl">محتوى الخبر الكامل (يظهر عند "اقرأ المزيد")</div>
          <textarea class="ft" id="f-content" rows="7" placeholder="اكتب تفاصيل الخبر هنا..."></textarea>
        </div>
        <div>
          <div class="fl">صورة الخبر</div>
          <div class="upload-zone" id="drop-zone" onclick="document.getElementById('f-img-file').click()">
            <input type="file" id="f-img-file" accept="image/*" onchange="handleFile(this)"/>
            <div id="drop-text">🖼️ اضغط لرفع صورة أو اسحبها هنا</div>
            <img id="img-preview" class="upload-preview"/>
          </div>
          <div class="fl" style="margin-top:10px">أو رابط الصورة (URL)</div>
          <input class="fi" id="f-img-url" type="text" placeholder="imgs/img5.jpg أو https://..." oninput="previewFromUrl(this.value)"/>
        </div>
        <div>
          <div class="fl">حالة النشر</div>
          <select class="fs" id="f-status">
            <option value="published">✅ منشور (يظهر فوراً على الموقع)</option>
            <option value="draft">📝 مسودة (لا يظهر)</option>
          </select>
          <div class="help" style="margin-top:10px">
            <span class="hi">💡</span>
            <span>عند الحفظ، يظهر الخبر تلقائياً على الموقع الرئيسي بدون أي إجراء إضافي.</span>
          </div>
        </div>
      </div>
      <div class="form-actions">
        <button class="btn btn-outline" onclick="resetForm()">🔄 إعادة تعيين</button>
        <button class="btn btn-gold" onclick="saveNews()">💾 حفظ ونشر فوراً</button>
      </div>
    </div>
  </div>

  <!-- PAGE: MANAGE -->
  <div class="page" id="pg-manage">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
      <h2 style="font-family:'Amiri',serif;color:#fff;font-size:22px">📋 إدارة الأخبار</h2>
      <button class="btn btn-gold btn-sm" onclick="showPage('add',null);setNav('add');prepareAdd()">✚ خبر جديد</button>
    </div>
    <div class="tbl-wrap">
      <div class="tbl-toolbar">
        <input class="search" type="text" placeholder="🔍 بحث..." id="srch" oninput="filterTable()"/>
        <button class="chip on" onclick="setFilter('all',this)">الكل</button>
        <button class="chip"   onclick="setFilter('published',this)">منشور</button>
        <button class="chip"   onclick="setFilter('draft',this)">مسودة</button>
      </div>
      <table>
        <thead>
          <tr>
            <th style="width:46px">#</th>
            <th>الخبر</th>
            <th style="width:110px">التصنيف</th>
            <th style="width:110px">التاريخ</th>
            <th style="width:80px">الحالة</th>
            <th style="width:130px">إجراءات</th>
          </tr>
        </thead>
        <tbody id="tbl-body"></tbody>
      </table>
      <div id="empty" style="display:none;text-align:center;padding:50px;color:var(--tx2)">
        <div style="font-size:44px;margin-bottom:12px;opacity:.3">📭</div>
        <div style="font-family:'Amiri',serif;font-size:18px">لا توجد أخبار</div>
      </div>
    </div>
  </div>

</div><!-- /main -->

<!-- DELETE CONFIRM -->
<div class="overlay" id="del-ov">
  <div class="modal">
    <button class="modal-close" onclick="closeOverlay('del-ov')">✕</button>
    <div class="modal-icon">🗑️</div>
    <div class="modal-title">تأكيد الحذف</div>
    <div class="modal-sub">هل أنت متأكد من حذف هذا الخبر؟<br/>سيختفي فوراً من الموقع ولا يمكن التراجع.</div>
    <div class="modal-acts">
      <button class="btn btn-outline" onclick="closeOverlay('del-ov')">إلغاء</button>
      <button class="btn btn-red" id="del-confirm-btn">🗑️ حذف</button>
    </div>
  </div>
</div>

<!-- PREVIEW -->
<div class="prev-overlay" id="prev-ov">
  <div class="prev-box">
    <button class="prev-close-btn" onclick="closeOverlay('prev-ov')">✕</button>
    <div id="prev-img-wrap"></div>
    <div class="prev-body">
      <div class="prev-meta">
        <span class="tag tag-5" id="pv-cat">—</span>
        <span style="font-size:11px;color:var(--tx2);margin-right:8px" id="pv-date">—</span>
      </div>
      <div class="prev-title" id="pv-title">—</div>
      <div class="prev-divider"></div>
      <div class="prev-text" id="pv-content">—</div>
    </div>
  </div>
</div>

<!-- TOASTS -->
<div class="toasts" id="toasts"></div>

<?php endif; ?>

<script>
// ===== STATE =====
let allNews = [];
let tableFilter = 'all';
let deleteId = null;
let uploadedFile = null;

// ===== API =====
async function api(action, body={}, method='POST') {
  try {
    let url = `admin.php?api=${action}`;
    let opts = { method };
    if (method === 'POST') {
      const fd = new FormData();
      for (const [k,v] of Object.entries(body)) fd.append(k, v);
      if (action === 'save' && uploadedFile) fd.append('image', uploadedFile);
      opts.body = fd;
    }
    const r = await fetch(url, opts);
    return r.json();
  } catch(e) { return {ok:false, msg:'خطأ في الاتصال'}; }
}

// ===== LOAD DATA =====
async function loadAll() {
  const r = await api('list','','GET');
  if (r.ok) { allNews = r.data; renderTable(); renderRecent(); }
  const s = await api('stats','','GET');
  if (s.ok) {
    document.getElementById('st-total').textContent = s.total;
    document.getElementById('st-pub').textContent   = s.published;
    document.getElementById('st-dft').textContent   = s.draft;
    document.getElementById('nb').textContent       = s.total;
  }
}

// ===== RENDER TABLE =====
const tagClass = { 'تميز':'tag-1','وعي رقمي':'tag-2','رياضة ذهنية':'tag-3','تربوي':'tag-4' };

function renderTable() {
  const srch = (document.getElementById('srch')?.value||'').toLowerCase();
  const list = allNews.filter(n => {
    if (tableFilter !== 'all' && n.status !== tableFilter) return false;
    if (srch && !n.title.includes(srch) && !n.category.includes(srch)) return false;
    return true;
  });
  const body = document.getElementById('tbl-body');
  document.getElementById('empty').style.display = list.length ? 'none' : 'block';
  body.innerHTML = list.map((n,i) => `
    <tr>
      <td style="color:var(--tx2);font-size:12px">${i+1}</td>
      <td>
        <div class="news-cell">
          <div class="thumb">${n.image ? `<img src="${esc(n.image)}" onerror="this.parentElement.innerHTML='📰'"/>` : '📰'}</div>
          <div class="news-cell-text">
            <div class="t1">${esc(n.title)}</div>
            <div class="t2">${esc((n.excerpt||'').substring(0,55))}${(n.excerpt||'').length>55?'...':''}</div>
          </div>
        </div>
      </td>
      <td><span class="tag ${tagClass[n.category]||'tag-5'}">${esc(n.category)}</span></td>
      <td style="font-size:12px;color:var(--tx2)">${esc(n.date)}</td>
      <td>
        <span class="${n.status==='published'?'dot-pub':'dot-dft'}"></span>
        <span style="font-size:12px;color:${n.status==='published'?'var(--green)':'var(--orange)'}">
          ${n.status==='published'?'منشور':'مسودة'}
        </span>
      </td>
      <td>
        <div class="acts">
          <button class="act act-t" title="معاينة" onclick="previewNews(${n.id})">👁️</button>
          <button class="act act-e" title="تعديل"  onclick="editNews(${n.id})">✏️</button>
          <button class="act act-t" title="تبديل"  onclick="toggleStatus(${n.id})">${n.status==='published'?'⏸':'▶️'}</button>
          <button class="act act-d" title="حذف"    onclick="askDelete(${n.id})">🗑️</button>
        </div>
      </td>
    </tr>
  `).join('');
}

function renderRecent() {
  const el = document.getElementById('recent-list');
  const items = allNews.slice(0,4);
  el.innerHTML = items.length ? items.map(n=>`
    <div style="display:flex;align-items:center;gap:10px;padding:10px;background:var(--s3);border-radius:8px;border:1px solid var(--bd)">
      <div class="thumb" style="width:42px;height:34px">${n.image?`<img src="${esc(n.image)}" onerror="this.parentElement.innerHTML='📰'"/>`:'📰'}</div>
      <div style="flex:1;min-width:0">
        <div style="font-size:13px;color:#fff;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${esc(n.title)}</div>
        <div style="font-size:11px;color:var(--tx2)">${esc(n.category)} · ${esc(n.date)}</div>
      </div>
      <span class="${n.status==='published'?'dot-pub':'dot-dft'}"></span>
    </div>
  `).join('') : '<div style="color:var(--tx2);font-size:13px;text-align:center;padding:20px">لا توجد أخبار بعد</div>';
}

function filterTable() { renderTable(); }
function setFilter(f, el) {
  tableFilter = f;
  document.querySelectorAll('.chip').forEach(c=>c.classList.remove('on'));
  el.classList.add('on');
  renderTable();
}

// ===== ADD / EDIT =====
function prepareAdd() {
  document.getElementById('f-id').value = '';
  document.getElementById('f-title').value = '';
  document.getElementById('f-cat').value = '';
  document.getElementById('f-date').value = '';
  document.getElementById('f-excerpt').value = '';
  document.getElementById('f-content').value = '';
  document.getElementById('f-img-url').value = '';
  document.getElementById('f-status').value = 'published';
  document.getElementById('img-preview').style.display = 'none';
  document.getElementById('drop-text').textContent = '🖼️ اضغط لرفع صورة أو اسحبها هنا';
  document.getElementById('add-title').textContent = '➕ إضافة خبر جديد';
  uploadedFile = null;
}

async function editNews(id) {
  const r = await api(`get&id=${id}`,'','GET');
  if (!r.ok) return;
  const n = r.data;
  document.getElementById('f-id').value      = n.id;
  document.getElementById('f-title').value   = n.title;
  document.getElementById('f-cat').value     = n.category;
  document.getElementById('f-date').value    = n.date;
  document.getElementById('f-excerpt').value = n.excerpt;
  document.getElementById('f-content').value = n.content;
  document.getElementById('f-img-url').value = n.image||'';
  document.getElementById('f-status').value  = n.status;
  document.getElementById('add-title').textContent = '✏️ تعديل الخبر';
  if (n.image) {
    const prev = document.getElementById('img-preview');
    prev.src = n.image; prev.style.display = 'block';
    document.getElementById('drop-text').textContent = '✅ صورة محددة';
  }
  uploadedFile = null;
  showPage('add',null); setNav('add');
  toast('✏️ جاهز للتعديل','');
}

async function saveNews() {
  const id      = document.getElementById('f-id').value;
  const title   = document.getElementById('f-title').value.trim();
  const cat     = document.getElementById('f-cat').value;
  const date    = document.getElementById('f-date').value.trim();
  const excerpt = document.getElementById('f-excerpt').value.trim();
  const content = document.getElementById('f-content').value.trim();
  const imgUrl  = document.getElementById('f-img-url').value.trim();
  const status  = document.getElementById('f-status').value;

  if (!title) { toast('❌ العنوان مطلوب','err'); document.getElementById('f-title').focus(); return; }
  if (!cat)   { toast('❌ اختر التصنيف','err'); return; }
  if (!date)  { toast('❌ التاريخ مطلوب','err'); return; }

  const body = { id, title, category:cat, date, excerpt, content, image_url:imgUrl, status };
  const r = await api('save', body);
  if (r.ok) {
    toast(`✅ ${r.msg} — يظهر الآن على الموقع!`, 'ok');
    await loadAll();
    showPage('manage',null); setNav('manage');
  } else {
    toast('❌ '+(r.msg||'حدث خطأ'),'err');
  }
}

function resetForm() { prepareAdd(); toast('🔄 تم إعادة التعيين',''); }

// ===== DELETE =====
function askDelete(id) {
  deleteId = id;
  document.getElementById('del-ov').classList.add('open');
}
document.getElementById('del-confirm-btn').onclick = async function() {
  if (!deleteId) return;
  const r = await api('delete', {id: deleteId});
  if (r.ok) {
    toast('🗑️ تم الحذف من الموقع','ok');
    await loadAll();
  }
  closeOverlay('del-ov');
  deleteId = null;
};

// ===== TOGGLE =====
async function toggleStatus(id) {
  const r = await api('toggle', {id});
  if (r.ok) {
    toast(r.status === 'published' ? '✅ تم النشر — يظهر على الموقع' : '⏸ تم إخفاؤه من الموقع', 'ok');
    await loadAll();
  }
}

// ===== PREVIEW =====
function previewNews(id) {
  const n = allNews.find(x=>x.id===id);
  if (!n) return;
  document.getElementById('pv-title').textContent   = n.title;
  document.getElementById('pv-cat').textContent     = n.category;
  document.getElementById('pv-date').textContent    = n.date;
  document.getElementById('pv-content').textContent = n.content;
  const wrap = document.getElementById('prev-img-wrap');
  wrap.innerHTML = n.image
    ? `<img class="prev-img" src="${esc(n.image)}" onerror="this.parentElement.innerHTML='<div class=prev-img-ph>🖼️</div>'"/>`
    : `<div class="prev-img-ph">🖼️</div>`;
  document.getElementById('prev-ov').classList.add('open');
}

// ===== UPLOAD =====
function handleFile(input) {
  const file = input.files[0];
  if (!file) return;
  uploadedFile = file;
  const reader = new FileReader();
  reader.onload = e => {
    const prev = document.getElementById('img-preview');
    prev.src = e.target.result; prev.style.display = 'block';
    document.getElementById('drop-text').textContent = '✅ '+file.name;
    document.getElementById('f-img-url').value = '';
  };
  reader.readAsDataURL(file);
}
function previewFromUrl(url) {
  if (!url) return;
  const prev = document.getElementById('img-preview');
  prev.src = url; prev.style.display = 'block';
  uploadedFile = null;
}

// Drag and drop
const dz = document.getElementById('drop-zone');
if (dz) {
  dz.addEventListener('dragover', e => { e.preventDefault(); dz.classList.add('drag'); });
  dz.addEventListener('dragleave', () => dz.classList.remove('drag'));
  dz.addEventListener('drop', e => {
    e.preventDefault(); dz.classList.remove('drag');
    const file = e.dataTransfer.files[0];
    if (file && file.type.startsWith('image/')) {
      const inp = document.getElementById('f-img-file');
      const dt = new DataTransfer(); dt.items.add(file);
      inp.files = dt.files; handleFile(inp);
    }
  });
}

// ===== NAVIGATION =====
const pageMap = { home:'pg-home', add:'pg-add', manage:'pg-manage' };
const titleMap = { home:'لوحة <span>التحكم</span>', add:'إضافة <span>خبر جديد</span>', manage:'إدارة <span>الأخبار</span>' };
function showPage(name, el) {
  Object.values(pageMap).forEach(id => { const p=document.getElementById(id); if(p) p.classList.remove('active'); });
  const pg = document.getElementById(pageMap[name]);
  if (pg) pg.classList.add('active');
  document.getElementById('top-title').innerHTML = titleMap[name]||'';
  if (el) setNav(name, el);
  document.getElementById('sidebar').classList.remove('open');
}
function setNav(name, el) {
  document.querySelectorAll('.nav-a').forEach(a=>a.classList.remove('active'));
  if (el) { el.classList.add('active'); return; }
  const navLinks = document.querySelectorAll('.nav-a');
  const map2 = { home:0, manage:1, add:2 };
  if (navLinks[map2[name]]) navLinks[map2[name]].classList.add('active');
}

// ===== OVERLAYS =====
function closeOverlay(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.overlay,.prev-overlay').forEach(ov=>{
  ov.addEventListener('click',e=>{ if(e.target===ov) ov.classList.remove('open'); });
});
document.addEventListener('keydown',e=>{ if(e.key==='Escape') { closeOverlay('del-ov'); closeOverlay('prev-ov'); }});

// ===== TOAST =====
function toast(msg, type) {
  const c = document.getElementById('toasts');
  const t = document.createElement('div');
  t.className = 'toast '+(type||'');
  t.innerHTML = `<span>${msg}</span>`;
  c.appendChild(t);
  setTimeout(()=>{ t.style.opacity='0'; t.style.transition='opacity .4s'; setTimeout(()=>t.remove(),400); }, 3200);
}

// ===== HELPERS =====
function esc(s) { const d=document.createElement('div'); d.textContent=s||''; return d.innerHTML; }

// ===== INIT =====
<?php if ($logged): ?>
loadAll();
<?php endif; ?>
</script>
</body>
</html>
