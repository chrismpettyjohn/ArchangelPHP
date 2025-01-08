<?php
require_once 'config/mysqli.php';
require_once 'middleware/auth.php';

requireLogin();
requireBetaCode();

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
    <title>Enter Hotel - Archangel 2</title>
    <link rel="stylesheet" href="/assets/css/theme.css">
    <style>
        .hotel-container {
            width: 100%;
            height: calc(100vh - 220px);
            margin-top: 1rem;
        }
        iframe {
            width: 100%;
            height: 100%;
            border: none;
            border-radius: 8px;
            background-color: var(--bg-dark);
        }
        .ticket-info {
            background-color: var(--bg-dark);
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
            font-family: monospace;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <img src="https://habrpg.com/img/logo.gif" alt="HABRPG" class="logo">
    
    <div class="container" style="max-width: 90%;">
        <p class="online-count"><?php echo $onlineUsers; ?> citizens exploring</p>
        
        <div class="ticket-info">
            Generated SSO Ticket: <?php echo htmlspecialchars($auth_ticket); ?>
        </div>
        
        <div class="hotel-container">
            <iframe src="about:blank" allowfullscreen></iframe>
        </div>
    </div>
    
    <footer>
        <p class="footer-text">Archangel 2</p>
    </footer>
</body>
</html>