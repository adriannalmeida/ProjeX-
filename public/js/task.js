const pusherAppKey = '17c409875705a77f352c';
const pusherCluster = 'eu';
const pusher = new Pusher(pusherAppKey, {
    cluster: pusherCluster,
    encrypted: true,
});
// Main function to open task panel
function openTaskPanel(data, taskId) {
    updateTaskDetails(data);
    handleTaskAssignees(data.task.accounts, data.project_id);
    updateTaskForms(taskId, data);
    handleModalVisibility(data.task.finish_date);
    initializePusher(taskId);
    populateCommentsModal(data.comments);
    populateUsersModal(taskId, data.project_members, data.task.accounts, data.project_id);
    if (data.is_deleted) {
        hideDeletedTaskActions();
    }
    setButtonRoles();
}
// Hide actions for deleted tasks
function hideDeletedTaskActions() {
    const actionsToHide = document.querySelectorAll('.panel-actions .icon-button, #toggleAssignIcon, #editTaskLink, #deleteTaskForm');
    actionsToHide.forEach(action => {
        action.style.display = 'none';
    });

    const completeButtons = document.querySelectorAll('#markCompletedForm, #markUncompletedForm');
    completeButtons.forEach(button => {
        button.style.display = 'none';
    });
}
// Add task to URL
function addTaskToUrl(taskId){
    const newUrl = `${window.location.pathname}/task/${taskId}`;
    history.pushState(null, '', newUrl);
}
//Remove task from URL
function removeTaskFromUrl(){
    const baseUrl = window.location.pathname.split('/task/')[0];
    history.pushState(null, '', baseUrl);
}
//Close all modals
function closeAllModals() {
    document.querySelectorAll('.modal').forEach(modal => {
        modal.classList.remove('fade-in-modal');
        modal.classList.add('fade-out-modal');
        modal.firstElementChild.classList.remove('open-modal-anim');
        modal.firstElementChild.classList.add('close-modal-anim');
        removeTaskFromUrl();
    });
    document.querySelectorAll('.panel').forEach(panel => {
        panel.firstElementChild.classList.remove('open-panel-anim');
        panel.firstElementChild.classList.add('close-panel-anim');
        panel.lastElementChild.classList.remove('open-panel-anim');
        panel.lastElementChild.classList.add('close-panel-anim');
        panel.classList.remove('fade-in-modal');
        panel.classList.add('fade-out-modal');
        removeTaskFromUrl();
    });
}
// Update task details in the modal
function updateTaskDetails(data) {
    const taskTitles = document.querySelectorAll('.taskTitleModal');
    const taskDescription = document.getElementById('taskDescriptionPanel');
    const taskStartDate = document.getElementById('taskStartDatePanel');
    const taskDeadlineDate = document.getElementById('taskDeadlineDatePanel');
    const taskFinishDate = document.getElementById('taskFinishDatePanel');
    const taskPriority = document.getElementById('taskPriorityPanel');
    const taskEventsList = document.querySelector('.list-group');
    // Clear existing events
    taskEventsList.innerHTML = '';

    taskTitles.forEach(taskTitle => {
        taskTitle.textContent = data.task.name;
    });

    taskDescription.textContent = data.task.description;
    taskStartDate.textContent = formatDate(data.task.start_date);
    taskDeadlineDate.textContent = formatDate(data.task.deadline_date);
    taskFinishDate.textContent = formatDate(data.task.finish_date);
    taskPriority.textContent = data.task.priority;
    taskPriority.parentElement.classList = `taskPriority ${data.task.priority}`

    // Populate task events
    data.project_events.forEach(event => {
        const eventItem = document.createElement('li');
        eventItem.className = 'list-group-item';

        const eventDate = document.createElement('strong');
        eventDate.textContent = new Date(event.time).toLocaleDateString('en-US', {
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });

        const eventType = document.createElement('span');
        eventType.classList = `event-icon`;

        if (event.type === 'Task_Assigned') {
            eventType.innerHTML = '<i class="fa fa-arrow-right"></i>';
        }
        else if (event.type === 'Task_Unassigned') {
            eventType.innerHTML = '<i class="fa fa-arrow-left"></i>';
        }
        else if (event.type === 'Task_Created') {
            eventType.innerHTML = '<i class="fa fa-plus"></i>';
        }
        else if (event.type === 'Task_Completed') {
            eventType.innerHTML = '<i class="fa fa-check"></i>';
        }
        else if (event.type === 'Task_Deactivated') {
            eventType.innerHTML = '<i class="fa fa-trash"></i>';
        }
        else {
            eventType.innerHTML = '<i class="fa fa-calendar"></i>';
        }


        const eventAccount = document.createElement('span');
        eventAccount.className = `event-account`;
        eventAccount.textContent = event.account_in_description;
        eventAccount.setAttribute( 'role',"button");
        if (event.route !== "") {
            eventAccount.onclick = () => {
                window.location = `${event.route}`;
            };
        }

        const eventDescription = document.createTextNode(` ${event.description} on `);


        const eventContent = document.createElement('div');
        eventContent.className = 'event-content';
        eventContent.appendChild(eventAccount);
        eventContent.appendChild(eventDescription);
        eventContent.appendChild(eventDate);


        // Append elements to the list item
        eventItem.appendChild(eventType);
        eventItem.appendChild(eventContent);

        // Append the list item to the task events list
        taskEventsList.appendChild(eventItem);
    });
}

