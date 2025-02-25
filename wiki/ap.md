# PA: Product and Presentation

For project managers and teams seeking an intuitive and adaptable solution for task management and collaboration, ProjeX is a cutting-edge project management tool that streamlines workflows, enhances communication, and fosters seamless team coordination. Unlike conventional project management tools, ProjeX prioritizes user experience by offering customizable layouts and features tailored to individual workflows, empowering users to boost both personal productivity and team efficiency.

## A9: Product

ProjeX is a web-based project management platform designed to streamline task organization and team collaboration for groups of any size. Developed to address specific workflow challenges, it offers role-based access control, with administrators managing the platform, project coordinators overseeing tasks and teams, and members collaborating on projects. Key features include task management, team communication via project chat, advanced search tools, and an adaptive design for seamless use across devices. ProjeX prioritizes flexibility, user-friendliness, and enhanced productivity, making it a versatile solution for effective project management.

### 1. Installation

#### Logging In to GitLab's Container Registry

To access the GitLab Container Registry, log in using FEUP credentials while connected to the FEUP VPN or network:

```bash
docker login gitlab.up.pt:5050
```

- **Username**: `upXXXXX@up.pt`
- **Password**: Your GitLab password

---

#### Command to Start the Docker Image from the Group's Container Registry:

```bash
docker run -d --name lbaw24044 -p 8001:80 gitlab.up.pt:5050/lbaw/lbaw2425/lbaw24044 
```

