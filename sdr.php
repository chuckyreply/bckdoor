<?php
session_start();
/**
 * auditor_all_in_one.php
 * PHP All-in-one CVE Auditor + Optional Terminal
 * Dark Bootstrap UI, many CVEs, system checks, JSON export
 *
 * Usage:
 *  - Place in web root and visit with browser.
 *  - Terminal disabled by default; enable with ?allow_shell=1
 *
 * Security note (again): run only on lab VMs you control.
 */

// ------------ Helpers -------------
function run($cmd) {
    // trim and return output (stderr merged)
    return trim(shell_exec($cmd . ' 2>&1'));
}
function exists_bin($bin) {
    return trim(shell_exec("which " . escapeshellarg($bin) . " 2>/dev/null")) !== '';
}
function semver_extract($s) {
    if (!$s) return null;
    // try common patterns: 1.2.3 or 1.2.3p2 or 1.2.3-ubuntu...
    if (preg_match('/([0-9]+\\.[0-9]+(\\.[0-9]+)?(p[0-9]+)?)/', $s, $m)) return $m[1];
    return null;
}
function ver_between($v, $min, $max) {
    if (!$v) return false;
    // use version_compare; allow p-suffixes by removing non-numeric tails for crude check when needed
    return (version_compare($v, $min, '>=') && version_compare($v, $max, '<='));
}
function badgeClass($status) {
    switch($status) {
        case 'Vulnerable': return 'badge-vuln';
        case 'Safe': return 'badge-safe';
        default: return 'badge-check';
    }
}

// ------------ System info & checks -------------
$kernel_full   = php_uname('r');
$kernel_ver    = semver_extract($kernel_full);
$sudo_raw      = run('sudo --version 2>/dev/null | head -n1');
$sudo_ver      = semver_extract($sudo_raw);
$pkexec_raw    = run('pkexec --version 2>/dev/null');
$pkexec_ver    = semver_extract($pkexec_raw);
$openssl_raw   = run('openssl version 2>/dev/null');
$openssl_ver   = semver_extract($openssl_raw);
$sshd_raw      = run('sshd -V 2>&1 | head -n1');
$sshd_ver      = null;
if (preg_match('/OpenSSH[_ ]([0-9]+\\.[0-9]+p[0-9]+)/', $sshd_raw, $m)) $sshd_ver = $m[1];
$xz_raw        = run('xz --version 2>/dev/null | head -n1');
$xz_ver        = semver_extract($xz_raw);
$php_system    = function_exists('system');
$has_gcc       = exists_bin('gcc');
$has_python3   = exists_bin('python3') || exists_bin('python');
$has_pkexec    = exists_bin('pkexec');

// ------------ CVE Catalog (extended) -------------
// Each entry: cve, product, desc, check => closure that returns 'Vulnerable'|'Safe'|'Check Required'
$cves = [];

// Helper to add common entries quickly
$add = function($cve,$product,$desc,$closure) use (&$cves) {
    $cves[] = ['cve'=>$cve,'product'=>$product,'desc'=>$desc,'check'=>$closure];
};

// sudo related
$add('CVE-2021-3156','sudo','Baron Samedit (heap overflow)', function() use ($sudo_ver) {
    if (!$sudo_ver) return 'Check Required';
    // patched at 1.8.32 or 1.9.5p2
    return (version_compare($sudo_ver,'1.8.32','<') || (substr($sudo_ver,0,3)=='1.9' && version_compare($sudo_ver,'1.9.5p2','<'))) ? 'Vulnerable' : 'Safe';
});
$add('CVE-2019-14287','sudo','Runas bypass (UID 0 bypass in some sudo versions)', function() use ($sudo_ver) {
    if (!$sudo_ver) return 'Check Required';
    return (version_compare($sudo_ver,'1.8.28','<')) ? 'Vulnerable' : 'Safe';
});
$add('CVE-2017-1000367','sudo','Environment variable handling issue', function() use ($sudo_ver) {
    if (!$sudo_ver) return 'Check Required';
    return (version_compare($sudo_ver,'1.8.21','<')) ? 'Vulnerable' : 'Safe';
});

