# EAP: Architecture Specification and Prototype

## A7: Web Resources Specification

> The main goal of Artifact A7 is to define the web resources needed for implementing the vertical prototype, organized into structured modules and clearly defined permissions for each user role. This specification ensures secure, consistent, and accessible resource handling, aligned with OpenAPI standards.

### 1. Overview

| Module ID | Description |
|-----------|-------------|
| **M01: Main Page and Navigation** | Web resources related to viewing and navigating the main page and its contents. This includes the system feature of accessing the website's primary interface for users to gain an overview of the site's content. *Related User Story*: US101 (View Main Page) |
| **M02: Authentication and Individual Profile Management** | Web resources for visitor and user authentication, including login, registration, and account creation by administrators. Additionally covers managing and editing individual profiles. *Related User Stories*: US201 (Register), US202 (Log In), US303 (Edit Profile)|
| **M03: Project Management** | Web resources for creating and viewing projects, enabling users to manage their team and access project information. *Related User Stories*: US301 (Create Project), US302 (View Projects) |
| **M04: Task Management** | Web resources for task-related operations within projects, including creating, viewing, editing, deleting, searching, and completing tasks. This module supports full lifecycle task management for project members. *Related User Stories*: US401 (Create Task), US402 (View Task Details), US403 (Edit Tasks), US404 (Delete Tasks), US405 (Complete Tasks), US406 (Search Tasks) |
| **M05: Project Member Management** | Web resources for managing project members, including adding new users to projects. This functionality is reserved for project coordinators. *Related User Story*: US501 (Add User) |
| **M06: User Administration** | Web resources for managing user accounts, allowing administrators to create, view, search, edit, supervise, and administer user accounts across the system. *Related User Stories*: US601 (Browse User Accounts), US602 (Supervise User Accounts), US603 (Create User Accounts), US604 (Administer Accounts) |


### 2. Permissions

| Permission ID | Name              | Description                                                                                       |
|---------------|-------------------|---------------------------------------------------------------------------------------------------|
| **PUB**       | Public            | Permission for all visitors to the website without any authentication. Grants access to main page, and to registration and login functionalities. *Relevant User Story*: US101 (View Main Page), US201 (Register), US202 (Log In) |
| **USR**       | Authenticated User| Permission for authenticated users to access standard functionalities, such as creating/viewing projects or editing their profile.  *Relevant User Stories*: US301 (Create Project), US302 (View Projects), US303 (Edit Profile) |
| **MEM**       | Project Member    | Permission for project members to create, view, edit, delete, complete, and search tasks within a project. This allows members to contribute to and track specific project tasks. *Relevant User Stories*: US401 (Create Task), US402 (View Task Details), US403 (Edit Tasks), US404 (Delete Tasks), US405 (Complete Tasks), US406 (Search Tasks) |
| **COORD**     | Project Coordinator | Permission for project coordinators to manage project members, specifically adding new users to their projects. *Relevant User Story*: US501 (Add User) |
| **ADM**       | Administrator     | Permission for system administrators to manage user accounts, including browsing, supervising, creating, and editing user details across the system. *Relevant User Stories*: US601 (Browse User Accounts), US602 (Supervise User Accounts), US603 (Create User Accounts), US604 (Administer Accounts) |



### 3. OpenAPI Specification

> OpenAPI specification in YAML format to describe the vertical prototype's web resources.

> Link to the `a7_openapi.yaml` file in the group's repository.


```yaml
openapi: 3.0.0

...
```

---


## A8: Vertical prototype

> Brief presentation of the artifact goals.

### 1. Implemented Features

#### 1.1. Implemented User Stories

> Identify the user stories that were implemented in the prototype.  

