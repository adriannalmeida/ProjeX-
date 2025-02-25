document.addEventListener('DOMContentLoaded', function () {

    const projectContainer = document.getElementById('project-container');
    const projectId = projectContainer.getAttribute('data-project-id');
    const noUsersMessage = document.querySelector('.no-invites');
    initializePusher(projectId); // Initialize real-time updates for the project

    // Handle form submission for inviting a user
    document.getElementById('invite-user-form').addEventListener('submit', function (event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);

        const xhr = new XMLHttpRequest();
        xhr.open('POST', form.action, true); // Set up an AJAX POST request
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

        xhr.onload = function () {
            const response = JSON.parse(xhr.responseText);

            if (xhr.status === 200) {
                resetForm(form);
                createToastNotification(true, response.message);
                if(noUsersMessage){
                    noUsersMessage.remove();
                }
            } else if (xhr.status === 422) {
                if (response.errors) {
                    showValidationErrors(form, response.errors);
                }
            } else {
                console.error('An unexpected error occurred:', xhr.responseText);
                createToastNotification(false, 'An unexpected error occurred. Please try again.');
            }
        };

        xhr.onerror = function () {
            console.error('Network error occurred.');
            createToastNotification(false, 'A network error occurred. Please try again.');
        };

        xhr.send(formData);

       
    });
});

// Initialize Pusher for real-time updates
function initializePusher(projectId){
    const pusherAppKey = '17c409875705a77f352c';
    const pusherCluster = 'eu';
    const pusher = new Pusher(pusherAppKey, {
        cluster: pusherCluster,
        encrypted: true,
    });
    const channel = pusher.subscribe('projeX');

    // Listen for 'invited-to-project' events
    channel.bind('invited-to-project', function (data) {
        // Ensure the event belongs to the correct project
        if (projectId == data.project_id) {
            const name = data.account_name;
            const email = data.account_email;
            // Find the table body
            const tableBody = document.querySelector('.invited-accounts-rows');
            const newRow = document.createElement('tr');
            // Populate the row with data
            newRow.innerHTML = `
            <td>${name}</td>
            <td>${email}</td>
        `;
            // Append the new row to the table
            tableBody.appendChild(newRow);
        }
    });
}