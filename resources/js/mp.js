document.addEventListener('DOMContentLoaded', () => {
    const filterButton = document.getElementById('filter-button');
    const filterDropdown = document.getElementById('filterDropdown');
    const tableRows = document.querySelectorAll('tbody tr');
    const addItemBtn = document.querySelector(".add-item-button");
    const addItemModal = document.querySelector(".floating-add-item-container");
    const chooseFileBtn = document.getElementById('chooseFileBtn');
    const fileInput = document.getElementById('fileInput');
    const editModal = document.querySelector('.floating-edit-item-container');
    const productForm = document.getElementById('productForm');
    const deleteModal = document.getElementById('deleteModal');
    let currentForm = null;

    // FILTER
    if (filterButton && filterDropdown) {
        filterButton.addEventListener('click', () => {
            filterDropdown.style.display = filterDropdown.style.display === 'none' ? 'block' : 'none';
        });
        filterDropdown.querySelectorAll('.filter-option').forEach(option => {
            option.addEventListener('click', () => {
                const cat = option.dataset.category;
                tableRows.forEach(row => row.style.display = cat === 'all' || row.dataset.category === cat ? '' : 'none');
                filterDropdown.style.display = 'none';
            });
        });
        document.addEventListener('click', e => {
            if (!filterDropdown.contains(e.target) && e.target !== filterButton) filterDropdown.style.display = 'none';
        });
    }

    // ADD ITEM MODAL
    if (addItemBtn && addItemModal) {
        addItemBtn.addEventListener("click", () => addItemModal.classList.toggle("show"));
    }

    // FILE INPUT
    if (chooseFileBtn && fileInput) {
        chooseFileBtn.addEventListener('click', () => fileInput.click());
    }

    // EDIT BUTTON
    document.querySelectorAll('.edit-button').forEach(btn => {
        btn.addEventListener('click', () => {
            const { id, name, category, price } = btn.dataset;
            productForm.action = `/products/${id}`;
            productForm.querySelector('#editName').value = name;
            productForm.querySelector('#editPrice').value = price;
            productForm.querySelector(`#edit${category.charAt(0).toUpperCase() + category.slice(1)}`).checked = true;
            editModal.style.display = 'flex';
        });
    });

    // CANCEL EDIT
    editModal.querySelectorAll('.cancel-button').forEach(btn => {
        btn.addEventListener('click', () => editModal.style.display = 'none');
    });

    // DELETE BUTTON
    document.querySelectorAll('.delete-button').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            currentForm = btn.closest('form');
            deleteModal.style.display = 'flex';
        });
    });
    deleteModal.querySelector('#cancelDelete').addEventListener('click', () => {
        deleteModal.style.display = 'none';
        currentForm = null;
    });
    deleteModal.querySelector('#confirmDelete').addEventListener('click', () => {
        if (currentForm) currentForm.submit();
    });
});
