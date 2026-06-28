<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, '..') !== false) {
    http_response_code(400);
    exit('Bad Request');
}

$requestedFile = __DIR__ . $uri;
if ($uri !== '/' && php_sapi_name() === 'cli-server') {
    $staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'map', 'mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'flv', 'wmv', 'm4v', '3gp'];
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    if (in_array($ext, $staticExtensions) && is_file($requestedFile)) {
        // 图片和字体缓存30天，CSS/JS缓存1小时
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'webp'];
        if (in_array($ext, $imageExts)) {
            header('Cache-Control: public, max-age=2592000, immutable');
        } elseif (in_array($ext, ['woff', 'woff2', 'ttf', 'eot'])) {
            header('Cache-Control: public, max-age=2592000, immutable');
        } else {
            header('Cache-Control: public, max-age=3600');
        }
        return false;
    }
}

require __DIR__ . '/index.php';
