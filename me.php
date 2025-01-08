<?php
require_once 'config/mysqli.php';
require_once 'middleware/auth.php';

requireLogin();

$user = getCurrentUser();
$onlineUsers = getOnlineUsers();

// Handle logout
if (isset($_POST['logout'])) {
    $stmt = $mysqli->prepare("UPDATE users SET online = '0' WHERE id = ?");
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
    <title>My Profile - Archangel 2</title>
    <style>
    :root {
        --bg-dark: #141a24;
        --bg-card: #1b2432;
        --text-primary: #ffffff;
        --text-secondary: #8b95a4;
        --accent-blue: #45a7ff;
        --border-color: #2a3241;
    }

    body {
        background-color: var(--bg-dark);
        color: var(--text-primary);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        margin: 0;
        padding: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .logo {
        margin: 2rem 0;
        max-width: 200px;
    }

    .container {
        background-color: var(--bg-card);
        padding: 2rem;
        border-radius: 8px;
        width: 100%;
        max-width: 400px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .online-count {
        color: var(--text-secondary);
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .error {
        color: #ff4b4b;
        margin: 0.5rem 0;
        font-size: 0.9rem;
    }

    .footer-text {
        color: var(--text-secondary);
        text-align: center;
        margin-top: 1rem;
    }

    .profile-info {
        margin-bottom: 2rem;
    }

    .profile-info h2 {
        color: var(--text-primary);
        margin: 0 0 1rem 0;
        text-align: center;
    }

    .profile-info p {
        color: var(--text-secondary);
        margin: 0.5rem 0;
        padding: 0.5rem 0;
        border-bottom: 1px solid var(--border-color);
    }

    .profile-actions {
        display: flex;
        gap: 1rem;
    }

    .profile-actions form {
        flex: 1;
    }

    .btn {
        width: 100%;
        padding: 0.75rem;
        border: none;
        border-radius: 4px;
        background-color: var(--accent-blue);
        color: white;
        font-weight: 500;
        cursor: pointer;
        transition: opacity 0.2s;
        text-decoration: none;
        display: inline-block;
        text-align: center;
        box-sizing: border-box;
    }

    .btn:hover {
        opacity: 0.9;
    }

    .btn-secondary {
        background-color: var(--border-color);
    }
    </style>
</head>
<body>
    <img src="https://habrpg.com/img/logo.gif" alt="HABRPG" class="logo">
    
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
            <a href="enter.php" class="btn">Enter Hotel</a>
        </div>
    </div>
    
    <footer>
        <p class="footer-text">Archangel 2</p>
    </footer>
</body>
</html>