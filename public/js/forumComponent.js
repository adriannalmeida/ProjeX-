document.addEventListener('DOMContentLoaded', function () {
    // Select the container for forum messages
    const forumMessagesContainer = document.getElementById('forumMessagesContainer');

    // Extract the project ID from the URL
    const currentUrl = window.location.href;
    const match = currentUrl.match(/\/project\/(\d+)\/?/);
    const projectId = match[1];

    // Pusher configuration
    const pusherAppKey = '17c409875705a77f352c';
    const pusherCluster = 'eu';

    // Initialize Pusher for real-time messaging
    const pusher = new Pusher(pusherAppKey, {
        cluster: pusherCluster,
        encrypted: true,
    });

    // Subscribe to the Pusher channel for real-time events
    const channel = pusher.subscribe(`projeX`);

    // Listen for the posted-message event
    channel.bind('posted-message', function (data) {
        if (data.project_id == projectId) {
            // Add the new message to the DOM
            addMessageToForum(data);
        }
    });

    // Form submission for posting a new message
    const form = document.getElementById('forumMessageForm');
    let accountId = null;
    if (form) {
        accountId = form.getAttribute('data-account-id') || null;
        form.addEventListener('submit', event => {
            event.preventDefault();
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            sendAjaxRequest(
                'POST',
                form.action,
                data,
                function () {
                    try {
                        const response = JSON.parse(this.responseText);

                        if (this.status >= 200 && this.status < 300) {
                            if (response.errors) {
                                showValidationErrors(form, response.errors);
                                form.reset();
                            } else {
                                createToastNotification(true, response.success);
                                form.reset();
                            }
                        } else if (response.errors) {
                            showValidationErrors(form, response.errors);
                        } else if (response.error) {
                            createToastNotification(false, response.error);
                        }
                    } catch (e) {
                        console.error('Error parsing server response.');
                    }
                },
                true // JSON format is used
            );
        });
    }

    // Function to add a new message to the forum dynamically
    function addMessageToForum(message) {
        const seeAll = '<a href="http://localhost:8000/project/0/forum" class="view-all-link">See all</a>';
        const newMessageHtml = `
        <div class="forum-message">
            <p><strong>${message.account_name}:</strong> ${message.message_content}</p>
        </div>
        `;

        // Remove "no messages" placeholder if present
        if (forumMessagesContainer.firstElementChild.classList.contains('no-messages')) {
            forumMessagesContainer.innerHTML = '';
        }

        // Add the message HTML to the forum
        forumMessagesContainer.innerHTML = newMessageHtml + forumMessagesContainer.innerHTML;

        // Add "See all" link if the message count reaches the limit
        if (forumMessagesContainer.children.length === 30) {
            forumMessagesContainer.innerHTML += seeAll;
        }

        // Maintain a maximum of 30 messages in the container
        else if (forumMessagesContainer.children.length > 31) {
            forumMessagesContainer.firstChild.remove();
            forumMessagesContainer.removeChild(forumMessagesContainer.children[30]);
        }
    }

    // Automatically scroll to the bottom of the forum on load
    forumMessagesContainer.scrollTop = forumMessagesContainer.scrollHeight;

    // Handle dynamic updates by scrolling to the bottom when new messages are added
    const observer = new MutationObserver(() => {
        forumMessagesContainer.scrollTop = forumMessagesContainer.scrollHeight;
    });
    observer.observe(forumMessagesContainer, { childList: true });
});
