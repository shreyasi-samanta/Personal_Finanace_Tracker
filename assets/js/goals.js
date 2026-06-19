document.addEventListener("DOMContentLoaded", function () {
    const goalForm = document.getElementById("goalForm");
    const editGoalForm = document.getElementById("editGoalForm");
    const contributeForm = document.getElementById("contributeForm");

    // Save Goal (Add)
    if (goalForm) {
        goalForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(goalForm);
            
            fetch("actions/goals.php?action=save", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    bootstrap.Modal.getInstance(document.getElementById("addGoalModal")).hide();
                    goalForm.reset();
                    Swal.fire({
                        icon: "success",
                        title: "Goal Created",
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
                        title: "Failed to Create Goal",
                        text: data.message,
                        background: "#121824",
                        color: "#f3f4f6"
                    });
                }
            })
            .catch(err => console.error("Error creating goal:", err));
        });
    }

    // Save Goal (Edit)
    if (editGoalForm) {
        editGoalForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(editGoalForm);
            
            fetch("actions/goals.php?action=save", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    bootstrap.Modal.getInstance(document.getElementById("editGoalModal")).hide();
                    editGoalForm.reset();
                    Swal.fire({
                        icon: "success",
                        title: "Goal Updated",
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
                        title: "Failed to Update Goal",
                        text: data.message,
                        background: "#121824",
                        color: "#f3f4f6"
                    });
                }
            })
            .catch(err => console.error("Error updating goal:", err));
        });
    }

    // Goal Contribution Submit
    if (contributeForm) {
        contributeForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(contributeForm);
            
            fetch("actions/goals.php?action=contribute", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    bootstrap.Modal.getInstance(document.getElementById("contributeModal")).hide();
                    contributeForm.reset();
                    Swal.fire({
                        icon: "success",
                        title: "Contribution Added",
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
                        title: "Failed to Add Contribution",
                        text: data.message,
                        background: "#121824",
                        color: "#f3f4f6"
                    });
                }
            })
            .catch(err => console.error("Error adding goal contribution:", err));
        });
    }

    // Global Click Handler for Goals list
    document.addEventListener("click", function (e) {
        // Contribute button
        const contributeBtn = e.target.closest(".btn-contribute-goal");
        if (contributeBtn) {
            const id = contributeBtn.dataset.id;
            const name = contributeBtn.dataset.name;
            document.getElementById("contribute_goal_id").value = id;
            document.getElementById("contributeGoalName").innerText = name;
            new bootstrap.Modal(document.getElementById("contributeModal")).show();
        }

        // Edit button
        const editBtn = e.target.closest(".btn-edit-goal");
        if (editBtn) {
            const id = editBtn.dataset.id;
            fetch(`actions/goals.php?action=get&id=${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        const g = res.data;
                        document.getElementById("edit_goal_id").value = g.id;
                        document.getElementById("edit_goal_name").value = g.name;
                        document.getElementById("edit_goal_target").value = g.target_amount;
                        document.getElementById("edit_goal_current").value = g.current_amount;
                        document.getElementById("edit_goal_date").value = g.target_date;
                        document.getElementById("edit_goal_status").value = g.status;
                        
                        new bootstrap.Modal(document.getElementById("editGoalModal")).show();
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
                .catch(err => console.error("Error loading goal details:", err));
        }

        // Delete button
        const deleteBtn = e.target.closest(".btn-delete-goal");
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";

            Swal.fire({
                title: "Are you sure?",
                text: "This will permanently delete this savings goal.",
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

                    fetch("actions/goals.php?action=delete", {
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
                    .catch(err => console.error("Error deleting goal:", err));
                }
            });
        }
    });
});
