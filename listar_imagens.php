<?php
$dir = 'img/';
$arquivos = [];

if (is_dir($dir)) {
    foreach (scandir($dir) as $file) {
        if (in_array(pathinfo($file, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'webp'])) {
            $arquivos[] = $dir . $file;
        }
    }
}

echo json_encode($arquivos);
