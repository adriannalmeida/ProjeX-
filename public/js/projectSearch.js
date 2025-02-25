// Function to update the results container based on search data
function updateResultsContainer(data){
    const resultsContainer = document.getElementById('results-container');
    const searchInput = document.getElementById('search-input');
    resultsContainer.innerHTML = '';

    // Exit if the search input is empty
    if (searchInput.value.length === 0) return;
    if (data.projects.length === 0) {
         // Display a "no results" message if no projects are found
        resultsContainer.innerHTML = `<li class="no-search-results">No results found for "${searchInput.value}".</li>`;
    } else {
        // Slice the array to limit to the first 10 results
        const limitedProjects = data.projects.slice(0, 10);

        // Add each project to the results container
        limitedProjects.forEach(project => {
            resultsContainer.innerHTML += `<li class="search-result">
                                                <a href="/project/${project.id}">
                                                    <p class="result-name">${project.name}</p>
                                                    <p class="result-description">${project.description}</p>
                                                </a>
                                            </li>`;
        });

        // Add "See all results" button if there are more than 10 results
        if (data.projects.length > 10) {
            resultsContainer.innerHTML += `<a class="allResultsButton">
                                                <p>See all results</p>
                                            </a>`;
            const allResultsButton = resultsContainer.querySelector('.allResultsButton');
            if (allResultsButton) {
                // Redirect to the full search results page when clicked
                allResultsButton.addEventListener('click', (e) => {
                    e.preventDefault();
                    const search = searchInput.value;

                    if (search === '') {
                        window.location.href = window.location.origin + window.location.pathname;
                    } else {
                        window.location.href = `${window.location.pathname}?search=${encodeURIComponent(search)}`;
                    }
                });
            }
        }
    }
}

// Handle incoming search response data
function searchHandler() {
    const data = JSON.parse(this.responseText);
    currentRequest -= 1;
    if (currentRequest === 0) {
        updateResultsContainer(data);
    }
}

// Function to handle tab changes and update content dynamically
function changedTab() {
    const data = JSON.parse(this.responseText);
    updateResultsContainer(data);
    const { projects, recentProjects, tab, pagination } = data;

    const urlParams = new URLSearchParams(window.location.search);
    
    // Remove any tab-specific parameters not relevant to the selected tab
    for (const key of urlParams.keys()) {
        if (key.startsWith('tab') && key !== 'tab') {
            urlParams.delete(key);
        }
    }

    // Update the 'tab' parameter to the newly selected tab
    urlParams.set('tab', tab);

    const newUrl = window.location.pathname + '?' + urlParams.toString();
    // Push the new URL state to the history
    history.pushState({ tab: tab }, '', newUrl);

    // Clear recent projects container if needed
    const recentProjectsContainer = document.getElementById('myRecent');
    const projectsTable = document.querySelector('.projects-table');
    const paginationContainer = document.querySelector('.pagination');
    const tableSection = document.getElementById('projectsSection');

    // Update recent projects
    if (recentProjectsContainer) {
        if (tab !== 'tabMy') {
            recentProjectsContainer.style.display = 'none';
        } else {
            // Populate recent projects
            recentProjectsContainer.style.display = ''; // Ensure it's visible
            recentProjectsContainer.innerHTML = `
                <a id="createNewProject" class="btn-project">
                    <div class="project-icon create-project">
                        <p>Create New Project</p>
                    </div>
                </a>
            `;

            if (recentProjects.length > 0) {
                recentProjects.forEach(project => {
                    recentProjectsContainer.innerHTML += `
                        <a href="/project/${project.id}" class="btn-project">
                            <div class="project-icon ${project.pivot?.is_favourite ? 'favorite-project' : 'not-favorite-project'}"
                                 data-id="${project.id}">
                                <b>★</b>
                            </div>
                            <div class="project-info">
                                <p class="project-name">${project.name}</p>
                                <p class="project-description">${project.description || ''}</p>
                            </div>
                        </a>
                    `;
                });
            }

            // Handle "Create New Project" modal
            const openCreateProjectModalButton = document.getElementById('createNewProject');
            const createProjectModal = document.getElementById('createProjectModal');
            // Open modal
            openCreateProjectModalButton.addEventListener('click', () => {
                createProjectModal.classList.remove('fade-out-modal');
                createProjectModal.classList.add('fade-in-modal');
                createProjectModal.firstElementChild.classList.remove('close-modal-anim');
                createProjectModal.firstElementChild.classList.add('open-modal-anim');
                createProjectModal.style.display = 'flex';
            });
        }
    }

    // Update projects table or show "No projects available" message
    if (projects.length > 0) {
        if (!projectsTable) {
            const tableMarkup = `
                <table class="projects-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="hide-mobile">Description</th>
                            <!-- Favourite column can be added here based on the active tab -->
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            `;
            tableSection.innerHTML = tableMarkup;
        }

        const projectsTableBody = document.querySelector('.projects-table tbody');
        const projectsTableHeader = document.querySelector('.projects-table thead tr');

        // Conditionally add/remove the "Favourite" column based on the tab
        if (tab === 'tabFav' || tab === 'tabMy') {
            if (!projectsTableHeader.querySelector('th:nth-child(3)')) {
                const favHeader = document.createElement('th');
                favHeader.innerText = 'Favourite';
                projectsTableHeader.appendChild(favHeader);
            }
        } else {
            const favHeader = projectsTableHeader.querySelector('th:nth-child(3)');
            if (favHeader) {
                favHeader.remove(); // Remove the Favourite column header
            }
        }

        // Now populate the table
        if (projectsTableBody) {
            projectsTableBody.innerHTML = ''; // Clear previous rows

            projects.forEach(project => {
                const row = document.createElement('tr');
                row.setAttribute('data-href', `/project/${project.id}`);
                row.classList.add('focusable');

                row.innerHTML = `
                    <td><p class="project-name">${project.name}</p></td>
                    <td class="hide-mobile"><p class="project-description">${project.description || '-'}</p></td>
                `;

                // Conditionally add the Favourite column data
                if (tab === 'tabFav' || tab === 'tabMy') {
                    const favCell = document.createElement('td');
                    favCell.innerHTML = `
                        <div class="${project.pivot?.is_favourite ? 'favorite-project' : 'not-favorite-project'}"
                            data-text="${project.pivot?.is_favourite ? 'Remove from favourites' : 'Add to favourites' }"
                            data-id="${project.id}">
                            <b class="centered focusable tooltip left"
                               data-text="${project.pivot?.is_favourite ? 'Remove from favourites' : 'Add to favourites' }"
                            >★</b>
                        </div>
                    `;
                    row.appendChild(favCell);
                }

                projectsTableBody.appendChild(row);
            });
            addFavoriteClickLink();

            // Open project from project table  
            let projectsTR = document.querySelectorAll('.projects-table > tbody > tr');
            projectsTR.forEach(project => {
                project.addEventListener('click', function () {
                    window.location.href = project.getAttribute('data-href');
                });
            });
        }

    } else {
        // If no projects exist, hide the table and show the message
        if (projectsTable) {
            projectsTable.remove(); // Remove the table if it exists
        }
        tableSection.innerHTML = `
             <h2>No projects available.</h2>
        `;
    }

    // Update pagination
    if (paginationContainer) {
        paginationContainer.innerHTML = pagination; // Replace the pagination HTML
    }

    setButtonRoles();
}
