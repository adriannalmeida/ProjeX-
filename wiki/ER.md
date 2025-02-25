
# ER: Requirements Specification Component

> For project managers and teams who need an intuitive, flexible way to manage tasks and collaborate,
ProjeX is a project management tool that smooths task management, communication, and team coordination. Unlike traditional project management tools, our product focuses on the user experience, offering customizable layouts and features adapted to each user's workflow that improve both individual and team efficiency.


## A1: ProjeX

> The main goal of Artifact A1 is to provide a clear and concise overview of the project's purpose, goals, and key features, ensuring that stakeholders understand the system's intended functionality and value.

ProjeX is a web-based project management system developed by a small software company to address the team's own challenges in finding a tool that met their specific needs. This led to the creation of a solution focused on flexibility, enhanced team productivity and offering an exceptional user experience.

The main goal of ProjeX is to provide an intuitive web-based platform that simplifies project coordination and task organization for teams of any size, ensuring effective communication and management of activities. When deployed, administrators will be responsible for managing the system, ensuring a smooth operation across the platform.

Users are mainly differentiated based on their different levels of permissions. ProjeX administrators manage the entire platform, overseeing user accounts, controlling all projects, and utilizing advanced search tools to find and filter specific information quickly. They can edit, block, and delete user accounts, as well as delete any project across the system.

Unauthenticated users can register themselves in the system or recover their password if they already have an account. Once they log in, they can manage their profiles, see their notifications, create new projects, view the currently assigned projects and mark them as Favourite. Additionally, authenticated users may be project members or project coordinators. As project members, they can create, search, assign, and manage tasks within their assigned projects. They can also view profiles of team members, communicate via project chat, and leave projects if necessary. Project coordinators have additional control over task priorities, member management, and project coordination. They can assign tasks, oversee their completion, add or remove team members, and also promote others to the role of coordinator.

The adaptive design in the platform ensures a consistent and user-friendly experience across all devices, from desktops to smartphones, allowing teams to stay connected and productive from anywhere.

ProjeX stands out for its role-based access, where different control levels are defined according to user roles – basic users, coordinators and administrators. It seeks to provide an easy to use interface that allows free flow of communication and advanced project workflow management, hence ideal for teams that want a multipurpose tool to manage and carry out their projects effectively.

---


## A2: Actors and User stories

> The main goal of Artifact A2 is to define and organize actors and their roles, illustrating user interactions within the project management tool.


### 1. Actors

For the ProjeX system, the actors are represented in Figure 1 and described in Table 1.

![ProjeX Actors](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/raw/main/docs/ER/ProjeXActors.png)

<p align="center">
  <b><i>Figure 1: ProjeX actors</i></b>
</p>



|Identifier|Description|
|----------|-----------|
|User|Represents any individual interacting with the system, including Visitors (unauthenticated users) who can log in or register, and Authenticated Users who have access to additional functionalities.|
|Visitor|Unauthenticated user that can log-in to their account or register itself (sign-up in the system)|
|Authenticated User|User that is logged-in in the system and can interact with it: create a project or accept a project invitation.|
|Project Member|Authenticated user that is a member of a project, allowing it to search, create, manage, assign, comment and complete tasks as well as see tasks details.|
Project Coordinator|Project member that is the “owner” of a project and has permissions to add and remove users of the project, assign tasks to them and assign new coordinators.|
|Administrator|Manages all aspects of the system, such as removing users, managing projects and guaranteeing the system works properly.|
|OAuth Google API|External API that can be used to register and authenticate users into the system.|

<p align="center">
  <b><i>Table 1: ProjeX actors description</i></b>
</p>


### 2. User Stories

For the ProjeX system, consider the user stories that are presented in the following sections.

#### 2.1. User

| Identifier | Name            | Priority | Responsible | Description |
|------------|-----------------|----------|-------------|-------------|
| US101      | View Main Page  | High     | Marta            | As a *User*, I want to view the main page so that I can get an overview of the website's content.|
| US102      | View About Us   | Medium   | Adriana            | As a *User*, I want to view the "About Us" page so that I can learn more about the website and its purpose. |
| US103      | View Main Features | Medium | Pedro            | As a *User*, I want to view the main features of the platform so that I can understand its key functionalities and how to use them effectively. |
| US104      | View Contacts   | Low      |  Bruno           | As a *User*, I want to view the contact information so that I know how to reach the organization for support or inquiries. |

<p align="center">
  <b><i>Table 2: User user stories</i></b>