You can access the project at:\
[http://localhost:8001](http://localhost:8001)

#### Command to Stop and Remove the Container:

```bash
docker stop lbaw24044
docker rm lbaw24044
```

#### Link to Source Code:

[Source Code Repository - PA Tag](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/tree/PA?ref_type=tags)
  

### 2. Usage

To create an account you need to provide a username and password with at least 8 characters. Country, City, and worfield are optional parameters. Alternatively you can use the accounts we've already set up (see below).

#### 2.1. Administration Credentials

To access the admin page, we need to login using the following credentials:   

| Username | Password |
| -------- | -------- |
| admin_alice    | hashed_password_1 |

#### 2.2. User Credentials

| Type          | Username  | Password |
| ------------- | --------- | -------- |
| Project Coordinator | admin_alice   | hashed_password_1 |
| Project Member  |  pedro_oliveira    | hashed_password_5 |

#### 2.3. MailTrap Credentials

| email | Password |
| ----- | -------- |
| ProjeX.service.mail@gmail.com    | ProjeXemail |

### 3. Application Help

We aimed to design an app that is both intuitive and user-friendly, ensuring that all features are straightforward and self-explanatory. Each input field is accompanied by a clear placeholder or label, providing guidance on the expected content. Additionally, buttons include tooltips that appear when hovered over, offering concise explanations of their functionality for enhanced clarity. To further enhance user understanding, we included an "About Us" page, offering insights into what ProjeX is all about.

### 4. Input Validation

Input data validation was implemented on both the client-side and server-side to ensure data integrity, security, and a smooth user experience. 
Client-side validation provides immediate feedback to the user, preventing invalid data from being sent to the server unnecessarily. Server-side validation ensures the integrity and security of incoming data that must respect specific constraints before it is saved in the database. 

### 5. Check Accessibility and Usability

The checklist of accessibility and usability can be seen in the following links:

- Accessibility:[Accessibility Check List](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/blob/main/docs/PA/CheckLists/checklist_acessibilidade.pdf)
- Usability: [Usability Check List](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/blob/main/docs/PA/CheckLists/Checklist_Usabilidade.pdf)

### 6. HTML & CSS Validation

The results of the validation of the HTML and CSS code. 
  
- HTML: [HTML Validation Reports](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/tree/main/docs/PA/HTMLValidation)
- CSS: [CSS Validation Reports](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/tree/main/docs/PA/CSSValidation)

### 7. Revisions to the Project

1. Creating a table on database for password resetting using Laravel's built-in functions.
2. Added attributes to change members´ permissions on project table.
3. Added remember_token attribute in account table to store a unique token associated with the user session.
4. Added last_accessed attribute on project_member table to track the last time a member accessed a specific project.
5. Added new user stories and changed some user stories priorities.

### 8. Implementation Details

#### 8.1. Libraries Used
- Laravel Framework: A PHP framework for building web applications. The entire product is built on Laravel for backend logic and API handling. Examples of its implementation can be found in [routes/web.php](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/blob/main/routes/web.php) for defining web routes and in the controllers located in [app/Http/Controllers](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/tree/main/app/Http/Controllers). Detailed documentation can be accessed on the [Laravel Official Documentation](https://laravel.com/docs/11.x).
- Pusher PHP Server: A library to integrate WebSocket-based real-time functionality. We used it to enable real-time notifications and updates for users. This functionality is defined in the [app/Events](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/tree/main/app/Events) directory, which broadcasts events, and respective controllers and javascript files. More details about Pusher can be found in its [documentation](https://pusher.com/docs/beams/reference/server-sdk-php/).
- Mailtrap PHP: A library to manage email testing. It was used to test email functionality during development. Configurations for Mailtrap are defined in [config/mail.php](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/blob/main/config/mail.php), and email templates can be found in [invitation.blade.php](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/blob/main/resources/views/pages/invitation.blade.php) . The official [Mailtrap documentation](https://api-docs.mailtrap.io/) provides more insights.
- ChartJS for Laravel: Integration of Chart.js with Laravel for data visualization.It was used to generates visual charts for data analytics about project events on project timeline page. Examples of this integration can be seen in [projectTimeline.blade.php](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/blob/main/resources/views/pages/projectTimeline.blade.php), where project timelines are rendered as charts. The [Chart.js documentation](https://www.chartjs.org/) contains additional information.
- Font Awesome: A library for scalable vector icons. It was used to provide icons for buttons, navigation, and status indicators. They are implemented in various files, such as [sidebar.blade.php](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/blob/main/resources/views/partials/sidebar.blade.php) for the project navigation bar and [adminViewAccounts.blade.php](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/blob/main/resources/views/partials/adminViewAccounts.blade.php) for user action buttons like edit, block, or delete. Additional resources are available on the [Font Awesome Official Website](https://fontawesome.com/). 

#### 8.2 User Stories

| US Identifier | Name    | Module | Priority                       | Team Members               | State  |
| ------------- | ------- | ------ | ------------------------------ | -------------------------- | ------ |
| US01                | View Main Page | M01 | High | **Marta** | 100% |
| US02                | Register| M02 | High | **Pedro** | 100% |
| US03                | Log In | M02 | High | **Pedro** | 100% |
| US04                | Create Project as a User| M03 | High | **Marta** | 100% |
| US05                | View Projects |  M03 | High | **Bruno** | 100%|
| US06                | Edit Profile | M02 | High | **Pedro** | 100%. |
| US07                | Create Task | M04 | High | **Pedro** | 100% |
| US08                | View Task Details | M04 | High | **Bruno** | 100% |
| US09                | Edit Task | M04 | High | **Adriana** | 100% |
| US10                | Delete Task | M04 | High | **Pedro** | 100% |
| US11                | Complete Task | M04 | High | **Pedro** |100% |
| US12                | Search Task |  M04 | High | **Pedro** | 100%|
| US13                | Add User to Project | M05 | High | **Marta**| 100% |
| US14                | Browse User Accounts | M06 | High | **Marta** | 100% |
| US15                | Supervise User Accounts | M06 | High | **Marta** | 100%|
| US16                | Create Accounts | M02 | High | **Pedro** | 100% |
| US17                | Administer Accounts | M06 | High | **Marta** | 100% |
| US18                | Access Projects | M03 | Medium | **Bruno** | 100% |
| US19                | Manage Project Invitations | M02 | Medium | **Pedro** **Adriana** | 100% |
| US20                | Favourite Projects | M03 | Medium | **Bruno** | 100% |
| US21                | Logout | M02 | Medium | **Pedro** | 100% |
| US22                | View Project Team | M03 | Medium | **Adriana** | 100% |
| US23                | Add Task table | M03 | Medium | **Adriana** | 100% |
| US24                | Block Accounts| M06 | Medium | **Marta** | 100% |
| US25                | Unblock Accounts | M06 | Medium | **Marta** | 100% |
| US26                  | Project invitation by email | M02 | Medium | **Marta** | 100% |
| US27                  | Error Messages | M01 | Medium | **Bruno** | 100% |
| US28                  | Access Notifications | M02 | Medium | **Pedro** | 100% |
| US29                  | Assign Users to Tasks | M04 | Medium | **Pedro** | 100% |
| US30                  | Comment on Tasks | M04 | Medium | **Pedro** | 100% |
| US31                  | Leave Project | M03 | Medium | **Adriana** | 100% |
| US32                | Notification Unassigned to Task| M04 | Medium | **Pedro**  | 100%|
| US33                  | Notification Assigned to Task | M04 | Medium | **Pedro** | 100% |
| US34              | Notification Change in Project Coordinator | M03 | Medium | **Pedro** | 100% |
| US35                  | Browse the Project Forum | M03 | Medium | **Bruno** | 100% |
| US36                  | View About Us | M01 | Medium | **Adriana** | 100% |
| US37                  | View Main Features | M01 | Medium | **Adriana** | 100% |
| US38                  | Post Message to Project Forum | M03 | Medium | **Pedro** | 100% |
| US39                  | Recover Password | M02 | Medium | **Bruno** | 100% |
| US40                  | Assign Coordinator | M05 | Medium | **Pedro** | 100% |
| US41                  | Edit Project Details | M03 | Medium | **Bruno** | 100% |
| US42                  | Remove Member | M05 | Medium | **Pedro** | 100% |
| US43                  | Archive Project | M03 | Medium | **Marta** | 100% |
| US44                  | Invite by Email | M02 | Medium | **Bruno** | 100% |
| US45                  | Notification Completed Task | M04 | Medium | **Pedro** | 100% |
| US46                  | Notification Accepted Invitation | M05 | Medium | **Pedro** | 100% |
| US47                  | Add Deadline to a Task | M04 | Medium | **Pedro** | 100% |
| US48                  | Browse Projects as  Administrator | M06 | Medium | **Marta** | 100% |
| US49                  | Project Details | M06 | Medium | **Marta** | 100% |
| US50                  | Delete Account | M02 | Medium |**Marta**  | 100% |
| US51                  | Browse Information | M01 | Medium |**Bruno**  | 100% |
| US52                  | Help | M01 | Medium |**Bruno**  | 100% |
| US53                  | View Project Timeline | M03 | Low | **Pedro** | 100% |
| US54                  | Edit Forum Message | M03 | Low | **Adriana** | 100% |
| US55                  | Delete Forum Message | M03 | Low | **Adriana** | 100% |
| US56                  | Project Privacy | M03 | Low | **Marta** | 100% |
| US57                  | Productivity Suggestions | M04 | Low | **Pedro** | 100% |
| US58                  | View Pending Invitation | M02 | Low | **Pedro** | 100% |
| US59                  | Filter Tasks by priority | M04 | Low | **Bruno**  | 100% |
| US60                  | Delete Task Table | M03 | Low | **Marta**  | 100% |
| US61                  | View Project Member Profile | M05 | Low | **Bruno** | 100% |
| US62                  | Project Side Bar | M03 | Low | **Bruno** | 100% |
| US63                  | Change Members Permissions | M05 | Low | **Marta** | 100% |
| US64                  | Table View of Project Tasks | M04 | Low | **Pedro** | 100% |
| US65                  | Task Hub | M04 | Low | **Pedro** | 100% |
| US66                  | Edit Project Layout | M03 | Low | **Marta** | 100% |
| US67                  |View Contacts | M01 | Low | **Adriana** | 100% |
| US68                  |Visualize Own Productivity Analytics | M03 | Low | | 0% |
| US69                  |Set Personal Achievement Goals | M03 | Low | | 0% |


---


## A10: Presentation
 
> This artifact corresponds to the presentation of the product.

### 1. Product presentation

- The product is a comprehensive project management tool designed to facilitate seamless collaboration, efficient task management, and enhanced team coordination. Developed with a user-centric approach, it empowers users to create, organize, and track projects and tasks effortlessly. Key features include the ability to register, log in, and manage profiles, along with project-specific functionalities such as creating, editing, and deleting projects or tasks. The product also integrates real-time notifications, supports user management by administrators (e.g., blocking, unblocking, and account supervision), and offers advanced project features like assigning coordinators, managing team permissions, and archiving projects.

- Additionally, the platform includes tools to enhance user experience and productivity, such as visualizing project timelines, filtering tasks by priority, and viewing project statistics. It provides a collaborative space with forums for team discussions, alongside features like task commenting and deadline management. The system ensures a robust and secure environment with functionalities such as password recovery, invitation management, and project privacy controls. By combining these features with a clean and intuitive interface, the product addresses the diverse needs of users, from individual contributors to project administrators, fostering a productive and well-organized workflow.

### 2. Video presentation


![ProjeX Presentation ScreenShot](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/raw/main/docs/PA/VideoPresentation/VideoScreenShot.png)

<p align="center">
  <b><i>Figure 1: ProjeX Video Presentation Screenshot</i></b>
</p>

[ProjeX Video Presentation](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/raw/main/docs/PA/VideoPresentation/lbaw24044.mp4)


---


## Revision history
1. 28/12/2024 - Added MailTrap Credentials
***
GROUP2444, 22/12/2024

* Adriana Almeida, up202109752@g.uporto.pt (Editor)
* Bruno Aguiar, up202205619@g.uporto.pt
* Marta Silva, up202208258@g.uporto.pt
* Pedro Oliveira, up202208345@g.uporto.pt