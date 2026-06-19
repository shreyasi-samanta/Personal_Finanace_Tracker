<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinanceTracker - System Diagnostics Verification</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome v6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background-color: #090d16;
            color: #f3f4f6;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .diag-card {
            background-color: #121824;
            border: 1px solid #1e293b;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 650px;
        }
        .step-item {
            padding: 12px 15px;
            border-bottom: 1px solid #1e293b;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .step-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>

<div class="diag-card p-4 p-sm-5">
    <div class="text-center mb-4">
        <i class="fa-solid fa-microchip text-primary fs-1 mb-2"></i>
        <h3 class="fw-bold font-outfit m-0">System Setup Diagnostics</h3>
        <p class="text-secondary small">Automated health validation for database connections, table schemas, and seeded records.</p>
    </div>

    <div class="card bg-dark border-secondary overflow-hidden mb-4">
        <?php
        $dbConnected = false;
        $schemaCorrect = false;
        $seededCategoriesCount = 0;
        
        // 1. Test db.php inclusion & DB connectivity
        echo '<div class="step-item">';
        echo '<span><i class="fa-solid fa-network-wired text-secondary me-2"></i>Database Connection Configuration</span>';
        if (file_exists('config/db.php')) {
            try {
                include_once 'config/db.php';
                if (isset($pdo)) {
                    $dbConnected = true;
                    echo '<span class="badge bg-success-subtle text-success"><i class="fa-solid fa-circle-check me-1"></i>Connected</span>';
                } else {
                    echo '<span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-circle-xmark me-1"></i>PDO Not Instantiated</span>';
                }
            } catch (Exception $e) {
                echo '<span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Error: ' . htmlspecialchars($e->getMessage()) . '</span>';
            }
        } else {
            echo '<span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-circle-xmark me-1"></i>db.php Missing</span>';
        }
        echo '</div>';

        // 2. Test Tables Exist
        if ($dbConnected) {
            echo '<div class="step-item">';
            echo '<span><i class="fa-solid fa-table text-secondary me-2"></i>Required Database Schema Tables</span>';
            try {
                $requiredTables = ['users', 'categories', 'transactions', 'budgets', 'goals'];
                $existingTables = [];
                $tableListQuery = $pdo->query("SHOW TABLES");
                while ($row = $tableListQuery->fetch(PDO::FETCH_NUM)) {
                    $existingTables[] = $row[0];
                }
                
                $missingTables = array_diff($requiredTables, $existingTables);
                if (count($missingTables) === 0) {
                    $schemaCorrect = true;
                    echo '<span class="badge bg-success-subtle text-success"><i class="fa-solid fa-circle-check me-1"></i>All 5 Tables Active</span>';
                } else {
                    echo '<span class="badge bg-danger-subtle text-danger" title="Missing: ' . implode(', ', $missingTables) . '"><i class="fa-solid fa-circle-xmark me-1"></i>Missing: ' . count($missingTables) . ' Tables</span>';
                }
            } catch (Exception $e) {
                echo '<span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Schema Query Error</span>';
            }
            echo '</div>';
        }

        // 3. Check seeded categories count
        if ($dbConnected && $schemaCorrect) {
            echo '<div class="step-item">';
            echo '<span><i class="fa-solid fa-tags text-secondary me-2"></i>Default Global Seed Categories</span>';
            try {
                $catCount = $pdo->query("SELECT COUNT(*) FROM categories WHERE user_id IS NULL")->fetchColumn();
                $seededCategoriesCount = (int)$catCount;
                if ($seededCategoriesCount > 0) {
                    echo '<span class="badge bg-success-subtle text-success"><i class="fa-solid fa-circle-check me-1"></i>' . $seededCategoriesCount . ' seeded categories</span>';
                } else {
                    echo '<span class="badge bg-warning-subtle text-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>0 Categories seeded</span>';
                }
            } catch (Exception $e) {
                echo '<span class="badge bg-danger-subtle text-danger"><i class="fa-solid fa-circle-xmark me-1"></i>Query Error</span>';
            }
            echo '</div>';
        }
        ?>
    </div>

    <!-- Diagnostic Verdict summary details -->
    <div class="text-center">
        <?php if ($dbConnected && $schemaCorrect && $seededCategoriesCount > 0): ?>
            <div class="alert alert-success bg-success-subtle border-0 text-success p-3 rounded mb-4">
                <h5 class="fw-bold font-outfit mb-1"><i class="fa-solid fa-circle-check me-1"></i>All Verification Checks Passed!</h5>
                <p class="mb-0 small">The database structure, configuration, and defaults are healthy and fully prepared for use.</p>
            </div>
            <a href="index.php" class="btn btn-emerald px-4 py-2"><i class="fa-solid fa-house me-1"></i>Go to Landing Page</a>
        <?php else: ?>
            <div class="alert alert-danger bg-danger-subtle border-0 text-danger p-3 rounded mb-4">
                <h5 class="fw-bold font-outfit mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i>Configuration Fixes Required</h5>
                <p class="mb-0 small">Ensure your MySQL server is running, the database name is <strong>finance_tracker</strong>, and you have run the <strong>schema.sql</strong> file inside your MySQL instance.</p>
            </div>
            <button onclick="window.location.reload();" class="btn btn-primary px-4 py-2"><i class="fa-solid fa-arrows-rotate me-1"></i>Re-run Diagnostics</button>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
