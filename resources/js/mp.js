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
    const overlay = document.getElementById('overlay');
    let currentForm = null;
    // FILTER FUNCTIONALITY
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
    // ADD ITEM MODAL FUNCTIONALITY
        if (addItemBtn && addItemModal) {
        addItemBtn.addEventListener("click", () => {
            addItemModal.classList.toggle("show");
            overlay.classList.toggle("show");
        });
    }
    // CANCEL BUTTON IN ADD ITEM MODAL FUNCTIONALITY 
    addItemModal.querySelectorAll('.cancel-button').forEach(btn => {
        btn.addEventListener('click', () => {
            addItemModal.classList.remove('show');
            overlay.classList.remove('show');
        });
    });
    // FILE INPUT FUNCTIONALITY
    if (chooseFileBtn && fileInput) {
        chooseFileBtn.addEventListener('click', () => fileInput.click());
    }

    // FLOATING ADD ITEM EDIT BUTTON FUNCTIONALITY
    document.querySelectorAll('.edit-button').forEach(btn => {
        btn.addEventListener('click', () => {
            const { id, name, category, price } = btn.dataset;
            productForm.action = `/products/${id}`;
            productForm.querySelector('#editName').value = name;
            productForm.querySelector('#editPrice').value = price;
            productForm.querySelector(`#edit${category.charAt(0).toUpperCase() + category.slice(1)}`).checked = true;
            editModal.style.display = 'flex';
            overlay.classList.add('show'); 
        });
    });
    // CANCEL BUTTON IN EDIT MODAL FUNCTIONALITY 
    editModal.querySelectorAll('.cancel-button').forEach(btn => {
        btn.addEventListener('click', () => {
            editModal.style.display = 'none';
            overlay.classList.remove('show'); 
        });
    });
    // DELETE BUTTON FUNCTIONALITY
    document.querySelectorAll('.delete-button').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            currentForm = btn.closest('form');
            deleteModal.style.display = 'flex';
            overlay.classList.add('show'); 
        });
    });
    deleteModal.querySelector('#cancelDelete').addEventListener('click', () => {
        deleteModal.style.display = 'none';
        overlay.classList.remove('show'); 
        currentForm = null;
    });
    deleteModal.querySelector('#confirmDelete').addEventListener('click', () => {
        if (currentForm) currentForm.submit();
    });
    // CLICK OVERLAY TO CLOSE 
    overlay.addEventListener('click', () => {
        addItemModal.classList.remove('show');
        editModal.style.display = 'none';
        deleteModal.style.display = 'none';
        overlay.classList.remove('show');
        currentForm = null;
    });
});