// polkit / pkexec
$add('CVE-2021-4034','polkit/pkexec','PwnKit local privilege escalation', function() use ($has_pkexec, $pkexec_raw) {
    if (!$has_pkexec) return 'Safe';
    // some distros don't show pkexec version; treat presence as check required but warn
    if (!$pkexec_raw) return 'Check Required';
    $v = semver_extract($pkexec_raw);
    if (!$v) return 'Check Required';
    return version_compare($v,'0.120','<') ? 'Vulnerable' : 'Safe';
});
$add('CVE-2021-3560','polkit','PolicyKit D-Bus authorization bypass (manual check advised)', function(){ return 'Check Required'; });

// kernel family
$add('CVE-2016-5195','kernel','Dirty COW (historical)', function() use ($kernel_ver) {
    if (!$kernel_ver) return 'Check Required';
    // generally affects old 2.x/3.x kernels, here mark legacy kernels
    return (version_compare($kernel_ver,'4.8.3','<')) ? 'Vulnerable' : 'Safe';
});
$add('CVE-2022-0847','kernel','Dirty Pipe (Linux 5.8 - 5.16.x)', function() use ($kernel_ver) {
    if (!$kernel_ver) return 'Check Required';
    return ver_between($kernel_ver,'5.8.0','5.16.20') ? 'Vulnerable' : 'Safe';
});
$add('CVE-2023-32233','kernel','Netfilter use-after-free (local privesc)', function() use ($kernel_ver) {
    if (!$kernel_ver) return 'Check Required';
    return ver_between($kernel_ver,'5.10.0','6.3.1') ? 'Vulnerable' : 'Safe';
});
$add('CVE-2023-0386','kernel','OverlayFS privilege escalation', function() use ($kernel_ver) {
    if (!$kernel_ver) return 'Check Required';
    return ver_between($kernel_ver,'5.1.0','6.1.0') ? 'Vulnerable' : 'Safe';
});
$add('CVE-2019-11477','kernel','SACK Panic (DoS)', function() use ($kernel_ver) {
    return 'Check Required'; // practical detection needs tcp config; leave manual
});

// openssl / crypto
$add('CVE-2014-0160','openssl','Heartbleed (OpenSSL 1.0.1 - 1.0.1f)', function() use ($openssl_ver) {
    if (!$openssl_ver) return 'Check Required';
    return ver_between($openssl_ver,'1.0.1','1.0.1f') ? 'Vulnerable' : 'Safe';
});
$add('CVE-2022-3602','openssl','OpenSSL X.509 parsing overflow (3.0.x)', function() use ($openssl_ver) {
    if (!$openssl_ver) return 'Check Required';
    return ver_between($openssl_ver,'3.0.0','3.0.6') ? 'Vulnerable' : 'Safe';
});
$add('CVE-2022-3786','openssl','OpenSSL X.509 DoS (3.0.x)', function() use ($openssl_ver) {
    if (!$openssl_ver) return 'Check Required';
    return ver_between($openssl_ver,'3.0.0','3.0.6') ? 'Vulnerable' : 'Safe';
});

// openssh
$add('CVE-2023-48795','openssh','Terrapin attack (SSH downgrade/cryptographic)', function() use ($sshd_ver) {
    if (!$sshd_ver) return 'Check Required';
    // crude check: if version is 8.7p1 specifically flagged in some advisories
    return (strpos($sshd_ver,'8.7') !== false) ? 'Vulnerable' : 'Safe';
});
$add('CVE-2018-15473','openssh','Username enumeration via timing', function() use ($sshd_ver) {
    if (!$sshd_ver) return 'Check Required';
    return 'Check Required';
});

// web servers & frameworks
$add('CVE-2021-41773','apache','Apache 2.4.49 path traversal/RCE', function() {
    // detecting apache version needs apache2 -v or httpd -v; keep manual
    return 'Check Required';
});
$add('CVE-2021-42013','apache','Apache 2.4.50 RCE (related)', function(){ return 'Check Required'; });
$add('CVE-2017-5638','struts2','Apache Struts2 Jakarta multipart RCE', function(){ return 'Check Required'; });
$add('CVE-2021-44228','log4j','Log4Shell (Log4j2 JNDI)', function(){ return 'Check Required'; });

// php / fpm / nginx specific
$add('CVE-2019-11043','php-fpm','PHP-FPM remote code execution via Nginx', function(){ return 'Check Required'; });