// Handle assignees section
function handleTaskAssignees(accounts, projectId) {
    const taskAssignees = document.getElementById('taskAssigneesPanel');
    taskAssignees.innerHTML = '';

    if (accounts && accounts.length > 0) {
        accounts.forEach(account => {
            const img = document.createElement('img');
            img.src = account.image_path;
            img.alt = account.name;
            img.className = "round-photo";
            img.title = account.name;
            img.setAttribute('data-user-id', account.id);
            img.setAttribute( 'role',"button");
            taskAssignees.appendChild(img);

            const name = document.createElement('span');
            name.className = "assignee-name";
            name.textContent = account.name;
            name.setAttribute( 'role',"button");
            name.setAttribute('data-user-id', account.id);

            img.addEventListener('click', function() {
                redirectToProfile(account.id, projectId);
            });

            name.addEventListener('click', function() {
                redirectToProfile(account.id, projectId);
            });

        });
    } else {
        const placeholder = document.createElement('p');
        placeholder.textContent = 'No assignees for this task.';
        placeholder.style.fontStyle = 'italic';
        taskAssignees.appendChild(placeholder);
    }
}

//Redirect to profile page
function redirectToProfile(userId, projectId) {
    window.location.href = `/project/${projectId}/projectMembers/${userId}`; // URL para o perfil
}


// Update task-related form actions
function updateTaskForms(taskId, data) {
    const markCompletedForm = document.getElementById('markCompletedForm');
    const markUncompletedForm = document.getElementById('markUncompletedForm');
    const deleteTaskForm = document.getElementById('deleteTaskForm');
    const editTaskForm = document.getElementById('editTaskForm');
    const commentForm = document.getElementById('commentForm');

    if (markCompletedForm) markCompletedForm.action = `/task/${taskId}/completed`;
    if (markUncompletedForm) markUncompletedForm.action = `/task/${taskId}/uncompleted`;
    if (deleteTaskForm) deleteTaskForm.action = `/task/${taskId}/delete`;
    if (commentForm) commentForm.action = `/task/${taskId}/comment`;
    if (editTaskForm) editTaskForm.action = `/task/${taskId}`;

    const editName = document.getElementById('editTaskName');
    const editDescription = document.getElementById('editTaskDescription');
    const editDeadlineDate = document.getElementById('editTaskDeadlineDate');
    const editFinishDate = document.getElementById('editTaskFinishDate');
    const editPriority = document.getElementById('editTaskPriority');

    if (editName) editName.value = data.task.name;
    if (editDescription) editDescription.value = data.task.description;
    if (editDeadlineDate) editDeadlineDate.value = data.task.deadline_date;
    if (editFinishDate) editFinishDate.value = data.task.finish_date;
    if (editPriority) editPriority.value = data.task.priority;
}

