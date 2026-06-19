<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - FinanceTracker</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome v6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom Style -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        body {
            background: radial-gradient(circle at center, rgba(59, 130, 246, 0.05), #090d16 80%) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            border: 1px solid var(--border-color);
            background-color: var(--bg-card);
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 420px;
        }
    </style>
</head>
<body class="bg-dark text-light p-3">

<div class="login-card p-4 p-sm-5 card-premium card-glow-primary">
    <!-- Brand Title -->
    <div class="text-center mb-4">
        <a href="index.php" class="text-decoration-none text-light d-inline-flex align-items-center mb-2">
            <i class="fa-solid fa-wallet text-emerald fs-2 me-2"></i>
            <h2 class="font-outfit fw-bold m-0 tracking-wide">Finance<span class="text-emerald">Tracker</span></h2>
        </a>
        <p class="text-secondary small">Access your secure financial command center</p>
    </div>

    <!-- Login Form -->
    <form id="loginForm" autocomplete="off">
        <div class="mb-3">
            <label for="login_input" class="form-label text-secondary small">Username or Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                <input type="text" class="form-control" id="login_input" name="login_input" placeholder="e.g. admin or john@example.com" required>
            </div>
        </div>
        
        <div class="mb-4">
            <div class="d-flex justify-content-between mb-1">
                <label for="password" class="form-label text-secondary small mb-0">Password</label>
            </div>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
            </div>
        </div>

        <button type="submit" class="btn btn-emerald w-100 py-2.5 mb-3">
            Sign In <i class="fa-solid fa-arrow-right me-1"></i>
        </button>

        <div class="text-center">
            <span class="text-secondary small">Don't have an account? <a href="register.php" class="text-emerald text-decoration-none fw-semibold">Sign Up</a></span>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/auth.js"></script>
</body>
</html>
