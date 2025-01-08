<?php
require_once '../config/mysqli.php';
require_once '../middleware/auth.php';

if (file_exists('../.env')) {
    $dotenv = parse_ini_file('../.env', true);
} else {
    die("Environment file not found.");
}

requireLogin();
requireBetaCode();

// Generate random 28 char auth ticket
$auth_ticket = bin2hex(random_bytes(14)); // 14 bytes = 28 hex chars

// Update user's auth ticket
$stmt = $mysqli->prepare("UPDATE users SET auth_ticket = ? WHERE id = ?");
$stmt->bind_param("si", $auth_ticket, $_SESSION['user_id']);
$stmt->execute();

// Get iframe URL from environment variables
$iframeUrl = isset($dotenv['IFRAME_URL']) ? $dotenv['IFRAME_URL'] : 'about:blank';
?>
<!DOCTYPE html>
<html>
<head>
    <title>HabRPG - Enter Hotel</title>
    <link href="/assets/css/theme.css" rel="stylesheet" type="text/css" />
    <style>
        html, body, iframe {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 8px;
            background-color: var(--bg-dark);
        }
    </style>
</head>
<body>
    <iframe src="<?php echo htmlspecialchars($iframeUrl . '?sso=' . $auth_ticket); ?>" allowfullscreen></iframe>
</body>
</html>