// Handle modal visibility based on task completion
function handleModalVisibility(finishDate, taskId) {
    const panel = document.getElementById('taskPanel');
    const detailsModal = document.getElementById('content-task-details');
    const editModal = document.getElementById('content-task-edit');
    const backToTaskPanel = document.getElementById('backToTaskPanel');
    const noUsersMessagee = document.getElementById('noUsersMessage');
            if (noUsersMessagee) {
                noUsersMessagee.style.display = 'none';  // Esconde a mensagem
            }

    if (backToTaskPanel) {
        backToTaskPanel.addEventListener('click', () => {

            // Alternate visibility between panels
            if (editModal) {
                editModal.classList.remove('open-panel-anim');
                editModal.classList.add('close-panel-anim');
            }
            if (detailsModal) {
                detailsModal.classList.remove('close-panel-anim');
                detailsModal.classList.add('open-panel-anim');
            }
        });
    }

    // Handle edit task
    if (document.getElementById('editTaskLink')) {
        document.getElementById('editTaskLink').addEventListener('click', (e) => {
            e.preventDefault();

            if (detailsModal) detailsModal.classList.remove('open-panel-anim');
            if (detailsModal) detailsModal.classList.add('close-panel-anim');

            if (editModal) editModal.classList.remove('close-panel-anim');
            if (editModal) editModal.classList.add('open-panel-anim');
            editModal.style.display = 'block';
        });
    }

    const markCompletedForm = document.getElementById('markCompletedForm');
    const markUncompletedForm = document.getElementById('markUncompletedForm');
    const taskFinishDateWrapper = document.getElementById('taskFinishDatePanel').parentElement;

    if (finishDate) {
        if (markCompletedForm) markCompletedForm.style.display = 'none';
        if (markUncompletedForm) markUncompletedForm.style.display = 'block';
    } else {
        taskFinishDateWrapper.style.display = 'none';
        if (markUncompletedForm) markUncompletedForm.style.display = 'none';
        if (markCompletedForm) markCompletedForm.style.display = 'block';
    }
    detailsModal.classList.remove('close-panel-anim');
    detailsModal.classList.add('open-panel-anim');
    if (editModal) editModal.classList.remove('open-panel-anim');
    if (editModal) editModal.classList.add('close-panel-anim');
    if (editModal) editModal.style.display = 'none';
    panel.classList.remove('fade-out-modal');
    panel.classList.add('fade-in-modal');
    panel.style.display = 'grid'
    const closeAssignIcon = document.getElementById('closeAssignIcon');
    const userSearch = document.getElementById('userSearch');
    const clearSearch = document.getElementById('clearSearch');
    const taskAssignPanel = document.getElementById('taskAssignPanel');
    const openAssignIcon = document.getElementById('openAssignIcon');
    if (taskAssignPanel) taskAssignPanel.style.display = 'none';
    if (closeAssignIcon) closeAssignIcon.style.display='none';
    if(userSearch){
        userSearch.value = '';
        userSearch.style.display='none';
    }
    if (clearSearch) {
        clearSearch.style.display = 'none';
    }
    if (openAssignIcon) {
        userSearch.value = '';
        openAssignIcon.style.display = 'inline';
    }

}


// Initialize Pusher for real-time updates
function initializePusher(taskId) {

    const channel = pusher.subscribe(`projeX`);
    channel.bind('posted-comment', (data) => {
        console.log(data);
        if (data.task_id == taskId) {
           addCommentToCommentsModal(data);
        }
    });
}
// Function to stop listening to Pusher Notifications
function stopListening() {
    const channel = pusher.subscribe(`projeX`);
    channel.unbind('posted-comment');
}

// Function to set up modal close behavior
function setupCloseModal(buttonId, modalElement) {
    const button = document.getElementById(buttonId);
    if (button) {
        button.addEventListener('click', () => {
            modalElement.firstElementChild.classList.remove('open-panel-anim');
            modalElement.firstElementChild.classList.add('close-panel-anim');
            modalElement.lastElementChild.classList.remove('open-panel-anim');
            modalElement.lastElementChild.classList.add('close-panel-anim');
            modalElement.classList.remove('fade-in-modal');
            modalElement.classList.add('fade-out-modal');
            // Revert the URL to the project URL (remove /task/{taskId})
            removeTaskFromUrl();
            stopListening();
        });
    }

}

// Function to handle form submission with optional reset
function handleFormSubmit(form, reset = false) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const actionUrl = this.action;

        fetch(actionUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        })
            .then(response => response.json())
            .then(data => {
                if (data.errors) {
                    showValidationErrors(form, data.errors);
                } else {
                    removeTaskFromUrl();
                    location.reload();
                    if (reset) resetForm(form);
                }
            })
            .catch(err => console.error('Error:', err));
    });
}
// Function to fetch task information
function getTaskInformation(taskId){
    sendAjaxRequest('get', `/task/${taskId}`, {}, function () {
        const data = JSON.parse(this.responseText);
        if (this.status >= 200 && this.status < 300) {
            openTaskPanel(data, taskId);
        }
        else{
            if (data.error) {
                createToastNotification(false, data.error);
            } else {
                createToastNotification(false, "Unknown error");
            }
            removeTaskFromUrl();
        }
    }, true);
}

