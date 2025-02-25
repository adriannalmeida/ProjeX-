// Initialize current request count
let currentRequest = 0;

document.addEventListener('DOMContentLoaded', function () {
    let debounceTimer;
    const searchInput = document.querySelector('#search-input');
    const filterSelect = document.querySelector('#filter-select'); // Optional filter
    const resultsContainer = document.querySelector('#results-container');
    const searchAjaxUrl = resultsContainer?.getAttribute('data-url');
    const tabsContainer = document.querySelector('#tabs-container');
    const tabsSearchAjaxUrl = tabsContainer?.getAttribute('data-url');
    const searchForm = document.querySelector('#search-form');
    const clearInput = document.querySelector('#clear-input');
    const tabs = document.querySelectorAll('.tab'); // Tab elements

    // Handle filter changes by submitting the search form
    if (filterSelect) filterSelect.addEventListener('change', function () {
        document.getElementById('search-form').submit();
    });

    // Stop if required elements are not present
    if (!searchInput || !resultsContainer || !searchAjaxUrl) return;

    // Get the ID of the currently active tab
    const getActiveTab = () => {
        const activeRadio = document.querySelector('.tab:checked'); // Find the checked radio button
        return activeRadio ? activeRadio.id : null; // Return the ID of the active radio button
    };

    // Construct the query parameters for the AJAX request
    function getQuery(){
        const search = searchInput.value.trim(); // Ensure no trailing spaces
        const filter = filterSelect?.value || ''; // Get the filter value if it exists
        const activeTab = getActiveTab();
        const queryParams = new URLSearchParams();

        if (search) queryParams.set('search', search); // Add search query only if it's not empty
        if (filter) queryParams.set('filter', filter); // Add filter if it exists
        if (activeTab) queryParams.set('tab', activeTab); // Add tab if it exists
        return queryParams.toString();
    }

    // Fetch search results with debounce to avoid frequent requests
    const fetchResults = () => {
        const search = searchInput.value;
        // Clear results if the search input is empty
        if (search.length === 0) {
            resultsContainer.innerHTML = '';
            return;
        }
        // Set a debounce timer to delay the AJAX request
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            if (getQuery()) {
                currentRequest += 1; // Increment current request count
                const queryUrl = `${searchAjaxUrl}?${getQuery()}`; // Construct the full query URL
                sendAjaxRequest('get', queryUrl, {}, searchHandler); // Make the AJAX request
            }
        }, 500);
    };

    // Attach event listeners for search input and filter changes
    searchInput.addEventListener('input', fetchResults);
    filterSelect?.addEventListener('change', fetchResults); // Handle filter changes if present

    // Handle form submission for search
    searchForm?.addEventListener('submit', function (event) {
        event.preventDefault();
        window.location.href = `${window.location.pathname}?${getQuery()}`;
    });

    // Show results container on focus
    searchInput.addEventListener('focus', () => {
        resultsContainer.style.display = 'block';
    });

    // Hide results container when clicking outside
    document.addEventListener('mousedown', (event) => {
        if (!resultsContainer.contains(event.target) && event.target !== searchInput) {
            resultsContainer.style.display = 'none';
        }
    });

    // Clear search input and results on button click
    clearInput?.addEventListener('click', () => {
        searchInput.value = '';
        if (filterSelect) filterSelect.value = '';
        resultsContainer.innerHTML = '';
        clearInput.style.display = 'none';
        window.location.href = `${window.location.pathname}?${getQuery()}`;
    });

    // Toggle clear button visibility based on input
    searchInput.addEventListener('input', () => {
        clearInput.style.display = searchInput.value ? 'block' : 'none';
    });

    // Tab functionality
    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            // Check if the radio button is checked
            if (tab.checked) {
                setTimeout(() => {
                    const queryUrl = `${tabsSearchAjaxUrl}?${getQuery()}`; // Construct the full query URL
                    sendAjaxRequest('get', queryUrl, {}, changedTab); // Make the AJAX request
                }, 200);
            }
        });
    });
});
