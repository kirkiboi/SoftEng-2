document.addEventListener('DOMContentLoaded', () => {
    // Removed Filter/Overlay logic here because mp.js handles it now.
    // mp.js manages #filter-button, #filterDropdown, and #overlay.

    // DATE FILTER LOGIC
    const dateBtn = document.getElementById('dateBtn');
    const dateInput = document.getElementById('dateInput');

    if (dateBtn && dateInput) {
        dateBtn.addEventListener('click', () => {
            if (dateInput.showPicker) {
                dateInput.showPicker(); // opens native date picker
            } else {
                dateInput.click(); // Fallback
            }
        });

        dateInput.addEventListener('change', () => {
            dateInput.close && dateInput.close(); // Attempt to close if possible, though native pickers handle this
            const form = dateInput.closest('form');
            if (form) form.submit();
        });
    }
});
