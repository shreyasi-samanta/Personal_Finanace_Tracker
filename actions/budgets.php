<?php
require_once __DIR__ . '/../config/db.php';

// Enforce login
if (!isset($_SESSION['user_id'])) {
    send_json_response('error', 'Unauthorized access.');
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 1. Fetch single budget details
    if ($action === 'get') {
        $id = $_GET['id'] ?? 0;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM budgets WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $budget = $stmt->fetch();
            
            if ($budget) {
                send_json_response('success', 'Budget details loaded.', ['data' => $budget]);
            } else {
                send_json_response('error', 'Budget record not found.');
            }
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }
}

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!verify_csrf_token($csrf_token)) {
        send_json_response('error', 'CSRF validation failed.');
    }

    // 2. Save Budget (Insert or Update via UPSERT)
    if ($action === 'save') {
        $id = $_POST['id'] ?? null; // If ID is present, we are editing a specific ID
        $category_id = $_POST['category_id'] ?? '';
        $amount = $_POST['amount'] ?? '';
        $month = $_POST['month'] ?? '';
        $year = $_POST['year'] ?? '';

        if (empty($category_id) || empty($amount) || empty($month) || empty($year)) {
            send_json_response('error', 'All fields are required (Category, Amount, Month, Year).');
        }

        if (!is_numeric($amount) || floatval($amount) <= 0) {
            send_json_response('error', 'Budget amount must be a positive decimal.');
        }

        if ($month < 1 || $month > 12) {
            send_json_response('error', 'Invalid month selection.');
        }

        // Validate that category is of type expense (cannot set budgets for income!)
        $catStmt = $pdo->prepare("SELECT type FROM categories WHERE id = ? AND (user_id IS NULL OR user_id = ?)");
        $catStmt->execute([$category_id, $user_id]);
        $category = $catStmt->fetch();
        
        if (!$category) {
            send_json_response('error', 'Invalid category selected.');
        }
        if ($category['type'] !== 'expense') {
            send_json_response('error', 'Budgets can only be created for Expense categories.');
        }

        try {
            if (empty($id)) {
                // Use UPSERT query to insert or overwrite for the same category + month + year
                $stmt = $pdo->prepare("
                    INSERT INTO budgets (user_id, category_id, amount, month, year) 
                    VALUES (?, ?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE amount = VALUES(amount)
                ");
                $stmt->execute([$user_id, $category_id, $amount, $month, $year]);
                send_json_response('success', 'Monthly budget allocation saved successfully.');
            } else {
                // Update explicit ID
                // Verify ownership
                $chkStmt = $pdo->prepare("SELECT id FROM budgets WHERE id = ? AND user_id = ?");
                $chkStmt->execute([$id, $user_id]);
                if (!$chkStmt->fetch()) {
                    send_json_response('error', 'Unauthorized budget edit request.');
                }

                $stmt = $pdo->prepare("
                    UPDATE budgets 
                    SET category_id = ?, amount = ?, month = ?, year = ? 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$category_id, $amount, $month, $year, $id, $user_id]);
                send_json_response('success', 'Monthly budget allocation updated successfully.');
            }
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }

    // 3. Delete Budget
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;

        try {
            // Verify ownership
            $chkStmt = $pdo->prepare("SELECT id FROM budgets WHERE id = ? AND user_id = ?");
            $chkStmt->execute([$id, $user_id]);
            if (!$chkStmt->fetch()) {
                send_json_response('error', 'Unauthorized budget delete request.');
            }

            $stmt = $pdo->prepare("DELETE FROM budgets WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            
            send_json_response('success', 'Monthly budget limit deleted.');
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }
}
?>
