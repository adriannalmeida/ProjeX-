# lbaw24044



# ProjeX
> For project managers and teams who need an intuitive, flexible way to manage tasks and collaborate,
ProjeX is a project management tool that smooths task management, communication, and team coordination. Unlike traditional project management tools, our product focuses on the user experience, offering customizable layouts and features adapted to each user's workflow that improve both individual and team efficiency.



## Project Components

- [ER: Requirements Specification](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/wikis/ER)
- [EBD: Database Specification](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/wikis/ebd)
- [EAP: Architecture Specification and Prototype](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/wikis/eap)
- [PA: Product and Presentation](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/wikis/pa)

## Artefacts Checklist

- [Checklist](https://docs.google.com/spreadsheets/d/1ugcxtuZJt_VKeHFe0ub1dWTqq5ScXR8SsqbW_3Wow3M/edit?gid=537406521#gid=537406521)

## Installation

### Logging In to GitLab's Container Registry

To access the GitLab Container Registry, log in using FEUP credentials while connected to the FEUP VPN or network:

```bash
docker login gitlab.up.pt:5050
```

- **Username**: `upXXXXX@up.pt`
- **Password**: Your GitLab password

---

###  Command to Start the Docker Image from the Group's Container Registry:

```bash
docker run -d --name lbaw24044 -p 8001:80 gitlab.up.pt:5050/lbaw/lbaw2425/lbaw24044 
```

You can access the project at:\
[http://localhost:8001](http://localhost:8001)

### Command to Stop and Remove the Container:

```bash
docker stop lbaw24044
docker rm lbaw24044
```

#### Link to Source Code:

[Source Code Repository - PA Tag](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/tree/PA?ref_type=tags)

## User Credentials
### Administration Credentials

To access the admin page, we need to login using the following credentials:   

| Username | Password |
| -------- | -------- |
| admin_alice    | hashed_password_1 |

### User Credentials

| Type          | Username  | Password |
| ------------- | --------- | -------- |
| Project Coordinator | admin_alice   | hashed_password_1 |
| Project Member  |  pedro_oliveira    | hashed_password_5 |

  
## Team
- Adriana Almeida, up202109752@g.uporto.pt 
- Bruno Aguiar, up202205619@g.uporto.pt
- Marta Silva, up202208258@g.uporto.pt
- Pedro Oliveira, up202208345@g.uporto.pt
##

Group24044