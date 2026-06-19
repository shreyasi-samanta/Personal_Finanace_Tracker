<?php
require_once __DIR__ . '/../config/db.php';

// Enforce login
if (!isset($_SESSION['user_id'])) {
    send_json_response('error', 'Unauthorized access.');
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // ----------------------------------------------------
    // Action 1: Dashboard Analytics & Widgets Data
    // ----------------------------------------------------
    if ($action === 'dashboard_data') {
        try {
            // A. Overall Metrics
            // Total Income
            $incStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0.00) FROM transactions WHERE user_id = ? AND type = 'income'");
            $incStmt->execute([$user_id]);
            $total_income = floatval($incStmt->fetchColumn());

            // Total Expense
            $expStmt = $pdo->prepare("SELECT COALESCE(SUM(amount), 0.00) FROM transactions WHERE user_id = ? AND type = 'expense'");
            $expStmt->execute([$user_id]);
            $total_expenses = floatval($expStmt->fetchColumn());

            // Current Balance
            $current_balance = $total_income - $total_expenses;

            // Accumulated Savings (Sum of current amounts in savings goals)
            $savStmt = $pdo->prepare("SELECT COALESCE(SUM(current_amount), 0.00) FROM goals WHERE user_id = ?");
            $savStmt->execute([$user_id]);
            $savings = floatval($savStmt->fetchColumn());

            // B. Recent Transactions (Limit 5)
            $txStmt = $pdo->prepare("
                SELECT t.id, t.amount, t.date, t.description, t.type, 
                       c.name as category_name, c.icon, c.color
                FROM transactions t
                JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = ?
                ORDER BY t.date DESC, t.id DESC
                LIMIT 5
            ");
            $txStmt->execute([$user_id]);
            $recent_transactions = $txStmt->fetchAll();

            // C. Chart 1: Income vs Expense (Past 6 Months)
            $barStmt = $pdo->prepare("
                SELECT DATE_FORMAT(date, '%Y-%m') as ym_key,
                       DATE_FORMAT(date, '%b %Y') as month_label,
                       SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_total,
                       SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_total
                FROM transactions
                WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                GROUP BY ym_key, month_label
                ORDER BY ym_key ASC
            ");
            $barStmt->execute([$user_id]);
            $bar_results = $barStmt->fetchAll();

            $income_vs_expense = [
                'labels' => [],
                'income' => [],
                'expenses' => []
            ];
            foreach ($bar_results as $row) {
                $income_vs_expense['labels'][] = $row['month_label'];
                $income_vs_expense['income'][] = floatval($row['income_total']);
                $income_vs_expense['expenses'][] = floatval($row['expense_total']);
            }

            // D. Chart 2: Expense by Category (Current Month)
            $doughnutStmt = $pdo->prepare("
                SELECT c.name as category_name, SUM(t.amount) as total_amount, c.color
                FROM transactions t
                JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = ? AND t.type = 'expense' AND MONTH(t.date) = MONTH(CURDATE()) AND YEAR(t.date) = YEAR(CURDATE())
                GROUP BY c.id, c.name, c.color
                ORDER BY total_amount DESC
            ");
            $doughnutStmt->execute([$user_id]);
            $doughnut_results = $doughnutStmt->fetchAll();

            $expense_by_category = [
                'labels' => [],
                'amounts' => [],
                'colors' => []
            ];
            foreach ($doughnut_results as $row) {
                $expense_by_category['labels'][] = $row['category_name'];
                $expense_by_category['amounts'][] = floatval($row['total_amount']);
                $expense_by_category['colors'][] = $row['color'];
            }

            // E. Chart 3: Savings Trend (Cumulative Net Savings over 6 Months)
            $trend_labels = [];
            $trend_savings = [];
            $cumulative = 0;
            
            // Fetch month-by-month net values
            foreach ($bar_results as $row) {
                $month_net = floatval($row['income_total']) - floatval($row['expense_total']);
                $cumulative += $month_net;
                
                $trend_labels[] = $row['month_label'];
                $trend_savings[] = $cumulative;
            }
            
            $savings_trend = [
                'labels' => $trend_labels,
                'savings' => $trend_savings
            ];

            // Pack response
            $dashboard_data = [
                'total_income' => $total_income,
                'total_expenses' => $total_expenses,
                'current_balance' => $current_balance,
                'savings' => $savings,
                'recent_transactions' => $recent_transactions,
                'income_vs_expense' => $income_vs_expense,
                'expense_by_category' => $expense_by_category,
                'savings_trend' => $savings_trend
            ];

            send_json_response('success', 'Dashboard details aggregated successfully.', ['data' => $dashboard_data]);

        } catch (PDOException $e) {
            send_json_response('error', 'Query aggregation failed: ' . $e->getMessage());
        }
    }

    // ----------------------------------------------------
    // Action 2: Custom Reports Filters Data
    // ----------------------------------------------------
    elseif ($action === 'report_data') {
        $filter_range = $_GET['range'] ?? 'month'; // 'month', 'year', 'custom'
        $year = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $month = isset($_GET['month']) && is_numeric($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';

        // Formulate WHERE clause dates
        $where_dates = "";
        $params = [$user_id];

        if ($filter_range === 'month') {
            $where_dates = "AND MONTH(date) = ? AND YEAR(date) = ?";
            $params[] = $month;
            $params[] = $year;
        } elseif ($filter_range === 'year') {
            $where_dates = "AND YEAR(date) = ?";
            $params[] = $year;
        } elseif ($filter_range === 'custom') {
            if (!empty($start_date) && !empty($end_date)) {
                $where_dates = "AND date BETWEEN ? AND ?";
                $params[] = $start_date;
                $params[] = $end_date;
            } else {
                // Default fallback to past 30 days
                $where_dates = "AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            }
        }

        try {
            // A. Calculate report total sums
            $sumStmt = $pdo->prepare("
                SELECT 
                    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as total_income,
                    COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as total_expenses
                FROM transactions
                WHERE user_id = ? $where_dates
            ");
            $sumStmt->execute($params);
            $summary_result = $sumStmt->fetch();
            
            $total_income = floatval($summary_result['total_income']);
            $total_expenses = floatval($summary_result['total_expenses']);
            $net_balance = $total_income - $total_expenses;

            $summary = [
                'total_income' => $total_income,
                'total_expenses' => $total_expenses,
                'net_balance' => $net_balance
            ];

            // B. Chart compare (Inflows vs Outflows group details)
            // If filtering by month: group by day of month. If year/custom: group by month.
            $compare_labels = [];
            $compare_income = [];
            $compare_expenses = [];

            if ($filter_range === 'month') {
                // Group by Day
                $compStmt = $pdo->prepare("
                    SELECT DAY(date) as day_key,
                           DATE_FORMAT(date, '%d %b') as date_label,
                           SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_total,
                           SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_total
                    FROM transactions
                    WHERE user_id = ? $where_dates
                    GROUP BY DAY(date), date_label
                    ORDER BY DAY(date) ASC
                ");
                $compStmt->execute($params);
                $comp_results = $compStmt->fetchAll();

                foreach ($comp_results as $row) {
                    $compare_labels[] = $row['date_label'];
                    $compare_income[] = floatval($row['income_total']);
                    $compare_expenses[] = floatval($row['expense_total']);
                }
            } else {
                // Group by Month
                $compStmt = $pdo->prepare("
                    SELECT DATE_FORMAT(date, '%Y-%m') as ym_key,
                           DATE_FORMAT(date, '%b %Y') as month_label,
                           SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_total,
                           SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_total
                    FROM transactions
                    WHERE user_id = ? $where_dates
                    GROUP BY ym_key, month_label
                    ORDER BY ym_key ASC
                ");
                $compStmt->execute($params);
                $comp_results = $compStmt->fetchAll();

                foreach ($comp_results as $row) {
                    $compare_labels[] = $row['month_label'];
                    $compare_income[] = floatval($row['income_total']);
                    $compare_expenses[] = floatval($row['expense_total']);
                }
            }

            $monthly_compare = [
                'labels' => $compare_labels,
                'income' => $compare_income,
                'expenses' => $compare_expenses
            ];

            // C. Expense Category distribution breakdown
            $distStmt = $pdo->prepare("
                SELECT c.name as category_name, SUM(t.amount) as total_amount, c.color
                FROM transactions t
                JOIN categories c ON t.category_id = c.id
                WHERE t.user_id = ? AND t.type = 'expense' $where_dates
                GROUP BY c.id, c.name, c.color
                ORDER BY total_amount DESC
            ");
            $distStmt->execute($params);
            $dist_results = $distStmt->fetchAll();

            $category_distribution = [
                'labels' => [],
                'amounts' => [],
                'colors' => []
            ];
            foreach ($dist_results as $row) {
                $category_distribution['labels'][] = $row['category_name'];
                $category_distribution['amounts'][] = floatval($row['total_amount']);
                $category_distribution['colors'][] = $row['color'];
            }

            // Pack response
            $report_data = [
                'summary' => $summary,
                'monthly_compare' => $monthly_compare,
                'category_distribution' => $category_distribution
            ];

            send_json_response('success', 'Report details loaded.', ['data' => $report_data]);

        } catch (PDOException $e) {
            send_json_response('error', 'Database reports aggregation failed: ' . $e->getMessage());
        }
    }
}
?>
