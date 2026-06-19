<?php
require_once __DIR__ . '/../config/db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate Fields
        if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
            send_json_response('error', 'All fields are required.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            send_json_response('error', 'Invalid email address format.');
        }

        if ($password !== $confirm_password) {
            send_json_response('error', 'Passwords do not match.');
        }

        if (strlen($password) < 6) {
            send_json_response('error', 'Password must be at least 6 characters long.');
        }

        // Check if user or email already exists
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                send_json_response('error', 'Username or Email is already registered.');
            }

            // Check if this is the first user (if so, make them admin, otherwise normal user)
            $countStmt = $pdo->query("SELECT COUNT(*) FROM users");
            $userCount = $countStmt->fetchColumn();
            $role = ($userCount == 0) ? 'admin' : 'user';

            // Hash password and Save User
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            $insertStmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$username, $email, $password_hash, $role]);

            send_json_response('success', 'Registration successful! You can now log in.');
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    } 
    
    elseif ($action === 'login') {
        $login_input = trim($_POST['login_input'] ?? ''); // Can be email or username
        $password = $_POST['password'] ?? '';

        if (empty($login_input) || empty($password)) {
            send_json_response('error', 'Please enter your username/email and password.');
        }

        try {
            // Find User by username or email
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$login_input, $login_input]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Secure Session Handling: Regenerate session ID to prevent Session Fixation
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                // Set CSRF token
                generate_csrf_token();

                // Determine redirect path
                $redirect = ($user['role'] === 'admin') ? 'admin/dashboard.php' : 'dashboard.php';
                
                send_json_response('success', 'Welcome back, ' . $user['username'] . '!', ['redirect' => $redirect]);
            } else {
                send_json_response('error', 'Invalid username/email or password.');
            }
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }
}

// If action is invalid or requested via GET
send_json_response('error', 'Invalid action or request method.');
?>
