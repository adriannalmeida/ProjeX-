document.querySelectorAll('.remove-task-table').forEach(button => {
    // Add an event listener to each "remove task table" button
    button.addEventListener('submit', function (e) {
        e.preventDefault()
        e.stopPropagation()
        // Send an AJAX DELETE request to the specified action URL
        sendAjaxRequest('delete', `${button.action}`, {}, function () {
            location.reload()
        });
    });
});