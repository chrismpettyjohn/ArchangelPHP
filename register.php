<?php
require_once 'config/mysqli.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $beta_code = $_POST['beta_code'] ?? '';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    // Validate beta code
    if (empty($beta_code)) {
        $errors['beta_code'] = 'Beta code is required';
    } else {
        $stmt = $mysqli->prepare("SELECT id, claimed_at FROM nova_beta_codes WHERE code = ?");
        $stmt->bind_param("s", $beta_code);
        $stmt->execute();
        $beta_result = $stmt->get_result();
        
        if ($beta_result->num_rows === 0) {
            $errors['beta_code'] = 'Invalid beta code';
        } else {
            $beta_data = $beta_result->fetch_assoc();
            if ($beta_data['claimed_at'] !== null) {
                $errors['beta_code'] = 'Beta code already claimed';
            }
        }
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email address';
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE mail = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors['email'] = 'Email already taken';
        }
    }
    
    // Validate username
    if (empty($username)) {
        $errors['username'] = 'Username is required';
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors['username'] = 'Username already taken';
        }
    }
    
    // Validate passwords
    if ($password !== $password_confirm) {
        $errors['password'] = 'Passwords do not match';
    }
    
    // If no errors, create the user and update beta code
    if (empty($errors)) {
        $mysqli->begin_transaction();
        try {
            // Insert user
            $current_time = time();
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $mysqli->prepare("INSERT INTO users (mail, username, password, online, account_created, ip_register, ip_current) VALUES (?, ?, ?, 1, ?, ?, ?)");
            $stmt->bind_param("sssiss", $email, $username, $hashed_password, $current_time, $ip_address, $ip_address);
            $stmt->execute();
            $user_id = $mysqli->insert_id;
            
            // Update beta code
            $stmt = $mysqli->prepare("UPDATE nova_beta_codes SET claimed_at = ?, users_id = ? WHERE code = ?");
            $stmt->bind_param("iis", $current_time, $user_id, $beta_code);
            $stmt->execute();
            
            $mysqli->commit();
            
            $_SESSION['user_id'] = $user_id;
            header('Location: me.php');
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            $errors['general'] = 'Registration failed: ' . $e->getMessage();
        }
    }
}

$onlineUsers = getOnlineUsers();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - ArchangelPHP</title>
    <style>
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Register</h1>
    <p>Online Users: <?php echo $onlineUsers; ?></p>
    
    <?php if (!empty($errors['general'])): ?>
        <p class="error"><?php echo htmlspecialchars($errors['general']); ?></p>
    <?php endif; ?>
    
    <form method="POST">
        <div>
            <label>Email:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            <?php if (!empty($errors['email'])): ?>
                <span class="error"><?php echo htmlspecialchars($errors['email']); ?></span>
            <?php endif; ?>
        </div>
        
        <div>
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            <?php if (!empty($errors['username'])): ?>
                <span class="error"><?php echo htmlspecialchars($errors['username']); ?></span>
            <?php endif; ?>
        </div>
        
        <div>
            <label>Password:</label>
            <input type="password" name="password" required>
            <?php if (!empty($errors['password'])): ?>
                <span class="error"><?php echo htmlspecialchars($errors['password']); ?></span>
            <?php endif; ?>
        </div>
        
        <div>
            <label>Confirm Password:</label>
            <input type="password" name="password_confirm" required>
        </div>
        
        <div>
            <label>Beta Code:</label>
            <input type="text" name="beta_code" value="<?php echo htmlspecialchars($_POST['beta_code'] ?? ''); ?>" required>
            <?php if (!empty($errors['beta_code'])): ?>
                <span class="error"><?php echo htmlspecialchars($errors['beta_code']); ?></span>
            <?php endif; ?>
        </div>
        
        <button type="submit">Register</button>
    </form>
    
    <p><a href="login.php">Back to Login</a></p>
</body>
</html>