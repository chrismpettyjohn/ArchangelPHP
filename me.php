<?php
require_once 'config/mysqli.php';
require_once 'middleware/auth.php';

requireLogin();

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
    <title>My Profile - ArchangelPHP</title>
    <style>
        .error { color: red; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>My Profile</h1>
    <p>Online Users: <?php echo $onlineUsers; ?></p>
    
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
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