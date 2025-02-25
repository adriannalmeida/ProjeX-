document.addEventListener('DOMContentLoaded', function () {
    // Modal elements
    const createProjectModal = document.getElementById('createProjectModal');
    const openCreateProjectModalButton = document.getElementById('createNewProject');
    const closeCreateProjectModalButton = document.getElementById('closeCreateProjectModal');
    const createProjectForm = document.getElementById('createProjectForm');

    // Open the create project modal
    openCreateProjectModalButton.addEventListener('click', () => {
        createProjectModal.classList.remove('fade-out-modal');
        createProjectModal.classList.add('fade-in-modal');
        createProjectModal.firstElementChild.classList.remove('close-modal-anim');
        createProjectModal.firstElementChild.classList.add('open-modal-anim');
        createProjectModal.style.display = 'flex';
    });

    // Close the create project modal
    closeCreateProjectModalButton.addEventListener('click', () => {
        createProjectModal.classList.remove('fade-in-modal');  // Remove fade-in effect
        createProjectModal.classList.add('fade-out-modal'); // Add fade-out effect
        createProjectModal.firstElementChild.classList.remove('open-modal-anim'); // Remove open animation
        createProjectModal.firstElementChild.classList.add('close-modal-anim'); // Add close animation
    });

    // Close the modal when the 'Escape' key is pressed
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { // Check if the 'Escape' key is pressed
            createProjectModal.classList.remove('fade-in-modal'); // Remove fade-in effect
            createProjectModal.classList.add('fade-out-modal'); // Add fade-in effect
            createProjectModal.firstElementChild.classList.remove('open-modal-anim'); // Remove open animation
            createProjectModal.firstElementChild.classList.add('close-modal-anim'); // Add open animation
        }
    });

    // Handle form submission for creating a new project
    createProjectForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const actionUrl = this.action;

        // Send form data using Fetch API
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
                    showValidationErrors(createProjectForm, data.errors);
                } else {
                    location.reload();
                    resetForm(createProjectForm);
                }
            })
            .catch(err => console.error('Error:', err));
    });

    // Navigate to project page when clicking on a project row
    const projects = document.querySelectorAll('.projects-table > tbody > tr');
    projects.forEach(project => {
        project.addEventListener('click', function () {
            window.location.href = project.getAttribute('data-href');
        });
    });
});