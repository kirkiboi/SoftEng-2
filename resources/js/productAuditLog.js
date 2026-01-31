document.addEventListener('DOMContentLoaded', () => {
    const filterButton = document.getElementById('filter-button');
    const filterDropdown = document.getElementById('filterDropdown');
    const overlay = document.getElementById('overlay');
    const dateBtn = document.getElementById('dateBtn');
    const dateInput = document.getElementById('dateInput');

    // FILTER DROPDOWN
    filterButton?.addEventListener('click', (e) => {
        e.stopPropagation();
        const isOpen = filterDropdown.style.display === 'block';
        filterDropdown.style.display = isOpen ? 'none' : 'block';
        overlay.classList.toggle('show', !isOpen);
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (
            filterDropdown &&
            !filterDropdown.contains(e.target) &&
            e.target !== filterButton
        ) {
            filterDropdown.style.display = 'none';
            overlay.classList.remove('show');
        }
    });

    // Overlay click closes everything
    overlay?.addEventListener('click', () => {
        filterDropdown.style.display = 'none';
        overlay.classList.remove('show');
    });

    // DATE FILTER
    dateBtn?.addEventListener('click', () => {
        dateInput.showPicker(); // opens native date picker
    });

    dateInput?.addEventListener('change', () => {
        dateInput.closest('form').submit();
    });
});