function detectTaskInURL(){
    const match = window.location.pathname.match(/\/task\/(\d+)$/); // Match /task/{taskId} at the end of the URL
    const taskId = match ? match[1] : null;
    const panel = document.getElementById('taskPanel');

    if (taskId) {
        // Open the task panel for the detected taskId
        getTaskInformation(taskId);
    }
    else{
        panel.style.display = 'none';
    }

}
document.addEventListener('DOMContentLoaded', function () {

    window.addEventListener('popstate', () => {
        detectTaskInURL();
    });

    //Function to handle the search bar behavior relative to searching accounts for task assignments
    setupAssignUsersSearch();

    const panel = document.getElementById('taskPanel');
    const editTaskForm = document.getElementById('editTaskForm');
    const createTaskForm = document.getElementById('createTaskForm');
    const commentForm = document.getElementById('commentForm');
    const createModal = document.getElementById('createModal');
    const closeCreateTaskModalButton = document.getElementById('closeCreateModal');
    const taskLinks = document.querySelectorAll('.task-link');
    const taskTableLinks = document.querySelectorAll('.taskTable-link');
    const taskDates = document.querySelectorAll('.taskFooter > .date');

    const userList = document.getElementById('userList');
    const noUsersMessage = document.getElementById('noUsersMessage');

    const commentsContainer = document.getElementById('commentsContainer');

    setupCloseModal('closePanel', panel);
    if (editTaskForm) setupCloseModal('closeEditModal', panel);
    if (closeCreateTaskModalButton) {
        closeCreateTaskModalButton.addEventListener('click', () => {
            createModal.classList.remove('fade-in-modal');
            createModal.classList.add('fade-out-modal');
            createModal.firstElementChild.classList.remove('open-modal-anim');
            createModal.firstElementChild.classList.add('close-modal-anim');
        });
    }


    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { // Check if the 'Escape' key is pressed
            closeAllModals();
        }
    });

    detectTaskInURL();
    taskLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const taskId = this.dataset.id;
            getTaskInformation(taskId);
            addTaskToUrl(taskId);
        });
    });


    taskTableLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const taskTableId = this.dataset.id;
            createModal.classList.remove('fade-out-modal');
            createModal.classList.add('fade-in-modal');
            createModal.firstElementChild.classList.remove('close-modal-anim');
            createModal.firstElementChild.classList.add('open-modal-anim');
            createModal.style.display = 'flex';
            createTaskForm.action = `/taskTable/${taskTableId}/storeTask`;

        });
    })
    const toggleAssignIcon = document.getElementById('toggleAssignIcon');
    const openAssignIcon = document.getElementById('openAssignIcon');
    const closeAssignIcon = document.getElementById('closeAssignIcon');
    const userSearch = document.getElementById('userSearch');
    const clearSearch = document.getElementById('clearSearch');
    const taskAssignPanel = document.getElementById('taskAssignPanel');
    // Toggle dropdown and icons
    if (toggleAssignIcon) {
        toggleAssignIcon.addEventListener('click', () => {
            const isDropdownVisible = taskAssignPanel.style.display === 'block';

            if (isDropdownVisible) {
                // Hide dropdown and show "user-plus" icon
                taskAssignPanel.style.display = 'none';
                if(userSearch)userSearch.style.display = 'none';
                clearSearch.style.display = 'none';
                openAssignIcon.style.display = 'inline';
                closeAssignIcon.style.display = 'none';
                toggleAssignIcon.setAttribute('data-text', 'Assign User');
                const noUsersMessage = document.getElementById('noUsersMessage');
                if (noUsersMessage) {
                    noUsersMessage.style.display = 'none';  // Esconde a mensagem
                }
                const match = window.location.pathname.match(/\/task\/(\d+)$/); // Match /task/{taskId} at the end of the URL
                const taskId = match ? match[1] : null;
                sendAjaxRequest('get', `/task/${taskId}`, {}, function () {
                    const data = JSON.parse(this.responseText);
                    if (this.status >= 200 && this.status < 300) {
                        populateUsersModal(taskId, data.project_members, data.task.accounts, data.project_id);
                    }
                    else{
                        if (data.error) {
                            createToastNotification(false, data.error);
                        } else {
                            createToastNotification(false, "Unknown error");
                        }
                        removeTaskFromUrl();
                    }
                }, true);
            } else {
                // Show dropdown and show "times" icon
                taskAssignPanel.style.display = 'block';
                if (noUsersMessage) {
                    noUsersMessage.style.display = 'none';  // Esconde a mensagem
                }
                if(userSearch)userSearch.value = '';
                if(userSearch)userSearch.style.display = 'block';
                clearSearch.style.display = 'none';
                openAssignIcon.style.display = 'none';
                closeAssignIcon.style.display = 'inline';
                toggleAssignIcon.setAttribute('data-text', 'Close');
            }
        });
    }

    // Handle form submission for Create Task
    if (createTaskForm) handleFormSubmit(createTaskForm, true);

    // Handle form submission for Edit Task
    if (editTaskForm) handleFormSubmit(editTaskForm, true);

    // Handle comment form submission
    if (commentForm) {
        commentForm.addEventListener('submit', event => {
            event.preventDefault();
            const formData = new FormData(commentForm);
            const xhr = new XMLHttpRequest();
            xhr.open('POST', commentForm.action, true);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
            xhr.setRequestHeader('Accept', 'application/json');

            xhr.onload = () => {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (xhr.status >= 200 && xhr.status < 300) {
                        if (response.errors) {
                            showValidationErrors(commentForm, response.errors);
                        } else {
                            commentForm.reset();
                            createToastNotification(true, response.success);
                        }
                    } else {
                        showValidationErrors(commentForm, response.errors);
                    }
                } catch (e) {
                    console.error('Error parsing server response:', xhr.responseText);
                }
            };

            xhr.onerror = () => {
                console.error('Network error.');
            };

            xhr.send(formData);
        });
    }
    commentsContainer.scrollTop = commentsContainer.scrollHeight;

    // Handle dynamic updates by scrolling to the bottom when new messages are added
    const observer = new MutationObserver(() => {
        commentsContainer.scrollTop = commentsContainer.scrollHeight;
    });
    observer.observe(commentsContainer, { childList: true });

    panel.addEventListener('show', () => {
        observer.observe(commentsContainer, { childList: true });
    });

    panel.addEventListener('hide', () => {
        observer.disconnect();
    });

    taskDates.forEach(date => {
        if (!isNaN( new Date(date.textContent))) date.textContent = formatDate(date.textContent);
        if (date.classList.contains('finished')) {
            let strong = document.createElement("strong");
            strong.textContent = 'Finish date: ';
            const content = date.textContent;
            date.textContent = '';
            date.appendChild(strong);
            date.append(content);
        }
    })
});

