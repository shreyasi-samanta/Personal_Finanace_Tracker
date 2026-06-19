<?php
require_once __DIR__ . '/includes/auth_check.php';

$page_title = 'Savings Goals';
$page_js = 'goals.js';

$user_id = $_SESSION['user_id'];
$filter_status = $_GET['status'] ?? 'active';

try {
    $stmt = $pdo->prepare("
        SELECT * 
        FROM goals 
        WHERE user_id = ? AND (status = ? OR ? = 'all')
        ORDER BY target_date ASC, id DESC
    ");
    $stmt->execute([$user_id, $filter_status, $filter_status]);
    $goals = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error retrieving savings goals: " . $e->getMessage());
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Set meta for CSRF Token in client requests -->
<meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 class="font-outfit fw-bold text-white mb-1">Financial Savings Goals</h2>
        <p class="text-secondary mb-0">Establish future milestones, save regularly, and track target progress.</p>
    </div>
    <div>
        <button class="btn btn-emerald px-3 py-2 btn-sm" data-bs-toggle="modal" data-bs-target="#addGoalModal">
            <i class="fa-solid fa-plus me-1"></i> Create Savings Goal
        </button>
    </div>
</div>

<!-- Filters Card -->
<div class="card-premium p-3 mb-4">
    <div class="d-flex gap-2 flex-wrap">
        <a href="goals.php?status=active" class="btn btn-sm <?php echo $filter_status === 'active' ? 'btn-emerald text-dark fw-bold' : 'btn-outline-secondary text-secondary border-secondary'; ?>">
            <i class="fa-solid fa-hourglass-half me-1"></i> Active Goals
        </a>
        <a href="goals.php?status=completed" class="btn btn-sm <?php echo $filter_status === 'completed' ? 'btn-emerald text-dark fw-bold' : 'btn-outline-secondary text-secondary border-secondary'; ?>">
            <i class="fa-solid fa-circle-check me-1"></i> Completed
        </a>
        <a href="goals.php?status=all" class="btn btn-sm <?php echo $filter_status === 'all' ? 'btn-emerald text-dark fw-bold' : 'btn-outline-secondary text-secondary border-secondary'; ?>">
            <i class="fa-solid fa-list me-1"></i> All Goals
        </a>
    </div>
</div>

<!-- Goals Visual Cards Grid -->
<div class="row g-4">
    <?php if (count($goals) === 0): ?>
        <div class="col-12 text-center text-muted py-5 card-premium p-4">
            <i class="fa-solid fa-bullseye fs-2 mb-2 d-block text-secondary"></i>
            No savings goals found matching your filter selection.
        </div>
    <?php else: ?>
        <?php foreach ($goals as $g): ?>
            <?php
                $target = floatval($g['target_amount']);
                $current = floatval($g['current_amount']);
                $percent = $target > 0 ? ($current / $target) * 100 : 0;
                $percentFormatted = number_format($percent, 1);
                
                // Calculate Days Left
                $targetDate = strtotime($g['target_date']);
                $today = strtotime(date('Y-m-d'));
                $diff = $targetDate - $today;
                $daysLeft = round($diff / (60 * 60 * 24));
                
                // Styling classes
                $isCompleted = $g['status'] === 'completed' || $current >= $target;
                $barColor = $isCompleted ? '#10b981' : '#8b5cf6';
                $barGlow = $isCompleted ? 'rgba(16, 185, 129, 0.5)' : 'rgba(139, 92, 246, 0.5)';
                $statusBadge = $isCompleted ? 
                    '<span class="badge bg-success-subtle text-success px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i>Completed</span>' : 
                    ($daysLeft < 0 ? 
                        '<span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i>Overdue</span>' : 
                        '<span class="badge bg-primary-subtle text-primary px-2 py-1"><i class="fa-solid fa-hourglass-half me-1"></i>Active</span>');
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card-premium p-3 h-100 d-flex flex-column justify-content-between" style="transition: var(--transition-smooth);" onmouseover="this.style.borderColor='<?php echo $barColor; ?>'; this.style.boxShadow='0 8px 30px -10px <?php echo $barGlow; ?>';" onmouseout="this.style.borderColor='var(--border-color)'; this.style.boxShadow='none';">
                    <div>
                        <!-- Title & Status -->
                        <div class="d-flex justify-content-between align-items-start mb-3 gap-2">
                            <h5 class="font-outfit fw-bold text-white mb-0 text-truncate"><?php echo sanitize($g['name']); ?></h5>
                            <?php echo $statusBadge; ?>
                        </div>

                        <!-- Progress Circle or Bar representation -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <span class="text-secondary small">Saved: <span class="fw-bold text-white">$<?php echo number_format($current, 2); ?></span></span>
                                <span class="text-secondary small">Target: <span class="fw-semibold text-muted">$<?php echo number_format($target, 2); ?></span></span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo min($percent, 100); ?>%; background-color: <?php echo $barColor; ?>; box-shadow: 0 0 8px <?php echo $barColor; ?>;"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-2 align-items-center">
                                <span class="small font-outfit fw-bold" style="color: <?php echo $barColor; ?>;"><?php echo $percentFormatted; ?>% completed</span>
                                <?php if (!$isCompleted && $daysLeft >= 0): ?>
                                    <span class="small text-muted"><i class="fa-regular fa-clock me-1"></i><?php echo $daysLeft; ?> days left</span>
                                <?php elseif ($isCompleted): ?>
                                    <span class="small text-emerald"><i class="fa-solid fa-gift me-1"></i>Goal Achieved!</span>
                                <?php else: ?>
                                    <span class="small text-rose"><i class="fa-solid fa-calendar-xmark me-1"></i>Overdue</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Controls -->
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top border-secondary gap-1">
                        <div>
                            <?php if (!$isCompleted): ?>
                                <button class="btn btn-emerald btn-sm py-1 px-3 btn-contribute-goal" data-id="<?php echo $g['id']; ?>" data-name="<?php echo sanitize($g['name']); ?>">
                                    <i class="fa-solid fa-vault me-1"></i> Deposit
                                </button>
                            <?php else: ?>
                                <button class="btn btn-dark border-secondary btn-sm py-1 px-3" disabled>
                                    <i class="fa-solid fa-check-double me-1"></i> Done
                                </button>
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-outline-light btn-sm btn-edit-goal py-1 px-2.5" data-id="<?php echo $g['id']; ?>">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                            <button class="btn btn-outline-danger btn-sm btn-delete-goal py-1 px-2.5" data-id="<?php echo $g['id']; ?>">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- ADD GOAL MODAL -->
<div class="modal fade" id="addGoalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="goalForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <div class="modal-content text-light">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-plus text-emerald me-2"></i>Create Savings Goal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="goal_name" class="form-label text-secondary small">Goal Name</label>
                        <input type="text" class="form-control" id="goal_name" name="name" placeholder="e.g. Emergency Fund or New Car" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="goal_target" class="form-label text-secondary small">Target Amount ($)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="goal_target" name="target_amount" placeholder="0.00" required>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="goal_current" class="form-label text-secondary small">Starting Savings ($)</label>
                            <input type="number" step="0.01" min="0.00" class="form-control" id="goal_current" name="current_amount" value="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="goal_date" class="form-label text-secondary small">Target Date</label>
                        <input type="date" class="form-control" id="goal_date" name="target_date" value="<?php echo date('Y-m-d', strtotime('+6 months')); ?>" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm border-secondary text-secondary px-3 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-emerald btn-sm px-4 py-2">Create Goal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- EDIT GOAL MODAL -->
<div class="modal fade" id="editGoalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editGoalForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id" id="edit_goal_id">
            <div class="modal-content text-light">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen-to-square text-emerald me-2"></i>Modify Savings Goal</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_goal_name" class="form-label text-secondary small">Goal Name</label>
                        <input type="text" class="form-control" id="edit_goal_name" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 mb-3">
                            <label for="edit_goal_target" class="form-label text-secondary small">Target Amount ($)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control" id="edit_goal_target" name="target_amount" required>
                        </div>
                        <div class="col-sm-6 mb-3">
                            <label for="edit_goal_current" class="form-label text-secondary small">Current Savings ($)</label>
                            <input type="number" step="0.01" min="0.00" class="form-control" id="edit_goal_current" name="current_amount" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_goal_date" class="form-label text-secondary small">Target Date</label>
                        <input type="date" class="form-control" id="edit_goal_date" name="target_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_goal_status" class="form-label text-secondary small">Status</label>
                        <select class="form-select" id="edit_goal_status" name="status" required>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm border-secondary text-secondary px-3 py-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-emerald btn-sm px-4 py-2">Update Goal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- DEPOSIT / CONTRIBUTION MODAL -->
<div class="modal fade" id="contributeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <form id="contributeForm" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
            <input type="hidden" name="id" id="contribute_goal_id">
            <div class="modal-content text-light">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-vault text-emerald me-2"></i>Deposit Savings</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-secondary small">Add money to your savings target: <strong id="contributeGoalName" class="text-white">Goal</strong></p>
                    <div class="mb-3">
                        <label for="contrib_amount" class="form-label text-secondary small">Deposit Amount ($)</label>
                        <input type="number" step="0.01" min="0.01" class="form-control text-center fs-4 fw-bold text-emerald" id="contrib_amount" name="amount" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm border-secondary text-secondary px-2.5 py-1.5" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-emerald btn-sm px-3.5 py-1.5">Confirm Deposit</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
