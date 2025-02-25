// Get the ID of the currently active tab
const getActiveTab = () => {
    const activeRadio = document.querySelector('.tab:checked'); // Find the checked radio button
    return activeRadio ? activeRadio.id : null; // Return the ID of the active radio button or null if none is selected
};

// Handle adding a project to favorites
const handleFavouriteClick = function (e) {
    e.preventDefault();
    e.stopPropagation();
    const favourite = this;
    sendAjaxRequest('put', `/project/${favourite.parentElement.getAttribute('data-id')}/addToFavorites`, {}, function () {
        location.reload()
    });
};

// Handle removing a project from favorites
const handleNotFavouriteClick = function (e) {
    e.preventDefault();
    e.stopPropagation();
    const notFavourite = this;
    sendAjaxRequest('put', `/project/${notFavourite.parentElement.getAttribute('data-id')}/removeFromFavorites`, {}, function () {
        location.reload()
    });
};

// Initialize favorite button click listeners when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function () {
    addFavoriteClickLink();
});

// Add event listeners to favorite and not-favorite buttons
function addFavoriteClickLink(){
    const notFavouriteButtons = document.querySelectorAll('.not-favorite-project > b');
    const favouriteButtons = document.querySelectorAll('.favorite-project > b');
    const tab = getActiveTab();
    if(tab !== "tabPub" && tab !== "tabArchived"){
        notFavouriteButtons.forEach(favourite => {
            favourite.addEventListener('click', handleFavouriteClick);
        });
        favouriteButtons.forEach(favourite => {
            favourite.addEventListener('click', handleNotFavouriteClick);
        })
    }
}