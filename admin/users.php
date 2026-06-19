<?php
$in_admin_folder = true;
require_once __DIR__ . '/../includes/auth_check.php';
enforce_admin();

$page_title = 'Manage Users';
$page_js = 'admin.js';

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->query("
        SELECT u.id, u.username, u.email, u.role, u.created_at,
               (SELECT COUNT(*) FROM transactions t WHERE t.user_id = u.id) as transaction_count
        FROM users u
        ORDER BY u.id ASC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error retrieving users: " . $e->getMessage());
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
?>

<!-- Set meta for CSRF Token in client requests -->
<meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">

<!-- Page Header -->
<div class="mb-4">
    <h2 class="font-outfit fw-bold text-white mb-1"><i class="fa-solid fa-users text-primary me-2"></i>User Directory Management</h2>
    <p class="text-secondary mb-0">Control roles, monitor user activity, and revoke user access from the database.</p>
</div>

<!-- Users Table -->
<div class="table-responsive table-premium">
    <table class="table table-dark table-striped align-middle mb-0">
        <thead>
            <tr>
                <th style="width: 8%;">ID</th>
                <th style="width: 25%;">Username</th>
                <th style="width: 28%;">Email</th>
                <th style="width: 15%;">Role</th>
                <th style="width: 12%;">Transactions</th>
                <th style="width: 12%;" class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <?php
                    $isSelf = intval($u['id']) === intval($user_id);
                    $regDate = date("M d, Y", strtotime($u['created_at']));
                    $roleBadge = $u['role'] === 'admin' ? 
                        '<span class="badge bg-primary px-2.5 py-1.5"><i class="fa-solid fa-user-shield me-1"></i>Admin</span>' : 
                        '<span class="badge bg-dark border border-secondary text-secondary px-2.5 py-1.5"><i class="fa-solid fa-user me-1"></i>User</span>';
                ?>
                <tr>
                    <td class="text-muted fw-semibold">#<?php echo $u['id']; ?></td>
                    <td class="fw-bold text-white">
                        <?php echo sanitize($u['username']); ?>
                        <?php if ($isSelf): ?>
                            <span class="badge bg-success-subtle text-success ms-1 small px-1.5 py-0.5">You</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo sanitize($u['email']); ?></td>
                    <td><?php echo $roleBadge; ?></td>
                    <td class="fw-semibold text-primary"><?php echo number_format($u['transaction_count']); ?></td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <!-- Toggle Role Button -->
                            <button class="btn btn-outline-light btn-sm btn-toggle-role py-1 px-2.5" 
                                    data-id="<?php echo $u['id']; ?>" 
                                    <?php echo $isSelf ? 'disabled title="You cannot change your own role"' : ''; ?>>
                                <i class="fa-solid fa-arrows-left-right-to-line"></i> Toggle Role
                            </button>
                            
                            <!-- Delete User Button -->
                            <button class="btn btn-outline-danger btn-sm btn-delete-user py-1 px-2.5" 
                                    data-id="<?php echo $u['id']; ?>" 
                                    data-username="<?php echo sanitize($u['username']); ?>"
                                    <?php echo $isSelf ? 'disabled title="You cannot delete yourself"' : ''; ?>>
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>
