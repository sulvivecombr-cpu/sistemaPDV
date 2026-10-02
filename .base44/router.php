<?php
// Keep the original /pdv links working while exposing the app at the preview root.
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if ($path === '/') {
    header('Location: /login.php');
    return true;
}
if ($path === '/pdv' || $path === '/pdv/') {
    header('Location: /dash.php');
    return true;
}
if (strpos($path, '/pdv/') === 0) {
    header('Location: ' . substr($_SERVER['REQUEST_URI'], 4), true, 307);
    return true;
}
if (strpos($path, '/.base44/') === 0 || preg_match('/\.(?:json|lock|md|yml)$/i', $path) && $path !== '/manifest.json') {
    http_response_code(404);
    return true;
}
return false;
