<?php
require_once 'config/mysqli.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $beta_code = $_POST['beta_code'] ?? '';
    
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
    
    // If no errors, create the user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        
        $stmt = $mysqli->prepare("INSERT INTO users (mail, username, password, beta_code, online) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("ssss", $email, $username, $hashed_password, $beta_code);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $mysqli->insert_id;
            header('Location: me.php');
            exit;
        } else {
            $errors['general'] = 'Registration failed';
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
        </div>
        
        <button type="submit">Register</button>
    </form>
    
    <p><a href="login.php">Back to Login</a></p>
</body>
</html>