<?php
require_once 'config/mysqli.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Generate random 28 char auth ticket
$auth_ticket = bin2hex(random_bytes(14)); // 14 bytes = 28 hex chars

// Update user's auth ticket
$stmt = $mysqli->prepare("UPDATE users SET auth_ticket = ? WHERE id = ?");
$stmt->bind_param("si", $auth_ticket, $_SESSION['user_id']);
$stmt->execute();

$onlineUsers = getOnlineUsers();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enter Hotel - ArchangelPHP</title>
    <style>
        .hotel-container {
            width: 100%;
            height: calc(100vh - 100px);
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
        }
        .ticket-info {
            background: #f5f5f5;
            padding: 10px;
            margin-bottom: 10px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <p>Online Users: <?php echo $onlineUsers; ?></p>
    
    <div class="ticket-info">
        Generated SSO Ticket: <?php echo htmlspecialchars($auth_ticket); ?>
    </div>
    
    <div class="hotel-container">
        <iframe src="about:blank" allowfullscreen></iframe>
    </div>
</body>
</html>