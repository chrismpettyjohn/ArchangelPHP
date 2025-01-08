<?php
require_once 'config/mysqli.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE mail = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                
                // Update online status and last login with Unix timestamp
                $timestamp = time();
                $updateStmt = $mysqli->prepare("UPDATE users SET online = 1, last_login = ? WHERE id = ?");
                $updateStmt->bind_param("ii", $timestamp, $user['id']);
                $updateStmt->execute();
                
                header('Location: me.php');
                exit;
            }
        }
        $error = 'Invalid email or password';
    }
}

$onlineUsers = getOnlineUsers();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - ArchangelPHP</title>
    <style>
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Login</h1>
    <p>Online Users: <?php echo $onlineUsers; ?></p>
    
    <?php if ($error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <div>
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    
    <p><a href="register.php">Register</a></p>
</body>
</html>