document.addEventListener('DOMContentLoaded', function () {
    // Elements for privacy settings editing
    const editPrivacyIcon = document.getElementById('edit-privacy-icon');
    const privacyForm = document.querySelector('#privacy-settings form');
    const cancelButton = document.getElementById('cancel-edit-privacy');
    privacyForm.style.display = 'none';

     // Show privacy form on edit icon click
    editPrivacyIcon.addEventListener('click', function (e) {
        e.preventDefault();
        privacyForm.style.display = 'block';
    });

    // Hide privacy form on cancel button click
    cancelButton.addEventListener('click', function () {
        privacyForm.style.display = 'none';
    });

    // Elements for editing the project description
    const descriptionDisplay = document.getElementById('description-display');
    const descriptionForm = document.getElementById('description-form');
    const descriptionInput = document.getElementById('description-input');
    const editDescriptionIcon = document.getElementById('edit-description-icon');
    const cancelEditDescriptionButton = document.getElementById('cancel-edit-description');

    // Show description form and hide the current display
    editDescriptionIcon.addEventListener('click', function (e) {
        e.preventDefault();
        descriptionDisplay.style.display = 'none';
        descriptionForm.style.display = 'block';
    });

    // Restore previous description and hide form on cancel
    cancelEditDescriptionButton.addEventListener('click', function () {
        descriptionForm.style.display = 'none';
        descriptionDisplay.style.display = 'block';
        descriptionInput.value = descriptionDisplay.querySelector('p').innerText.trim();
    });

    // Elements for changing the project coordinator
    const editIcon = document.getElementById('editCoordinatorIcon');
    const changeSection = document.getElementById('change-coordinator-section');

    // Toggle visibility of the coordinator change section
    if (editIcon) {
        editIcon.addEventListener('click', function (event) {
            event.preventDefault();
            changeSection.style.display = changeSection.style.display === 'none' ? 'block' : 'none';
        });
    }

    // Elements for coordinator selection and form action update
    const newCoordinatorSelect = document.getElementById('new-coordinator');
    const changeCoordinatorForm = document.getElementById('change-coordinator-form');
    const projectContainer = document.getElementById('project-container');
    const projectId = projectContainer.getAttribute('data-project-id');

    // Update the form action dynamically based on selected coordinator
    const updateFormAction = () => {
        const selectedCoordinatorId = newCoordinatorSelect.value;

        changeCoordinatorForm.action = `/project/${projectId}/changeCoordinator/${selectedCoordinatorId}`;
    };

    updateFormAction();

    newCoordinatorSelect.addEventListener('change', updateFormAction);

    // Handle submission of the project description form
    document.getElementById('description-form').addEventListener('submit', function (event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);

        // Send an AJAX request to update the description
        sendAjaxRequest(
            'POST',
            form.action,
            Object.fromEntries(formData.entries()),
            function () { // Handler function
                const response = JSON.parse(this.responseText);

                if (this.status === 200) {
                    // Update the description display with the new value
                    const descriptionDisplay = document.querySelector('#description-display p');
                    descriptionDisplay.innerText = formData.get('description').trim();

                    // Hide the form and show the updated description
                    form.style.display = 'none';
                    document.getElementById('description-display').style.display = 'block';

                    // Show success notification
                    createToastNotification(true, response.message);
                } else if (this.status === 422) {
                    // Show validation errors if any
                    if (response.errors) {
                        showValidationErrors(form, response.errors);
                    }
                } else {
                    console.error('An unexpected error occurred:', this.responseText);
                    createToastNotification(false, 'An unexpected error occurred. Please try again.');
                }
            },
            true // Use JSON format
        );
    });

});
