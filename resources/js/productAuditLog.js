const filterButton = document.getElementById('filter-button');
const filterDropdown = document.getElementById('filterDropdown');

filterButton.addEventListener('click', () => {
    filterDropdown.style.display =
        filterDropdown.style.display === 'block' ? 'none' : 'block';
});

document.addEventListener('click', (e) => {
    if (!filterDropdown.contains(e.target) && e.target !== filterButton) {
        filterDropdown.style.display = 'none';
    }
});