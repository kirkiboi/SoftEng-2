document.addEventListener('DOMContentLoaded', () => {
    // ELEMENTS
    const filterButton = document.querySelector('.filter-icon');
    const filterDropdown = document.querySelector('.filter-drop-down-modal');

    const addIngredientButton = document.querySelector('.add-ingredient-button');
    const addIngredientModal = document.querySelector('.floating-add-ingredient-container');
    const addIngredientCancelButton = document.getElementById("add-ingredient-cancel-button");

    const recordStockInButton = document.querySelector('.record-stock-in-button');
    const recordStockInModal = document.querySelector('.record-stock-in-container');

    const recordProductStockInButton = document.querySelector('.record-product-stock-in-button');
    const recordProductStockInModal = document.querySelector('.record-product-stock-in-container');

    const editIngredientModal = document.querySelector('.floating-edit-ingredient-container');
    const editIngredientCancelButton = document.querySelector('.edit-cancel-button');

    const deleteModal = document.getElementById('deleteModal');
    const overlay = document.getElementById('overlay');

    let currentForm = null;

    // OVERLAY HELPERS
    const openOverlay = () => overlay.classList.add('show');
    const closeOverlay = () => overlay.classList.remove('show');

    // CLOSE EVERYTHING
    function closeAll() {
        filterDropdown?.classList.remove('show');
        addIngredientModal?.classList.remove('active');
        editIngredientModal?.classList.remove('active');
        recordStockInModal?.classList.remove('active');
        recordProductStockInModal?.classList.remove('active');
        deleteModal?.classList.remove('active');
        document.getElementById('stockOutModal')?.classList.remove('active');

        currentForm = null;
        closeOverlay();
    }

    // DELETE MODAL
    document.querySelectorAll('.delete-button').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            currentForm = btn.closest('form');
            deleteModal.classList.add('active');
            openOverlay();
        });
    });
    deleteModal.querySelector('#cancelDelete').addEventListener('click', () => {
        closeAll();
    });
    deleteModal.querySelector('#confirmDelete').addEventListener('click', () => {
        if (currentForm) currentForm.submit();
    });
    // Prevent clicks inside delete modal from closing it
    deleteModal.addEventListener('click', e => e.stopPropagation());

    // FILTER
    filterButton?.addEventListener('click', e => {
        e.stopPropagation();
        closeAll();
        filterDropdown.classList.add('show');
        // Do NOT open overlay for filter dropdown
    });

    // ADD INGREDIENT
    addIngredientButton?.addEventListener('click', e => {
        e.stopPropagation();
        closeAll();
        addIngredientModal.classList.add('active');
        openOverlay();
    });
    // RECORD STOCK IN
    recordStockInButton?.addEventListener('click', e => {
        e.stopPropagation();
        closeAll();
        recordStockInModal.classList.add('active');
        openOverlay();
        openOverlay();
    });
    // RECORD PRODUCT STOCK IN
    recordProductStockInButton?.addEventListener('click', e => {
        e.stopPropagation();
        closeAll();
        recordProductStockInModal.classList.add('active');
        openOverlay();
    });
    // EDIT INGREDIENT
    document.querySelectorAll('.edit-ingredient-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation();
            closeAll();
            const form = document.getElementById('editIngredientForm');
            const id = btn.dataset.id;
            form.action = `/ingredients/${id}`;
            form.querySelector('[name="name"]').value = btn.dataset.name;
            form.querySelector('[name="category"]').value = btn.dataset.category;
            form.querySelector('[name="unit"]').value = btn.dataset.unit;
            form.querySelector('[name="cost_per_unit"]').value = btn.dataset.cost;
            form.querySelector('[name="threshold"]').value = btn.dataset.threshold;
            editIngredientModal.classList.add('active');
            openOverlay();
        });
    });
    editIngredientCancelButton?.addEventListener('click', closeAll);
    // STOCK IN CANCEL
    const stockInCancelButton = document.getElementById('stock-in-cancel-button');
    stockInCancelButton?.addEventListener('click', closeAll);
    // PRODUCT STOCK IN CANCEL
    const productStockInCancelButton = document.getElementById('product-stock-in-cancel-button');
    productStockInCancelButton?.addEventListener('click', closeAll);
    // STOCK OUT MODAL
    document.getElementById('openStockOut')?.addEventListener('click', e => {
        e.stopPropagation();
        closeAll();
        document.getElementById('stockOutModal').classList.add('active');
        openOverlay();
    });
    document.getElementById('cancelStockOut')?.addEventListener('click', closeAll);
    // OVERLAY CLICK
    overlay?.addEventListener('click', closeAll);
    addIngredientCancelButton.addEventListener("click",()=>{
        addIngredientModal.classList.toggle("active");
        overlay.classList.toggle("show");
    });
});