</p>

#### 2.2. Visitor

|Identifier|Name|Priority|Responsible|Description|
|----------|-----------|----------|----------|-----------|
| US201      | Register                          | High       | Pedro            | As a *Visitor*, I want to register so that I become an authenticated user with full access to the system functionalities. |
| US202      | Log In                            | High       | Pedro            | As a *Visitor*, I want to login so that I can access my personal information and projects. |
| US203      | Recover Password                  | Medium     |  Bruno           | As a *Visitor*, I want to recover my password via email so that I can access my account. |
| US204      | Project invitation                | Medium     | Marta            | As a *Visitor*, I want to receive email invitations to projects, so that I can join them conveniently. |


<p align="center">
  <b><i>Table 3: Visitor user stories</i></b>
</p>

#### 2.3. Authenticated User

|Identifier|Name|Priority|Responsible|Description|
|----------|-----------|----------|----------|-----------|
| US301      | Create Project                    | High       | Adriana            | As an *Authenticated User* I want to create a new project so that I can better manage my team. |
| US302      | View Projects                     | High       |  Bruno           | As an *Authenticated User* I want to view my projects so that I can better organize my work. |
| US303      | Edit Profile                      | High| Pedro            | As an *Authenticated User* I want to view and edit my profile so that it’s always up to date. |
| US304      | Access Projects                   | Medium     |  Bruno           | As an *Authenticated User* I want to access my projects so that I can work on them. |
| US305      | Manage Project Invitations        | Medium |  Marta           | As an *Authenticated User* I want to manage my project invitations so I can choose which projects to join. |
| US306      | Favourite Projects                 | Medium     |  Adriana           | As an *Authenticated User* I want to mark my favourite projects so that I can easily track them. |
| US307      | Notifications                     | Medium     | Marta            | As an *Authenticated User* I want to access my notifications so that I am aware of my projects’ development. |
| US308      | Logout                            | Medium     | Pedro            | As an *Authenticated User* I want to logout from my account so that I can ensure the security of my information. |
| US309      | Delete Account                    | Medium     |  Pedro           | As an *Authenticated User* I want to delete my account so that I can permanently disassociate my data from the platform. |
| US310     | Browse Information                | Medium        | Pedro            | As a *Authenticated User*, I want to browse public information so that I am aware of current projects and their progress. |
| US311      | View Pending Invitations                  | Low     |  Pedro           | As an *Authenticated User* I want to see my pending invitations to projects so that I can evaluate and decide whether to accept or decline them. |

<p align="center">
  <b><i>Table 4: Authenticated User user stories</i></b>
</p>

#### 2.4. Project Member

