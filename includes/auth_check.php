<?php
// Ensure db config is included (it starts secure session)
require_once __DIR__ . '/../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Function to enforce admin access
function enforce_admin() {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: ../dashboard.php?error=unauthorized');
        exit;
    }
}
?>
