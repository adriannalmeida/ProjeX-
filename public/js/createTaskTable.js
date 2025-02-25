document.addEventListener('DOMContentLoaded', function () {
    // Elements for the Create Task Table modal and form
    const createTaskTableModalLink = document.getElementsByClassName('create-task-table');
    const createTaskTableModal = document.getElementById('createTaskTableModal');
    const closeCreateTaskTableModalButton = document.getElementById('closeCreateTaskTableModal');
    const createTaskTableForm = document.getElementById('createTaskTableForm');

    // Open the modal when a link is clicked
    if (createTaskTableModalLink.length > 0 && createTaskTableModal) {
        for (let i = 0; i < createTaskTableModalLink.length; i++) {
            createTaskTableModalLink[i].addEventListener('click', function (e) {
                e.preventDefault();
                createTaskTableModal.classList.remove('fade-out-modal');
                createTaskTableModal.classList.add('fade-in-modal');
                createTaskTableModal.firstElementChild.classList.remove('close-modal-anim');
                createTaskTableModal.firstElementChild.classList.add('open-modal-anim');
                createTaskTableModal.style.display = 'flex';
            });
        }
    }

    // Close the modal when the close button is clicked
    if (closeCreateTaskTableModalButton && createTaskTableModal) {
        closeCreateTaskTableModalButton.addEventListener('click', () => {
            createTaskTableModal.classList.remove('fade-in-modal');
            createTaskTableModal.classList.add('fade-out-modal');
            createTaskTableModal.firstElementChild.classList.remove('open-modal-anim');
            createTaskTableModal.firstElementChild.classList.add('close-modal-anim');
        });
    
        // Close the modal when the 'Escape' key is pressed
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { // Check if the 'Escape' key is pressed
                createTaskTableModal.classList.remove('fade-in-modal');
                createTaskTableModal.classList.add('fade-out-modal');
                createTaskTableModal.firstElementChild.classList.remove('open-modal-anim');
                createTaskTableModal.firstElementChild.classList.add('close-modal-anim');
            }
        });
    }

    // Handle form submission for creating a task table
    if (createTaskTableForm) {
        createTaskTableForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const actionUrl = this.action;

            // Send the form data via Fetch API
            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (data.errors) {
                        showValidationErrors(createTaskTableForm, data.errors);
                    } else {
                        location.reload();
                        resetForm(createTaskTableForm);
                    }
                })
                .catch(err => console.error('Error:', err));
        });
    }
});