let tasks = document.querySelectorAll('.taskList > *');

if (tasks) {
    tasks.forEach(function(task) {
        task.addEventListener('dragstart', handleDragStart);
        task.addEventListener('dragover', handleDragOver);
        task.addEventListener('dragenter', handleDragEnter);
        task.addEventListener('dragleave', handleDragLeave);
        task.addEventListener('dragend', handleDragEnd);
        task.addEventListener('drop', handleDrop);
    });

    let dragSrcEl

    // Function to handle drag start event
    function handleDragStart() {
        this.style.opacity = '0.2';
        dragSrcEl = this;
    }

    // Function to handle style on end of drag event
    function handleDragEnd() {
        this.style.opacity = '';
        tasks.forEach(function (item) {
            item.classList.remove('over')
        })
    }

    // Function to handle drag over event
    function handleDragOver(e) {
        e.preventDefault();
        return false;
    }

    //Function triggered when a draggable element enters a valid drop target
    function handleDragEnter() {
        if (this.classList.contains('table-empty')) {
            if (this.getElementsByClassName('task-position').length === 0) {
                let taskPosition = document.createElement("div");
                taskPosition.classList.add('task-position');
                taskPosition.appendChild(document.createElement("p"));
                this.insertBefore(taskPosition, this.firstChild);
            }
        }
        else if (this.getElementsByClassName('task-position').length === 0 && dragSrcEl !== this) {
            let taskPositionBefore = document.createElement("div");
            let taskPositionAfter = document.createElement("div");
            taskPositionBefore.classList.add('task-position');
            taskPositionBefore.appendChild(document.createElement("p"));
            taskPositionAfter.classList.add('task-position');
            taskPositionAfter.appendChild(document.createElement("p"));
            this.appendChild(taskPositionBefore);
            this.appendChild(taskPositionAfter);
            this.insertBefore(taskPositionBefore, this.firstChild);
        }
    }

    //Function triggered when a draggable element leaved in a valid drop target
    function handleDragLeave(e) {
        if (e.currentTarget === this && !this.contains(e.relatedTarget)) {
            const taskPositions = this.getElementsByClassName('task-position');
            if (taskPositions.length > 1) taskPositions[1].remove();
            if (taskPositions.length > 0) taskPositions[0].remove();
        }
        else if (e.relatedTarget.classList.contains('task-position')) {
            e.relatedTarget.classList.add('over');
        }
        else if (e.relatedTarget.parentElement.classList.contains('task-position')) {
            e.relatedTarget.parentElement.classList.add('over');
        }
        else {
            this.firstElementChild.classList.remove('over');
            this.lastElementChild.classList.remove('over');
        }
    }

    // Function to handle drop event
    function handleDrop(e) {
        e.stopPropagation() // stops the browser from redirecting.

        const taskPositions = this.getElementsByClassName('task-position');
        const dragDestEl = this;

        if (dragSrcEl !== this
        && ((this.classList.contains('task')
            && (taskPositions[0].classList.contains('over')
                || taskPositions[1].classList.contains('over')))
        || (this.classList.contains('table-empty')
                && (taskPositions[0].classList.contains('over'))))) {

            const taskId = dragSrcEl.getElementsByClassName('task-link')[0].getAttribute('data-id');

            sendAjaxRequest('get', `/task/${taskId}`, {}, function () {
                if (this.status >= 200 && this.status < 300) {
                    const data = JSON.parse(this.responseText);
                    firstTaskHandler(data.task, taskPositions, dragSrcEl, dragDestEl);
                }
                else{
                    if (this.error) {
                        createToastNotification(false, this.error);
                    } else {
                        createToastNotification(false, "Unknown error");
                    }
                    removeTaskFromUrl();
                }
            }, true);
            return true
        }
        else {
            if (taskPositions.length > 1) taskPositions[1].remove();
            if (taskPositions.length > 0) taskPositions[0].remove();
            return false
        }
    }
}

