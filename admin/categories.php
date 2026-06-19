<?php
$in_admin_folder = true;
require_once __DIR__ . '/../includes/auth_check.php';
enforce_admin();

$page_title = 'Manage Categories';
$page_js = 'admin.js';

try {
    $stmt = $pdo->query("
        SELECT *,
               (SELECT COUNT(*) FROM transactions WHERE category_id = categories.id) as transaction_count
        FROM categories 
        WHERE user_id IS NULL
        ORDER BY type ASC, name ASC
    ");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error retrieving default categories: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Set meta for CSRF Token in client requests -->
<meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-outfit fw-bold text-white mb-1"><i class="fa-solid fa-tags text-primary me-2"></i>Global Categories Manager</h2>
        <p class="text-secondary mb-0">Add default system categories, change icons, and configure colors for dashboard charts.</p>
    </div>
    <div>
        <button class="btn btn-primary px-3 py-2 btn-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fa-solid fa-plus me-1"></i> Add Category
        </button>
    </div>
</div>

<!-- Categories Table -->
<div class="table-responsive table-premium">
    <table class="table table-dark table-striped align-middle mb-0">
        <thead>
            <tr>
                <th style="width: 10%;">ID</th>
                <th style="width: 25%;">Category Name</th>
                <th style="width: 15%;">Type</th>
                <th style="width: 15%;">Icon Symbol</th>
                <th style="width: 15%;">Chart Color</th>
                <th style="width: 10%;">Usage Count</th>
                <th style="width: 10%;" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $c): ?>
                <?php
                    $isIncome = $c['type'] === 'income';
                    $typeBadge = $isIncome ? 
                        '<span class="badge bg-success-subtle text-success px-2 py-1">Income</span>' : 
                        '<span class="badge bg-danger-subtle text-danger px-2 py-1">Expense</span>';
                ?>
                <tr>
                    <td class="text-muted fw-semibold">#<?php echo $c['id']; ?></td>
                    <td class="fw-bold text-white"><?php echo sanitize($c['name']); ?></td>
                    <td><?php echo $typeBadge; ?></td>
                    <td>
                        <span class="d-inline-flex bg-dark rounded p-2 border border-secondary text-white justify-content-center align-items-center" style="width: 38px; height: 38px;">
                            <i class="fa-solid <?php echo sanitize($c['icon']); ?>" style="color: <?php echo sanitize($c['color']); ?>"></i>
                        </span>
                        <code class="small text-muted ms-2"><?php echo sanitize($c['icon']); ?></code>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="d-inline-block rounded-circle border border-dark" style="width: 16px; height: 16px; background-color: <?php echo sanitize($c['color']); ?>;"></span>
                            <code class="small text-secondary"><?php echo strtoupper(sanitize($c['color'])); ?></code>
                        </div>
                    </td>
                    <td class="fw-semibold text-white"><?php echo number_format($c['transaction_count']); ?></td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <button class="btn btn-outline-light btn-sm btn-edit-category py-1 px-2.5" 
                                    data-id="<?php echo $c['id']; ?>"
                                    data-name="<?php echo sanitize($c['name']); ?>"
                                    data-type="<?php echo $c['type']; ?>"
                                    data-icon="<?php echo sanitize($c['icon']); ?>"
                                    data-color="<?php echo sanitize($c['color']); ?>">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm btn-delete-category py-1 px-2.5" 
                                    data-id="<?php echo $c['id']; ?>">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ADD CATEGORY MODAL -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="categoryForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="modal-content text-light">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-plus text-primary me-2"></i>Create Global Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cat_name" class="form-label text-secondary small">Category Name</label>
                        <input type="text" class="form-control" id="cat_name" name="name" placeholder="e.g. Health & Fitness" required>
                    </div>
                    <div class="mb-3">
                        <label for="cat_type" class="form-label text-secondary small">Type</label>
                        <select class="form-select" id="cat_type" name="type" required>
                            <option value="expense" selected>Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="cat_icon" class="form-label text-secondary small">FontAwesome Icon Class</label>
                            <input type="text" class="form-control" id="cat_icon" name="icon" value="fa-tag" required>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="cat_color" class="form-label text-secondary small">Chart Color Hex</label>
                            <input type="color" class="form-control" id="cat_color" name="color" value="#6c757d" required style="height: 44px; padding: 2px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm border-secondary text-secondary px-3 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 py-2">Create Category</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- EDIT CATEGORY MODAL -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editCategoryForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id" id="edit_cat_id">
            <div class="modal-content text-light">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Modify Global Category</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_cat_name" class="form-label text-secondary small">Category Name</label>
                        <input type="text" class="form-control" id="edit_cat_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_cat_type" class="form-label text-secondary small">Type</label>
                        <select class="form-select" id="edit_cat_type" name="type" required>
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="edit_cat_icon" class="form-label text-secondary small">FontAwesome Icon Class</label>
                            <input type="text" class="form-control" id="edit_cat_icon" name="icon" required>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="edit_cat_color" class="form-label text-secondary small">Chart Color Hex</label>
                            <input type="color" class="form-control" id="edit_cat_color" name="color" required style="height: 44px; padding: 2px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm border-secondary text-secondary px-3 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4 py-2">Update Category</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
