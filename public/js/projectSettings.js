document.addEventListener('DOMContentLoaded', function() {
    const selects = document.querySelectorAll('.component-select');

    // Function to dynamically update dropdown options
    function updateOptions() {
        // Get all selected values
        const selectedValues = Array.from(selects)
            .map(select => select.value)
            .filter(value => value !== 'none'); // Exclude "none"

        selects.forEach(select => {
            const options = select.querySelectorAll('option');
            options.forEach(option => {
                // Disable if the component is selected elsewhere
                option.disabled = option.value !== 'none' && option.value !== select.value && selectedValues.includes(option.value);
            });
        });
    }

    // Attach change event to all dropdowns to trigger the update function
    selects.forEach(select => {
        select.addEventListener('change', updateOptions);
    });

    // Initial call to ensure dropdown options are set correctly on load
    updateOptions();

    // Permissions editing elements
    const editPermissionIcon = document.getElementById('edit-permission-icon');
    const permissionsForm = document.getElementById('permissions-form');
    const cancelPermissionsEdit = document.getElementById('cancel-permissions-edit');

    // Show permissions form when the edit icon is clicked
    if (editPermissionIcon) {
        editPermissionIcon.addEventListener('click', function(event) {
            event.preventDefault();
            permissionsForm.style.display = 'block';
        });
    }

    // Hide permissions form when the cancel button is clicked
    if (cancelPermissionsEdit) {
        cancelPermissionsEdit.addEventListener('click', function() {
            permissionsForm.style.display = 'none';
        });
    }
});