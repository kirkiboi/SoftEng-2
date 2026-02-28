document.addEventListener('DOMContentLoaded', () => {
    const filterBtn = document.getElementById('filter-button');
    const filterDropdown = document.getElementById('filterDropdown');

    filterBtn?.addEventListener('click', () => {
        filterDropdown.style.display = filterDropdown.style.display === 'block' ? 'none' : 'block';
    });

    const dateInput = document.getElementById('dateInput');
    dateInput?.addEventListener('change', () => {
        dateInput.form.submit();
    });
});
