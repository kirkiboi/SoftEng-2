document.addEventListener('DOMContentLoaded', () => {
    const table = document.getElementById('varianceTable');
    if (!table) return;

    const rows = Array.from(table.querySelectorAll('tbody tr[data-name]'));
    const search = document.getElementById('varianceSearch');
    const filter = document.getElementById('varianceFilter');
    const pagDiv = document.getElementById('variancePagination');
    const perPage = 10;
    let currentPage = 1;

    function getFiltered() {
        const q = search.value.toLowerCase();
        const f = filter.value;
        return rows.filter(r => {
            const name = r.dataset.name;
            const v = parseFloat(r.dataset.variance);
            const matchSearch = !q || name.includes(q);
            const matchFilter = f === 'all' || (f === 'over' && v < 0) || (f === 'under' && v >= 0);
            return matchSearch && matchFilter;
        });
    }

    function render() {
        const filtered = getFiltered();
        const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * perPage;
        const end = start + perPage;

        rows.forEach(r => r.style.display = 'none');
        filtered.slice(start, end).forEach(r => r.style.display = '');

        pagDiv.innerHTML = '';
        if (totalPages > 1) {
            for (let i = 1; i <= totalPages; i++) {
                const btn = document.createElement('button');
                btn.textContent = i;
                btn.style.cssText = `padding:0.4rem 0.8rem; border-radius:50px; border:1px solid #e0e0e0; cursor:pointer; font-weight:600; font-size:0.85rem; ${i === currentPage ? 'background:#2975da; color:white; border-color:#2975da;' : 'background:white;'}`;
                btn.addEventListener('click', () => { currentPage = i; render(); });
                pagDiv.appendChild(btn);
            }
        }
    }

    if (search) search.addEventListener('input', () => { currentPage = 1; render(); });
    if (filter) filter.addEventListener('change', () => { currentPage = 1; render(); });
    render();
});
