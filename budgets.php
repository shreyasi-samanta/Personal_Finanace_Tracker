<?php
require_once __DIR__ . '/includes/auth_check.php';

$page_title = 'Budgets';
$page_js = 'budgets.js';

$user_id = $_SESSION['user_id'];

// Default to current month and year
$filter_month = isset($_GET['month']) && is_numeric($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$filter_year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

try {
    // 1. Get user's budgets for the selected month/year + sum of matching expenses
    $stmt = $pdo->prepare("
        SELECT b.*, 
               c.name as category_name, c.icon as category_icon, c.color as category_color,
               COALESCE(
                   (SELECT SUM(t.amount) 
                    FROM transactions t 
                    WHERE t.user_id = b.user_id 
                      AND t.category_id = b.category_id 
                      AND t.type = 'expense' 
                      AND MONTH(t.date) = b.month 
                      AND YEAR(t.date) = b.year), 
                   0.00
               ) as spent
        FROM budgets b
        JOIN categories c ON b.category_id = c.id
        WHERE b.user_id = ? AND b.month = ? AND b.year = ?
        ORDER BY c.name ASC
    ");
    $stmt->execute([$user_id, $filter_month, $filter_year]);
    $budgets = $stmt->fetchAll();

    // 2. Get Expense categories for the Add Budget modal selection
    $catStmt = $pdo->prepare("
        SELECT id, name 
        FROM categories 
        WHERE type = 'expense' AND (user_id IS NULL OR user_id = ?) 
        ORDER BY name ASC
    ");
    $catStmt->execute([$user_id]);
    $expense_categories = $catStmt->fetchAll();

} catch (PDOException $e) {
    die("Error retrieving budget planner data: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Set meta for CSRF Token in client requests -->
<meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-outfit fw-bold text-white mb-1">Monthly Budget Planner</h2>
        <p class="text-secondary mb-0">Set category limits and monitor active spending indicators.</p>
    </div>
    <div>
        <button class="btn btn-emerald px-3 py-2 btn-sm" data-bs-toggle="modal" data-bs-target="#addBudgetModal">
            <i class="fa-solid fa-plus me-1"></i> Add Budget Target
        </button>
    </div>
</div>

<!-- Month / Year Filter Selector -->
<div class="card-premium p-3 mb-4">
    <form method="GET" action="budgets.php" class="row g-3 align-items-end justify-content-start">
        <div class="col-sm-4 col-md-3">
            <label class="form-label text-secondary small">Month</label>
            <select name="month" class="form-select">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $monthName = date('F', mktime(0, 0, 0, $m, 1));
                    $selected = $filter_month === $m ? 'selected' : '';
                    echo "<option value='{$m}' {$selected}>{$monthName}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-sm-4 col-md-3">
            <label class="form-label text-secondary small">Year</label>
            <select name="year" class="form-select">
                <?php
                $currentYear = (int)date('Y');
                for ($y = $currentYear - 3; $y <= $currentYear + 3; $y++) {
                    $selected = $filter_year === $y ? 'selected' : '';
                    echo "<option value='{$y}' {$selected}>{$y}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-sm-4 col-md-2">
            <button type="submit" class="btn btn-primary w-100 py-2 btn-sm"><i class="fa-solid fa-circle-right me-1"></i> View Budgets</button>
        </div>
    </form>
</div>

<!-- Active Notifications Alert Panel -->
<?php
$alerts = [];
foreach ($budgets as $b) {
    $percent = $b['amount'] > 0 ? ($b['spent'] / $b['amount']) * 100 : 0;
    if ($percent > 100) {
        $overdraft = number_format($b['spent'] - $b['amount'], 2);
        $alerts[] = [
            'type' => 'danger',
            'icon' => 'fa-circle-exclamation',
            'message' => '<strong>Budget Exceeded!</strong> You have exceeded the <strong>' . sanitize($b['category_name']) . '</strong> budget by <strong>$' . $overdraft . '</strong>.'
        ];
    } elseif ($percent >= 80) {
        $alerts[] = [
            'type' => 'warning',
            'icon' => 'fa-triangle-exclamation',
            'message' => '<strong>Budget Warning!</strong> You have used <strong>' . number_format($percent, 1) . '%</strong> of your monthly allocation for <strong>' . sanitize($b['category_name']) . '</strong>.'
        ];
    }
}
?>

<?php if (count($alerts) > 0): ?>
    <div class="row mb-4">
        <div class="col-12">
            <div class="card-premium p-3 bg-dark border-secondary">
                <h6 class="font-outfit fw-bold text-white mb-2"><i class="fa-solid fa-bell text-warning me-2 animate-bounce"></i>Budget Alerts</h6>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($alerts as $a): ?>
                        <div class="alert alert-<?php echo $a['type']; ?> bg-<?php echo $a['type']; ?>-subtle border-0 mb-0 py-2 px-3 small text-<?php echo $a['type'] === 'danger' ? 'danger' : 'warning'; ?>" role="alert">
                            <i class="fa-solid <?php echo $a['icon']; ?> me-1"></i>
                            <?php echo $a['message']; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Budgets Visual Progress List -->
<div class="row g-4">
    <?php if (count($budgets) === 0): ?>
        <div class="col-12 text-center text-muted py-5 card-premium p-4">
            <i class="fa-solid fa-scale-balanced fs-2 mb-2 d-block text-secondary"></i>
            No budget targets created for this period (<?php echo date('F Y', mktime(0, 0, 0, $filter_month, 1, $filter_year)); ?>).
        </div>
    <?php else: ?>
        <?php foreach ($budgets as $b): ?>
            <?php
                $spent = floatval($b['spent']);
                $target = floatval($b['amount']);
                $percent = $target > 0 ? ($spent / $target) * 100 : 0;
                $percentFormatted = number_format($percent, 1);
                
                // Set progress bar styling class
                if ($percent > 100) {
                    $barClass = 'progress-bar-glow-danger';
                    $textClass = 'text-rose';
                } elseif ($percent >= 80) {
                    $barClass = 'progress-bar-glow-warning';
                    $textClass = 'text-warning';
                } else {
                    $barClass = 'progress-bar-glow-emerald';
                    $textClass = 'text-emerald';
                }
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card-premium p-3 h-100 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Title & Category Info -->
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="font-outfit fw-bold text-white mb-0">
                                <span class="d-inline-block text-center rounded-circle bg-dark me-2" style="width: 32px; height: 32px; line-height: 32px;">
                                    <i class="fa-solid <?php echo sanitize($b['category_icon']); ?>" style="color: <?php echo sanitize($b['category_color']); ?>"></i>
                                </span>
                                <?php echo sanitize($b['category_name']); ?>
                            </h5>
                            <span class="small text-muted font-outfit uppercase"><?php echo date('M Y', mktime(0, 0, 0, $b['month'], 1, $b['year'])); ?></span>
                        </div>

                        <!-- Spent vs Limit Values -->
                        <div class="d-flex justify-content-between align-items-baseline mb-2">
                            <span class="text-secondary small">Spent: <span class="fw-bold <?php echo $textClass; ?>">$<?php echo number_format($spent, 2); ?></span></span>
                            <span class="text-secondary small">Limit: <span class="fw-semibold text-white">$<?php echo number_format($target, 2); ?></span></span>
                        </div>

                        <!-- Progress Bar wrapper -->
                        <div class="progress mb-3">
                            <div class="progress-bar <?php echo $barClass; ?>" role="progressbar" style="width: <?php echo min($percent, 100); ?>%"></div>
                        </div>
                    </div>

                    <!-- Bottom Controls -->
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary">
                        <span class="small font-outfit fw-bold <?php echo $textClass; ?>"><?php echo $percentFormatted; ?>% allocated</span>
                        <div class="d-flex gap-1">
                            <button class="btn btn-outline-light btn-sm btn-edit-budget py-1 px-2.5" data-id="<?php echo $b['id']; ?>">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm btn-delete-budget py-1 px-2.5" data-id="<?php echo $b['id']; ?>">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ADD BUDGET MODAL -->
<div class="modal fade" id="addBudgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="budgetForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="modal-content text-light">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-plus text-emerald me-2"></i>Set Budget Target</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="budget_category" class="form-label text-secondary small">Expense Category</label>
                        <select class="form-select" id="budget_category" name="category_id" required>
                            <option value="">Select Expense Category</option>
                            <?php foreach ($expense_categories as $ec): ?>
                                <option value="<?php echo $ec['id']; ?>"><?php echo sanitize($ec['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="budget_amount" class="form-label text-secondary small">Monthly Limit ($)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="budget_amount" name="amount" placeholder="0.00" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="budget_month" class="form-label text-secondary small">Month</label>
                            <select class="form-select" id="budget_month" name="month" required>
                                <?php
                                for ($m = 1; $m <= 12; $m++) {
                                    $monthName = date('F', mktime(0, 0, 0, $m, 1));
                                    $selected = $filter_month === $m ? 'selected' : '';
                                    echo "<option value='{$m}' {$selected}>{$monthName}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="budget_year" class="form-label text-secondary small">Year</label>
                            <select class="form-select" id="budget_year" name="year" required>
                                <?php
                                $currentYear = (int)date('Y');
                                for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
                                    $selected = $filter_year === $y ? 'selected' : '';
                                    echo "<option value='{$y}' {$selected}>{$y}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm border-secondary text-secondary px-3 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-emerald btn-sm px-4 py-2">Save Budget</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- EDIT BUDGET MODAL -->
<div class="modal fade" id="editBudgetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editBudgetForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id" id="edit_budget_id">
            <div class="modal-content text-light">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square text-emerald me-2"></i>Modify Budget Target</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_budget_category" class="form-label text-secondary small">Expense Category</label>
                        <select class="form-select" id="edit_budget_category" name="category_id" required>
                            <?php foreach ($expense_categories as $ec): ?>
                                <option value="<?php echo $ec['id']; ?>"><?php echo sanitize($ec['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_budget_amount" class="form-label text-secondary small">Monthly Limit ($)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="edit_budget_amount" name="amount" required>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label for="edit_budget_month" class="form-label text-secondary small">Month</label>
                            <select class="form-select" id="edit_budget_month" name="month" required>
                                <?php
                                for ($m = 1; $m <= 12; $m++) {
                                    $monthName = date('F', mktime(0, 0, 0, $m, 1));
                                    echo "<option value='{$m}'>{$monthName}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="edit_budget_year" class="form-label text-secondary small">Year</label>
                            <select class="form-select" id="edit_budget_year" name="year" required>
                                <?php
                                $currentYear = (int)date('Y');
                                for ($y = $currentYear - 2; $y <= $currentYear + 2; $y++) {
                                    echo "<option value='{$y}'>{$y}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm border-secondary text-secondary px-3 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-emerald btn-sm px-4 py-2">Update Budget</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
