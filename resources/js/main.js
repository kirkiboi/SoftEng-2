document.addEventListener('DOMContentLoaded', () => {

    const sidebarButton = document.querySelector(".drop-down-container-button");
    const sidebarContainer = document.querySelector(".side-bar-container");
    const sidebarButtons = document.querySelectorAll(".button");
    const spans = document.querySelectorAll(".subsystem-span");

    if (sidebarButton && sidebarContainer) {
        sidebarButton.addEventListener("click", () => {
            sidebarContainer.classList.toggle('collapsed');

            sidebarButtons.forEach(button => {
                button.classList.toggle('clicked');
            });

            spans.forEach(span => {
                span.classList.toggle('clicked');
            });
        });
    }

    sidebarButtons.forEach(button => {
        button.addEventListener('click', () => {
            button.classList.toggle('clicked');
        });
    });

});
