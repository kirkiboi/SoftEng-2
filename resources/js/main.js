const sidebarButton = document.querySelector(".drop-down-container-button");
const sidebarContainer = document.querySelector(".side-bar-container");
const sidebarButtons = document.querySelectorAll(".button");
const spans = document.querySelectorAll('.subsystem-span');
const addItemButton = document.querySelector(".add-item-button");
const addItemMainContainer = document.querySelector(".floating-add-item-container");
const filterButton = document.getElementById('filter-button');
const filterDropdown = document.getElementById('filterDropdown');
const filterOptions = document.querySelectorAll('.filter-option');
const tableRows = document.querySelectorAll('tbody tr');
    filterButton.addEventListener('click', () => {
        filterDropdown.style.display = filterDropdown.style.display === 'none' ? 'block' : 'none';
    });
    filterOptions.forEach(option => {
        option.addEventListener('click', () => {
            const category = option.getAttribute('data-category');
            tableRows.forEach(row => {
                if(category === 'all') {
                    row.style.display = '';
                } else {
                    row.style.display = row.dataset.category === category ? '' : 'none';
                }
            });
            filterDropdown.style.display = 'none';
        });
    });
    document.addEventListener('click', function(e) {
        if(!filterDropdown.contains(e.target) && e.target !== filterButton) {
            filterDropdown.style.display = 'none';
        }
    });
document.getElementById('chooseFileBtn').addEventListener('click', function () {
    document.getElementById('fileInput').click();
});
addItemButton.addEventListener("click", ()=>{
    addItemMainContainer.classList.toggle("show");
});
sidebarButtons.forEach(button => {
    button.addEventListener('click', () => {
        button.classList.toggle('clicked');
    });
});
sidebarButton.addEventListener("click",() =>{
    sidebarContainer.classList.toggle('collapsed');    
    sidebarButtons.forEach(button => {
        button.classList.toggle('clicked');
    });
    spans.forEach(span => {
        span.classList.toggle('clicked');
    });
});