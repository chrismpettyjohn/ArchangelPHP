<?php
require_once 'config/mysqli.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$onlineUsers = getOnlineUsers();

// Handle logout
if (isset($_POST['logout'])) {
    $stmt = $mysqli->prepare("UPDATE users SET online = 0 WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile - ArchangelPHP</title>
</head>
<body>
    <h1>My Profile</h1>
    <p>Online Users: <?php echo $onlineUsers; ?></p>
    
    <div>
        <h2><?php echo htmlspecialchars($user['username']); ?></h2>
        <p>Motto: <?php echo htmlspecialchars($user['motto'] ?? 'No motto set'); ?></p>
        <p>Look: <?php echo htmlspecialchars($user['look'] ?? 'No look set'); ?></p>
        <p>Last Login: <?php echo $user['last_login'] ? date('Y-m-d H:i:s', $user['last_login']) : 'Never'; ?></p>
    </div>
    
    <form method="POST">
        <button type="submit" name="logout">Logout</button>
    </form>
    
    <p><a href="enter.php">Enter Hotel</a></p>
</body>
</html>