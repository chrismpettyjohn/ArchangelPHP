<?php
require_once '../config/mysqli.php';
require_once '../middleware/auth.php';

redirectIfLoggedIn();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $beta_code = trim($_POST['beta_code'] ?? '');
    $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'];

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    }

    // Validate username
    if (empty($username) || strlen($username) < 3 || strlen($username) > 20) {
        $errors['username'] = 'Username must be between 3 and 20 characters';
    }

    // Check if username exists
    $stmt = $mysqli->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $errors['username'] = 'Username already taken';
    }

    // Validate password
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = 'Password must be at least 8 characters and include letters and numbers';
    }

    if ($password !== $password_confirm) {
        $errors['password'] = 'Passwords do not match';
    }

    // Validate beta code
    $stmt = $mysqli->prepare("SELECT id FROM nova_beta_codes WHERE code = ? AND claimed_at IS NULL");
    $stmt->bind_param("s", $beta_code);
    $stmt->execute();
    if ($stmt->get_result()->num_rows === 0) {
        $errors['beta_code'] = 'Invalid or already used beta code';
    }

    // If no errors, create the account
    if (empty($errors)) {
        $mysqli->begin_transaction();

        try {
            // Create user account
            $current_time = time();
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO users (mail, username, password, account_created, last_login, ip_current, ip_register) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiiis", $email, $username, $password_hash, $current_time, $current_time, $ip_address, $ip_address);
            $stmt->execute();
            $user_id = $mysqli->insert_id;


            // Create player 
            $stmt = $mysqli->prepare("INSERT INTO archangel_players (users_id) VALUES (?)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            // Create player skills
            $stmt = $mysqli->prepare("INSERT INTO archangel_players_skills (users_id) VALUES (?)");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            // Mark beta code as used
            $stmt = $mysqli->prepare("UPDATE nova_beta_codes SET claimed_at = ?, users_id = ? WHERE code = ?");
            $stmt->bind_param("iis", $current_time, $user_id, $beta_code);
            $stmt->execute();

            $mysqli->commit();

            // Log the user in
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;

            // Redirect to the game
            header('Location: /me');
            exit;
        } catch (Exception $e) {
            $mysqli->rollback();
            echo $e;
            $errors['general'] = 'An error occurred while creating your account. Please try again.';
        }
    }
}

// Get online users count
$onlineUsers = getOnlineUsers();
?>
<!DOCTYPE html>
<html>
<head>
    <title>HabRPG - Register</title>
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
                <p class="hint-text">Password must be at least 8 characters long and include a mix of letters and numbers</p>
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
        
        <p class="footer-text">Already have an account? <a href="/login">Login</a></p>
    </div>
    
    <footer>
        <p class="footer-text">Archangel 2</p>
    </footer>
</body>
</html>