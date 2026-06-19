document.addEventListener("DOMContentLoaded", function () {
    const transactionForm = document.getElementById("transactionForm");
    const editTransactionForm = document.getElementById("editTransactionForm");
    const typeSelect = document.getElementById("tx_type");
    const editTypeSelect = document.getElementById("edit_tx_type");
    const categorySelect = document.getElementById("tx_category");
    const editCategorySelect = document.getElementById("edit_tx_category");

    // Dynamic categories based on type selection in forms
    if (typeSelect) {
        typeSelect.addEventListener("change", function () {
            loadCategories(typeSelect.value, categorySelect);
        });
        // initial load
        loadCategories(typeSelect.value, categorySelect);
    }

    if (editTypeSelect) {
        editTypeSelect.addEventListener("change", function () {
            loadCategories(editTypeSelect.value, editCategorySelect);
        });
    }

    function loadCategories(type, selectElement, selectValue = null) {
        if (!selectElement) return;
        selectElement.innerHTML = `<option value="">Loading categories...</option>`;
        
        fetch(`actions/transactions.php?action=get_categories&type=${type}`)
            .then(res => res.json())
            .then(res => {
                if (res.status === "success") {
                    selectElement.innerHTML = `<option value="">Select Category</option>`;
                    res.data.forEach(c => {
                        const option = document.createElement("option");
                        option.value = c.id;
                        option.innerText = c.name;
                        if (selectValue && parseInt(c.id) === parseInt(selectValue)) {
                            option.selected = true;
                        }
                        selectElement.appendChild(option);
                    });
                } else {
                    selectElement.innerHTML = `<option value="">Failed to load categories</option>`;
                }
            })
            .catch(err => {
                console.error("Error fetching categories:", err);
                selectElement.innerHTML = `<option value="">Error loading categories</option>`;
            });
    }

    // Save transaction (Add)
    if (transactionForm) {
        transactionForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(transactionForm);
            
            fetch("actions/transactions.php?action=save", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    bootstrap.Modal.getInstance(document.getElementById("addTransactionModal")).hide();
                    transactionForm.reset();
                    loadCategories(typeSelect.value, categorySelect); // reset categories dropdown
                    Swal.fire({
                        icon: "success",
                        title: "Transaction Saved",
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false,
                        background: "#121824",
                        color: "#f3f4f6"
                    }).then(() => {
                        location.reload(); // Reload table & dashboard metrics
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
            .catch(err => console.error("Error saving transaction:", err));
        });
    }

    // Save transaction (Edit)
    if (editTransactionForm) {
        editTransactionForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const formData = new FormData(editTransactionForm);
            
            fetch("actions/transactions.php?action=save", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    bootstrap.Modal.getInstance(document.getElementById("editTransactionModal")).hide();
                    editTransactionForm.reset();
                    Swal.fire({
                        icon: "success",
                        title: "Transaction Updated",
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
            .catch(err => console.error("Error updating transaction:", err));
        });
    }

    // Global listeners for edit and delete buttons on transaction table rows
    document.addEventListener("click", function (e) {
        // Edit button clicked
        const editBtn = e.target.closest(".btn-edit-transaction");
        if (editBtn) {
            const id = editBtn.dataset.id;
            fetch(`actions/transactions.php?action=get&id=${id}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === "success") {
                        const tx = res.data;
                        document.getElementById("edit_tx_id").value = tx.id;
                        document.getElementById("edit_tx_amount").value = tx.amount;
                        document.getElementById("edit_tx_date").value = tx.date;
                        document.getElementById("edit_tx_description").value = tx.description;
                        document.getElementById("edit_tx_type").value = tx.type;
                        
                        // Load categories for the type, and select the current category
                        loadCategories(tx.type, editCategorySelect, tx.category_id);
                        
                        // Show Modal
                        new bootstrap.Modal(document.getElementById("editTransactionModal")).show();
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
                .catch(err => console.error("Error fetching transaction details:", err));
        }

        // Delete button clicked
        const deleteBtn = e.target.closest(".btn-delete-transaction");
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || "";
            
            Swal.fire({
                title: "Are you sure?",
                text: "This action will permanently delete this transaction record.",
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

                    fetch("actions/transactions.php?action=delete", {
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
                    .catch(err => console.error("Error deleting transaction:", err));
                }
            });
        }
    });
});
