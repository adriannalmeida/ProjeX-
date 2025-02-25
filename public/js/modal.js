document.addEventListener('DOMContentLoaded', function () {
    let closeModal = false;

    // Detect when the mouse is pressed
    window.addEventListener('mousedown', (event) => {
        if (event.target.classList.contains("modal") || event.target.classList.contains("panel")) closeModal = true;
    });

    // Detect when the mouse is released
    window.addEventListener('mouseup', (event) => {
        // Handle closing a modal
        if ((event.target.classList.contains("modal"))&& closeModal) {
            if (event.target.style.display !== 'none') {
                const closeButton = event.target.querySelector('.close-modal');
                if (closeButton) {
                    // Simulate a click on the close button to close the modal
                    closeButton.click();
                }
                else event.target.style.display = 'none';
            }
        }
        // Handle closing a panel
        if ((event.target.classList.contains("panel")) && closeModal) {
            if (event.target.style.display !== 'none') {
                const closeButton = event.target.querySelector('.close-panel');
                if (closeButton) {
                    // Simulate a click on the close button to close the modal
                    closeButton.click();
                }
                else event.target.style.display = 'none';
            }
        }
        closeModal = false;
    });
});