// Function to handle realocation
function firstTaskHandler(data, taskPositions, dragSrcEl, dragDestEl) {
    const idSrc = data.id;

    if (dragDestEl.classList.contains('table-empty')) {
        const table = dragDestEl.parentElement.parentElement.firstElementChild.textContent;

        sendAjaxRequest('put', `/task/${idSrc}/change-position/${0}/${encodeURIComponent(table)}`, {}, function () {
            if (this.status >= 200 && this.status < 300) {
                const data = JSON.parse(this.responseText);
                swapHandler(data, dragSrcEl, dragDestEl, false)
            }
            else{
                if (this.error) {
                    createToastNotification(false, this.error);
                } else {
                    createToastNotification(false, "Unknown error");
                }
                removeTaskFromUrl();
            }
        }, true);
        taskPositions[0].remove();

    }
    else {
        const taskId = dragDestEl.getElementsByClassName('task-link')[0].getAttribute('data-id');

        sendAjaxRequest('get', `/task/${taskId}`, {}, function () {
            if (this.status >= 200 && this.status < 300) {
                const data = JSON.parse(this.responseText);
                secondTaskHandler(data.task, taskPositions, dragSrcEl, dragDestEl, idSrc)
            }
            else{
                if (this.error) {
                    createToastNotification(false, this.error);
                } else {
                    createToastNotification(false, "Unknown error");
                }
                removeTaskFromUrl();
            }
        }, true);

    }
}

// Function to handle realocation
function secondTaskHandler(data, taskPositions, dragSrcEl, dragDestEl, idSrc) {
    let posDest = data.position;
    const table = dragDestEl.parentElement.parentElement.firstElementChild.textContent;

    let after = false;
    if (taskPositions[1].classList.contains('over')) {
        posDest += 1;
        after = true;
    }

    sendAjaxRequest('put', `/task/${idSrc}/change-position/${posDest}/${encodeURIComponent(table)}`, {}, function () {
        if (this.status >= 200 && this.status < 300) {
            const data = JSON.parse(this.responseText);
            swapHandler(data, dragSrcEl, dragDestEl, after);
        }
        else{
            if (this.error) {
                createToastNotification(false, this.error);
            } else {
                createToastNotification(false, "Unknown error");
            }
            removeTaskFromUrl();
        }
    }, true);

    taskPositions[1].remove();
    taskPositions[0].remove();
}

// Function to handle swap
function swapHandler(data, dragSrcEl, dragDestEl, after) {
    if (data.success) {
        if (dragSrcEl.parentElement.childElementCount === 1) {
            let taskEmpty = document.createElement("li");
            let text = document.createElement("p");
            text.innerText = "No tasks available in this table.";
            taskEmpty.classList.add('table-empty');
            taskEmpty.appendChild(text);
            // Add drag-and-drop event listeners to the new empty state element
            taskEmpty.addEventListener('dragstart', handleDragStart);
            taskEmpty.addEventListener('dragover', handleDragOver);
            taskEmpty.addEventListener('dragenter', handleDragEnter);
            taskEmpty.addEventListener('dragleave', handleDragLeave);
            taskEmpty.addEventListener('dragend', handleDragEnd);
            taskEmpty.addEventListener('drop', handleDrop);
            dragSrcEl.parentElement.append(taskEmpty);
        }
        if (after) dragDestEl.parentElement.insertBefore(dragSrcEl, dragDestEl.nextElementSibling);
        else dragDestEl.parentElement.insertBefore(dragSrcEl, dragDestEl);
        if (dragDestEl.classList.contains('table-empty')) dragDestEl.remove();
    }
    else {
        location.reload();
    }
}

