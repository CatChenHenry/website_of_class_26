<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (strpos($uri, '..') !== false) {
    http_response_code(400);
    exit('Bad Request');
}

$requestedFile = __DIR__ . $uri;
if ($uri !== '/' && php_sapi_name() === 'cli-server') {
    $staticExtensions = ['css', 'js', 'jpg', 'jpeg', 'png', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'map'];
    $ext = strtolower(pathinfo($uri, PATHINFO_EXTENSION));
    if (in_array($ext, $staticExtensions) && is_file($requestedFile)) {
        return false;
    }
}

require __DIR__ . '/index.php';
