<?php
require_once '../config/mysqli.php';
require_once '../middleware/auth.php';

requireLogin();

$user = getCurrentUser();
$onlineUsers = getOnlineUsers();

// Handle logout
if (isset($_POST['logout'])) {
    $stmt = $mysqli->prepare("UPDATE users SET online = '0' WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    
    session_destroy();
    header('Location: login');
    exit;
}

$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'no_beta':
            $error = 'You need a valid beta code to enter the hotel.';
            break;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>HabRPG - My Profile</title>
    <link href="/assets/css/theme.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <img src="/assets/img/logo.png" alt="HABRPG" class="logo">
    
    <div class="container">
        <p class="online-count"><?php echo $onlineUsers; ?> citizens exploring</p>
        
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
            <p>Motto: <?php echo htmlspecialchars($user['motto'] ?? 'No motto set'); ?></p>
            <p>Look: <?php echo htmlspecialchars($user['look'] ?? 'No look set'); ?></p>
            <p>Last Login: <?php echo $user['last_login'] ? date('Y-m-d H:i:s', $user['last_login']) : 'Never'; ?></p>
        </div>
        
        <div class="profile-actions">
            <form method="POST">
                <button type="submit" name="logout" class="btn btn-secondary">Logout</button>
            </form>
            <a href="/enter" class="btn">Enter Hotel</a>
        </div>
    </div>
    
    <footer>
        <p class="footer-text">Archangel 2</p>
    </footer>
</body>
</html>