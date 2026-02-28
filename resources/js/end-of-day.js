document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.eod-tab');
    const panels = document.querySelectorAll('.eod-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));
            tab.classList.add('active');
            const panel = document.getElementById('panel-' + tab.dataset.tab);
            if (panel) panel.classList.add('active');
        });
    });

    const dateInput = document.querySelector('.eod-date-input');
    dateInput?.addEventListener('change', () => {
        dateInput.closest('form').submit();
    });
});