// Function to handle accounts for task assignments
function populateUsersModal(taskId, allUsers, assignedUsers, projectId) {
    const usersList = document.getElementById('taskAssignPanel');
    if (usersList) usersList.innerHTML = ''; // Clear previous content



    allUsers.forEach(user => {
        //Unkown user cannot be assigned
        if (user.email === 'unknown@example.com') {
            return; // Ignora a iteração se o usuário for "unknown"
        }

        const listItem = document.createElement('li');
        listItem.className = 'user-item';

        // Account Image
        const avatar = document.createElement('img');
        avatar.src = user.image_path;
        avatar.alt = user.name;
        avatar.className = "round-photo";
        avatar.setAttribute( 'role',"button");

        // User name
        const label = document.createElement('label');
        label.textContent = user.name;
        label.setAttribute( 'role',"button");

        // Checkbox
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.checked = assignedUsers.some(assigned => assigned.id === user.id);

        // Redirect to user profile page
        avatar.addEventListener('click', () => {
            redirectToProfile(user.id, projectId);
        });

        label.addEventListener('click', () => {
            redirectToProfile(user.id, projectId);
        });
        // Toggle assignment
        checkbox.addEventListener('change', () => {
            if (checkbox.checked) {
                assignUserToTask(taskId, user.id);
            } else {
                removeUserFromTask(taskId, user.id);
            }
        });

        // Append elements
        listItem.appendChild(avatar);
        listItem.appendChild(label);
        listItem.appendChild(checkbox);
        if (usersList) usersList.appendChild(listItem);
    });
}

// Function to handle task assignment
function handleTaskAssignAction(endpoint, method, payload, successMessage, errorMessage) {
    return fetch(endpoint, {
        method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: payload ? JSON.stringify(payload) : null
    })
        .then(response => {
            if (!response.ok) throw new Error(errorMessage);
            return response.json();
        })
        .then(data => {
            createToastNotification(true, successMessage);
            return data;
        })
        .catch(error => {
            createToastNotification(false, errorMessage);
            throw error;
        });
}
// Function to add the assigned users to the task
function addAssignedUser(account) {
    const taskAssignees = document.getElementById('taskAssigneesPanel');
    // Check if "No assignees" placeholder exists and remove it
    const placeholder = taskAssignees.querySelector('p');
    if (placeholder && placeholder.textContent === 'No assignees for this task.') {
        placeholder.remove();
    }

    // Create a new image element for the assigned user
    const img = document.createElement('img');
    img.src = account.image_path;
    img.alt = account.name;
    img.className = "round-photo";
    img.title = account.name;
    img.setAttribute('data-user-id', account.id); // For identifying the user
    taskAssignees.appendChild(img);
}

