
document.addEventListener('DOMContentLoaded', () => {
    const overlay = document.getElementById('overlay');
    const filterButton = document.querySelector(".filter-icon-container") || document.querySelector(".filter-icon");
    const filterModal = document.querySelector(".filter-dropdown");
    const dateInput = document.getElementById('dateInput');

    // FUNCIONALITY SA FILTER NGA SVG
    filterButton.addEventListener("click",()=>{
        filterModal.classList.toggle("active");
        overlay.classList.toggle("active");
        console.log("oten");
    });

    // Overlay click closes everything
    overlay?.addEventListener('click', () => {
        filterModal.classList.toggle("active");
        overlay.classList.remove('active');
    });
    dateInput?.addEventListener('change', () => {
        dateInput.closest('form').submit();
    });
});