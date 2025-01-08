<?php

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }
}

function requireBetaCode() {
    global $mysqli;

    if (!isset($_SESSION['user_id'])) {
        header('Location: /login.php');
        exit;
    }

    $stmt = $mysqli->prepare("SELECT id FROM nova_beta_codes WHERE users_id = ? AND claimed_at IS NOT NULL");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $beta_result = $stmt->get_result();

    if ($beta_result->num_rows === 0) {
        header('Location: /me.php?error=no_beta');
        exit;
    }
}

function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        header('Location: /me.php');
        exit;
    }
}
