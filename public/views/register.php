<?php
require_once '../config/mysqli.php';
require_once '../middleware/auth.php';

redirectIfLoggedIn();

// [Previous PHP code remains the same until the HTML part]
$onlineUsers = getOnlineUsers();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Archangel 2</title>
    <link href="/assets/css/theme.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <img src="https://habrpg.com/img/logo.gif" alt="HABRPG" class="logo">
    
    <div class="container">
        <h1>Register</h1>
        <p class="online-count"><?php echo $onlineUsers; ?> citizens exploring</p>
        
        <?php if (!empty($errors['general'])): ?>
            <p class="error"><?php echo htmlspecialchars($errors['general']); ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email address</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="Enter your email" required>
                <?php if (!empty($errors['email'])): ?>
                    <p class="error"><?php echo htmlspecialchars($errors['email']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" placeholder="Choose a username" required>
                <?php if (!empty($errors['username'])): ?>
                    <p class="error"><?php echo htmlspecialchars($errors['username']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Create a password" required>
                <p class="hint-text">Password must be at least 8 characters long and include a mix of letters, numbers, and symbols</p>
                <?php if (!empty($errors['password'])): ?>
                    <p class="error"><?php echo htmlspecialchars($errors['password']); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="password_confirm" placeholder="Confirm your password" required>
            </div>
            
            <div class="form-group">
                <label>Beta Code</label>
                <input type="text" name="beta_code" value="<?php echo htmlspecialchars($_POST['beta_code'] ?? ''); ?>" placeholder="Enter your beta code" required>
                <?php if (!empty($errors['beta_code'])): ?>
                    <p class="error"><?php echo htmlspecialchars($errors['beta_code']); ?></p>
                <?php endif; ?>
            </div>
            
            <button type="submit" class="btn">Register</button>
        </form>
        
        <p class="footer-text">Already have an account? <a href="login.php">Login</a></p>
    </div>
    
    <footer>
        <p class="footer-text">Archangel 2</p>
    </footer>
</body>
</html>