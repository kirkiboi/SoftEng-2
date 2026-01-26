document.addEventListener('DOMContentLoaded', () => {
    const sidebarButton = document.querySelector(".drop-down-container-button");
    const sidebarContainer = document.querySelector(".side-bar-container");
    const spans = document.querySelectorAll(".subsystem-span");
    
    //subsystems i will make this effecient later on, need to put this up in the meantime fuckkk
    const POScontainer = document.querySelector(".point-of-sales-container");
    const POSfeatures = document.querySelector(".subsystem-feature-pos");
    const POSicon = document.querySelector(".button-pos");

    POScontainer.addEventListener("click",()=>{
        POScontainer.classList.toggle("active");
        POSfeatures.classList.toggle("active");
        POSicon.classList.toggle("active");
    });
    if (sidebarButton && sidebarContainer) {
        sidebarButton.addEventListener("click", () => {
            sidebarContainer.classList.toggle('collapsed');
            spans.forEach(span => span.classList.toggle('clicked'));
            sidebarButton.classList.toggle('clicked');
        });
    }
});
