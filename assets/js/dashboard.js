document.addEventListener("DOMContentLoaded", function () {
    let incomeExpenseChart = null;
    let expenseCategoryChart = null;
    let savingsTrendChart = null;

    // Load all dashboard data and initialize charts
    function loadDashboardData() {
        fetch("actions/reports.php?action=dashboard_data")
            .then(res => res.json())
            .then(res => {
                if (res.status === "success") {
                    updateMetricCards(res.data);
                    renderRecentTransactions(res.data.recent_transactions);
                    renderIncomeExpenseChart(res.data.income_vs_expense);
                    renderExpenseCategoryChart(res.data.expense_by_category);
                    renderSavingsTrendChart(res.data.savings_trend);
                } else {
                    console.error("Failed to load dashboard data:", res.message);
                }
            })
            .catch(err => console.error("Error fetching dashboard metrics:", err));
    }

    function updateMetricCards(data) {
        document.getElementById("total-income").innerText = "$" + parseFloat(data.total_income).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById("total-expenses").innerText = "$" + parseFloat(data.total_expenses).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById("current-balance").innerText = "$" + parseFloat(data.current_balance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById("total-savings").innerText = "$" + parseFloat(data.savings).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    function renderRecentTransactions(transactions) {
        const tbody = document.getElementById("recent-transactions-tbody");
        if (!tbody) return;
        tbody.innerHTML = "";
        
        if (transactions.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No recent transactions found.</td></tr>`;
            return;
        }

        transactions.forEach(t => {
            const dateStr = new Date(t.date).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
            const amountFormatted = parseFloat(t.amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const typeBadge = t.type === 'income' ? 
                `<span class="badge bg-success-subtle text-success px-2 py-1"><i class="fa-solid fa-arrow-down me-1"></i>Income</span>` : 
                `<span class="badge bg-danger-subtle text-danger px-2 py-1"><i class="fa-solid fa-arrow-up me-1"></i>Expense</span>`;
            const amountColor = t.type === 'income' ? 'text-emerald' : 'text-rose';
            const amountSign = t.type === 'income' ? '+' : '-';
            
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${dateStr}</td>
                <td>
                    <span class="d-inline-block text-center rounded-circle bg-dark me-2" style="width:30px; height:30px; line-height:30px;">
                        <i class="fa-solid ${t.icon}" style="color:${t.color}"></i>
                    </span>
                    ${t.category_name}
                </td>
                <td><span class="text-secondary">${t.description ? t.description : '-'}</span></td>
                <td>${typeBadge}</td>
                <td class="fw-bold ${amountColor}">${amountSign}$${amountFormatted}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    function renderIncomeExpenseChart(data) {
        const ctx = document.getElementById("incomeExpenseChart");
        if (!ctx) return;

        if (incomeExpenseChart) incomeExpenseChart.destroy();

        incomeExpenseChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Income',
                        data: data.income,
                        backgroundColor: '#10b981',
                        borderRadius: 4
                    },
                    {
                        label: 'Expense',
                        data: data.expenses,
                        backgroundColor: '#f43f5e',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#9ca3af', font: { family: 'Outfit' } } }
                },
                scales: {
                    x: { ticks: { color: '#9ca3af' }, grid: { color: '#1e293b' } },
                    y: { ticks: { color: '#9ca3af' }, grid: { color: '#1e293b' } }
                }
            }
        });
    }

    function renderExpenseCategoryChart(data) {
        const ctx = document.getElementById("expenseCategoryChart");
        if (!ctx) return;

        if (expenseCategoryChart) expenseCategoryChart.destroy();

        if (data.amounts.length === 0) {
            // Render default dummy message or clean chart state if no data
            expenseCategoryChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['No Expenses'],
                    datasets: [{ data: [1], backgroundColor: ['#374151'] }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { labels: { color: '#9ca3af', font: { family: 'Outfit' } } }
                    }
                }
            });
            return;
        }

        expenseCategoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.amounts,
                    backgroundColor: data.colors,
                    borderWidth: 1,
                    borderColor: '#121824'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: '#9ca3af', font: { family: 'Outfit', size: 11 } }
                    }
                }
            }
        });
    }

    function renderSavingsTrendChart(data) {
        const ctx = document.getElementById("savingsTrendChart");
        if (!ctx) return;

        if (savingsTrendChart) savingsTrendChart.destroy();

        savingsTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Savings Trend',
                    data: data.savings,
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.15)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#9ca3af', font: { family: 'Outfit' } } }
                },
                scales: {
                    x: { ticks: { color: '#9ca3af' }, grid: { color: '#1e293b' } },
                    y: { ticks: { color: '#9ca3af' }, grid: { color: '#1e293b' } }
                }
            }
        });
    }

    // Initialize Page
    loadDashboardData();
});
