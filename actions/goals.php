<?php
require_once __DIR__ . '/../config/db.php';

// Enforce login
if (!isset($_SESSION['user_id'])) {
    send_json_response('error', 'Unauthorized access.');
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // 1. Fetch single goal details
    if ($action === 'get') {
        $id = $_GET['id'] ?? 0;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM goals WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $goal = $stmt->fetch();
            
            if ($goal) {
                send_json_response('success', 'Goal details loaded.', ['data' => $goal]);
            } else {
                send_json_response('error', 'Goal not found.');
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

    // 2. Save Goal (Insert or Update)
    if ($action === 'save') {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $target_amount = $_POST['target_amount'] ?? '';
        $current_amount = $_POST['current_amount'] ?? 0.00;
        $target_date = $_POST['target_date'] ?? '';
        $status = $_POST['status'] ?? 'active';

        if (empty($name) || empty($target_amount) || empty($target_date)) {
            send_json_response('error', 'All fields are required (Goal Name, Target Amount, Target Date).');
        }

        if (!is_numeric($target_amount) || floatval($target_amount) <= 0) {
            send_json_response('error', 'Target amount must be a positive decimal.');
        }

        if (!is_numeric($current_amount) || floatval($current_amount) < 0) {
            send_json_response('error', 'Current amount must be a non-negative decimal.');
        }

        if (!in_array($status, ['active', 'completed', 'failed'])) {
            send_json_response('error', 'Invalid status selection.');
        }

        // Auto-complete status if target reached
        if (floatval($current_amount) >= floatval($target_amount)) {
            $status = 'completed';
        }

        try {
            if (empty($id)) {
                // INSERT NEW
                $stmt = $pdo->prepare("
                    INSERT INTO goals (user_id, name, target_amount, current_amount, target_date, status) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $name, $target_amount, $current_amount, $target_date, $status]);
                send_json_response('success', 'Savings goal created successfully.');
            } else {
                // UPDATE EXISTING
                // Verify ownership
                $chkStmt = $pdo->prepare("SELECT id FROM goals WHERE id = ? AND user_id = ?");
                $chkStmt->execute([$id, $user_id]);
                if (!$chkStmt->fetch()) {
                    send_json_response('error', 'Unauthorized goal edit request.');
                }

                $stmt = $pdo->prepare("
                    UPDATE goals 
                    SET name = ?, target_amount = ?, current_amount = ?, target_date = ?, status = ? 
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$name, $target_amount, $current_amount, $target_date, $status, $id, $user_id]);
                send_json_response('success', 'Savings goal updated successfully.');
            }
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }

    // 3. Contribute to Goal
    elseif ($action === 'contribute') {
        $id = $_POST['id'] ?? 0;
        $amount = $_POST['amount'] ?? '';

        if (empty($amount) || !is_numeric($amount) || floatval($amount) <= 0) {
            send_json_response('error', 'Contribution amount must be a positive number.');
        }

        try {
            // Fetch goal details
            $stmt = $pdo->prepare("SELECT * FROM goals WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $goal = $stmt->fetch();

            if (!$goal) {
                send_json_response('error', 'Goal not found or unauthorized access.');
            }

            $new_current = floatval($goal['current_amount']) + floatval($amount);
            $status = $goal['status'];

            // Auto-complete check
            if ($new_current >= floatval($goal['target_amount'])) {
                $status = 'completed';
            }

            $upStmt = $pdo->prepare("UPDATE goals SET current_amount = ?, status = ? WHERE id = ? AND user_id = ?");
            $upStmt->execute([$new_current, $status, $id, $user_id]);

            send_json_response('success', 'Contribution added! Keep up the great savings work.');
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }

    // 4. Delete Goal
    elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;

        try {
            // Verify ownership
            $chkStmt = $pdo->prepare("SELECT id FROM goals WHERE id = ? AND user_id = ?");
            $chkStmt->execute([$id, $user_id]);
            if (!$chkStmt->fetch()) {
                send_json_response('error', 'Unauthorized goal delete request.');
            }

            $stmt = $pdo->prepare("DELETE FROM goals WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            
            send_json_response('success', 'Savings goal deleted.');
        } catch (PDOException $e) {
            send_json_response('error', 'Database error: ' . $e->getMessage());
        }
    }
}
?>
