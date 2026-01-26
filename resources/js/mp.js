document.addEventListener('DOMContentLoaded', () => {
    const filterButton = document.getElementById('filter-button');
    const filterDropdown = document.getElementById('filterDropdown');
    const filterOptions = document.querySelectorAll('.filter-option');
    const tableRows = document.querySelectorAll('tbody tr');
    if (filterButton && filterDropdown) {
        filterButton.addEventListener('click', () => {
            filterDropdown.style.display =
                filterDropdown.style.display === 'none' ? 'block' : 'none';
        });
        filterOptions.forEach(option => {
            option.addEventListener('click', () => {
                const category = option.dataset.category;

                tableRows.forEach(row => {
                    row.style.display =
                        category === 'all' || row.dataset.category === category
                            ? ''
                            : 'none';
                });

                filterDropdown.style.display = 'none';
            });
        });
        document.addEventListener('click', e => {
            if (!filterDropdown.contains(e.target) && e.target !== filterButton) {
                filterDropdown.style.display = 'none';
            }
        });
    }
    const addItemButton = document.querySelector(".add-item-button");
    const addItemMainContainer = document.querySelector(".floating-add-item-container");

    if (addItemButton && addItemMainContainer) {
        addItemButton.addEventListener("click", () => {
            addItemMainContainer.classList.toggle("show");
        });
    }
    const chooseFileBtn = document.getElementById('chooseFileBtn');
    const fileInput = document.getElementById('fileInput');
    if (chooseFileBtn && fileInput) {
        chooseFileBtn.addEventListener('click', () => {
            fileInput.click();
        });
    }

});
