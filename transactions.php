<?php
require_once __DIR__ . '/includes/auth_check.php';

$page_title = 'Transactions';
$page_js = 'transactions.js';

$user_id = $_SESSION['user_id'];

// Get filters from URL GET params
$filter_type = $_GET['type'] ?? '';
$filter_category = $_GET['category'] ?? '';
$filter_start = $_GET['start_date'] ?? '';
$filter_end = $_GET['end_date'] ?? '';

// Pagination variables
$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// Construct SQL query
$query_parts = [];
$params = [':user_id' => $user_id];

if (!empty($filter_type)) {
    $query_parts[] = "t.type = :type";
    $params[':type'] = $filter_type;
}
if (!empty($filter_category)) {
    $query_parts[] = "t.category_id = :category_id";
    $params[':category_id'] = $filter_category;
}
if (!empty($filter_start)) {
    $query_parts[] = "t.date >= :start_date";
    $params[':start_date'] = $filter_start;
}
if (!empty($filter_end)) {
    $query_parts[] = "t.date <= :end_date";
    $params[':end_date'] = $filter_end;
}

$where_clause = "";
if (count($query_parts) > 0) {
    $where_clause = "AND " . implode(" AND ", $query_parts);
}

try {
    // 1. Get Categories for the filter dropdown
    $catStmt = $pdo->prepare("SELECT id, name, type FROM categories WHERE user_id IS NULL OR user_id = ? ORDER BY type ASC, name ASC");
    $catStmt->execute([$user_id]);
    $filter_categories = $catStmt->fetchAll();

    // 2. Count total rows for pagination
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM transactions t
        WHERE t.user_id = :user_id $where_clause
    ");
    $countStmt->execute($params);
    $total_rows = $countStmt->fetchColumn();
    $total_pages = ceil($total_rows / $limit);
    if ($total_pages < 1) $total_pages = 1;

    // 3. Fetch paginated transactions
    $txStmt = $pdo->prepare("
        SELECT t.*, c.name as category_name, c.icon as category_icon, c.color as category_color 
        FROM transactions t
        JOIN categories c ON t.category_id = c.id
        WHERE t.user_id = :user_id $where_clause
        ORDER BY t.date DESC, t.id DESC
        LIMIT $limit OFFSET $offset
    ");
    $txStmt->execute($params);
    $transactions = $txStmt->fetchAll();

} catch (PDOException $e) {
    die("Error retrieving transactions: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Set meta for CSRF Token in client requests -->
<meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-outfit fw-bold text-white mb-1">Transaction Ledger</h2>
        <p class="text-secondary mb-0">Review, search, and manage your cashflow details.</p>
    </div>
    <div>
        <!-- Button trigger Add Modal -->
        <button class="btn btn-emerald px-3 py-2 btn-sm" data-bs-toggle="modal" data-bs-target="#addTransactionModal">
            <i class="fa-solid fa-plus me-1"></i> Add Transaction
        </button>
    </div>
</div>

<!-- Filters Card -->
<div class="card-premium p-3 mb-4">
    <form method="GET" action="transactions.php" class="row g-3 align-items-end">
        <div class="col-sm-6 col-md-3">
            <label class="form-label text-secondary small">Type</label>
            <select name="type" class="form-select">
                <option value="">All Types</option>
                <option value="income" <?php echo $filter_type === 'income' ? 'selected' : ''; ?>>Inflow (Income)</option>
                <option value="expense" <?php echo $filter_type === 'expense' ? 'selected' : ''; ?>>Outflow (Expense)</option>
            </select>
        </div>
        <div class="col-sm-6 col-md-3">
            <label class="form-label text-secondary small">Category</label>
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                <?php foreach ($filter_categories as $c): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $filter_category == $c['id'] ? 'selected' : ''; ?>>
                        [<?php echo ucfirst($c['type']); ?>] <?php echo sanitize($c['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6 col-md-2">
            <label class="form-label text-secondary small">From Date</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo sanitize($filter_start); ?>">
        </div>
        <div class="col-sm-6 col-md-2">
            <label class="form-label text-secondary small">To Date</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo sanitize($filter_end); ?>">
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary w-100 py-2 btn-sm"><i class="fa-solid fa-filter me-1"></i> Filter</button>
            <a href="transactions.php" class="btn btn-outline-secondary w-100 py-2 border-secondary btn-sm"><i class="fa-solid fa-undo me-1"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Ledger Content Table -->
<div class="table-responsive table-premium mb-4">
    <table class="table table-dark table-striped align-middle mb-0">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 20%;">Category</th>
                <th style="width: 33%;">Description</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 12%;">Amount</th>
                <th style="width: 8%;" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($transactions) === 0): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="fa-regular fa-folder-open fs-2 mb-2 d-block text-secondary"></i>
                        No transactions match your selection criteria.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($transactions as $t): ?>
                    <?php 
                        $dateFormatted = date("M d, Y", strtotime($t['date']));
                        $amountFormatted = number_format($t['amount'], 2);
                        $isIncome = $t['type'] === 'income';
                        $badgeClass = $isIncome ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger';
                        $amountClass = $isIncome ? 'text-emerald' : 'text-rose';
                        $amountSign = $isIncome ? '+' : '-';
                    ?>
                    <tr>
                        <td><?php echo $dateFormatted; ?></td>
                        <td class="fw-semibold">
                            <span class="d-inline-block text-center rounded-circle bg-dark me-2" style="width: 32px; height: 32px; line-height: 32px;">
                                <i class="fa-solid <?php echo sanitize($t['category_icon']); ?>" style="color: <?php echo sanitize($t['category_color']); ?>"></i>
                            </span>
                            <?php echo sanitize($t['category_name']); ?>
                        </td>
                        <td><span class="text-secondary"><?php echo sanitize($t['description']); ?></span></td>
                        <td>
                            <span class="badge <?php echo $badgeClass; ?> px-2.5 py-1.5">
                                <i class="fa-solid <?php echo $isIncome ? 'fa-arrow-down' : 'fa-arrow-up'; ?> me-1" style="font-size:0.75rem;"></i>
                                <?php echo ucfirst($t['type']); ?>
                            </span>
                        </td>
                        <td class="fw-bold <?php echo $amountClass; ?>">
                            <?php echo $amountSign; ?>$<?php echo $amountFormatted; ?>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end">
                                <button class="btn btn-outline-light btn-sm btn-edit-transaction" data-id="<?php echo $t['id']; ?>">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-outline-danger btn-sm btn-delete-transaction" data-id="<?php echo $t['id']; ?>">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Pagination Nav -->
<?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination pagination-sm justify-content-center gap-1">
            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                <a class="page-link bg-card border-secondary text-secondary" href="?page=<?php echo $page-1; ?>&type=<?php echo $filter_type; ?>&category=<?php echo $filter_category; ?>&start_date=<?php echo $filter_start; ?>&end_date=<?php echo $filter_end; ?>">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
            </li>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link <?php echo $page == $i ? 'bg-emerald text-dark fw-bold border-emerald' : 'bg-card border-secondary text-secondary'; ?>" href="?page=<?php echo $i; ?>&type=<?php echo $filter_type; ?>&category=<?php echo $filter_category; ?>&start_date=<?php echo $filter_start; ?>&end_date=<?php echo $filter_end; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link bg-card border-secondary text-secondary" href="?page=<?php echo $page+1; ?>&type=<?php echo $filter_type; ?>&category=<?php echo $filter_category; ?>&start_date=<?php echo $filter_start; ?>&end_date=<?php echo $filter_end; ?>">
                    <i class="fa-solid fa-angle-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<!-- ADD TRANSACTION MODAL -->
<div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="transactionForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="modal-content text-light">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-plus text-emerald me-2"></i>New Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="tx_type" class="form-label text-secondary small">Type</label>
                        <select class="form-select" id="tx_type" name="type" required>
                            <option value="expense" selected>Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tx_category" class="form-label text-secondary small">Category</label>
                        <select class="form-select" id="tx_category" name="category_id" required>
                            <!-- Dynamically loaded via Javascript -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="tx_amount" class="form-label text-secondary small">Amount ($)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="tx_amount" name="amount" placeholder="0.00" required>
                    </div>
                    <div class="mb-3">
                        <label for="tx_date" class="form-label text-secondary small">Date</label>
                        <input type="date" class="form-control" id="tx_date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="tx_description" class="form-label text-secondary small">Description (Optional)</label>
                        <textarea class="form-control" id="tx_description" name="description" rows="2" placeholder="Detail notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm border-secondary text-secondary px-3 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-emerald btn-sm px-4 py-2">Save Record</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- EDIT TRANSACTION MODAL -->
<div class="modal fade" id="editTransactionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editTransactionForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id" id="edit_tx_id">
            <div class="modal-content text-light">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square text-emerald me-2"></i>Modify Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_tx_type" class="form-label text-secondary small">Type</label>
                        <select class="form-select" id="edit_tx_type" name="type" required>
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tx_category" class="form-label text-secondary small">Category</label>
                        <select class="form-select" id="edit_tx_category" name="category_id" required>
                            <!-- Dynamically loaded via JS -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tx_amount" class="form-label text-secondary small">Amount ($)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="edit_tx_amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tx_date" class="form-label text-secondary small">Date</label>
                        <input type="date" class="form-control" id="edit_tx_date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_tx_description" class="form-label text-secondary small">Description (Optional)</label>
                        <textarea class="form-control" id="edit_tx_description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm border-secondary text-secondary px-3 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-emerald btn-sm px-4 py-2">Update Record</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
