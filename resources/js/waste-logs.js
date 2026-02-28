document.addEventListener('DOMContentLoaded', () => {
    const filterBtn = document.getElementById('filter-button');
    const filterDropdown = document.getElementById('filterDropdown');

    filterBtn?.addEventListener('click', () => {
        if (filterDropdown.style.display === 'none') {
            filterDropdown.style.display = 'block';
        } else {
            filterDropdown.style.display = 'none';
        }
    });

    const dateInput = document.getElementById('dateInput');
    dateInput?.addEventListener('change', () => {
        dateInput.form.submit();
    });
});
