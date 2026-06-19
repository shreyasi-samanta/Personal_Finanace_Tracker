<?php
// Determine the prefix based on directory depth
$path_prefix = (isset($in_admin_folder) && $in_admin_folder) ? '../' : '';
$admin_prefix = (isset($in_admin_folder) && $in_admin_folder) ? '' : 'admin/';

// Get current page file name
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar / Navigation Header -->
<nav class="sidebar bg-card border-end border-secondary d-flex flex-column p-3 text-light" style="min-width: 260px; z-index: 1030;">
    <!-- Brand / Logo -->
    <a href="<?php echo $path_prefix; ?>dashboard.php" class="d-flex align-items-center mb-3 mb-md-5 me-md-auto text-light text-decoration-none">
        <i class="fa-solid fa-wallet text-emerald fs-4 me-2"></i>
        <span class="fs-4 fw-bold font-outfit tracking-wide">Finance<span class="text-emerald">Tracker</span></span>
    </a>
    
    <!-- User Profile Brief -->
    <div class="user-profile-widget d-flex align-items-center p-2 mb-4 bg-dark rounded-3 border border-secondary">
        <div class="avatar-circle bg-emerald text-dark d-flex align-items-center justify-content-center fw-bold me-2" style="width: 38px; height: 38px; border-radius: 50%;">
            <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
        </div>
        <div class="user-info overflow-hidden">
            <h6 class="mb-0 text-truncate font-outfit" style="font-size: 0.95rem;"><?php echo sanitize($_SESSION['username'] ?? 'User'); ?></h6>
            <span class="text-muted text-capitalize" style="font-size: 0.75rem;"><?php echo sanitize($_SESSION['user_role'] ?? 'user'); ?></span>
        </div>
    </div>
    
    <hr class="border-secondary mt-0">
    
    <!-- Nav Items -->
    <ul class="nav nav-pills flex-column mb-auto gap-1">
        <li class="nav-item">
            <a href="<?php echo $path_prefix; ?>dashboard.php" class="nav-link text-light <?php echo $current_page === 'dashboard.php' && !(isset($in_admin_folder) && $in_admin_folder) ? 'active bg-emerald text-dark fw-bold' : ''; ?>">
                <i class="fa-solid fa-chart-pie me-2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?php echo $path_prefix; ?>transactions.php" class="nav-link text-light <?php echo $current_page === 'transactions.php' && !(isset($in_admin_folder) && $in_admin_folder) ? 'active bg-emerald text-dark fw-bold' : ''; ?>">
                <i class="fa-solid fa-money-bill-transfer me-2"></i> Transactions
            </a>
        </li>
        <li>
            <a href="<?php echo $path_prefix; ?>budgets.php" class="nav-link text-light <?php echo $current_page === 'budgets.php' ? 'active bg-emerald text-dark fw-bold' : ''; ?>">
                <i class="fa-solid fa-scale-balanced me-2"></i> Budgets
            </a>
        </li>
        <li>
            <a href="<?php echo $path_prefix; ?>goals.php" class="nav-link text-light <?php echo $current_page === 'goals.php' ? 'active bg-emerald text-dark fw-bold' : ''; ?>">
                <i class="fa-solid fa-bullseye me-2"></i> Savings Goals
            </a>
        </li>
        <li>
            <a href="<?php echo $path_prefix; ?>reports.php" class="nav-link text-light <?php echo $current_page === 'reports.php' && !(isset($in_admin_folder) && $in_admin_folder) ? 'active bg-emerald text-dark fw-bold' : ''; ?>">
                <i class="fa-solid fa-chart-column me-2"></i> Reports
            </a>
        </li>
        
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
            <hr class="border-secondary my-2">
            <span class="text-uppercase text-muted px-3 mb-1 fw-bold" style="font-size: 0.68rem; letter-spacing: 0.05em;">Admin Panel</span>
            
            <li>
                <a href="<?php echo $path_prefix; ?><?php echo $admin_prefix; ?>dashboard.php" class="nav-link text-light <?php echo $current_page === 'dashboard.php' && (isset($in_admin_folder) && $in_admin_folder) ? 'active bg-primary text-white fw-bold' : ''; ?>">
                    <i class="fa-solid fa-gauge-high me-2 text-primary"></i> System Stats
                </a>
            </li>
            <li>
               <a href="/finance_tracker/admin/users.php" class="nav-link text-light <?php echo $current_page === 'users.php' ? 'active bg-primary text-white fw-bold' : ''; ?>">
    <i class="fa-solid fa-users me-2 text-primary"></i> Manage Users
</a>
            </li>
            <li>
                <a href="/finance_tracker/admin/categories.php" class="nav-link text-light <?php echo $current_page === 'categories.php' ? 'active bg-primary text-white fw-bold' : ''; ?>">
    <i class="fa-solid fa-tags me-2 text-primary"></i> Manage Categories
</a>
            </li>
            <li>
                <a href="<?php echo $path_prefix; ?><?php echo $admin_prefix; ?>transactions.php" class="nav-link text-light <?php echo $current_page === 'transactions.php' && (isset($in_admin_folder) && $in_admin_folder) ? 'active bg-primary text-white fw-bold' : ''; ?>">
                    <i class="fa-solid fa-list me-2 text-primary"></i> Global Audit
                </a>
            </li>
        <?php endif; ?>
    </ul>
    
    <hr class="border-secondary">
    
    <!-- Logout -->
    <a href="<?php echo $path_prefix; ?>logout.php" class="btn btn-outline-danger w-100 mt-auto btn-sm py-2">
        <i class="fa-solid fa-right-from-bracket me-1"></i> Log Out
    </a>
</nav>

<!-- Page Content Area Starts -->
<main class="flex-grow-1 p-3 p-md-4 overflow-auto" style="height: 100vh;">
    <!-- Responsive Header Toggle (for small viewports) -->
    <div class="d-md-none bg-card p-3 rounded border border-secondary mb-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-wallet text-emerald fs-5 me-2"></i>
            <span class="fs-5 fw-bold font-outfit">Finance<span class="text-emerald">Tracker</span></span>
        </div>
        <button class="btn btn-outline-light btn-sm" id="sidebarToggle">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>
