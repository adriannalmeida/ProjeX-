// Function to reset a form
function resetForm(form) {
    form.reset(); // Clear all inputs
    form.querySelectorAll('.error').forEach(error => error.remove()); // Remove error messages
}
// Function to show validation errors
function showValidationErrors(form, errors) {
    form.querySelectorAll('.error').forEach(error => error.remove());
    for (let field in errors) {
        const input = form.querySelector(`[name="${field}"]`);
        if (input) {
            const error = document.createElement('span');
            error.className = 'error';
            error.textContent = errors[field][0];
            input.parentNode.appendChild(error);
        }
    }
    console.log(errors);
}

// Function to make elements focusable with the keyboard, for better accessibility
function setButtonRoles() {
    document.querySelectorAll('.focusable').forEach(function(button) {
        button.setAttribute('tabIndex', '0');
        button.addEventListener('keyup', simulateClick);
    });
}
// Function to simulate a click event when pressing enter or space on a focused element
function simulateClick(e) {
    if(e.keyCode === 13 || e.keyCode === 32) {
        this.click();
    }
}

setButtonRoles();