|Identifier|Name|Priority|Responsible|Description|
|----------|-----------|----------|----------|-----------|
| US401      | Create Task                       | High       |  Pedro           | As a *Project Member* I want to create a task so that I can assign the work that has to be done to the project. |
| US402      | View Task Details                 | High       | Bruno            | As a *Project Member* I want to view task details so that I understand clearly what needs to be done. |
| US403      | Edit Tasks                      | High       |  Adriana           | As a *Project Member* I want to edit tasks so that I can make adjustments based on project needs. |
| US404      | Delete Tasks                      | High       |  Adriana           | As a *Project Member* I want to delete tasks so that I can make remove tasks that are no longer relevant to the project. |
| US405      | Complete Tasks                    | High       | Adriana            | As a *Project Member* I want to mark a task as completed so that everyone knows which tasks are finished. |
| US406      | Search Tasks                      | High       |  Marta           | As a *Project Member* I want to search for tasks so I can quickly find specific tasks. |
| US407      | Assign Users to Tasks             | Medium     | Pedro            | As a *Project Member* I want to assign tasks to users so that everyone knows what they need to work on. |
| US408      | Comment on Tasks                  | Medium     |  Marta           | As a *Project Member* I want to comment on tasks so that I can collaborate with other users working on the same task. |
| US409      | Leave Project                     | Medium     |  Adriana           | As a *Project Member* I want to leave the project so that I can remove myself from a project I am no longer involved in. |
| US410      | View Project Team                 | Medium     |  Bruno           | As a *Project Member* I want to view the project team so that I know who I am working with. |
| US411      | View Team Members Profile         | Medium     |  Bruno           | As a *Project Member* I want to view team members' profiles so that I can learn more about their expertise and contact them easily. |
| US412      | Error Messages                    | Medium     | Bruno            | As a *Project Member* I want clear error messages when something goes wrong so that I can understand and fix it. |
| US413      | Help                              | Medium     | Pedro            | As a *Project Member* I want helpful tool tips when hovering over buttons so that I can understand their functions. |
| US414      | Notification Unassigned to Task | Medium | Marta       | As a *Project Member* I want a notification when a task I am unassigned so that I can stop working on that task. |
| US415      | Notification Assigned to Task     | Medium     |  Marta          | As a *Project Member* I want a notification when I’m assigned to a new task so that I can start working on it immediately. |
| US416      | Notification Change in Project Coordinator | Medium | Marta     | As a *Project Member* I want a notification when the project coordinator changes so that I know who is in charge. |
| US417      | Browse the Project Forum          | Medium        | Pedro            | As a *Project Member* I want to browse the project forum so that I can see what other members said and their questions. |
| US418      | Post Message to Project Forum     | Medium        |  Pedro           | As a *Project Member* I want to post messages to the project forum so that I can interact with other members and get help with my doubts. |
| US419      | Visualize Own Productivity Analytics | Low  |  Adriana           | As a *Project Member* I want to view productivity analytics so that I can assess my performance. |
| US420      | Set Personal Achievement Goals    | Low     | Adriana            | As a *Project Member* I want to set personal achievement goals so that I can stay motivated and track my progress. |
| US421      | Productivity Suggestions          | Low     | Adriana            | As a *Project Member* I want productivity suggestions so that I can focus on important tasks and manage my workload efficiently. |
| US422      | Edit Layout                       | Low     |  Bruno           | As a *Project Member* I want to edit my project layout so that I can improve and personalize my experience. |
| US423      | View Project Timeline             | Low        | Bruno            | As a *Project Member* I want to view the project timeline so that I know what has changed. |
| US424      | Edit Forum Message                | Low        | Pedro            | As a *Project Member* I want to edit my forum messages so that I can correct previous posts. |
| US425      | Delete Forum Message              | Low        | Pedro            | As a *Project Member* I want to delete my forum messages so that they are no longer visible. |
| US426      | Filter Tasks by Priority              | Low        | Bruno            | As a *Project Member* I want to filter tasks by priotity so that I can effectively organize and prioritize my work. |
| US427      | View Project Member Profile Page          | Low        | Pedro            | As a *Project Member*  I want to view other members' profile pages so that I can access information about the people I am collaborating with. |
| US428      | Project Side Bar        | Low        | Bruno            | As a *Project Member*  I want to access a project side bar so that I can quickly navigate between different project sections and features. |
| US429      | Table View of Project Tasks        | Low        | Pedro            | As a *Project Member*  I want to view project tasks in a table format so that I can easily track and manage tasks in an organized manner. |
| US430      | Task Hub        | Low        | Pedro            | As a *Project Member* I want to access a Task Hub that displays tasks from all my projects so that I can manage and track them in one central location. |


<p align="center">
  <b><i>Table 5: Project Member user stories</i></b>
</p>

#### 2.5. Project Coordinator

|Identifier|Name|Priority|Responsible|Description|
|----------|-----------|----------|----------|-----------|
| US501      | Add User                          | High       | Marta            | As a *Project Coordinator* I want to be able to add new users to the project so that I ensure everyone involved in the project has access to all its information. |
| US502      | Assign Coordinator                | Medium     | Adriana            | As a *Project Coordinator* I want to be able to assign a new coordinator so that I can transfer leadership responsibilities to another member if needed. |
| US503      | Edit Project Details              | Medium     | Bruno            | As a *Project Coordinator* I want to be able to edit project details so that I ensure all project information is updated and relevant for the team members. |
| US504      | Remove Member                     | Medium     | Marta            | As a *Project Coordinator* I want to be able to remove a member from the project so that I can remove a member that is no longer involved in the project. |
| US505      | Archive Project                   | Medium     | Bruno            | As a *Project Coordinator* I want to be able to archive a completed or inactive project so that I can keep the project list organized. |
| US506      | Invite by Email                   | Medium | Bruno            | As a *Project Coordinator* I want to be able to invite other users to my project by email so that they can easily join it. |
| US507      | Notification Completed Task       | Medium     | Marta            | As a *Project Coordinator* I want to receive a notification when a task in my project is completed so that I can easily keep track of the progress. |
| US508      | Notification Accepted Invitation  | Medium     | Marta            | As a *Project Coordinator* I want to receive a notification when a user accepts an invitation to my project so that I can easily keep track of the new project members. |
| US509      | Add Deadline to a Task            | Medium     | Adriana            | As a *Project Coordinator* I want to add a deadline to a task, so that team members can prioritize their work accordingly. |
| US510                | Add Task table | Medium | Marta| As a Project Coordinator I want to be able to add new task tables to the project so that I can better organize and manage the tasks related to that project. |
| US511      | Team’s Productivity Analysis      | Low     | Adriana            | As a *Project Coordinator* I want to view productivity analytics, so that I can assess team performance and identify areas for improvement. |
| US512      | Project Privacy                   | Low        | Marta            | As a *Project Coordinator* I want to be able to set the project to private or public so that only the supposed users are able to see it. |
| US513      | Delete Task Table                 | Low        | Marta            | As a *Project Coordinator* I want to be able to delete tasktables so that I can remove unnecessary tables and keep the project organized. |
| US514      | Change Members Permissions                | Low        | Marta            | As a *Project Coordinator* I want to be able to change members' permissions so that I can manage their access levels and roles within the project. |


