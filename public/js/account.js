document.addEventListener('DOMContentLoaded', function () {
    // Initialize Pusher with the application key and cluster
    const pusherAppKey = '17c409875705a77f352c';
    const pusherCluster = 'eu';
    const pusher = new Pusher(pusherAppKey, {
        cluster: pusherCluster,
        encrypted: true,
    });

    // Subscribe to the 'projeX' channel
    const channel = pusher.subscribe(`projeX`);

    // Listen for the 'invited-to-project' event
    channel.bind('invited-to-project', function (data) {
        // Check if the invitation is meant for the currently logged-in user
        if (data.account_id === window.userId) {
            // Update the invitations list dynamically
            const invitationItem = `
                <li class="container-list-item invitation-item">
                    <p class="invitation-project">${data.project_name}</p>
                    <div class="accept-actions">
                        <form action="/invitation/${data.invitation_id}/accept" method="POST" class="accept-form">
                      <input type="hidden" name="_method" value="PATCH"> 
                      <input type="hidden" name="_token" value="${window.Laravel.csrfToken}">
                      <button type="submit" class="accept-btn" title="Accept"><i class="fas fa-check"></i></button>
                  </form>
                  <form action="/invitation/${data.invitation_id}/decline" method="POST" class="decline-form">
                    <input type="hidden" name="_method" value="DELETE"> 
                    <input type="hidden" name="_token" value="${window.Laravel.csrfToken}">
                    <button type="submit" class="decline-btn" title="Decline"><i class="fas fa-times"></i></button>
                  </form>
                    </div>
                </li>
            `;

            // Get the invitations list and insert the new invitation at the top
            const invitationsList = document.querySelector('.invitations-list');

            if (invitationsList) {
                invitationsList.insertAdjacentHTML('afterbegin', invitationItem);
            } else {
                // If it's the first invitation, create the invitations list
                const invitationsSection = document.querySelector('.invitations-section');
                const newInvitationsList = document.createElement('ul');
                newInvitationsList.classList.add('invitations-list');
                newInvitationsList.insertAdjacentHTML('afterbegin', invitationItem);

                // Add the new list to the DOM
                invitationsSection.appendChild(newInvitationsList);
            }

            // Remove the "No invitations" message if it's shown
            const noInvitationsMessage = document.querySelector('.no-invitations');
            if (noInvitationsMessage) {
                noInvitationsMessage.remove();
            }
        }
    });

    // Listen for the 'project-notification' event
    channel.bind('project-notification', function (data) {
        // Check if the notification is for the authenticated user
        if (data.account_id === window.userId) {
            const notificationDate = new Date(data.notification_date);
            const formattedDate = new Intl.DateTimeFormat('en-US', {
                month: 'long', // Full month name
                day: 'numeric', // Day of the month
                year: 'numeric' // Full year
            }).format(notificationDate);
            // Update the notifications list dynamically
            const notificationItem = `
                <li class="container-list-item notification-item">
                    <p class="notification-description">${data.notification_description}</p>
                    <p class="notification-date">${formattedDate}</p>
                    <div class="accept-actions">
                        <form action="/notification/${data.notification_id}/check" method="POST" class="accept-form">
                          <input type="hidden" name="_method" value="PATCH"> 
                          <input type="hidden" name="_token" value="${window.Laravel.csrfToken}">
                          <button type="submit" class="accept-btn" title="Accept"><i class="fas fa-check"></i></button>
                        </form>
                    </div>
                </li>
            `;

            // Get the notifications list and insert the new notification at the top
            const notificationsList = document.querySelector('.notifications-list');

            if (notificationsList) {
                notificationsList.insertAdjacentHTML('afterbegin', notificationItem);
            } else {
                // If it's the first notification, create the notification list
                const notificationsSection = document.querySelector('.notifications-section');
                const newnotificationsList = document.createElement('ul');
                newnotificationsList.classList.add('notifications-list');
                newnotificationsList.insertAdjacentHTML('afterbegin', notificationItem);

                // Add the new list to the DOM
                notificationsSection.appendChild(newnotificationsList);
            }

            // Remove the "No notifications" message if it's shown
            const noNotificationsMessage = document.querySelector('.no-notifications');
            if (noNotificationsMessage) {
                noNotificationsMessage.remove();
            }
        }
    });
});
