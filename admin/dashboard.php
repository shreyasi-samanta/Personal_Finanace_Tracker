<?php
$in_admin_folder = true;
require_once __DIR__ . '/../includes/auth_check.php';
enforce_admin();

$page_title = 'System Administration';
$page_js = 'admin.js';

try {
    // Aggregated stats
    $usersCount = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $txCount = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
    $budgetsCount = $pdo->query("SELECT COUNT(*) FROM budgets")->fetchColumn();
    $goalsCount = $pdo->query("SELECT COUNT(*) FROM goals")->fetchColumn();
    $totalVolume = $pdo->query("SELECT COALESCE(SUM(amount), 0.00) FROM transactions")->fetchColumn();
    
    // DB Table status info
    $tableStatusStmt = $pdo->query("SHOW TABLE STATUS");
    $tables = $tableStatusStmt->fetchAll();
} catch (PDOException $e) {
    die("Admin aggregation failed: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Set meta for CSRF Token in client requests -->
<meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">

<!-- Page Header -->
<div class="mb-4">
    <h2 class="font-outfit fw-bold text-white mb-1"><i class="fa-solid fa-gauge-high text-primary me-2"></i>System Administration</h2>
    <p class="text-secondary mb-0">Overview of system health, database tables, and system-wide stats.</p>
</div>

<!-- System Metrics Stats Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total Users Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 h-100 card-glow-primary">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small font-outfit uppercase">Total Users</span>
                <span class="d-inline-flex bg-primary-subtle text-primary rounded p-2"><i class="fa-solid fa-users"></i></span>
            </div>
            <h3 class="font-outfit fw-bold text-white mb-0"><?php echo number_format($usersCount); ?></h3>
            <span class="text-muted small mt-1 d-block">Registered accounts</span>
        </div>
    </div>
    
    <!-- Total Transactions Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 h-100" style="transition: var(--transition-smooth);" onmouseover="this.style.borderColor='rgba(13, 148, 136, 0.4)'; this.style.boxShadow='0 8px 30px -10px rgba(13, 148, 136, 0.15)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small font-outfit uppercase">Total Transactions</span>
                <span class="d-inline-flex bg-info-subtle text-info rounded p-2"><i class="fa-solid fa-money-bill-transfer"></i></span>
            </div>
            <h3 class="font-outfit fw-bold text-white mb-0"><?php echo number_format($txCount); ?></h3>
            <span class="text-muted small mt-1 d-block">Inflow & Outflow entries</span>
        </div>
    </div>
    
    <!-- System cash Volume Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 h-100 card-glow-emerald">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small font-outfit uppercase">Transaction Volume</span>
                <span class="d-inline-flex bg-success-subtle text-emerald rounded p-2"><i class="fa-solid fa-sack-dollar"></i></span>
            </div>
            <h3 class="font-outfit fw-bold text-white mb-0">$<?php echo number_format($totalVolume, 2); ?></h3>
            <span class="text-muted small mt-1 d-block">Gross monetary flows</span>
        </div>
    </div>

    <!-- Active Budgets & Goals Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 h-100" style="transition: var(--transition-smooth);" onmouseover="this.style.borderColor='rgba(245, 158, 11, 0.4)'; this.style.boxShadow='0 8px 30px -10px rgba(245, 158, 11, 0.15)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small font-outfit uppercase">Budgets & Goals</span>
                <span class="d-inline-flex bg-warning-subtle text-warning rounded p-2"><i class="fa-solid fa-bullseye"></i></span>
            </div>
            <h3 class="font-outfit fw-bold text-white mb-0"><?php echo number_format($budgetsCount) . ' / ' . number_format($goalsCount); ?></h3>
            <span class="text-muted small mt-1 d-block">Allocations & targets set</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- DB Health & Tables Status -->
    <div class="col-lg-8">
        <div class="card-premium p-3 mb-4">
            <h5 class="font-outfit fw-bold text-white mb-3"><i class="fa-solid fa-database text-primary me-2"></i>Database Storage Health</h5>
            <div class="table-responsive table-premium mb-0">
                <table class="table table-dark table-striped align-middle mb-0" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Table Name</th>
                            <th>Storage Engine</th>
                            <th>Row Count</th>
                            <th>Data Size</th>
                            <th>Index Size</th>
                            <th>Free Overhead</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $t): ?>
                            <?php
                                $dataKb = number_format($t['Data_length'] / 1024, 1);
                                $indexKb = number_format($t['Index_length'] / 1024, 1);
                                $freeKb = number_format($t['Data_free'] / 1024, 1);
                            ?>
                            <tr>
                                <td class="fw-semibold text-white"><?php echo sanitize($t['Name']); ?></td>
                                <td><span class="badge bg-dark border border-secondary text-secondary"><?php echo sanitize($t['Engine']); ?></span></td>
                                <td class="fw-bold"><?php echo number_format($t['Rows'] ?? 0); ?></td>
                                <td><?php echo $dataKb; ?> KB</td>
                                <td><?php echo $indexKb; ?> KB</td>
                                <td class="<?php echo $t['Data_free'] > 0 ? 'text-warning' : 'text-muted'; ?>"><?php echo $freeKb; ?> KB</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Server Environment Info -->
    <div class="col-lg-4">
        <div class="card-premium p-3 h-100">
            <h5 class="font-outfit fw-bold text-white mb-3"><i class="fa-solid fa-circle-info text-secondary me-2"></i>System Status</h5>
            
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
                    <span class="small text-secondary">Server Host:</span>
                    <span class="small fw-semibold text-white"><?php echo sanitize($_SERVER['SERVER_SOFTWARE'] ?? 'PHP Server'); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
                    <span class="small text-secondary">PHP Version:</span>
                    <span class="small fw-semibold text-white"><?php echo phpversion(); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom border-secondary">
                    <span class="small text-secondary">PDO Driver:</span>
                    <span class="small fw-semibold text-white">MySQL (PDO_MYSQL)</span>
                </div>
                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="small text-secondary">Session ID:</span>
                    <span class="small text-truncate text-muted" style="max-width: 160px; font-family: monospace;"><?php echo session_id(); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
