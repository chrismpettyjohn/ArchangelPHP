<?php
$request = trim($_SERVER['REQUEST_URI'], '/');
$filename = __DIR__ . '/views/' . basename($request) . '.php';

if ($request == '') {
    include(__DIR__ . '/views/login.php');
    exit;
}

if (file_exists($filename)) {
    include $filename;
    exit;
} else {
    http_response_code(404);
    echo "File not found.";
}
?>
