document.addEventListener('DOMContentLoaded', () => {
    const filterBtn = document.getElementById('filter-button');
    const filterDropdown = document.getElementById('filterDropdown');

    filterBtn?.addEventListener('click', () => {
        filterDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (filterBtn && filterDropdown && !filterBtn.contains(e.target) && !filterDropdown.contains(e.target)) {
            filterDropdown.classList.remove('show');
        }
    });

    const dateInput = document.getElementById('dateInput');
    dateInput?.addEventListener('change', () => {
        const form = dateInput.closest('form');
        if (form) form.submit();
    });
});
