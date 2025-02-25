document.addEventListener('DOMContentLoaded', () => {
    const tableBody = document.querySelector('#project-tasks-table tbody');
    const deadlineHeader = document.querySelector('[data-sort-key="deadline"]');
    const priorityHeader = document.querySelector('[data-sort-key="priority"]');


    // Extract tasks from the table
    const extractTasks = () => {
        const tasks = Array.from(tableBody.querySelectorAll('tr'))
            .filter(row => !row.classList.contains('summary-row'))
            .map(row => {
                const deadlineText = row.children[1].textContent.trim();
                const priorityText = row.children[2].textContent.trim();
                const priorityMap = {
                    'High': 3,
                    'Medium': 2,
                    'Low': 1
                };
                return {
                    name: row.children[0].textContent.trim(),
                    deadline: deadlineText && deadlineText !== 'N/A' ? new Date(deadlineText) : null,
                    priority: priorityMap[priorityText] || 0, // Default to 0 if not found
                    id: row.children[0].children[0].getAttribute('data-id')
                };
                
            });
        return tasks;

    };

    // Render the tasks back to the table
    const renderTasks = (tasks) => {
        const summaryRow = tableBody.querySelector('.summary-row');
        const summaryHTML = summaryRow ? summaryRow.outerHTML : '';

        const taskRowsHTML = tasks.map((task) => `
            <tr class="project-tasks-item" data-task-id="${task.id}">
                <td>
                    <a href="#" class="task-link" data-id=${task.id}>${task.name}</a>
                </td>
                <td>${task.deadline ? formatDate(task.deadline) : 'N/A'}</td>
                <td>
                    ${task.priority === 3 ? '<i class="fa-solid fa-flag priority-high"></i> High' :
                        task.priority === 2 ? '<i class="fa-solid fa-flag priority-medium"></i> Medium' :
                '<i class="fa-solid fa-flag priority-low"></i> Low'}
                </td>
            </tr>
        `).join('');

        tableBody.innerHTML = taskRowsHTML + summaryHTML;
        activateTaskLinks();
    };

    //Function that links each task to opening the task panel
    function activateTaskLinks(){
        const taskLinks = document.querySelectorAll('.task-link');
        taskLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const taskId = this.dataset.id;
                getTaskInformation(taskId);
                addTaskToUrl(taskId);
            });
        });
    }

    // Sort tasks based on given criteria
    const sortTasks = (tasks, sortCriteria) => {
        return [...tasks].sort((a, b) => {
            for (const { key, order } of sortCriteria) {
                let comparison = 0;

                if (key === 'deadline') {
                    const aHasDeadline = !!a.deadline;
                    const bHasDeadline = !!b.deadline;

                    if (!aHasDeadline && bHasDeadline) return 1; // No deadline goes to the bottom
                    if (aHasDeadline && !bHasDeadline) return -1; // No deadline goes to the bottom
                    if (aHasDeadline && bHasDeadline) {
                        comparison = a.deadline - b.deadline; // Compare deadlines
                    }
                } else if (key === 'priority') {
                    comparison = -a.priority + b.priority; // Compare priorities
                }

                if (comparison !== 0) return order === 'asc' ? comparison : -comparison;
            }
            return 0;
        });
    };

    const sortCriteria = []; // Active sort criteria

    // Handle sorting when a header is clicked
    const handleSort = (key, order) => {
        const existingCriterionIndex = sortCriteria.findIndex(c => c.key === key);

        if (existingCriterionIndex >= 0) {
            // If the same key is clicked, toggle the order (asc/desc)
            const existingCriterion = sortCriteria[existingCriterionIndex];
            existingCriterion.order = existingCriterion.order === 'asc' ? 'desc' : 'asc';
        } else {
            // If it's a new sorting criterion, add it with the given order
            if (order && order !== 'remove') {
                sortCriteria.push({ key, order });
            }
        }

        // Extract tasks, sort them, and render
        if (tableBody.querySelectorAll('tr').length > 1) {
            const tasks = extractTasks();
            const sortedTasks = sortTasks(tasks, sortCriteria);
            renderTasks(sortedTasks);

            // Update the UI indicators
            updateDropdownIndicators();
        }
    };

    const removeSortCriterion = (key) => {
        // Remove the sorting criterion for the given key
        const indexToRemove = sortCriteria.findIndex(c => c.key === key);
        if (indexToRemove >= 0) {
            sortCriteria.splice(indexToRemove, 1);
        }

        // Extract tasks, sort them, and render
        if (tableBody.querySelectorAll('tr').length > 1) {
            const tasks = extractTasks();
            const sortedTasks = sortTasks(tasks, sortCriteria);
            renderTasks(sortedTasks);

            // Update the UI indicators
            updateDropdownIndicators();
        }
    };

    // Update dropdown sort indicators in the UI
    const updateDropdownIndicators = () => {
        const headers = document.querySelectorAll('.project-tasks-sortable-header');
        headers.forEach(header => {
            const key = header.dataset.sortKey;
            const criterion = sortCriteria.find(c => c.key === key);
            // Remove existing indicators
            const existingIndicator = header.querySelector('.tasks-sort-icon');
            if (existingIndicator) existingIndicator.remove();
            if (criterion) {
                // Create the icon for ascending or descending
                const icon = document.createElement('i');
                icon.classList.add('tasks-sort-icon', 'fa-solid');
                if (criterion.order === 'asc') {
                    icon.classList.add('fa-arrow-up-wide-short');
                } else if (criterion.order === 'desc') {
                    icon.classList.add('fa-arrow-down-short-wide');
                }

                // If there are multiple sorting criteria, add position indicator inside the icon
                if (sortCriteria.length > 1) {
                    const position = document.createElement('span');
                    position.classList.add('sort-position');
                    position.textContent = ` ${sortCriteria.indexOf(criterion) + 1}`; // Position number
                    icon.appendChild(position);  // Append the position inside the icon
                }

                // Append the icon to the header
                header.appendChild(icon);
            }
        });
    };

    // Check if all tasks have the same priority
    const checkIfAllTasksHaveSamePriority = (tasks) => {
        const priorities = tasks.map(task => task.priority);
        return new Set(priorities).size === 1; // All priorities are the same
    };

    // Check if all tasks have the same deadline
    const checkIfAllTasksHaveSameDeadline = (tasks) => {
        const deadlines = tasks.map(task => task.deadline ? task.deadline.toISOString() : 'N/A');
        return new Set(deadlines).size === 1; // All deadlines are the same
    };

    // Show or hide sort buttons based on task consistency
    const updateSortButtonsVisibility = () => {

        if (tableBody.querySelectorAll('tr').length > 1) {
            const tasks = extractTasks();

            if (checkIfAllTasksHaveSamePriority(tasks)) {
                priorityHeader.querySelector('.tasks-dropdown-button').style.display = 'none';
            } else {
                priorityHeader.querySelector('.tasks-dropdown-button').style.display = 'inline-block';
            }

            if (checkIfAllTasksHaveSameDeadline(tasks)) {
                deadlineHeader.querySelector('.tasks-dropdown-button').style.display = 'none';
            } else {
                deadlineHeader.querySelector('.tasks-dropdown-button').style.display = 'inline-block';
            }
        }
        else {
            priorityHeader.querySelector('.tasks-dropdown-button').style.display = 'none';
            deadlineHeader.querySelector('.tasks-dropdown-button').style.display = 'none';
        }
    };

    // Call this function to initialize and check if buttons should be hidden
    updateSortButtonsVisibility();

    //CLose the dropdowns if there is a click outside of it
    const handleOutsideClick = (event) => {
        document.querySelectorAll('.tasks-dropdown-menu').forEach(menu => {
            if (!menu.contains(event.target) && !menu.previousElementSibling.contains(event.target)) {
                menu.style.display = 'none'; // Close the menu
            }
        });
    };

    document.addEventListener('click', handleOutsideClick);

    document.querySelectorAll('.project-tasks-sortable-header').forEach(header => {
        const dropdownButton = header.querySelector('.tasks-dropdown-button');
        const dropdownMenu = header.querySelector('.tasks-dropdown-menu');
        const options = dropdownMenu.querySelectorAll('li');

        dropdownButton.addEventListener('click', () => {
            dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
        });
        options.forEach(option => {
            const order = option.dataset.sortOrder;
            const removeSelection = option.querySelector('.tasks-remove-selection');

            option.addEventListener('click', () => {
                // Remove "remove selection" cross from all options
                options.forEach(opt => {
                    const otherRemoveSelection = opt.querySelector('.tasks-remove-selection');
                    if (otherRemoveSelection) {
                        otherRemoveSelection.style.display = 'none';
                    }
                });

                // Handle sorting and display cross for the current selection
                handleSort(header.dataset.sortKey, order);
                dropdownMenu.style.display = 'none';
                if (removeSelection) {
                    removeSelection.style.display = 'inline';
                }
            });

            // Handle "remove selection" click
            removeSelection.addEventListener('click', (e) => {
                e.stopPropagation(); // Prevent triggering the option click
                removeSortCriterion(header.dataset.sortKey); // Remove sorting criterion
                dropdownMenu.style.display = 'none';
                removeSelection.style.display = 'none';
            });
        });
    });
});