<p align="center">
  <b><i>Table 6: Project Coordinator user stories</i></b>
</p>

#### 2.6. Administrator

|Identifier|Name|Priority|Responsible|Description|
|----------|-----------|----------|----------|-----------|
| US601      | Browse User Accounts               | High|  Bruno           | As an *Administrator* I want to search user accounts , so that I can access all user's information. |
| US602      | Supervise User Accounts               | High| Adriana            | As an *Administrator* I want to view user accounts' details , so that I can supervise user information. |
| US603      | Create User Accounts               | High| Pedro            | As an *Administrator* I want to create user accounts so that I can manually grant new users access to ProjeX. |
| US604      | Administer Accounts               | High| Marta            | As an *Administrator* I want to edit user accounts, so that I can ensure accurate user information. |
| US605      | Browse Projects                   | Medium     | Bruno            | As an *Administrator* I want to be able to browse all projects so that I can monitor all projects. |
| US606      | Project Details                   | Medium     | Marta            | As an *Administrator* I want to view the details of any project, so that I can understand the project's objectives. |
| US607      | Block Accounts            | Medium     | Bruno            | As an *Administrator* I want to block user accounts, so that I can manage access to the system, preventing unauthorized actions. |
| US608      | Unblock Accounts            | Medium     | Bruno            | As an *Administrator* I want to unblock user accounts, so that I can manage access to the system, preventing unauthorized actions. |
| US609      | Delete Account                    | Medium     | Pedro            | As an *Administrator* I want to be able to delete user accounts so that I can permanently remove users. |
| US610      | Delete Projects                    | Low     | Marta            | As an As an Administrator, I want to be able to delete projects so that I can ensure the platform remains organized and compliant. |

<p align="center">
  <b><i>Table 7: Administrator user stories</i></b>
</p>

### 3. Supplementary Requirements

Section including business rules, technical requirements, and restrictions.  

#### 3.1. Business rules

| Identifier | Name                 | Description |
|------------|----------------------|-------------|
| BR01       | Account Deletion      | Upon account deletion, user personal data is eliminated and shared data is kept anonymous. |
| BR02       | Administrators        | The administrator accounts are independent from the user accounts, and administrators cannot create nor collaborate in projects. |
| BR03       | Project Coordinators  | Each non archived project must have at least one Project Coordinator. |
| BR04       | Banned Users          | Only administrators can ban users. Banned users’ information will be stored to ensure the user does not register again. |

<p align="center">
  <b><i>Table 8: ProjeX business rules</i></b>
</p>

#### 3.2. Technical requirements