| User Story reference | Name      | Priority    | Responsible        | Description                                           |
| -------------------- | --------- | ----------- | ------------------ | ----------------------------------------------------- |
| US101                | View Main Page | High | Marta | As a User, I want to view the main page so that I can get an overview of the website's content. |
| US201                |Register| High | Pedro | As a Visitor, I want to register so that I become an authenticated user with full access to the system functionalities. |
| US202                | Log In | High | Pedro | As a Visitor, I want to login so that I can access my personal information and projects. |
| US301                | Create Project | High | Adriana | As an Authenticated User I want to create a new project so that I can better manage my team. |
| US302                | View Projects | High | Bruno | As an Authenticated User I want to view my projects so that I can better organize my work.|
| US303                | Edit Profile | High | Pedro | As an Authenticated User I want to view and edit my profile so that it’s always up to date. |
| US401                | Create Task | High | Pedro | As a Project Member I want to create a task so that I can assign the work that has to be done to the project. |
| US402                | View Task Details | High | Bruno | As a Project Member I want to view task details so that I understand clearly what needs to be done. |
| US403                | Edit Task | High | Adriana | As a Project Member I want to edit tasks so that I can make adjustments based on project needs. |
| US404                | Delete Task | High | Adriana| As a Project Member I want to delete tasks so that I can make remove tasks that are no longer relevant to the project. |
| US405                | Complete Task | High | Adriana |As a Project Member I want to mark a task as completed so that everyone knows which tasks are finished. |
| US406                | Search Task | High | Marta | As a Project Member I want to search for tasks so I can quickly find specific tasks.|
| US501                | Add User | High | Marta| As a Project Coordinator I want to be able to add new users to the project so that I ensure everyone involved in the project has access to all its information. |
| US601                | Browse User Accounts | High | Bruno | As an Administrator I want to search user accounts , so that I can access all user's information.|
| US602                | Supervise User Accounts | High | Adriana | As an Administrator I want to view user accounts' details , so that I can supervise user information. |
| US603                | Create Accounts | High | Pedro | As an Administrator I want to create user accounts so that I can manually grant new users access to ProjeX. |
| US604                | Administer Accounts | High | Marta | As an Administrator I want to edit user accounts, so that I can ensure accurate user information. |




...

#### 1.2. Implemented Web Resources

> Identify the web resources that were implemented in the prototype.  

> Module M01: Main Page and Navigation  

| Web Resource Reference | URL                            |
| ---------------------- | ------------------------------ |
| R101: Main Page | GET/mainPage |


> Module M02: Authentication and Individual Profile Management 

| Web Resource Reference | URL                            |
| ---------------------- | ------------------------------ |
| R201: Login Form       | GET/login                      |
| R202: Login Action     | POST/login                     |
| R203: Register Form    | GET/register                   |
| R204: Register Action    | POST/register                  |
| R205: Logout           | GET/logout                     |
| R206: View Profile     | GET/account                    |
| R207: Edit Profile Form     | GET/account/edit          |
| R208: Edit Profile Action   | GET/account/update/{id?}  |
| R209: Update Password Action  | PUT/account/updatePassword/{id?}    |
| R210: Accept Invite Action | PATCH/invitations/{invitation}/accept    |
| R211: Decline Invite Action | DELETE//invitations/{invitation}/decline |


> Module M03: Project Management

| Web Resource Reference | URL                            |
| ---------------------- | ------------------------------ |
| R301: View Projects | GET/projects|
| R302: Create Project Action | GET/projects   |
| R303: Search Project  | GET/projects/search |
| R304: View Project Page | GET/project/{id}   |
| R305: View Project Members | GET/project/{project}/projectMembers |
| R306: Add Project to Favourites | PATCH/project/{projectId}/addToFavorites|
   

> Module M04: Task Management 

| Web Resource Reference | URL                            |
| ---------------------- | ------------------------------ |
| R401: View Task Tables | GET/project/{project}/task-tables |
| R402: Create Task Tables Action | POST/project/{project}/task-tables|
| R403: View Task            | GET/task/{task}|
| R404: Create Task Action   | GET/taskTable/{taskTable}/storeTask |
| R405: Edit Task Action     | POST/task/{task}|
| R406: Delete Task Action   | PUT/task/{task}/delete|
| R407: Complete Task Action | PATCH/task/{task}/complete    |
| R408: Incomplete Task Action |PATCH/task/{task}/incomplete |
| R409: Change Task Position | PUT/task/{task}/change-position/{posDest}/{tableDest}|
| R410: Search Task          | GET/project/{project}/task-tables/search |

> Module M05:  Project Member Management

| Web Resource Reference | URL                            |
| ---------------------- | ------------------------------ |
| R501: Add User to Project | POST/project/{project}/invite |

> Module M06: User Administration

| Web Resource Reference | URL                            |
| ---------------------- | ------------------------------ |
| R601: Supervise User Accounts     | GET/admin    |
| R602: Browse User Accounts   | GET/admin/search |
| R603: Create User Accounts Form   | POST/admin |
| R604: Block User | PATCH/admin/block/{id}   |
| R605: Edit User Account | GET/account/manage{id} |


...

### 2. Prototype

> Command to start the Docker image from the group's Container Registry.
> User credentials necessary to test all features.
> Link to the source code in the group's Git repository.


---


## Revision history

Changes made to the first submission:
1. Item 1
1. ..

***
GROUPYYgg, DD/MM/20YY
 
* Group member 1 name, email (Editor)
* Group member 2 name, email
* ...