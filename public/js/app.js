// Utility function to encode data for x-www-form-urlencoded requests
function encodeForAjax(data) {
  if (data == null) return null;
  return Object.keys(data).map(function(k){
    return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
  }).join('&');
}

// Function to send AJAX requests
function sendAjaxRequest(method, url, data, handler, json = false) {
  let request = new XMLHttpRequest();
  request.open(method, url, true);
  request.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);
  if (!json) {
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    request.send(encodeForAjax(data));
  } else {
    request.setRequestHeader('Content-Type', 'application/json');
    request.setRequestHeader('Accept', 'application/json');
    request.send(JSON.stringify(data));
  }

  request.addEventListener('load', handler);
}

// Format a date string to "dd MMM yyyy"
const formatDate = (date) => {
  if (!date) return 'N/A';
  // Convert the string to a Date object if it is not already
  const dateObj = new Date(date);

  if (isNaN(dateObj)) return 'N/A'; // If date is invalid, return 'N/A'

  const options = { day: '2-digit', month: 'short', year: 'numeric' };
  return new Intl.DateTimeFormat('en-GB', options).format(dateObj);
};

// Function to create toast notifications
function createToastNotification(success, msg) {
  let toast = document.getElementById('notification');
  if (toast) toast.remove();

  let type = document.createElement('div');
  if (success) {
    type.classList.add('success');
    type.innerHTML += `<i class="material-symbols-outlined green"> check_circle </i>`;
  }
  else {
    type.classList.add('error');
    type.innerHTML += `<i class="material-symbols-outlined red"> error </i>`;
  }
  type.innerHTML += `<p>${msg}</p>
                     <div class="notification-progress"></div>`;

  toast = document.createElement('div');
  toast.id = 'notification';

  toast.append(type);

  const mainHeader = document.querySelector('body > header');
  mainHeader.insertAdjacentElement('afterend', toast);
}

