<?php
require_once __DIR__ . '/../includes/auth_check.php';

// Enforce admin permission guards
enforce_admin();

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        send_json_response('error', 'CSRF validation failed.');
    }

    // ----------------------------------------------------
    // User Management Actions
    // ----------------------------------------------------
    if ($action === 'toggle_role') {
        $target_user_id = $_POST['user_id'] ?? 0;
        
        if (intval($target_user_id) === intval($user_id)) {
            send_json_response('error', 'You cannot change your own role.');
        }

        try {
            // Fetch current role
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$target_user_id]);
            $user = $stmt->fetch();

            if (!$user) {
                send_json_response('error', 'User not found.');
            }

            $new_role = $user['role'] === 'admin' ? 'user' : 'admin';
            
            $upStmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $upStmt->execute([$new_role, $target_user_id]);

            send_json_response('success', 'User role updated to ' . $new_role . '.');
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }

    elseif ($action === 'delete_user') {
        $target_user_id = $_POST['user_id'] ?? 0;
        
        if (intval($target_user_id) === intval($user_id)) {
            send_json_response('error', 'You cannot delete your own admin account.');
        }

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$target_user_id]);
            send_json_response('success', 'User account and all associated transactions/budgets have been deleted.');
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }

    // ----------------------------------------------------
    // Category Management Actions
    // ----------------------------------------------------
    elseif ($action === 'save_category') {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'expense';
        $icon = trim($_POST['icon'] ?? 'fa-tag');
        $color = trim($_POST['color'] ?? '#6c757d');

        if (empty($name)) {
            send_json_response('error', 'Category name is required.');
        }

        if (!in_array($type, ['income', 'expense'])) {
            send_json_response('error', 'Invalid category type.');
        }

        try {
            if (empty($id)) {
                // Insert global category (user_id is NULL)
                $stmt = $pdo->prepare("INSERT INTO categories (name, type, icon, color, user_id) VALUES (?, ?, ?, ?, NULL)");
                $stmt->execute([$name, $type, $icon, $color]);
                send_json_response('success', 'Global system category added.');
            } else {
                // Update
                $stmt = $pdo->prepare("UPDATE categories SET name = ?, type = ?, icon = ?, color = ? WHERE id = ? AND user_id IS NULL");
                $stmt->execute([$name, $type, $icon, $color, $id]);
                send_json_response('success', 'Global system category updated.');
            }
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }

    elseif ($action === 'delete_category') {
        $target_cat_id = $_POST['id'] ?? 0;

        try {
            // Prevent deletion if transactions are linked (or let CASCADE handle it, but DB has RESTRICT on transactions table category_id!)
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE category_id = ?");
            $checkStmt->execute([$target_cat_id]);
            if ($checkStmt->fetchColumn() > 0) {
                send_json_response('error', 'Cannot delete this category. There are transactions linked to it. Please re-assign them first.');
            }

            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ? AND user_id IS NULL");
            $stmt->execute([$target_cat_id]);
            send_json_response('success', 'Global category deleted.');
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }

    // ----------------------------------------------------
    // Global Transaction Audit Action
    // ----------------------------------------------------
    elseif ($action === 'delete_transaction') {
        $tx_id = $_POST['id'] ?? 0;

        try {
            $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ?");
            $stmt->execute([$tx_id]);
            send_json_response('success', 'Transaction has been deleted by administrator audit.');
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }
}

send_json_response('error', 'Invalid action or request method.');
?>
