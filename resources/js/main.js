document.addEventListener("DOMContentLoaded", () => {
    // 1. SIDEBAR COLLAPSE LOGIC
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

    // 2. SUBSYSTEM INTERACTION (HIGHLIGHTING & DROPDOWNS)
    document.querySelectorAll(".subsystem").forEach(subsystem => {
        subsystem.addEventListener("click", function() {
            // A. Handle Highlighting (Yellow Pill)
            // Remove highlight from all subsystems first
            document.querySelectorAll(".subsystem").forEach(s => s.classList.remove("active-page"));
            // Add highlight to the one currently clicked
            this.classList.add("active-page");

            // B. Handle Dropdown Menus (Features)
            const featureContainer = this.nextElementSibling;
            
            // Look for the specific arrow icon inside this subsystem
            const arrow = this.querySelector(".fa-angles-right"); 

            // Only try to toggle if there is actually a sub-menu (subsystem-feature)
            if (featureContainer && featureContainer.classList.contains('subsystem-feature')) {
                featureContainer.classList.toggle("active");
                
                // Rotate the arrow icon if found
                if (arrow) {
                    arrow.classList.toggle("active");
                }
            }
        });
    });
});