| Identifier | Name            | Description |
|------------|-----------------|-------------|
| **TR01**   | **Performance** | **The system should have response times shorter than 2 seconds to ensure the user's attention. <br><br>In project management, quick responsiveness is essential for maintaining user engagement and productivity. Slow performance can lead to frustration and inefficiency, which can disrupt the workflow of teams.** |
| **TR02**   | **Scalability** | **The system must be prepared to deal with the growth in the number of users and their actions. <br><br>As users can be involved in multiple projects simultaneously, the system must scale to handle increasing data and user activity without performance degradation. Scalability ensures the platform can grow with user needs, supporting larger teams and projects.** |
| **TR03**   | **Availability** | **The system must have an uptime of 99.9%, ensuring it is accessible nearly all the time, to avoid disruptions in project progress and collaboration. <br><br>High availability is crucial for project management tools, as users rely on them for real-time task tracking and communication. Any downtime could lead to delays and a loss of productivity, making continuous access a critical requirement.** |
| TR04       | Robustness      | The system must be prepared to handle and continue operating when runtime errors occur. |
| TR05       | Accessibility   | The system must ensure that everyone can access the pages, regardless of whether they have any handicap or not, or the Web browser they use. |
| TR06       | Security        | The system must ensure secure user authentication and data protection, preventing unauthorized access to projects and sensitive information. |
| TR07       | Customization   | The system should allow users to personalise their layout and workspace without compromising the overall system performance or slowing down task management workflows. |
| TR08       | Database Management | PostgreSQL must be used as the database system. |
| TR09       | Web Application | The system should be implemented as a web application with dynamic pages (HTML5, JavaScript, CSS3, and PHP). |
| TR10       | Portability     | The server-side system should work across multiple platforms (Linux, Mac OS, etc.). |
| TR11       | Ethics          | The system must protect user data and ensure privacy, adhering to ethical standards in data collection and user consent. |
| TR12       | Usability       | The system should be simple and easy to use. The ProjeX system is designed to facilitate project development and management, not just be one more tool to work with, so good usability is very important. |

<p align="center">
  <b><i>Table 9: ProjeX technical requirements</i></b>
</p>

#### 3.3. Restrictions

| Identifier | Name            | Description |
|------------|-----------------|-------------|
|C01|Deadline| The system must be developed and ready to be used by the end of the semester.|

<p align="center">
  <b><i>Table 10: ProjeX restrictions</i></b>
</p>


## A3: Information Architecture

> The main goal of Artifact A3 is to present a clear and structured representation of the system's information architecture, including the sitemap and wireframes. It outlines the main pages, their relationships, and provides visual wireframes for key user interfaces.


### 1. Sitemap

ProjeX begins with the homepage, connecting to authentication, profile, notifications, invites, projects related, and admin pages, with clear links between key sections like tasks, projects, and user management. The projects page contains components that link to additional pages. These components are a compiled version of those pages and may be absent if the user chooses to remove them while editing the layout.

![ProjeX SiteMap](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/raw/main/docs/ER/ProjeXSiteMap.png)
  <b><i>Figure 2: ProjeX SiteMap</i></b>
</p>

### 2. Wireframes

For the ProjeX system, the wireframes for the Logged-in Home Page and Project Page are presented in Figures 3 and 4, respectively.

#### UI14: Logged-in Home Page

![ProjeX Logged-inHomePage](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/raw/main/docs/ER/LoggedInHomePage.png)
  <b><i>Figure 3: ProjeX Logged-in Home Page wireframe</i></b>
</p>

#### UI16: Project Page

![ProjeX ProjectPage](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/raw/main/docs/ER/ProjectPage.png)
  <b><i>Figure 4: ProjeX Project Page wireframe</i></b>
</p>

#### Extra:
#### UI06: Authentication Page

![ProjeX AuthenticationPage](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/raw/main/docs/ER/AuthenticationPage.png)
  <b><i>Figure 5: ProjeX Authentication Page wireframe</i></b>
</p>

#### UI11: User Profile Page

![ProjeX UserProfilePage](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/raw/main/docs/ER/UserProfilePage.png)
  <b><i>Figure 6: ProjeX User Profile Page wireframe</i></b>
</p>

#### UI01: Unauthenticated User Home Page

![ProjeX HomePage](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/raw/main/docs/ER/HomePage.png)
  <b><i>Figure 7: ProjeX Home Page wireframe</i></b>
</p>


## Revision history

Changes made to the first submission:
1. 26/09/2024 ER Created
2. 26/09/2024 Added content to A1
3. 29/09/2024 Finished A1 and started A2
4. 03/10/2024 Finished A2 and started A3
5. 08/10/2024 Finished A3
6. 04/11/2024 Changed User Stories Priorities
7. 27/11/2024 Added User Story to add task table
8. 22/12/2024 Added new implemented User Stories
9. 22/12/2024 Changed User Story 'Browse Information' to Authenticated User table

***
GROUP 44, 10/10/2024

* Adriana Almeida, up202109752@g.uporto.pt 
* Bruno Aguiar, up202205619@g.uporto.pt
* Marta Silva, up202208258@g.uporto.pt
* Pedro Oliveira, up202208345@g.uporto.pt (Editor)
