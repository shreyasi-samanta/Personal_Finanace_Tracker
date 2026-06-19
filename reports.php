<?php
require_once __DIR__ . '/config/db.php';

// Enforce login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// ----------------------------------------------------
// Premium CSV Export Action (Pre-header Check)
// ----------------------------------------------------
if (isset($_GET['export_csv'])) {
    $filter_range = $_GET['range'] ?? 'month';
    $year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $month = isset($_GET['month']) && is_numeric($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';

    $where_dates = "";
    $params = [$user_id];

    if ($filter_range === 'month') {
        $where_dates = "AND MONTH(t.date) = ? AND YEAR(t.date) = ?";
        $params[] = $month;
        $params[] = $year;
    } elseif ($filter_range === 'year') {
        $where_dates = "AND YEAR(t.date) = ?";
        $params[] = $year;
    } elseif ($filter_range === 'custom') {
        if (!empty($start_date) && !empty($end_date)) {
            $where_dates = "AND t.date BETWEEN ? AND ?";
            $params[] = $start_date;
            $params[] = $end_date;
        } else {
            $where_dates = "AND t.date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        }
    }

    try {
        $stmt = $pdo->prepare("
            SELECT t.date, c.name as category_name, t.type, t.amount, t.description 
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ? $where_dates
            ORDER BY t.date DESC, t.id DESC
        ");
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Send CSV headers
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=FinanceReport_' . $filter_range . '_' . date('Ymd_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        
        // Write header columns
        fputcsv($output, ['Date', 'Category', 'Type', 'Amount ($)', 'Description']);
        
        foreach ($rows as $row) {
            fputcsv($output, [
                $row['date'],
                $row['category_name'],
                ucfirst($row['type']),
                number_format($row['amount'], 2, '.', ''),
                $row['description']
            ]);
        }
        
        fclose($output);
        exit;
    } catch (PDOException $e) {
        die("Export failed: " . $e->getMessage());
    }
}

// Proceed to HTML rendering
$page_title = 'Reports';
$page_js = 'reports.js';

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>

<!-- Set meta for CSRF Token in client requests -->
<meta name="csrf-token" content="<?php echo generate_csrf_token(); ?>">

<!-- Page Header (Hides during printing) -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2 d-print-none">
    <div>
        <h2 class="font-outfit fw-bold text-white mb-1">Financial Analytics & Reports</h2>
        <p class="text-secondary mb-0">Generate, visualize, and print complete records of cash performance.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-light btn-sm px-3 py-2 border-secondary" id="printReportBtn">
            <i class="fa-solid fa-print me-1"></i> Print Report
        </button>
        <button class="btn btn-emerald btn-sm px-3 py-2" onclick="triggerCSVDownload()">
            <i class="fa-solid fa-file-csv me-1"></i> Export CSV
        </button>
    </div>
</div>

<!-- Filters Panel (Hides during printing) -->
<div class="card-premium p-3 mb-4 d-print-none">
    <form id="reportFilterForm" class="row g-3 align-items-end">
        <div class="col-sm-6 col-md-3">
            <label class="form-label text-secondary small">Reporting Period</label>
            <select name="range" id="rep_range" class="form-select">
                <option value="month" selected>By Month</option>
                <option value="year">By Year</option>
                <option value="custom">Custom Range</option>
            </select>
        </div>
        
        <!-- Month Selector (shown for 'month' range) -->
        <div class="col-sm-6 col-md-3 range-month-select">
            <label class="form-label text-secondary small">Month</label>
            <select name="month" class="form-select">
                <?php
                $currentMonth = (int)date('m');
                for ($m = 1; $m <= 12; $m++) {
                    $monthName = date('F', mktime(0, 0, 0, $m, 1));
                    $selected = $currentMonth === $m ? 'selected' : '';
                    echo "<option value='{$m}' {$selected}>{$monthName}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Year Selector (shown for 'month' and 'year' ranges) -->
        <div class="col-sm-6 col-md-2 range-year-select">
            <label class="form-label text-secondary small">Year</label>
            <select name="year" class="form-select">
                <?php
                $currentYear = (int)date('Y');
                for ($y = $currentYear - 4; $y <= $currentYear + 1; $y++) {
                    $selected = $currentYear === $y ? 'selected' : '';
                    echo "<option value='{$y}' {$selected}>{$y}</option>";
                }
                ?>
            </select>
        </div>

        <!-- Custom Start Date (shown for 'custom' range) -->
        <div class="col-sm-6 col-md-2 range-custom-date d-none">
            <label class="form-label text-secondary small">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?php echo date('Y-m-d', strtotime('-30 days')); ?>">
        </div>

        <!-- Custom End Date (shown for 'custom' range) -->
        <div class="col-sm-6 col-md-2 range-custom-date d-none">
            <label class="form-label text-secondary small">End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
        </div>

        <div class="col-12 col-md-2">
            <button type="submit" class="btn btn-primary w-100 py-2 btn-sm"><i class="fa-solid fa-rotate me-1"></i> Update View</button>
        </div>
    </form>
</div>

<!-- Print Report Brand Header (Visible ONLY during print layout) -->
<div class="d-none d-print-block mb-5 text-center text-dark">
    <h1 class="font-outfit fw-bold mb-1">FINANCE REPORT SUMMARY</h1>
    <p class="text-muted small">Generated on <?php echo date('Y-m-d H:i:s'); ?> for <?php echo sanitize($_SESSION['username']); ?></p>
    <hr style="border-top: 2px solid #000;">
</div>

<!-- Summary Metrics Table Grid -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 bg-card border-secondary text-print-dark">
            <span class="text-secondary small d-block mb-1">Income Received</span>
            <h4 class="font-outfit fw-bold text-emerald mb-0" id="rep-total-income">$0.00</h4>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 bg-card border-secondary text-print-dark">
            <span class="text-secondary small d-block mb-1">Expenses Incurred</span>
            <h4 class="font-outfit fw-bold text-rose mb-0" id="rep-total-expenses">$0.00</h4>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 bg-card border-secondary text-print-dark">
            <span class="text-secondary small d-block mb-1">Net Balance</span>
            <h4 class="font-outfit fw-bold text-white mb-0" id="rep-net-balance">$0.00</h4>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card-premium p-3 bg-card border-secondary text-print-dark">
            <span class="text-secondary small d-block mb-1">Savings Rate</span>
            <h4 class="font-outfit fw-bold text-white mb-0" id="rep-savings-rate">0.0%</h4>
        </div>
    </div>
</div>

<!-- Report Graphs layout -->
<div class="row g-4">
    <!-- Chart A: Group comparison -->
    <div class="col-lg-8">
        <div class="card-premium p-3 bg-card border-secondary h-100 text-print-dark">
            <h5 class="font-outfit fw-bold text-white mb-3"><i class="fa-solid fa-chart-column text-emerald me-2 d-print-none"></i>Monthly Income vs Expense Compare</h5>
            <div class="chart-container" style="position: relative; height: 320px;">
                <canvas id="reportCompareChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Chart B: Category Breakdown -->
    <div class="col-lg-4">
        <div class="card-premium p-3 bg-card border-secondary h-100 text-print-dark">
            <h5 class="font-outfit fw-bold text-white mb-3"><i class="fa-solid fa-chart-pie text-rose me-2 d-print-none"></i>Expense Category Breakdown</h5>
            <div class="chart-container" style="position: relative; height: 280px;">
                <canvas id="reportCategoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle filter view fields depending on Period Range Select
document.addEventListener("DOMContentLoaded", function () {
    const rangeSelect = document.getElementById("rep_range");
    const monthDivs = document.querySelectorAll(".range-month-select");
    const yearDivs = document.querySelectorAll(".range-year-select");
    const customDivs = document.querySelectorAll(".range-custom-date");

    if (rangeSelect) {
        rangeSelect.addEventListener("change", function () {
            const val = rangeSelect.value;
            
            if (val === 'month') {
                monthDivs.forEach(el => el.classList.remove("d-none"));
                yearDivs.forEach(el => el.classList.remove("d-none"));
                customDivs.forEach(el => el.classList.add("d-none"));
            } else if (val === 'year') {
                monthDivs.forEach(el => el.classList.add("d-none"));
                yearDivs.forEach(el => el.classList.remove("d-none"));
                customDivs.forEach(el => el.classList.add("d-none"));
            } else if (val === 'custom') {
                monthDivs.forEach(el => el.classList.add("d-none"));
                yearDivs.forEach(el => el.classList.add("d-none"));
                customDivs.forEach(el => el.classList.remove("d-none"));
            }
        });
    }
});

// Trigger download for CSV matching the query string
function triggerCSVDownload() {
    const form = document.getElementById("reportFilterForm");
    const queryParams = new URLSearchParams(new FormData(form)).toString();
    window.location.href = `reports.php?export_csv=true&${queryParams}`;
}
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
