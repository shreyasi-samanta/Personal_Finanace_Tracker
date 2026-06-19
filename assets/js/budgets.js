document.addEventListener("DOMContentLoaded", function () {
    const budgetForm = document.getElementById("budgetForm");
    const editBudgetForm = document.getElementById("editBudgetForm");

    // Save budget (Add)
    if (budgetForm) {
        budgetForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(budgetForm);
            
            fetch("actions/budgets.php?action=save", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    bootstrap.Modal.getInstance(document.getElementById("addBudgetModal")).hide();
                    budgetForm.reset();
                    Swal.fire({
                        icon: "success",
                        title: "Budget Set",
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
                        title: "Failed to Set Budget",
                        text: data.message,
                        background: "#121824",
                        color: "#f3f4f6"
                    });
                }
            })
            .catch(err => console.error("Error setting budget:", err));
        });
    }

    // Update budget (Edit)
    if (editBudgetForm) {
        editBudgetForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(editBudgetForm);
            
            fetch("actions/budgets.php?action=save", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    bootstrap.Modal.getInstance(document.getElementById("editBudgetModal")).hide();
                    editBudgetForm.reset();
                    Swal.fire({
                        icon: "success",
                        title: "Budget Updated",
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
                        title: "Failed to Update Budget",
                        text: data.message,
                        background: "#121824",
                        color: "#f3f4f6"
                    });
                }
            })
            .catch(err => console.error("Error updating budget:", err));
        });
    }

    // Global click listener for edit/delete actions
    document.addEventListener("click", function (e) {
        // Edit button click
        const editBtn = e.target.closest(".btn-edit-budget");
        if (editBtn) {
            const id = editBtn.dataset.id;
            fetch(`actions/budgets.php?action=get&id=${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        const b = res.data;
                        document.getElementById("edit_budget_id").value = b.id;
                        document.getElementById("edit_budget_category").value = b.category_id;
                        document.getElementById("edit_budget_amount").value = b.amount;
                        document.getElementById("edit_budget_month").value = b.month;
                        document.getElementById("edit_budget_year").value = b.year;
                        
                        new bootstrap.Modal(document.getElementById("editBudgetModal")).show();
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: res.message,
                            background: "#121824",
                            color: "#f3f4f6"
                        });
                    }
                })
                .catch(err => console.error("Error fetching budget details:", err));
        }

        // Delete button click
        const deleteBtn = e.target.closest(".btn-delete-budget");
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";

            Swal.fire({
                title: "Are you sure?",
                text: "This will remove this monthly budget limit.",
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

                    fetch("actions/budgets.php?action=delete", {
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
                                title: "Failed to Delete",
                                text: data.message,
                                background: "#121824",
                                color: "#f3f4f6"
                            });
                        }
                    })
                    .catch(err => console.error("Error deleting budget:", err));
                }
            });
        }
    });
});
