document.addEventListener("DOMContentLoaded", function () {
    let reportCompareChart = null;
    let reportCategoryChart = null;

    const filterForm = document.getElementById("reportFilterForm");
    const printBtn = document.getElementById("printReportBtn");

    function loadReportsData() {
        const queryParams = new URLSearchParams(new FormData(filterForm)).toString();
        
        fetch(`actions/reports.php?action=report_data&${queryParams}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === "success") {
                    updateSummaryTable(res.data.summary);
                    renderCompareChart(res.data.monthly_compare);
                    renderCategoryChart(res.data.category_distribution);
                } else {
                    console.error("Failed to load reports:", res.message);
                }
            })
            .catch(err => console.error("Error loading reports data:", err));
    }

    function updateSummaryTable(summary) {
        document.getElementById("rep-total-income").innerText = "$" + parseFloat(summary.total_income).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById("rep-total-expenses").innerText = "$" + parseFloat(summary.total_expenses).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        
        const balance = parseFloat(summary.net_balance);
        const balEl = document.getElementById("rep-net-balance");
        balEl.innerText = (balance >= 0 ? "+" : "") + "$" + balance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        if (balance >= 0) {
            balEl.className = "fs-4 fw-bold text-emerald";
        } else {
            balEl.className = "fs-4 fw-bold text-rose";
        }

        const savingsRate = summary.total_income > 0 ? (summary.net_balance / summary.total_income) * 100 : 0;
        document.getElementById("rep-savings-rate").innerText = (savingsRate > 0 ? savingsRate.toFixed(1) : "0.0") + "%";
    }

    function renderCompareChart(data) {
        const ctx = document.getElementById("reportCompareChart");
        if (!ctx) return;

        if (reportCompareChart) reportCompareChart.destroy();

        reportCompareChart = new Chart(ctx, {
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

    function renderCategoryChart(data) {
        const ctx = document.getElementById("reportCategoryChart");
        if (!ctx) return;

        if (reportCategoryChart) reportCategoryChart.destroy();

        if (data.amounts.length === 0) {
            reportCategoryChart = new Chart(ctx, {
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

        reportCategoryChart = new Chart(ctx, {
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

    // Submit filter form
    if (filterForm) {
        filterForm.addEventListener("submit", function (e) {
            e.preventDefault();
            loadReportsData();
        });
        
        // Trigger report fetch on change of quick filters
        const quickFilters = filterForm.querySelectorAll("select, input[type='date']");
        quickFilters.forEach(f => {
            f.addEventListener("change", function () {
                loadReportsData();
            });
        });
    }

    // Print Report
    if (printBtn) {
        printBtn.addEventListener("click", function () {
            window.print();
        });
    }

    // Initial load
    if (filterForm) {
        loadReportsData();
    }
});