document.addEventListener('DOMContentLoaded', function () {
  const pusherAppKey = '17c409875705a77f352c';
  const pusherCluster = 'eu';
  // Confirmation button system for user actions
  const actions = document.querySelectorAll('.confirm-action ')

  let interval
  const progressBar = document.createElement("div")
  progressBar.classList.add("notification-progress")
  actions.forEach((action) => {
    action.addEventListener("click", async (elem) => {
      if (action.classList.contains("wait")) {
        elem.preventDefault()
        actions.forEach((others) => {
          if (others !== action) {
            changeButtonText(others)
          }
        })
      }
      else if (action.classList.contains("confirm-action")) {
        elem.preventDefault()
        actions.forEach((others) => {
          if (others !== action) {
            changeButtonText(others)
          }
        })
        action.innerHTML += "Are you sure?"
        action.classList.add("wait")
        action.parentElement.appendChild(progressBar)
        const buttonWidth = action.offsetWidth - 5;
        progressBar.style.width = `${buttonWidth}px`;
        await new Promise(r => setTimeout(r, 300))
        action.classList.remove("wait")
        action.classList.remove("confirm-action")
        interval = setInterval(function() { changeButtonText(action)  }, 2700)
      }
    })})

  function changeButtonText(currAction)
  {
    if (!currAction.classList.contains("confirm-action")){
      currAction.innerHTML = currAction.innerHTML.replace("Are you sure?", "")
      currAction.classList.add("confirm-action")
      clearInterval(interval)
      currAction.parentElement.removeChild(progressBar)
    }
  }

  // dropdown-invitations.js
  // Real-time updates using Pusher
  const pusher = new Pusher(pusherAppKey, {
    cluster: pusherCluster,
    encrypted: true,
  });

  function addAnimation(element, animationClass) {
    element.classList.add(animationClass);
    element.addEventListener('animationend', () => {
      element.classList.remove(animationClass);
    }, { once: true });
  }

  const channel = pusher.subscribe(`projeX`);

  // Handle real-time project invitations
  channel.bind('invited-to-project', function (data) {
    // Check if the invitation is for the logged-in user
    if (data.account_id === window.userId) {
      // Create new invitation item
      const invitationItem = `
          <div class="container-list-item invitation-item">
              <p>${data.project_name}</p>
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
          </div>
      `;

      // Update the dropdown
      const invitationsDropdown = document.querySelector('.invitations-dropdown');

      // Remove the "No new Invitations." message if it exists
      const noInvitationsMessage = invitationsDropdown.querySelector('p');
      if (noInvitationsMessage && noInvitationsMessage.textContent === "No new Invitations.") {
        noInvitationsMessage.remove();
      }
      // If there are already 3 invitations, remove the last one
      const existingInvitations = invitationsDropdown.querySelectorAll('.invitation-item');
      if (existingInvitations.length >= 3) {
        existingInvitations[existingInvitations.length - 1].remove();
      }

      // Add the new invitation to the top of the dropdown
      invitationsDropdown.insertAdjacentHTML('afterbegin', invitationItem);

      const invitationCountBadge = document.getElementById('invitation-badge');
      if (invitationCountBadge) {
        let currentCount = parseInt(invitationCountBadge.textContent) || 0;
        invitationCountBadge.textContent = currentCount + 1;
      } else {
        // If no badge exists, create itdropdown-toggle-notification
        const badge = document.createElement('span');
        badge.classList.add('badge');
        badge.id = 'invitation-badge';
        badge.textContent = '1';
        document.querySelector('#dropdown-toggle-invite').appendChild(badge);
      }
      const invitationIcon = document.querySelector('#dropdown-toggle-invite i');
      addAnimation(invitationIcon, 'icon-animation');
    }
  });

  // Handle real-time notifications
  // Bind 'project-notification' event to the channel
  channel.bind('project-notification', function (data) {
    // Check if the notification is for the logged-in user
    if (data.account_id === window.userId) {
      // Create a new notification item
      const notificationItem = `
            <div class="container-list-item notification-item">
                <p>${data.notification_description}</p>
                <div class="accept-actions">
                    <form action="/notification/${data.notification_id}/check" method="POST" class="decline-form">
                        <input type="hidden" name="_method" value="PATCH"> 
                        <input type="hidden" name="_token" value="${window.Laravel.csrfToken}">
                        <button type="submit" class="accept-btn" title="Mark as Read"><i class="fas fa-check"></i></button>
                    </form>
                </div>
            </div>
        `;

      // Update the notifications dropdown
      const notificationsDropdown = document.querySelector('.notifications-dropdown');

      // Remove the "No new Notifications." message if it exists
      const noNotificationsMessage = notificationsDropdown.querySelector('p');
      if (noNotificationsMessage && noNotificationsMessage.textContent === "No new Notifications.") {
        noNotificationsMessage.remove();
      }

      // If there are already 3 notifications, remove the last one
      const existingNotifications = notificationsDropdown.querySelectorAll('.notification-item');
      if (existingNotifications.length >= 3) {
        existingNotifications[existingNotifications.length - 1].remove();
      }

      // Add the new notification to the top of the dropdown
      notificationsDropdown.insertAdjacentHTML('afterbegin', notificationItem);

      // Update the notifications badge
      const notificationBadge = document.querySelector('#notification-badge');
      if (notificationBadge) {
        let currentCount = parseInt(notificationBadge.textContent) || 0;
        notificationBadge.textContent = currentCount + 1;
      } else {
        // If no badge exists, create it
        const badge = document.createElement('span');
        badge.classList.add('badge');
        badge.id = 'notification-badge';
        badge.textContent = '1';
        document.querySelector('#dropdown-toggle-notification').appendChild(badge);
      }
      const notificationIcon = document.querySelector('#dropdown-toggle-notification i');
      addAnimation(notificationIcon, 'icon-animation');
    }
  });

  /// Favicon update based on dark mode
  const favicon = document.querySelector('link[rel="icon"]');
// Media query to detect dark mode preference
  const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
// Function to update the favicon
  const updateFavicon = () => {
    const timestamp = new Date().getTime(); // Generate a unique timestamp to ensure the browser treats each icon update as a new request
    if (darkModeMediaQuery.matches) {
      favicon.href = `/assets/faviconDark.ico?cache=${timestamp}`;
    } else {
      favicon.href = `/assets/faviconWhite.ico?cache=${timestamp}`;
    }
  };

// Initial check
  updateFavicon();

// Listen for changes in the color scheme
  darkModeMediaQuery.addEventListener('change', updateFavicon);


});
  