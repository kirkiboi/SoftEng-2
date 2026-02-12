document.addEventListener("DOMContentLoaded", () => {
    const filterButton = document.getElementById("filter-button");
    const filterDropdown = document.getElementById("filterDropdown");
    const tableRows = document.querySelectorAll("tbody tr");
    const addItemBtn = document.querySelector(".add-item-button");
    const addItemModal = document.querySelector(".floating-add-item-container");
    const chooseFileBtn = document.getElementById("chooseFileBtn");
    const fileInput = document.getElementById("fileInput");
    const editModal = document.querySelector(".floating-edit-item-container");
    const productForm = document.getElementById("productForm");
    const deleteModal = document.getElementById("deleteModal");
    const overlay = document.getElementById("overlay");
    let currentForm = null;
    // FINALIZED FILTER FUNCTIONALITY
    // SERVER-SIDE FILTERING (Links are used now, so JS filtering removed)
    if (filterButton && filterDropdown) {
        const defaultIcon = filterButton.querySelector(".bi-funnel");
        const activeIcon = filterButton.querySelector(".bi-x-lg");

        filterButton.addEventListener("click", (e) => {
            e.stopPropagation();
            const isDropdownOpen = window.getComputedStyle(filterDropdown).display === "block";
            filterDropdown.style.display = isDropdownOpen ? "none" : "block";

            if (!isDropdownOpen) {
                if (defaultIcon) defaultIcon.setAttribute("style", "display: none !important");
                if (activeIcon) activeIcon.setAttribute("style", "display: block !important");
            } else {
                if (defaultIcon) defaultIcon.setAttribute("style", "display: block !important");
                if (activeIcon) activeIcon.setAttribute("style", "display: none !important");
            }
        });

        document.addEventListener("click", (e) => {
            if (!filterDropdown.contains(e.target) && !filterButton.contains(e.target)) {
                filterDropdown.style.display = "none";
                if (defaultIcon) defaultIcon.setAttribute("style", "display: block !important");
                if (activeIcon) activeIcon.setAttribute("style", "display: none !important");
            }
        });
    }

    // WASTE STOCK MODAL LOGIC
    const wasteModal = document.getElementById('wasteStockModal');
    const wasteForm = document.getElementById('wasteStockForm');
    const wasteName = document.getElementById('wasteStockName');
    const overlayWaste = document.getElementById('overlay'); // Reuse existing overlay if possible, or use modal's own backdrop

    document.querySelectorAll('.waste-button').forEach(btn => {
        btn.addEventListener('click', () => {
             const { id, name } = btn.dataset;
             wasteForm.action = `/products/${id}/waste`;
             wasteName.textContent = name;
             wasteModal.style.display = 'flex';
             // If overlay is separate from modal container:
             if(overlay) overlay.classList.add('show');
        });
    });

    document.getElementById('closeWasteStock')?.addEventListener('click', () => {
        wasteModal.style.display = 'none';
        if(overlay) overlay.classList.remove('show');
    });

    document.getElementById('cancelWasteStock')?.addEventListener('click', () => {
        wasteModal.style.display = 'none';
        if(overlay) overlay.classList.remove('show');
    });

    // Close waste modal on overlay click (if using shared overlay)
    if(overlay) {
        overlay.addEventListener('click', () => {
            wasteModal.style.display = 'none';
        });
    }
    // ADD ITEM MODAL FUNCTIONALITY
    if (addItemBtn && addItemModal) {
        addItemBtn.addEventListener("click", () => {
            addItemModal.classList.toggle("show");
            overlay.classList.toggle("show");
        });
    }
    // CANCEL BUTTON IN ADD ITEM MODAL FUNCTIONALITY
    addItemModal.querySelectorAll(".cancel-button").forEach((btn) => {
        btn.addEventListener("click", () => {
            addItemModal.classList.remove("show");
            overlay.classList.remove("show");
        });
    });
    // FILE INPUT FUNCTIONALITY
    if (chooseFileBtn && fileInput) {
        chooseFileBtn.addEventListener("click", () => fileInput.click());
    }

    // FLOATING ADD ITEM EDIT BUTTON FUNCTIONALITY
    document.querySelectorAll(".edit-button").forEach((btn) => {
        btn.addEventListener("click", () => {
            const { id, name, category, price } = btn.dataset;
            productForm.action = `/products/${id}`;
            productForm.querySelector("#editName").value = name;
            productForm.querySelector("#editPrice").value = price;
            productForm.querySelector(
                `#edit${category.charAt(0).toUpperCase() + category.slice(1)}`,
            ).checked = true;
            editModal.style.display = "flex";
            overlay.classList.add("show");
        });
    });
    // CANCEL BUTTON IN EDIT MODAL FUNCTIONALITY
    editModal.querySelectorAll(".cancel-button").forEach((btn) => {
        btn.addEventListener("click", () => {
            editModal.style.display = "none";
            overlay.classList.remove("show");
        });
    });
    // DELETE BUTTON FUNCTIONALITY
    document.querySelectorAll(".delete-button").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            e.preventDefault();
            currentForm = btn.closest("form");
            deleteModal.style.display = "flex";
            overlay.classList.add("show");
        });
    });
    deleteModal.querySelector("#cancelDelete").addEventListener("click", () => {
        deleteModal.style.display = "none";
        overlay.classList.remove("show");
        currentForm = null;
    });
    deleteModal
        .querySelector("#confirmDelete")
        .addEventListener("click", () => {
            if (currentForm) currentForm.submit();
        });
    // CLICK OVERLAY TO CLOSE
    overlay.addEventListener("click", () => {
        addItemModal.classList.remove("show");
        editModal.style.display = "none";
        deleteModal.style.display = "none";
        overlay.classList.remove("show");
        currentForm = null;
    });
    // AUTO-HIDE ALERT NOTIFICATIONS
    const alert = document.querySelector(".my-alert");
    if (alert) {
        // Wait 3 seconds, then fade out
        setTimeout(() => {
            alert.style.transition = "opacity 0.5s ease";
            alert.style.opacity = "0";

            // Completely remove from DOM after fade finishes
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 3000);
    }
});
