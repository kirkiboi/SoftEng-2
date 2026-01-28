document.addEventListener("DOMContentLoaded", () => {
    const sidebarButton = document.querySelector(".drop-down-container-button");
    const sidebarContainer = document.querySelector(".side-bar-container");
    const spans = document.querySelectorAll(".subsystem-span");
    if (sidebarButton && sidebarContainer) {
        sidebarButton.addEventListener("click", () => {
            sidebarContainer.classList.toggle("collapsed");
            spans.forEach(span => span.classList.toggle("clicked"));
            sidebarButton.classList.toggle("clicked");
        });
    }
    document.querySelectorAll(".subsystem").forEach(subsystem => {
        subsystem.addEventListener("click", () => {
            const featureContainer = subsystem.nextElementSibling;
            const icon = subsystem.querySelector("i");
            if (!featureContainer) return;

            subsystem.classList.toggle("active");
            featureContainer.classList.toggle("active");
            if (icon) {
                icon.classList.toggle("active");
            }
        });
    });
});