// databases
$add('CVE-2016-6662','mysql','MySQL config injection (older)', function(){ return 'Check Required'; });

// xz-utils supply chain
$add('CVE-2024-3094','xz-utils','Backdoor in xz-utils 5.6.0/5.6.1 (supply chain)', function() use ($xz_ver) {
    if (!$xz_ver) return 'Check Required';
    return ver_between($xz_ver,'5.6.0','5.6.1') ? 'Vulnerable' : 'Safe';
});
$add('CVE-2020-15999','freetype','FreeType font parsing RCE (example)', function(){ return 'Check Required'; });

// samba / smb
$add('CVE-2017-0144','smb','EternalBlue (SMBv1)', function(){ return 'Check Required'; });

// Add any additional CVEs you want similarly...
// For brevity we added a representative set; you can expand $cves easily.

// ------------ Action: export JSON -------------
if (isset($_GET['action']) && $_GET['action']==='export-json') {
    $out = [];
    foreach ($cves as $e) {
        $out[] = [
            'cve'=>$e['cve'],
            'product'=>$e['product'],
            'desc'=>$e['desc'],
            'status'=>$e['check'](
                ($e['product']=='sudo'?$sudo_ver:
                ($e['product']=='polkit/pkexec'?$pkexec_raw:
                ($e['product']=='openssl'?$openssl_ver:
                ($e['product']=='openssh'?$sshd_ver:
                ($e['product']=='xz-utils'?$xz_ver:
                ($e['product']=='kernel'?$kernel_ver:'')))))))
        ];
    }
    header('Content-Type: application/json');
    echo json_encode(['scanned_at'=>date('c'),'system'=>[
        'kernel'=>$kernel_full,'sudo'=>$sudo_raw,'pkexec'=>$pkexec_raw,'openssl'=>$openssl_raw,'sshd'=>$sshd_raw,'xz'=>$xz_raw
    ],'results'=>$out], JSON_PRETTY_PRINT);
    exit;
}

// ------------ Terminal (optional) -------------
// Enabled only if ?allow_shell=1 to reduce accidental exposure
$allow_shell = (isset($_GET['allow_shell']) && $_GET['allow_shell']=='1');
$terminal_output = '';
if ($allow_shell && isset($_POST['command'])) {
    $cmd = $_POST['command'];
    // store history in session
    if (!isset($_SESSION['term_history'])) $_SESSION['term_history'] = [];
    $_SESSION['term_history'][] = $cmd;
    $terminal_output = run($cmd);
}

