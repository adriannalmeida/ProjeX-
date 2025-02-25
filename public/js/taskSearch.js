// Function to handle the search of tasks results
function searchHandler() {
    const resultsContainer = document.getElementById('results-container');
    const searchInput = document.getElementById('search-input');
    let data;
    // Parse JSON response
    try {
        data = JSON.parse(this.responseText);
    } catch (error) {
        console.error('Erro ao interpretar JSON:', error);
        return;
    }
    currentRequest -= 1;

    // Check if there is no request pending
    if (currentRequest === 0) {
        resultsContainer.innerHTML = '';
        if (searchInput.value.length === 0) return;
        if (data.taskTables.length === 0|| data.taskTables.every(taskTable => taskTable.tasks.length === 0)) {
            resultsContainer.innerHTML = `<li class="no-search-results">No results found for "${searchInput.value}".</li>`;
        } else {
            // Display task details
            data.taskTables.forEach(taskTable => {
                taskTable.tasks.forEach(task => {
                    resultsContainer.innerHTML += `<li class="search-result">
                                                        <a href="#" class="task-link" data-id="${ task.id }">
                                                            <p class="result-name">${task.name}</p>
                                                            <p class="result-description">${task.description}</p>
                                                        </a>
                                                    </li>`;
                });
            });
            // Add event listener to task links
            const searchResults = resultsContainer.getElementsByClassName('search-result');

            Array.from(searchResults).forEach(result => {
                const taskLink = result.getElementsByClassName('task-link')[0];
                if (taskLink) {
                    taskLink.addEventListener('click', function (e) {
                        e.preventDefault();
                        const taskId = this.dataset.id;
                        getTaskInformation(taskId);
                    });
                }
            });
            // Add button to see all results  if there are more than 10 results
            if (searchResults.length >= 10) {
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