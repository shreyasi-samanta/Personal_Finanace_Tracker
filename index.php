<?php
require_once __DIR__ . '/config/db.php';
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FinanceTracker - Take Control of Your Personal Finances</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome v6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-dark text-light">

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-darker border-bottom border-secondary sticky-top px-md-4 py-3">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <i class="fa-solid fa-wallet text-emerald fs-4 me-2"></i>
                <span class="fs-4 fw-bold font-outfit tracking-wide">Finance<span class="text-emerald">Tracker</span></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarText">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarText">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-lg-3">
                    <li class="nav-item"><a class="nav-link text-secondary" href="#features">Features</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link text-secondary" href="#contact">Contact</a></li>
                </ul>
                <div class="d-flex gap-2">
                    <?php if ($is_logged_in): ?>
                        <a href="dashboard.php" class="btn btn-emerald btn-sm px-4 py-2">Go to Dashboard <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-light btn-sm px-4 py-2 border-secondary">Log In</a>
                        <a href="register.php" class="btn btn-emerald btn-sm px-4 py-2">Get Started</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="landing-hero py-5 min-vh-90 d-flex align-items-center">
        <div class="container py-5">
            <div class="row align-items-center gap-5 gap-lg-0">
                <div class="col-lg-6">
                    <span class="badge bg-emerald-glow text-emerald border border-success border-opacity-25 px-3 py-2 rounded-pill mb-3 fw-bold small">
                        <i class="fa-solid fa-sparkles me-1 text-emerald"></i> Modern Smart Wealth Management
                    </span>
                    <h1 class="display-4 fw-bold font-outfit text-white mb-3" style="line-height: 1.15;">
                        Control Your Money.<br>
                        Build Your <span class="text-emerald">Financial Future</span>.
                    </h1>
                    <p class="text-secondary fs-5 mb-4 pe-md-4">
                        Track income, expenses, set custom monthly budget goals, check detailed reports, and watch your savings grow with our modern security-focused personal financial command center.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <?php if ($is_logged_in): ?>
                            <a href="dashboard.php" class="btn btn-emerald btn-lg px-4 py-2.5">Open Your Dashboard <i class="fa-solid fa-arrow-right ms-1"></i></a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-emerald btn-lg px-4 py-2.5">Sign Up For Free <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a>
                            <a href="#features" class="btn btn-outline-light btn-lg px-4 py-2.5 border-secondary text-secondary">See Features</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="glass-card p-4 rounded-3 border border-secondary shadow-lg">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="font-outfit fw-bold text-secondary text-uppercase small">Dashboard Sample</span>
                            <span class="badge bg-success-subtle text-emerald px-2 py-1 small"><i class="fa-solid fa-circle-check me-1"></i>Live Connected</span>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <div class="bg-darker p-3 rounded border border-secondary text-start">
                                    <span class="text-muted small">Current Balance</span>
                                    <h4 class="mb-0 text-emerald font-outfit mt-1 fw-bold">$5,830.40</h4>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-darker p-3 rounded border border-secondary text-start">
                                    <span class="text-muted small">Active Budgets</span>
                                    <h4 class="mb-0 text-white font-outfit mt-1 fw-bold">85% Limit</h4>
                                </div>
                            </div>
                        </div>
                        <div class="bg-darker p-3 rounded border border-secondary text-start mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">Savings Goal (Emergency Fund)</span>
                                <span class="text-emerald small font-outfit fw-bold">75%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar progress-bar-glow-emerald" style="width: 75%"></div>
                            </div>
                        </div>
                        <div class="bg-darker p-3 rounded border border-secondary">
                            <span class="text-muted small d-block mb-2">Recent Analytics</span>
                            <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-secondary">
                                <span class="small"><i class="fa-solid fa-money-bill-wave text-emerald me-2"></i>Freelance Work</span>
                                <span class="small text-emerald fw-bold">+$1,250.00</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-1 mt-1">
                                <span class="small"><i class="fa-solid fa-utensils text-rose me-2"></i>Food & Dining</span>
                                <span class="small text-rose fw-bold">-$45.20</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5 bg-card border-top border-bottom border-secondary">
        <div class="container py-5 text-center">
            <h2 class="display-6 fw-bold font-outfit text-white mb-2">Full-Featured Wealth Management</h2>
            <p class="text-secondary mb-5">Everything you need to analyze, save, and grow your wealth in one application</p>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card-premium p-4 text-start h-100 card-glow-emerald">
                        <div class="d-inline-flex align-items-center justify-content-center bg-dark text-emerald rounded-3 p-3 mb-3 border border-secondary">
                            <i class="fa-solid fa-wallet fs-4"></i>
                        </div>
                        <h4 class="font-outfit text-white mb-2 fw-semibold">Income & Expense CRUD</h4>
                        <p class="text-secondary mb-0">Record transaction histories, categorize cashflow, and easily monitor inputs and outputs through visual layouts.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-premium p-4 text-start h-100 card-glow-primary">
                        <div class="d-inline-flex align-items-center justify-content-center bg-dark text-primary rounded-3 p-3 mb-3 border border-secondary">
                            <i class="fa-solid fa-chart-line fs-4"></i>
                        </div>
                        <h4 class="font-outfit text-white mb-2 fw-semibold">Budget Planning & Alert</h4>
                        <p class="text-secondary mb-0">Define category-specific monthly budget caps. Progress bars change colors and alert you when allocations exceed targets.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-premium p-4 text-start h-100 card-glow-rose">
                        <div class="d-inline-flex align-items-center justify-content-center bg-dark text-rose rounded-3 p-3 mb-3 border border-secondary">
                            <i class="fa-solid fa-bullseye fs-4"></i>
                        </div>
                        <h4 class="font-outfit text-white mb-2 fw-semibold">Savings Goals</h4>
                        <p class="text-secondary mb-0">Establish financial targets, make contributions from savings, and track your milestone completions with charts.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container py-5">
            <div class="row align-items-center g-5">
                <div class="col-lg-6 text-start">
                    <h2 class="display-6 fw-bold font-outfit text-white mb-3">Designed For Financial Freedom</h2>
                    <p class="text-secondary mb-3">
                        Our Personal Finance Tracker is built for individuals who want complete clarity over where their money goes. By using robust database design and security standards, we protect your records while offering actionable visual insights.
                    </p>
                    <p class="text-secondary mb-4">
                        Powered by secure PHP sessions, filtered analytics, dynamic category configurations, and visual dashboards built with Chart.js, managing cashflow has never been more straightforward.
                    </p>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-check text-emerald me-2 fs-5"></i>
                                <span class="fw-semibold">Bcrypt Passwords</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-check text-emerald me-2 fs-5"></i>
                                <span class="fw-semibold">PDO Prepared Stmts</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-check text-emerald me-2 fs-5"></i>
                                <span class="fw-semibold">Anti-CSRF Protection</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-check text-emerald me-2 fs-5"></i>
                                <span class="fw-semibold">100% AJAX Forms</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card-premium p-4 bg-card border-secondary text-start">
                        <h4 class="font-outfit text-white mb-3 fw-bold"><i class="fa-solid fa-shield-halved text-emerald me-2"></i>Security Standard</h4>
                        <p class="text-secondary small">
                            We take coding practices seriously. Our architecture incorporates parameterized SQL bindings, escaping protocols to eliminate Cross-Site Scripting, and token-validated operations to prevent CSRF exploits.
                        </p>
                        <hr class="border-secondary my-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Core Engine:</span>
                            <span class="badge bg-dark border border-secondary text-secondary">PHP 8 + MySQL</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 bg-card border-top border-secondary">
        <div class="container py-5">
            <div class="max-width-600 mx-auto text-center" style="max-width: 600px;">
                <h2 class="display-6 fw-bold font-outfit text-white mb-2">Connect With Us</h2>
                <p class="text-secondary mb-4">Have questions or feedback? Drop us a line below.</p>
                <form id="contactForm" class="text-start">
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Your Name</label>
                        <input type="text" class="form-control" placeholder="e.g. Jane Smith" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small">Email Address</label>
                        <input type="email" class="form-control" placeholder="e.g. jane@example.com" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary small">Message</label>
                        <textarea class="form-control" rows="4" placeholder="Type your message here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-emerald w-100 py-2.5">Send Message</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4 bg-darker border-top border-secondary text-center text-muted small">
        <div class="container">
            <p class="mb-1">© 2026 FinanceTracker. Developed securely using PHP, MySQL, Bootstrap 5, AJAX, and Chart.js.</p>
            <p class="mb-0">Protected with cryptographically secure session IDs and SQL parameterized binding.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById("contactForm").addEventListener("submit", function(e) {
            e.preventDefault();
            Swal.fire({
                icon: "success",
                title: "Message Sent",
                text: "Thank you! We've received your query.",
                background: "#121824",
                color: "#f3f4f6",
                confirmButtonColor: "#10b981"
            }).then(() => {
                document.getElementById("contactForm").reset();
            });
        });
    </script>
</body>
</html>
