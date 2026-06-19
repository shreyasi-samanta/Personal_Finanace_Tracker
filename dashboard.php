<?php
require_once __DIR__ . '/includes/auth_check.php';

$page_title = 'Dashboard';
$page_js = 'dashboard.js';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-outfit fw-bold text-white mb-1">Financial Command Center</h2>
        <p class="text-secondary mb-0">Hello, <span class="text-emerald fw-semibold"><?php echo sanitize($_SESSION['username']); ?></span>. Here is your wealth performance summary.</p>
    </div>
    <div>
        <!-- Quick action to add transaction -->
        <a href="transactions.php" class="btn btn-emerald px-3 py-2 btn-sm"><i class="fa-solid fa-plus me-1"></i> Manage Cashflow</a>
    </div>
</div>

<!-- Metrics Stats Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total Income Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 h-100 card-glow-emerald">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small font-outfit uppercase">Total Income</span>
                <span class="d-inline-flex bg-success-subtle text-emerald rounded p-2"><i class="fa-solid fa-arrow-down-long"></i></span>
            </div>
            <h3 class="font-outfit fw-bold text-white mb-0" id="total-income">--</h3>
            <span class="text-muted small mt-1 d-block">All-time cash inflow</span>
        </div>
    </div>
    
    <!-- Total Expenses Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 h-100 card-glow-rose">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small font-outfit uppercase">Total Expenses</span>
                <span class="d-inline-flex bg-danger-subtle text-rose rounded p-2"><i class="fa-solid fa-arrow-up-long"></i></span>
            </div>
            <h3 class="font-outfit fw-bold text-white mb-0" id="total-expenses">--</h3>
            <span class="text-muted small mt-1 d-block">All-time money outflows</span>
        </div>
    </div>
    
    <!-- Net Balance Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 h-100 card-glow-primary">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small font-outfit uppercase">Net Balance</span>
                <span class="d-inline-flex bg-primary-subtle text-primary rounded p-2"><i class="fa-solid fa-scale-balanced"></i></span>
            </div>
            <h3 class="font-outfit fw-bold text-white mb-0" id="current-balance">--</h3>
            <span class="text-muted small mt-1 d-block">Total remaining cashflow</span>
        </div>
    </div>

    <!-- Savings Card -->
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 h-100" style="transition: var(--transition-smooth);" onmouseover="this.style.borderColor='rgba(139, 92, 246, 0.4)'; this.style.boxShadow='0 8px 30px -10px rgba(139, 92, 246, 0.15)';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-secondary small font-outfit uppercase">Savings</span>
                <span class="d-inline-flex bg-warning-subtle text-warning rounded p-2"><i class="fa-solid fa-vault"></i></span>
            </div>
            <h3 class="font-outfit fw-bold text-white mb-0" id="total-savings">--</h3>
            <span class="text-muted small mt-1 d-block">Net earnings accumulated</span>
        </div>
    </div>
</div>

<!-- Charts & Transactions Layout -->
<div class="row g-4 mb-4">
    <!-- Left column: Charts -->
    <div class="col-lg-8">
        <!-- Income vs Expense Bar Chart -->
        <div class="card-premium p-3 mb-4">
            <h5 class="font-outfit fw-bold text-white mb-3"><i class="fa-solid fa-chart-column text-emerald me-2"></i>Income vs Expense Performance</h5>
            <div class="chart-container" style="position: relative; height: 320px;">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>
        
        <!-- Savings Trend Line Chart -->
        <div class="card-premium p-3">
            <h5 class="font-outfit fw-bold text-white mb-3"><i class="fa-solid fa-chart-line text-primary me-2"></i>Savings Growth History</h5>
            <div class="chart-container" style="position: relative; height: 250px;">
                <canvas id="savingsTrendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Right column: Category Breakdown & Recent Transactions -->
    <div class="col-lg-4">
        <!-- Expense Categories Doughnut Chart -->
        <div class="card-premium p-3 mb-4">
            <h5 class="font-outfit fw-bold text-white mb-3"><i class="fa-solid fa-chart-pie text-rose me-2"></i>Expense Breakdown</h5>
            <div class="chart-container" style="position: relative; height: 220px;">
                <canvas id="expenseCategoryChart"></canvas>
            </div>
        </div>
        
        <!-- Recent Transactions Widget -->
        <div class="card-premium p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="font-outfit fw-bold text-white mb-0"><i class="fa-solid fa-clock-rotate-left text-secondary me-2"></i>Recent Transactions</h5>
                <a href="transactions.php" class="text-emerald text-decoration-none small">View All</a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-dark table-borderless align-middle mb-0" style="font-size:0.85rem;">
                    <tbody id="recent-transactions-tbody">
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <div class="spinner-border spinner-border-sm text-emerald me-1" role="status"></div> Loading transactions...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
