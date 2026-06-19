<?php
$in_admin_folder = true;
require_once __DIR__ . '/../includes/auth_check.php';
enforce_admin();

$page_title = 'Global Audit Ledger';
$page_js = 'admin.js';

// Pagination setup
$limit = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

try {
    // 1. Get total row count for pagination
    $total_rows = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
    $total_pages = ceil($total_rows / $limit);
    if ($total_pages < 1) $total_pages = 1;

    // 2. Fetch paginated list
    $stmt = $pdo->prepare("
        SELECT t.*, u.username, c.name as category_name, c.icon as category_icon, c.color as category_color 
        FROM transactions t
        JOIN users u ON t.user_id = u.id
        JOIN categories c ON t.category_id = c.id
        ORDER BY t.date DESC, t.id DESC
        LIMIT :limit OFFSET :offset
    ");
    // Bind integer values specifically to avoid quotes issues on LIMIT/OFFSET in some MySQL setups
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $transactions = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error retrieving global transaction log: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Set meta for CSRF Token in client requests -->
<meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">

<!-- Page Header -->
<div class="mb-4">
    <h2 class="font-outfit fw-bold text-white mb-1"><i class="fa-solid fa-list text-primary me-2"></i>Global Transaction Audit Log</h2>
    <p class="text-secondary mb-0">System-wide review of cashflow records. Delete transaction entries for administrative auditing.</p>
</div>

<!-- Ledger Audit Table -->
<div class="table-responsive table-premium mb-4">
    <table class="table table-dark table-striped align-middle mb-0">
        <thead>
            <tr>
                <th style="width: 12%;">User</th>
                <th style="width: 12%;">Date</th>
                <th style="width: 20%;">Category</th>
                <th style="width: 28%;">Description</th>
                <th style="width: 12%;">Type</th>
                <th style="width: 10%;">Amount</th>
                <th style="width: 6%;" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($transactions) === 0): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">
                        <i class="fa-regular fa-folder-open fs-2 mb-2 d-block text-secondary"></i>
                        No transactions registered in the database.
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
                        <td class="fw-bold text-white">
                            <span class="d-inline-block text-center rounded-circle bg-primary text-white me-2" style="width: 28px; height: 28px; line-height: 28px; font-size: 0.8rem;">
                                <?php echo strtoupper(substr($t['username'], 0, 1)); ?>
                            </span>
                            <?php echo sanitize($t['username']); ?>
                        </td>
                        <td><?php echo $dateFormatted; ?></td>
                        <td class="fw-semibold">
                            <span class="d-inline-block text-center rounded-circle bg-dark me-2" style="width: 28px; height: 28px; line-height: 28px;">
                                <i class="fa-solid <?php echo sanitize($t['category_icon']); ?>" style="color: <?php echo sanitize($t['category_color']); ?>; font-size: 0.85rem;"></i>
                            </span>
                            <?php echo sanitize($t['category_name']); ?>
                        </td>
                        <td><span class="text-secondary small"><?php echo sanitize($t['description']); ?></span></td>
                        <td>
                            <span class="badge <?php echo $badgeClass; ?> px-2 py-1">
                                <?php echo ucfirst($t['type']); ?>
                            </span>
                        </td>
                        <td class="fw-bold <?php echo $amountClass; ?>">
                            <?php echo $amountSign; ?>$<?php echo $amountFormatted; ?>
                        </td>
                        <td class="text-end">
                            <button class="btn btn-outline-danger btn-sm btn-audit-delete-tx py-1 px-2" data-id="<?php echo $t['id']; ?>">
                                <i class="fa-solid fa-trash-can"></i> Purge
                            </button>
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
                <a class="page-link bg-card border-secondary text-secondary" href="?page=<?php echo $page-1; ?>">
                    <i class="fa-solid fa-angle-left"></i>
                </a>
            </li>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                    <a class="page-link <?php echo $page == $i ? 'bg-primary text-white fw-bold border-primary' : 'bg-card border-secondary text-secondary'; ?>" href="?page=<?php echo $i; ?>">
                        <?php echo $i; ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                <a class="page-link bg-card border-secondary text-secondary" href="?page=<?php echo $page+1; ?>">
                    <i class="fa-solid fa-angle-right"></i>
                </a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
