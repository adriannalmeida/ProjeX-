document.addEventListener("DOMContentLoaded", () => {
    const fileInput = document.getElementById('file-input');
    const fileNameDisplay = document.getElementById('file-name');
    const profilePhoto = document.querySelector('.profile-photo img');

    // Update file name and preview image when a file is selected
    fileInput.addEventListener('change', () => {
        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            fileNameDisplay.textContent = `Selected file: ${file.name}`;
            // Load and display the image preview
            const reader = new FileReader();
            reader.onload = (event) => {
                profilePhoto.src = event.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            // Clear the file name if no file is selected
            fileNameDisplay.textContent = '';
        }
    });
});
