const dateBtn = document.getElementById('dateBtn');
const dateInput = document.getElementById('dateInput');

if (dateBtn && dateInput) {
    dateBtn.addEventListener('click', () => {
        dateInput.style.display = 'block';
        dateInput.focus();
    });

    dateInput.addEventListener('change', () => {
        dateInput.form.submit();
    });

    document.addEventListener('click', (e) => {
        if (!dateBtn.contains(e.target) && !dateInput.contains(e.target)) {
            dateInput.style.display = 'none';
        }
    });
}