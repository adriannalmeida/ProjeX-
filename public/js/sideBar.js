// Wait for the DOM content to be fully loaded
document.addEventListener('DOMContentLoaded', function () {
    const openSideBar = document.getElementById('open-side-bar')
    const openSideBarMobile = document.getElementById('open-side-bar-mobile')

    // Add event listener for desktop sidebar toggle
    openSideBar.addEventListener('click', function (e) {
        e.preventDefault();
        openSideBar.parentElement.parentElement.parentElement.classList.toggle('open');
    });

    // Add event listener for mobile sidebar toggle
    openSideBarMobile.addEventListener('click', function (e) {
        e.preventDefault();
        openSideBar.parentElement.parentElement.parentElement.classList.toggle('open');
    });
});