// ------------ Render HTML -------------
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>All-in-one CVE Auditor</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body { background:#0f1115; color:#fff; }
  .card { background:#16171b; border:1px solid #222; }
  .table thead th { color:#fff; border-bottom:1px solid #222; }
  .badge-safe { background:#198754; }
  .badge-vuln { background:#dc3545; }
  .badge-check { background:#ffc107; color:#000; }
  .term { background:#000; color:#0f0; font-family:monospace; padding:12px; border-radius:6px; height:300px; overflow:auto; white-space:pre-wrap; }
  a.cve-link { color:#9ad; text-decoration:none; }
  .small-muted { color:#fff; font-size:.9rem; }
  .sys-badge { margin-right:.5rem; margin-bottom:.4rem; display:inline-block; padding:.4rem .6rem; border-radius:.4rem; background:#222; color:#fff; }
  h5 { color:#fff;}
</style>
</head>
<body>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-start mb-3">
    <h3>🔐 CVE Auditor — All in One</h3>
    <div>
      <a href="?action=export-json" class="btn btn-outline-light btn-sm">Export JSON</a>
      <?php if ($allow_shell): ?>
        <a href="?" class="btn btn-outline-danger btn-sm">Disable Shell</a>
      <?php else: ?>
        <a href="?allow_shell=1" class="btn btn-outline-success btn-sm">Enable Shell (unsafe)</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- System summary -->
  <div class="card mb-3 p-3">
    <div class="row">
      <div class="col-md-8">
        <div class="small-muted"><strong>System Info</strong></div>
        <div class="small-muted">Kernel: <?= htmlspecialchars($kernel_full) ?></div>
        <div class="small-muted">sudo: <?= htmlspecialchars($sudo_raw ?: 'not found') ?> &nbsp; pkexec: <?= htmlspecialchars($pkexec_raw ?: 'not found') ?></div>
        <div class="small-muted">openssl: <?= htmlspecialchars($openssl_raw ?: 'not found') ?> &nbsp; sshd: <?= htmlspecialchars($sshd_raw ?: 'not found') ?></div>
      </div>
      <div class="col-md-4 text-end">
        <div class="small-muted">System Checks</div>
        <div style="margin-top:.5rem;">
          <span class="sys-badge <?= $php_system? 'badge-safe':'badge-vuln' ?>">PHP system(): <?= $php_system? 'ON':'OFF'?></span>
          <span class="sys-badge <?= $has_gcc? 'badge-safe':'badge-vuln' ?>">gcc: <?= $has_gcc? 'ON':'OFF'?></span>
          <span class="sys-badge <?= $has_python3? 'badge-safe':'badge-vuln' ?>">python3: <?= $has_python3? 'ON':'OFF'?></span>
          <span class="sys-badge <?= $has_pkexec? 'badge-safe':'badge-vuln' ?>">pkexec: <?= $has_pkexec? 'ON':'OFF'?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- CVE table -->
  <div class="card mb-3 p-3">
    <h5>Potential CVEs Detected</h5>
    <p class="small-muted">Automated detection based on presence/version heuristics. This is advisory-only—some distros backport patches without changing version strings.</p>
    <div class="table-responsive">
      <table class="table table-dark table-hover align-middle">
        <thead>
          <tr>
            <th style="min-width:130px">CVE</th>
            <th>Product</th>
            <th>Description</th>
            <th style="min-width:120px">Status</th>
            <th>Notes</th>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach ($cves as $e) {
                // pass appropriate param to check closure
                $prod = $e['product'];
                if ($prod==='sudo') $status = $e['check']($sudo_ver);
                elseif ($prod==='polkit/pkexec' || $prod==='polkit') $status = $e['check']($pkexec_ver);
                elseif ($prod==='openssl') $status = $e['check']($openssl_ver);
                elseif ($prod==='openssh') $status = $e['check']($sshd_ver);
                elseif ($prod==='xz-utils') $status = $e['check']($xz_ver);
                elseif ($prod==='kernel') $status = $e['check']($kernel_ver);
                else $status = $e['check']();
                $cls = badgeClass($status);
                $cve_link = 'https://cve.mitre.org/cgi-bin/cvename.cgi?name=' . urlencode($e['cve']);
                echo '<tr>';
                echo '<td><a class="cve-link" href="'.$cve_link.'" target="_blank">'.$e['cve'].'</a></td>';
                echo '<td>'.htmlspecialchars($e['product']).'</td>';
                echo '<td>'.htmlspecialchars($e['desc']).'</td>';
                echo '<td><span class="'.$cls.' px-2 py-1">'.htmlspecialchars($status).'</span></td>';
                echo '<td class="small-muted">Auto-check (heuristic)</td>';
                echo '</tr>';
            }
          ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Terminal -->
  <div class="card p-3">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <h5 class="mb-0">Interactive Terminal <?php if(!$allow_shell) echo '<span class="small-muted"> (disabled)</span>'; ?></h5>
      <div class="small-muted">Use only on lab. Terminal executes server-side commands.</div>
    </div>

    <?php if ($allow_shell): ?>
      <form method="post" class="mb-2">
        <div class="input-group">
          <input name="command" type="text" class="form-control" placeholder="Enter command (e.g., uname -a)" required>
          <button class="btn btn-outline-light" type="submit">Run</button>
        </div>
      </form>
      <div class="term"><?= htmlspecialchars($terminal_output ?: (isset($_SESSION['term_history'])? "History:\n- " . implode("\n- ", array_slice($_SESSION['term_history'],-20)) : "No output")) ?></div>
    <?php else: ?>
      <div class="p-3 small-muted">Terminal is disabled. To enable, append <code>?allow_shell=1</code> to URL. (Only enable in isolated lab).</div>
    <?php endif; ?>
  </div>

  <footer class="text-center small-muted mt-3">Generated: <?= date('Y-m-d H:i:s'); ?> • Advisory-only detector — no exploits included</footer>
</div>
</body>
</html>
