document.addEventListener('DOMContentLoaded', function () {
    const inputField = document.getElementById('username-email');
    const suggestionsBox = document.getElementById('suggestions');

    if (!inputField || !suggestionsBox) return;
    
    // Event listener to handle user input in the search field
    inputField.addEventListener('input', function () {
        const query = inputField.value.trim().toLowerCase(); 
        if (query.length > 0) {
            // Send AJAX request to fetch matching users
            sendAjaxRequest('GET', `/search-users?query=${query}`, {}, function () {
                const data = JSON.parse(this.responseText); 

                suggestionsBox.innerHTML = '';

                if (data.length > 0) {
                    suggestionsBox.style.display = 'block';
                    data.forEach(user => {
                        const userNameLower = user.name.toLowerCase(); 
                        const userEmailLower = user.email.toLowerCase(); 

                         // Skip if the user is "unknown" or does not match the query
                        if ((userNameLower === 'unknown' || userEmailLower === 'unknown') || 
                            !(userNameLower.includes(query) || userEmailLower.includes(query))) {
                            return;
                        }

                        if (userNameLower.includes(query) || userEmailLower.includes(query)) {
                            // Create a suggestion item if the user matches the query
                            const suggestion = document.createElement('div');
                            suggestion.classList.add('suggestion-item');
                            suggestion.textContent = `${user.name} (${user.email})`;

                            // Fill input with the selected user's email on click
                            suggestion.addEventListener('click', () => {
                                inputField.value = user.email; 
                                suggestionsBox.innerHTML = ''; 
                                suggestionsBox.style.display = 'none'; 
                            });

                            suggestionsBox.appendChild(suggestion);
                        }
                    });
                    

                } else {
                    // Display "No users found" if there are no results
                    suggestionsBox.innerHTML = '<div class="no-results">No users found</div>';
                    suggestionsBox.style.display = 'block';
                }
            }, true);
        } else {
            // Hide and clear suggestions if the input is empty
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
        }
    });

    // Hide the suggestions box when clicking outside of it
    document.addEventListener('click', function (e) {
        if (!suggestionsBox.contains(e.target) && e.target !== inputField) {
            suggestionsBox.style.display = 'none';
            suggestionsBox.innerHTML = '';
        }
    });
});
