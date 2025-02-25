document.addEventListener('DOMContentLoaded', function () {
     // Select relevant elements and constants
    const forumMessagesContainer = document.getElementById('forumMessages');
    const currentUrl = window.location.href;
    const match = currentUrl.match(/\/project\/(\d+)\//);
    const projectId = match[1];
    const pusherAppKey = '17c409875705a77f352c';
    const pusherCluster = 'eu';
    const noMessagesDiv = document.querySelector('.no-messages-forum');

    // Initialize Pusher for real-time messaging
    const pusher = new Pusher(pusherAppKey, {
        cluster: pusherCluster,
        encrypted: true,
    });

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

    // Add a new message to the forum dynamically
    function addMessageToForum(message) {
        let messageActionsHtml = '';
        let editFormHtml = '';

        // Check if the author of the message is the current user
        if (message.account_id == accountId) {
            messageActionsHtml = `
            <div class="message-actions">
                <a href="#" class="icon-button editMessageButton" id="edit-message" data-message-id="${message.message_id}">
                    <i class="fa fa-pencil"></i>
                </a>
                <form action="/forum/${message.message_id}" method="POST" class="confirmation">
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="icon-button confirm-action" id="deleteMessageButton">
                        <i class="fa fa-trash"></i>
                    </button>
                </form>
            </div>
        `;

            editFormHtml = `
            <form class="edit-message-form" style="display: none;" action="/forum/${message.message_id}/edit" method="POST">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                <textarea class="message-input" name="message" rows="1" required>${message.message_content}</textarea>
                <button type="submit" class="saveMessageButton">Save</button>
                <button type="button" class="btn-cancel cancelEditMessage">Cancel</button>
            </form>
        `;
        }

        const newMessageHtml = `
        <section class="message-item">
            <img class="round-photo" src="${message.account_image}" alt="Profile Picture">
            <div class="message-header">
                <div class="account-info">
                    <p class="account-name">${message.account_name}</p>
                </div>
                <p class="message-date">${message.message_date}</p>
            </div>
            <div class="message-content">
                <p>${message.message_content}</p>
            </div>
            ${messageActionsHtml}
            ${editFormHtml}
        </section>
    `;
    if (noMessagesDiv) {
        noMessagesDiv.style.display = 'none';
    }
        // Add the message HTML to the forum
        forumMessagesContainer.innerHTML += newMessageHtml;
    }


    // Scroll to the bottom on load
    forumMessagesContainer.scrollTop = forumMessagesContainer.scrollHeight;

    // Handle dynamic updates by scrolling to the bottom when new messages are added
    const observer = new MutationObserver(() => {
        forumMessagesContainer.scrollTop = forumMessagesContainer.scrollHeight;
    });
    observer.observe(forumMessagesContainer, { childList: true });

    // Handle opening the edit message form
    forumMessagesContainer.addEventListener('click', function (event) {
        if (event.target.closest('.editMessageButton')) {
            event.preventDefault();

            const button = event.target.closest('.editMessageButton');
            const messageItem = button.closest('.message-item');
            const editForm = messageItem.querySelector('.edit-message-form');
            const messageContent = messageItem.querySelector('.message-content');

            if (editForm && messageContent) {
                messageContent.style.display = 'none';
                editForm.style.display = 'block';
            }
        }
    });

    // Handle canceling the edit message form
    forumMessagesContainer.addEventListener('click', function (event) {
        if (event.target.closest('.cancelEditMessage')) {
            event.preventDefault();

            const button = event.target.closest('.cancelEditMessage');
            const messageItem = button.closest('.message-item');
            const editForm = messageItem.querySelector('.edit-message-form');
            const messageContent = messageItem.querySelector('.message-content');

            if (editForm && messageContent) {
                messageContent.style.display = 'block';
                editForm.style.display = 'none';
            }
        }
    });

    // Handle submitting the edit message form
    forumMessagesContainer.addEventListener('submit', function (event) {
        if (event.target.closest('.edit-message-form')) {
            event.preventDefault();

            const formE = event.target;
            const formData = new FormData(formE);

            sendAjaxRequest(
                'POST',
                formE.action,
                Object.fromEntries(formData.entries()),
                function () {
                    const response = JSON.parse(this.response);
                    if (this.status === 200) {
                        const messageItem = formE.closest('.message-item');
                        const messageContent = messageItem.querySelector('.message-content p');
                        messageContent.innerText = formData.get('message').trim();
                        formE.style.display = 'none';
                        messageContent.parentElement.style.display = 'block';
                        createToastNotification(true, response.success);
                    } else if (this.status === 422) {
                        if (response.errors) {
                            showValidationErrors(formE, response.errors);
                        }
                    } else {
                        console.error('An unexpected error occurred:', response.responseText);
                        createToastNotification(false, 'An unexpected error occurred. Please try again.');
                    }
                },
                true
            );
        }
    });
});
