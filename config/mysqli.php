<?php
session_start();

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (!file_exists($envFile)) {
    die('.env file not found');
}

$env = parse_ini_file($envFile);
if ($env === false) {
    die('.env file could not be parsed');
}

// Create mysqli connection
$mysqli = new mysqli(
    $env['DB_HOST'],
    $env['DB_USER'],
    $env['DB_PASS'],
    $env['DB_NAME'],
    $env['DB_PORT']
);

if ($mysqli->connect_error) {
    die('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
}

// Helper functions
function getOnlineUsers() {
    global $mysqli;
    $result = $mysqli->query("SELECT COUNT(*) as count FROM users WHERE online = 1");
    $row = $result->fetch_assoc();
    return $row['count'];
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    global $mysqli;
    $stmt = $mysqli->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}