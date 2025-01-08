<?php
require_once '../config/mysqli.php';
require_once '../middleware/auth.php';

redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        // First check if user exists and get their ID
        $stmt = $mysqli->prepare("SELECT users.id, users.password FROM users WHERE users.mail = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($user = $result->fetch_assoc()) {
            // Now check if they have a valid beta code
            $stmt = $mysqli->prepare("SELECT id FROM nova_beta_codes WHERE users_id = ? AND claimed_at IS NOT NULL");
            $stmt->bind_param("i", $user['id']);
            $stmt->execute();
            $beta_result = $stmt->get_result();
            
            if ($beta_result->num_rows === 0) {
                $error = 'No valid beta code found for this account';
            } else if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                
                // Update online status and last login with Unix timestamp
                $timestamp = time();
                $updateStmt = $mysqli->prepare("UPDATE users SET online = '1', last_login = ? WHERE id = ?");
                $updateStmt->bind_param("ii", $timestamp, $user['id']);
                $updateStmt->execute();
                
                header('Location: me');
                exit;
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}

$onlineUsers = getOnlineUsers();
?>
<!DOCTYPE html>
<html>
<head>
    <title>HabRPG - Login</title>
    <link href="/assets/css/theme.css" rel="stylesheet" type="text/css" />
   
</head>
<body>
    <img src="https://habrpg.com/img/logo.gif" alt="HABRPG" class="logo">
    
    <div class="container">
        <h1>Login</h1>
        <p class="online-count"><?php echo $onlineUsers; ?> citizens exploring</p>
        
        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="Enter your email" required>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <p class="footer-text">Don't have an account? <a href="/register">Sign up</a></p>
    </div>
    
    <footer>
        <p class="footer-text">Archangel 2</p>
    </footer>
</body>
</html>