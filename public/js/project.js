// Ensure the page is marked as accessed when loaded
document.addEventListener('DOMContentLoaded', function () {
    sendAjaxRequest('put', `/project/${window.location.pathname.split('/')[2]}/accessed`, {}, function () {
        const data = JSON.parse(this.responseText);
        if (!data.success) {
            // Display appropriate error notification
            if (data.error) {
                createToastNotification(false, this.error);
            } else {
                createToastNotification(false, "Unknown error");
            }
        }
    });
});

// Toggle the visibility of the right sidebar
function toggleRightSidebar() {
    const sidebar = document.getElementById('right-side-bar');
    const toggleIcon = document.getElementById('toggle-icon');
    const mainContent = document.getElementById('main-content');
    const searchHeader =document.getElementById('search-header-bar');

    // Add/remove the 'closed' class to toggle the sidebar
    sidebar.classList.toggle('closed');

    // Adjust layout and toggle icon based on sidebar state
    if (sidebar.classList.contains('closed')) {
        mainContent.style.marginRight = '5rem';
        searchHeader.style.marginRight = '5rem';
        toggleIcon.classList.remove('fa-chevron-right');
        toggleIcon.classList.add('fa-chevron-left');
    } else {
        mainContent.style.marginRight = '20rem';
        searchHeader.style.marginRight = '20rem';
        toggleIcon.classList.remove('fa-chevron-left');
        toggleIcon.classList.add('fa-chevron-right');
    }
}