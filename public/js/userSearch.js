// Function to handle the search bar in the manage users page
function searchHandler() {
    const resultsContainer = document.getElementById('results-container');
    const searchInput = document.getElementById('search-input');
    const data = JSON.parse(this.responseText);

    currentRequest -= 1;
    // If there are no more requests pending
    if (currentRequest === 0) {
        resultsContainer.innerHTML = '';
        if (searchInput.value.length === 0) return;
        if (data.users.length === 0) {
            resultsContainer.innerHTML = `<li class="no-search-results">No results found for "${searchInput.value}".</li>`;
        } else {
            data.users.forEach(user => {
                resultsContainer.innerHTML += `<li class="search-result">
                                                    <a href="/account/manage/${user.id}">
                                                        <p class="result-name">${user.name}</p>
                                                        <p class="result-description">${user.email}</p>
                                                    </a>
                                                </li>`;
            });

            // If there are more than 10 results, add a button to see all results
            if (data.users.length >= 10) {
                resultsContainer.innerHTML += `<a class="allResultsButton">
                                                    <p>See all results</p>
                                                </a>`;
                const allResultsButton = resultsContainer.querySelector('.allResultsButton')
                if (allResultsButton) {
                    allResultsButton.addEventListener('click', (e) => {
                        e.preventDefault();
                        const search = searchInput.value;

                        if (search === '') {
                            window.location.href = window.location.origin + window.location.pathname;
                        }
                        else {
                            window.location.href = `${window.location.pathname}?search=${encodeURIComponent(search)}`;
                        }
                    })
                }
            }
        }
    }
}
