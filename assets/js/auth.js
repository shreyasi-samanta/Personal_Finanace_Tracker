document.addEventListener("DOMContentLoaded", function () {
    const loginForm = document.getElementById("loginForm");
    const registerForm = document.getElementById("registerForm");

    // Login Form Submission
    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(loginForm);
            
            fetch("actions/auth.php?action=login", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Login Successful",
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                        background: "#121824",
                        color: "#f3f4f6"
                    }).then(() => {
                        window.location.href = data.redirect;
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Login Failed",
                        text: data.message,
                        background: "#121824",
                        color: "#f3f4f6",
                        confirmButtonColor: "#3b82f6"
                    });
                }
            })
            .catch(err => {
                console.error("Login Error:", err);
            });
        });
    }

    // Register Form Submission
    if (registerForm) {
        registerForm.addEventListener("submit", function (e) {
            e.preventDefault();
            
            const password = document.getElementById("reg_password").value;
            const confirmPassword = document.getElementById("reg_confirm_password").value;
            
            if (password !== confirmPassword) {
                Swal.fire({
                    icon: "warning",
                    title: "Password Mismatch",
                    text: "Passwords do not match!",
                    background: "#121824",
                    color: "#f3f4f6"
                });
                return;
            }

            const formData = new FormData(registerForm);
            fetch("actions/auth.php?action=register", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Registered Successfully",
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false,
                        background: "#121824",
                        color: "#f3f4f6"
                    }).then(() => {
                        window.location.href = "login.php";
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Registration Failed",
                        text: data.message,
                        background: "#121824",
                        color: "#f3f4f6",
                        confirmButtonColor: "#3b82f6"
                    });
                }
            })
            .catch(err => {
                console.error("Registration Error:", err);
            });
        });
    }
});
