document.addEventListener("DOMContentLoaded", function () {
    const categoryForm = document.getElementById("categoryForm");
    const editCategoryForm = document.getElementById("editCategoryForm");

    // Save Category (Add)
    if (categoryForm) {
        categoryForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(categoryForm);
            
            fetch("../actions/admin.php?action=save_category", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    bootstrap.Modal.getInstance(document.getElementById("addCategoryModal")).hide();
                    categoryForm.reset();
                    Swal.fire({
                        icon: "success",
                        title: "Category Created",
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                        background: "#121824",
                        color: "#f3f4f6"
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Failed to Save",
                        text: data.message,
                        background: "#121824",
                        color: "#f3f4f6"
                    });
                }
            })
            .catch(err => console.error("Error creating category:", err));
        });
    }

    // Save Category (Edit)
    if (editCategoryForm) {
        editCategoryForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(editCategoryForm);
            
            fetch("../actions/admin.php?action=save_category", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    bootstrap.Modal.getInstance(document.getElementById("editCategoryModal")).hide();
                    editCategoryForm.reset();
                    Swal.fire({
                        icon: "success",
                        title: "Category Updated",
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                        background: "#121824",
                        color: "#f3f4f6"
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Failed to Update",
                        text: data.message,
                        background: "#121824",
                        color: "#f3f4f6"
                    });
                }
            })
            .catch(err => console.error("Error updating category:", err));
        });
    }

    // Global Click Handlers for Admin tables
    document.addEventListener("click", function (e) {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";

        // 1. Toggle User Role
        const toggleRoleBtn = e.target.closest(".btn-toggle-role");
        if (toggleRoleBtn) {
            const userId = toggleRoleBtn.dataset.id;
            const params = new URLSearchParams();
            params.append("user_id", userId);
            params.append("csrf_token", csrfToken);

            fetch("../actions/admin.php?action=toggle_role", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: params.toString()
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Role Changed",
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                        background: "#121824",
                        color: "#f3f4f6"
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text: data.message,
                        background: "#121824",
                        color: "#f3f4f6"
                    });
                }
            })
            .catch(err => console.error("Error toggling role:", err));
        }

        // 2. Delete User
        const deleteUserBtn = e.target.closest(".btn-delete-user");
        if (deleteUserBtn) {
            const userId = deleteUserBtn.dataset.id;
            const username = deleteUserBtn.dataset.username;

            Swal.fire({
                title: `Delete user ${username}?`,
                text: "This will permanently delete this account, including all their transactions, budgets, and savings goals!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#f43f5e",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Yes, delete user",
                background: "#121824",
                color: "#f3f4f6"
            }).then((result) => {
                if (result.isConfirmed) {
                    const params = new URLSearchParams();
                    params.append("user_id", userId);
                    params.append("csrf_token", csrfToken);

                    fetch("../actions/admin.php?action=delete_user", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: params.toString()
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false,
                                background: "#121824",
                                color: "#f3f4f6"
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Failed",
                                text: data.message,
                                background: "#121824",
                                color: "#f3f4f6"
                            });
                        }
                    })
                    .catch(err => console.error("Error deleting user:", err));
                }
            });
        }

        // 3. Edit Category Modal Setup
        const editCatBtn = e.target.closest(".btn-edit-category");
        if (editCatBtn) {
            const id = editCatBtn.dataset.id;
            const name = editCatBtn.dataset.name;
            const type = editCatBtn.dataset.type;
            const icon = editCatBtn.dataset.icon;
            const color = editCatBtn.dataset.color;

            document.getElementById("edit_cat_id").value = id;
            document.getElementById("edit_cat_name").value = name;
            document.getElementById("edit_cat_type").value = type;
            document.getElementById("edit_cat_icon").value = icon;
            document.getElementById("edit_cat_color").value = color;

            new bootstrap.Modal(document.getElementById("editCategoryModal")).show();
        }

        // 4. Delete Category
        const deleteCatBtn = e.target.closest(".btn-delete-category");
        if (deleteCatBtn) {
            const id = deleteCatBtn.dataset.id;

            Swal.fire({
                title: "Are you sure?",
                text: "This will remove this default category from the system.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#f43f5e",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Yes, delete it",
                background: "#121824",
                color: "#f3f4f6"
            }).then((result) => {
                if (result.isConfirmed) {
                    const params = new URLSearchParams();
                    params.append("id", id);
                    params.append("csrf_token", csrfToken);

                    fetch("../actions/admin.php?action=delete_category", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: params.toString()
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false,
                                background: "#121824",
                                color: "#f3f4f6"
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Failed",
                                text: data.message,
                                background: "#121824",
                                color: "#f3f4f6"
                            });
                        }
                    })
                    .catch(err => console.error("Error deleting category:", err));
                }
            });
        }

        // 5. Global Transaction Audit Delete
        const auditDeleteTxBtn = e.target.closest(".btn-audit-delete-tx");
        if (auditDeleteTxBtn) {
            const id = auditDeleteTxBtn.dataset.id;

            Swal.fire({
                title: "Remove transaction?",
                text: "Admin Audit: This will permanently purge this transaction record from the system database.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#f43f5e",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "Yes, delete it",
                background: "#121824",
                color: "#f3f4f6"
            }).then((result) => {
                if (result.isConfirmed) {
                    const params = new URLSearchParams();
                    params.append("id", id);
                    params.append("csrf_token", csrfToken);

                    fetch("../actions/admin.php?action=delete_transaction", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: params.toString()
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Deleted!",
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false,
                                background: "#121824",
                                color: "#f3f4f6"
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Failed",
                                text: data.message,
                                background: "#121824",
                                color: "#f3f4f6"
                            });
                        }
                    })
                    .catch(err => console.error("Error auditing transaction deletion:", err));
                }
            });
        }
    });
});