function assignUserToTask(taskId, userId) {
    const endpoint = `/task/${taskId}/assign-user`;
    const payload = { userId };
    const successMessage = 'User assigned successfully.';
    const errorMessage = 'Failed to assign user.';
    handleTaskAssignAction(endpoint, 'POST', payload, successMessage, errorMessage)
        .then(data => {
            if (data && data.user) {
                addAssignedUser(data.user);
            }
        })
        .catch(error => {
            console.error('Error while assigning user:', error);
        });
}
// Function to remove the assigned users
function removeAssignedUser(userId) {
    const taskAssignees = document.getElementById('taskAssigneesPanel');
    // Find the user's image by its data-user-id attribute
    const userImg = taskAssignees.querySelector(`img[data-user-id="${userId}"]`);

    if (userImg) {
        userImg.remove();
    }

    if (taskAssignees.children.length === 0) {
        const placeholder = document.createElement('p');
        placeholder.textContent = 'No assignees for this task.';
        placeholder.style.fontStyle = 'italic';
        taskAssignees.appendChild(placeholder);
    }
}
// Function to remove the assigned users from the task and handle success message
function removeUserFromTask(taskId, userId) {
    const endpoint = `/task/${taskId}/removeAccount/${userId}`;
    const successMessage = 'User removed successfully.';
    const errorMessage = 'Failed to remove user.';
    handleTaskAssignAction(endpoint, 'DELETE', null, successMessage, errorMessage);
    removeAssignedUser(userId);
}
//Helper function to populateCommentsModal
function addCommentToCommentsModal(comment){
    // If it is the first comment on the task, erase the No comments available message
    if (commentsContainer.querySelector('p') && commentsContainer.querySelector('p').textContent.trim() === "No comments available.") {
        commentsContainer.innerHTML = "";
    }
    const notificationDate = new Date(comment.create_date); // Example date string

    const formattedDate = notificationDate.toLocaleString('en-GB', {
        day: '2-digit',        // Day with leading zero
        month: 'short',        // Abbreviated month
        year: 'numeric',       // Full year
        hour: '2-digit',       // 24-hour format
        minute: '2-digit',     // Minute with leading zeros
        hour12: false          // Ensure 24-hour time format
    }).replace(',', '');
    const commentSection = document.createElement('section');
    commentSection.className = 'message-item';

    const headerDiv = document.createElement('div');
    headerDiv.className = 'message-header';

    const accountInfoDiv = document.createElement('div');
    accountInfoDiv.className = 'account-info';

    const img = document.createElement('img');
    img.src = comment.image_path;
    img.alt = 'Profile Picture';
    img.className = 'round-photo';

    const nameP = document.createElement('p');
    nameP.className = 'account-name';
    nameP.textContent = comment.get_account.name;

    accountInfoDiv.appendChild(nameP);

    const dateP = document.createElement('p');
    dateP.className = 'message-date';
    dateP.textContent = formattedDate;

    headerDiv.appendChild(accountInfoDiv);
    headerDiv.appendChild(dateP);

    const contentP = document.createElement('p');
    contentP.className = 'message-content';
    contentP.textContent = comment.content;

    commentSection.appendChild(img);
    commentSection.appendChild(headerDiv);
    commentSection.appendChild(contentP);

    commentsContainer.appendChild(commentSection);
}
// Function to populate the comments modal
function populateCommentsModal(comments) {
    commentsContainer.innerHTML = '';

    if (comments.length > 0) {
        comments.forEach(comment => {
            addCommentToCommentsModal(comment);
        });
    } else {
        const placeholder = document.createElement('p');
        placeholder.textContent = 'No comments available.';
        placeholder.style.fontStyle = 'italic';
        commentsContainer.appendChild(placeholder);
    }
    // Scroll to the bottom of the comments container
    commentsContainer.scrollTop = commentsContainer.scrollHeight;


}

// Initializes the user search functionality for the task assignment panel
function setupAssignUsersSearch() {
    const userSearch = document.getElementById('userSearch');
    const clearSearch = document.getElementById('clearSearch');

    if (userSearch) {
        userSearch.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            const users = taskAssignPanel.querySelectorAll('li'); // Items in the user list
            let visibleUsersCount = 0;

            users.forEach(user => {
                const userName = user.textContent.toLowerCase(); // Name of the user in the list item
                if (userName.includes(query)) {
                    user.style.display = ''; // Show the user
                    visibleUsersCount++;
                } else {
                    user.style.display = 'none'; // Hide the user
                }
            });
            if (visibleUsersCount === 0) {
                if (taskAssignPanel) taskAssignPanel.style.display = 'none';
                if (noUsersMessage) noUsersMessage.style.display = 'block';
            } else {
                if (taskAssignPanel) taskAssignPanel.style.display = 'block';
                if (noUsersMessage) noUsersMessage.style.display = 'none';
            }
        });
        // When the user starts typing, show the "x" button
        userSearch.addEventListener('input', function () {
            if (userSearch.value.length > 0) {
                clearSearch.style.display = 'block';  // Show the "x" button
            } else {
                clearSearch.style.display = 'none';   // Hide the "x" button if the field is empty
            }
        });

        // When the user clicks the "x" button, clear the search field
        clearSearch.addEventListener('click', function () {
            userSearch.value = '';  // Clear the input value
            clearSearch.style.display = 'none';  // Hide the "x" button

            // Show all users again
            const users = taskAssignPanel.querySelectorAll('li');
            users.forEach(user => {
                user.style.display = ''; // Show all users
            });

            // Reset the visibility of the taskAssignPanel and noUsersMessage
            if (taskAssignPanel) taskAssignPanel.style.display = 'block';
            if (noUsersMessage) noUsersMessage.style.display = 'none';
        });
    }

}
