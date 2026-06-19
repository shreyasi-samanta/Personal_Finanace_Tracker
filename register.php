<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - FinanceTracker</title>
    
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
            background: radial-gradient(circle at center, rgba(16, 185, 129, 0.03), #090d16 80%) !important;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .register-card {
            border: 1px solid var(--border-color);
            background-color: var(--bg-card);
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            width: 100%;
            max-width: 450px;
        }
    </style>
</head>
<body class="bg-dark text-light p-3">

<div class="register-card p-4 p-sm-5 card-premium card-glow-emerald">
    <!-- Brand Title -->
    <div class="text-center mb-4">
        <a href="index.php" class="text-decoration-none text-light d-inline-flex align-items-center mb-2">
            <i class="fa-solid fa-wallet text-emerald fs-2 me-2"></i>
            <h2 class="font-outfit fw-bold m-0 tracking-wide">Finance<span class="text-emerald">Tracker</span></h2>
        </a>
        <p class="text-secondary small">Start tracking, budgeting, and planning today</p>
    </div>

    <!-- Registration Form -->
    <form id="registerForm" autocomplete="off">
        <div class="mb-3">
            <label for="username" class="form-label text-secondary small">Username</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-regular fa-user"></i></span>
                <input type="text" class="form-control" id="username" name="username" placeholder="e.g. johndoe" required>
            </div>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label text-secondary small">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-regular fa-envelope"></i></span>
                <input type="email" class="form-control" id="email" name="email" placeholder="e.g. john@example.com" required>
            </div>
        </div>
        
        <div class="row">
            <div class="col-sm-6 mb-3">
                <label for="reg_password" class="form-label text-secondary small">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" id="reg_password" name="password" placeholder="••••••••" required>
                </div>
            </div>
            <div class="col-sm-6 mb-4">
                <label for="reg_confirm_password" class="form-label text-secondary small">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock text-muted"></i></span>
                    <input type="password" class="form-control" id="reg_confirm_password" name="confirm_password" placeholder="••••••••" required>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-emerald w-100 py-2.5 mb-3">
            Create Account <i class="fa-solid fa-user-plus me-1"></i>
        </button>

        <div class="text-center">
            <span class="text-secondary small">Already have an account? <a href="login.php" class="text-emerald text-decoration-none fw-semibold">Sign In</a></span>
        </div>
    </form>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="assets/js/auth.js"></script>
</body>
</html>
