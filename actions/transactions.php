<?php
require_once __DIR__ . '/../config/db.php';

// Enforce login
if (!isset($_SESSION['user_id'])) {
    send_json_response('error', 'Unauthorized access.');
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 1. Get Categories for the select options
    if ($action === 'get_categories') {
        $type = $_GET['type'] ?? 'expense';
        if (!in_array($type, ['income', 'expense'])) {
            send_json_response('error', 'Invalid category type requested.');
        }

        try {
            // Retrieve system categories (user_id is NULL) + user custom categories
            $stmt = $pdo->prepare("
                SELECT id, name, icon, color 
                FROM categories 
                WHERE type = ? AND (user_id IS NULL OR user_id = ?)
                ORDER BY name ASC
            ");
            $stmt->execute([$type, $user_id]);
            $categories = $stmt->fetchAll();
            
            send_json_response('success', 'Categories loaded.', ['data' => $categories]);
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }
    
    // 2. Fetch single transaction details for editing
    elseif ($action === 'get') {
        $id = $_GET['id'] ?? 0;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $transaction = $stmt->fetch();
            
            if ($transaction) {
                send_json_response('success', 'Transaction details loaded.', ['data' => $transaction]);
            } else {
                send_json_response('error', 'Transaction not found.');
            }
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token for modifying actions
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        send_json_response('error', 'CSRF validation failed. Refresh the page and try again.');
    }

    // 3. Save Transaction (Insert or Update)
    if ($action === 'save') {
        $id = $_POST['id'] ?? null; // Null means insert, INT means update
        $type = $_POST['type'] ?? 'expense';
        $category_id = $_POST['category_id'] ?? '';
        $amount = $_POST['amount'] ?? '';
        $date = $_POST['date'] ?? '';
        $description = trim($_POST['description'] ?? '');

        // Validation
        if (empty($category_id) || empty($amount) || empty($date)) {
            send_json_response('error', 'All fields are required (Category, Amount, Date).');
        }

        if (!in_array($type, ['income', 'expense'])) {
            send_json_response('error', 'Invalid transaction type.');
        }

        if (!is_numeric($amount) || floatval($amount) <= 0) {
            send_json_response('error', 'Amount must be a positive number.');
        }

        // Verify that the category exists and matches type
        $catStmt = $pdo->prepare("SELECT id FROM categories WHERE id = ? AND type = ? AND (user_id IS NULL OR user_id = ?)");
        $catStmt->execute([$category_id, $type, $user_id]);
        if (!$catStmt->fetch()) {
            send_json_response('error', 'Invalid category selected.');
        }

        try {
            if (empty($id)) {
                // INSERT NEW
                $stmt = $pdo->prepare("
                    INSERT INTO transactions (user_id, category_id, type, amount, date, description) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $category_id, $type, $amount, $date, $description]);
                send_json_response('success', 'Transaction added successfully.');
            } else {
                // UPDATE EXISTING
                // First verify ownership
                $chkStmt = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ?");
                $chkStmt->execute([$id, $user_id]);
                if (!$chkStmt->fetch()) {
                    send_json_response('error', 'Unauthorized transaction update request.');
                }

                $stmt = $pdo->prepare("
                    UPDATE transactions 
                    SET category_id = ?, type = ?, amount = ?, date = ?, description = ? 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$category_id, $type, $amount, $date, $description, $id, $user_id]);
                send_json_response('success', 'Transaction updated successfully.');
            }
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }

    // 4. Delete Transaction
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;

        try {
            // Verify ownership
            $chkStmt = $pdo->prepare("SELECT id FROM transactions WHERE id = ? AND user_id = ?");
            $chkStmt->execute([$id, $user_id]);
            if (!$chkStmt->fetch()) {
                send_json_response('error', 'Unauthorized transaction delete request.');
            }

            $stmt = $pdo->prepare("DELETE FROM transactions WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            
            send_json_response('success', 'Transaction deleted successfully.');
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }
}
?>
