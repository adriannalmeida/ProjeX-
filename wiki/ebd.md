# EBD: Database Specification Component

## A4: Conceptual Data Model

> The main goal of Artifact A4 is to provide a clear representation of the system's entities, their attributes, and relationships, in order to effectively organize and retrieve data. The conceptual data model serves as a framework for understanding how the users interact with tasks and projects within the application.

### 1. Class diagram

![ProjeX HomePage](https://gitlab.up.pt/lbaw/lbaw2425/lbaw24044/-/raw/main/docs/EBD/ClassDiagram.png)
  <b><i>Figure 1: ProjeX Class Diagram</i></b>
</p>

### 2. Additional Business Rules
 
| **Identifier** | **Description** |
|----------------|------------------|
| BR01           | Administrators cannot be banned. |
| BR02           | Only an administrator can unban a previously banned user. |
| BR03           | Users can only receive and accept invitations from project coordinators. |
| BR04           | A user can only send messages in forums related to projects they are members of. |
| BR05           | When project members are removed from a project, they must also be removed from all task assignments related to that project. |
| BR06           | Invitations can only be sent to users that currently are not project members. |
| BR07           | Only project coordinators can archive a project. |
| BR08           | The order of a project's task tables is customizable only by the project coordinator. |
| BR09           | The position of a task table must be unique within each project. |
| BR10           | The name of a task table must be unique within each project. |
| BR11           | A user cannot have more than one component in the same position within the same project. |
| BR12           | When a project coordinator exits the project, a new coordinator is assigned if there are other project members. If no members remain, the project is automatically archived. |
| BR13           | Users can only be assigned tasks in projects they are members of. |
| BR14           | Only project coordinators can archive tasks. |
| BR15           | A user cannot be assigned the same task more than once. |
| BR16           | The position of a task must be unique within each task table. |
| BR17           | Deleted tasks must be saved in a specific "deleted tasks" table. |
| BR18           | Task deadlines cannot be set in the past when being created. |
| BR19           | Deleted tasks must be saved in a specific "deleted tasks" table. |
| BR20           | A notification must be sent to all project members when the project coordinator changes. |
| BR21           | Notifications must be sent to all assigned members and a project event with eventType = 'Task_Completed' must be created when a task is completed. |
| BR22           | Notifications must be sent and a project event with eventType = 'Task_Assigned' must be created when a task is assigned to a project member. |
| BR23           | When an invitation is accepted, a notification must be sent to all current project members to inform them of the new member’s addition to the project. |


---


## A5: Relational Schema, validation and schema refinement

> The main goal of artifact A5 is to present and validate the Relational Schema, ensuring it adheres to BCNF and includes keys, attributes, and integrity rules.

### 1. Relational Schema

> The Relational Schema includes the relation schemas, attributes, domains, primary keys, foreign keys and other integrity rules: UNIQUE, DEFAULT, NOT NULL, CHECK.  


| Relation reference | Relation Compact Notation                        |
| ------------------ | ------------------------------------------------ |
| R01      | country(<ins>id</ins>, name **NN**)|
| R02   | city(<ins>id</ins>, name **NN**, country → country)|
| R03     | account_image(<ins>id</ins>, image **NN**) |
| R04      | account(<ins>id</ins>, username **UK** **NN**, password **NN**, name **NN**, e-mail **UK** **NN**, workfield, city → city, blocked **NN** **DF** false, admin **NN** **DF** false, account_image_id → account_image **UK** ) |
| R05      | project(<ins>id</ins>, name **NN**, description, isPublic **NN** **DF** false, archived **NN** **DF** false, createDate **NN** **DF** Today **CK** createDate <= Today, finishDate **CK** finishDate NULL \|\| startDate < finishDate, projectCoordinator → account **NN**) |
| R06      | project_member(<ins>account</ins> → account, <ins>project</ins> → project, isFavourite **NN** **DF** false, forumComponent **NN** **DF** 'None', analyticsComponent **NN** **DF** 'None', membersComponent **NN** **DF** 'None', productivityComponent **NN** **DF** 'None') |
| R07      | invitation(<ins>id</ins>, project → project **NN**, account → account **NN**, accepted **NN**) |
| R08      | task_table(<ins>id</ins>, name **NN**, project → project **NN**, position **NN** **CK** position >= 0) |
| R09      | task(<ins>id</ins>, taskTable → task_table **NN**, name **NN**, description, startDate **NN** **DF** Today **CK** startDate <= Today, deliveryDate **CK** deliveryDate NULL \|\| startDate <= deliveryDate, finishDate **CK** finishDate NULL \|\| startDate <= finishDate, priority **NN**, position **NN** **CK** position >= 0) |
| R10     | account_task(<ins>account</ins> → account, <ins>task</ins> → task) |
| R11      | project_event(<ins>id</ins>, account → account **NN**, task → task **NN**, date **NN** **DF** Today **CK** date <= Today, eventType **NN**) |
| R12      | comment(<ins>id</ins>, account → account **NN**, project → project **NN**, content **NN**, createDate **NN** **DF** Today **CK** createDate <= Today, task → task **NN**) |
| R13      | forum_message(<ins>id</ins>, account → account **NN**, project → project **NN**, content **NN**, createDate **NN** **DF** Today **CK** createDate <= Today) |
| R14      | notification(<ins>id</ins>, createDate **NN** **DF** Today **CK** createDate <= Today, viewed **NN** **DF** false, emittedTo → account **NN**) |
| R15      | coordinator_notification(<ins>id</ins> → notification, notifies → project **NN**) |
| R16      | accepted_invite_notification(<ins>id</ins> → notification, notifies → project **NN**) |
| R17      | task_completed_notification(<ins>id</ins> → notification, notifies → project_event **NN**) |
| R18      | assigned_task_notification(<ins>id</ins> → notification, notifies → project_event **NN**) |

Label:

+ **UK** = UNIQUE KEY
+ **NN** = NOT NULL
+ **DF** = DEFAULT
+ **CK** = CHECK

### 2. Domains

| Domain Name | Domain Specification           |
| ----------- | ------------------------------ |
| Today    | DATE DEFAULT CURRENT_DATE  |
| Layout	      | ENUM ('None', 'RightUp', 'RightDown', 'LeftUp', 'LeftDown')     |
| Priority    | ENUM ('High', 'Medium', 'Low') |
| EventType    | ENUM ('Task_Created', 'Task_Completed', 'Task_Priority_Changed', 'Task_Deactivated', 'Task_Assigned', 'Task_Unassigned') |

### 3. Schema validation

| **TABLE R01**   | country                    |
|-----------------|-------------------------|
| **Keys**        | {id}     |
| **Functional Dependencies:**              |
| FD0101          | id → {name} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R02**   | city                    |
|-----------------|-------------------------|
| **Keys**        | {id}     |
| **Functional Dependencies:**              |
| FD0201          | id → {name, country} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R03**   | account_image                    |
|-----------------|-------------------------|
| **Keys**        | {id}     |
| **Functional Dependencies:**              |
| FD0301          | id → {image} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R04**   | account                    |
|-----------------|-------------------------|
| **Keys**        | {id}, {username}, {e-mail}     |
| **Functional Dependencies:**              |
| FD0401          | id → {username, password, name, e-mail, workfield, city, blocked, admin, account_image_id} |
| FD0402          | username → {id, password, name, e-mail, workfield, city, blocked, admin, account_image_id} |
| FD0403          | e-mail → {id, username, password, name, workfield, city, blocked, admin, account_image_id} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R05**   | project                 |
|-----------------|-------------------------|
| **Keys**        | {id}                    |
| **Functional Dependencies:**              |
| FD0501          | id → {name, description, isPublic, archived, createDate, finishDate, projectCoordinator} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R06**   | project_member           |
|-----------------|-------------------------|
| **Keys**        | {account, project}     |
| **Functional Dependencies:**              |
| FD0601          | account, project → {isFavourite, forumComponent, analyticsComponent, membersComponent, productivityComponent} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R07**   | invitation              |
|-----------------|-------------------------|
| **Keys**        | {id}, {project, account}|
| **Functional Dependencies:**              |
| FD0701          | id → {project, account, accepted} |
| FD0702          | project, account → {id, accepted} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R08**   | task_table               |
|-----------------|-------------------------|
| **Keys**        | {id}, {project, position}|
| **Functional Dependencies:**              |
| FD0801          | id → {name, project, position} |
| FD0802          | project, position → {id, name} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R09**   | task                    |
|-----------------|-------------------------|
| **Keys**        | {id}, {taskTable, position}|
| **Functional Dependencies:**              |
| FD0901          | id → {taskTable, name, description, startDate, deliveryDate, finishDate, priority, position} |
| FD0902          | taskTable, position → {id, name, description, startDate, deliveryDate, finishDate, priority} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R10**   | account_task                    |
|-----------------|-------------------------|
| **Keys**        | {account, task}, |
| **Functional Dependencies:**              |
| **NORMAL FORM** | BCNF                    |

| **TABLE R11**   | project_event            |
|-----------------|-------------------------|
| **Keys**        | {id}                    |
| **Functional Dependencies:**              |
| FD1101          | id → {account, task, date, eventType} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R12**   | comment                 |
|-----------------|-------------------------|
| **Keys**        | {id}                    |
| **Functional Dependencies:**              |
| FD1201          | id → {account, project, content, createDate, task} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R13**   | forum_message            |
|-----------------|-------------------------|
| **Keys**        | {id}                    |
| **Functional Dependencies:**              |
| FD1301          | id → {account, project, content, createDate} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R14**   | notification            |
|-----------------|-------------------------|
| **Keys**        | {id}                    |
| **Functional Dependencies:**              |
| FD1401          | id → {createDate, viewed, emittedTo} |
| **NORMAL FORM** | BCNF                    |

| **TABLE R15**   | coordinator_notification |
|-----------------|-------------------------|
| **Keys**        | {id}                    |
| **Functional Dependencies:**              |
| FD1501          | id → {notifies}         |
| **NORMAL FORM** | BCNF                    |

| **TABLE R16**   | accepted_invite_notification |
|-----------------|----------------------------|
| **Keys**        | {id}                        |
| **Functional Dependencies:**                 |
| FD1601          | id → {notifies}            |
| **NORMAL FORM** | BCNF                        |

| **TABLE R17**   | task_completed_notification |
|-----------------|----------------------------|
| **Keys**        | {id}                        |
| **Functional Dependencies:**                 |
| FD1701          | id → {notifies}            |
| **NORMAL FORM** | BCNF                        |

| **TABLE R18**   | assigned_task_notification  |
|-----------------|----------------------------|
| **Keys**        | {id}                        |
| **Functional Dependencies:**                 |
| FD1801          | id → {notifies}            |
| **NORMAL FORM** | BCNF                        |


> Justification of the BCNF: All relations are in BCNF because in each, all non-key attributes are fully functionally dependent on the primary key, with no partial or transitive dependencies. Additionally, in every functional dependency, the left-hand side is a superkey, ensuring compliance with BCNF.
Since all relations conform to Boyce–Codd Normal Form (BCNF), the relational schema as a whole is in BCNF, eliminating the need for further normalization.
---


## A6: Indexes, triggers, transactions and database population

> The main goal of artifact A6 is to implement indexes, integrity constraints, and database population, ensuring optimal performance and enforcing business rules. It includes designing indexes, defining triggers, and handling transactions, with complete SQL scripts for schema setup and data population.

### 1. Database Workload
 
> A study of the predicted system load (database load).
> Estimate of tuples at each relation.

| **Relation reference** | **Relation Name** | **Order of magnitude**        | **Estimated growth** |
| ------------------ | ------------- | ------------------------- | -------- |
| R01                | country        | 250 | no growth |
| R02                | city        | 10k | dozens per year |
| R03                | account        | 5k | hundreds per month |
| R04                | account_image        | 4k | hundreds per month |
| R05                | project        | 1k | dozens per month |
| R06                | project_member        | 10k | hundreds per month |
| R07                | invitation       | 12k | hundreds per month |
| R08                | task_table        | 5k | hundreds per month |
| R09                | task        | 50k | hundreds per month |
| R10                | account_task        | 100k | hundreds per month |
| R11                | project_event        | 200k | thousands per month |
| R12                | comment        | 25k | thousands per month |
| R13                | forum_message        | 10k | thousands per month |
| R14                | notification        | 140k | thousands per month |
| R15                | coordinator_notification        | 1k | dozens per month |
| R16                | accepted_invite_notification       | 10k | hundreds per month |
| R17                | task_completed_notification        | 29k | hundreds per month |
| R18                | assigned_task_notification        | 100k | hundreds per month |


### 2. Proposed Indices

#### 2.1. Performance Indices
 
> Indices proposed to improve performance of the identified queries.

| **Index**           | IDX01                                  |
| ---                 | ---                                    |
| **Relation**        | notification    |
| **Attribute**       |  emittedTo  |
| **Type**            | B-tree             |
| **Cardinality**     | Medium |
| **Clustering**      | No                |
| **Justification**   | Given the large volume of notifications in the system, a B-tree index on emittedTo will significantly improve the performance of queries where users retrieve their own notifications. This attribute is frequently used in filtering, such as when showing notifications specific to a user. The cardinality is medium because although each user has a unique identifier, a single user can receive many notifications, resulting in several rows with the same emittedTo value. Clustering is not recommended because notifications are frequently inserted, and constant updates would make maintaining the physical order of the table inefficient. As a result, clustering would add unnecessary overhead.  |
| `SQL code`                                                  |`CREATE INDEX notification_user ON notification USING btree(emitted_to);`|


| **Index**           | IDX02                                  |
| ---                 | ---                                    |
| **Relation**        | comment    |
| **Attribute**       |  task  |
| **Type**            | B-tree             |
| **Cardinality**     | Medium |
| **Clustering**      | No                |
| **Justification**   | A B-tree index on task will enhance the performance of queries that retrieve comments related to a specific task. Since users frequently fetch comments associated with tasks, indexing the task attribute will speed up these queries. The cardinality is medium because a task can have multiple comments, resulting in several rows sharing the same task value. Clustering is not recommended because comments are often added in real-time, and keeping the table physically ordered would introduce unnecessary maintenance overhead without providing significant query performance gains. |
| `SQL code`                                                  |`CREATE INDEX comments_task ON comment USING btree(task);`|


| **Index**           | IDX03                                  |
| ---                 | ---                                    |
| **Relation**        | project_event    |
| **Attribute**       |  task  |
| **Type**            | B-tree             |
| **Cardinality**     | Medium |
| **Clustering**      | No                |
| **Justification**   |A B-tree index on task will enhance the performance of queries that retrieve project events related to a specific task. This will allow us to associate all project events to its respective task, and therefore project, which would be useful for quickly displaying the project timeline. The cardinality is medium because multiple project_events can share the same task.  Clustering is not recommended due to frequent insertions and keeping the table physically ordered would introduce unnecessary maintenance overhead without providing significant query performance gains.   |
| `SQL code`                                                  |`CREATE INDEX projectEvent_task ON project_event USING btree(task);`|

| **Index**           | IDX04                                  |
| ---                 | ---                                    |
| **Relation**        |   task  |
| **Attribute**       |  task_table  |
| **Type**            | B-tree             |
| **Cardinality**     | Medium |
| **Clustering**      | No                |
| **Justification**   | The B-tree index on task_table in the task relation facilitates identifying the task table to which each task belongs, which is essential for locating the associated project. The cardinality is medium as multiple tasks can belong to the same task table,  resulting in several rows sharing the same task_table value. Clustering is not used to avoid physical reordering overhead due to frequent task insertions/updates. |
| `SQL code`                                                  |`CREATE INDEX task_taskTable ON task USING btree(task_table);`|

| **Index**           | IDX05                                  |
| ---                 | ---                                    |
| **Relation**        |  task_table  |
| **Attribute**       |  project  |
| **Type**            | B-tree             |
| **Cardinality**     | Medium |
| **Clustering**      | Yes                |
| **Justification**   | This B-tree index on the project attribute in task_table enables direct identification of the project to which each task table belongs. The cardinality is medium, as a task table belongs to only one project, though a single project may contain multiple task tables,  resulting in several rows sharing the same project value. It is a good candidate for clustering since this table is not frequently updated. |
| `SQL code`                                                  |`CREATE INDEX taskTable_project ON task_table USING btree(project);`|


#### 2.2. Full-text Search Indices 

> The system being developed must provide full-text search features supported by PostgreSQL. Thus, it is necessary to specify the fields where full-text search will be available and the associated setup, namely all necessary configurations, indexes definitions and other relevant details.  

| **Index**           | IDX01                                  |
| ---                 | ---                                    |
| **Relation**        |project   |
| **Attribute**       | name, description   |
| **Type**            | GiST             |
| **Clustering**      | No                |
| **Justification**   | This index allows fast and efficient keyword searches on project names and descriptions, improving full-text search (FTS) performance when users look for relevant projects. The index type is GiST because new projects are expected to be created often.   |
> SQL code:
``` sql 
DROP FUNCTION IF EXISTS project_search_update_tsvector();
CREATE FUNCTION project_search_update_tsvector() RETURNS TRIGGER AS $$
BEGIN
   IF TG_OP = 'INSERT' THEN
      NEW.tsvectors := 
         setweight(to_tsvector('english', coalesce(NEW.name, ' ')), 'A') ||
         setweight(to_tsvector('english', coalesce(NEW.description, ' ')), 'B');
      RETURN NEW;
   END IF;


   IF TG_OP = 'UPDATE' THEN
      IF NEW.name <> OLD.name OR NEW.description <> OLD.description THEN
         NEW.tsvectors := 
            setweight(to_tsvector('english', coalesce(NEW.name, ' ')), 'A') ||
            setweight(to_tsvector('english', coalesce(NEW.description, ' ')), 'B');
      END IF;
      RETURN NEW;
   END IF;

   RETURN NULL; 
END;
$$ LANGUAGE plpgsql;

-- Trigger to update tsvectors before INSERT or UPDATE on the project table
CREATE TRIGGER project_search_update
   BEFORE INSERT OR UPDATE ON project
   FOR EACH ROW
   EXECUTE PROCEDURE project_search_update_tsvector();

-- Create a GiST index on the tsvectors column 
CREATE INDEX project_search_idx ON project USING GiST(tsvectors);

```

| **Index**           | IDX02                                  |
| ---                 | ---                                    |
| **Relation**        |task   |
| **Attribute**       | name, description   |
| **Type**            | GiST             |
| **Clustering**      | No                |
| **Justification**   | This index improves task search by task names and descriptions, optimizing keyword-based searches, making it easier to navigate through large volumes of tasks. The index type is GiST because new tasks are expected to be created daily.   |
> SQL code:
``` sql 

ALTER TABLE task ADD COLUMN tsvectors TSVECTOR;


-- Function to update the tsvectors field for task
DROP FUNCTION IF EXISTS task_search_update_tsvector();
CREATE FUNCTION task_search_update_tsvector() RETURNS TRIGGER AS $$
BEGIN
   IF TG_OP = 'INSERT' THEN
      NEW.tsvectors := 
         setweight(to_tsvector('english', coalesce(NEW.name, '')), 'A') || 
         setweight(to_tsvector('english', coalesce(NEW.description, '')), 'B');
      RETURN NEW;
   END IF;


   IF TG_OP = 'UPDATE' THEN
      IF NEW.name <> OLD.name OR NEW.description <> OLD.description THEN
         NEW.tsvectors := 
            setweight(to_tsvector('english', coalesce(NEW.name, '')), 'A') || 
            setweight(to_tsvector('english', coalesce(NEW.description, '')), 'B');
      	END IF;
	RETURN NEW;
   END IF;


   RETURN NULL; 
END;
$$ LANGUAGE plpgsql;


-- Trigger to update tsvectors before INSERT or UPDATE on the task table
CREATE TRIGGER task_search_update 
   BEFORE INSERT OR UPDATE ON task 
   FOR EACH ROW 
   EXECUTE PROCEDURE task_search_update_tsvector();


-- Create a GiST index on the tsvectors column
CREATE INDEX task_search_idx ON task USING GIN(tsvectors);
```


### 3. Triggers
 
> User-defined functions and trigger procedures that add control structures to the SQL language or perform complex computations, are identified and described to be trusted by the database server. Every kind of function (SQL functions, Stored procedures, Trigger procedures) can take base types, composite types, or combinations of these as arguments (parameters). In addition, every kind of function can return a base type or a composite type. Functions can also be defined to return sets of base or composite values.  

| **Trigger**      | TRIGGER01                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR01, ensuring that administrators cannot be banned. It raises an exception if an attempt is made to set `blocked = TRUE` for an account with `admin = TRUE` or if an attempt is made to assign administrator status to a banned account. |
> SQL CODE
      
```sql
CREATE OR REPLACE FUNCTION check_account_ban() RETURNS TRIGGER AS $BODY$
BEGIN
   IF NEW.blocked = TRUE AND NEW.admin = TRUE AND OLD.blocked IS DISTINCT FROM NEW.blocked THEN
      RAISE EXCEPTION 'Administrators cannot be banned';
   END IF;
   IF NEW.admin = TRUE AND NEW.blocked = TRUE AND OLD.admin IS DISTINCT FROM NEW.admin THEN
      RAISE EXCEPTION 'A banned account cannot be set as an administrator';
   END IF;
RETURN NEW;
END
$BODY$ LANGUAGE plpgsql;

CREATE TRIGGER check_account_ban
   BEFORE UPDATE OF blocked, admin
   ON "account"
   FOR EACH ROW
   EXECUTE PROCEDURE check_account_ban();
```

---

| **Trigger**      | TRIGGER02                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR04, ensuring that users can only send messages in forums related to projects they are members of. It raises an exception if a user attempts to post in a forum for a project they are not part of. |
> SQL CODE
      
```sql
CREATE OR REPLACE FUNCTION check_account_in_project_for_forum_message() RETURNS TRIGGER AS $$
BEGIN
   IF NOT EXISTS (
      SELECT 1 
      FROM project_member 
      WHERE account = NEW.account AND project = NEW.project
   ) THEN
      RAISE EXCEPTION 'User can only send messages in forums related to projects they are members of';
   END IF;
   RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER check_account_in_project_for_forum_message
   BEFORE INSERT ON forum_message
   FOR EACH ROW
   EXECUTE PROCEDURE check_account_in_project_for_forum_message();
```

---

| **Trigger**      | TRIGGER03                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR05, ensuring that when project members are removed from a project, they are also removed from all task assignments related to that project.|
> SQL CODE
      
```sql
CREATE OR REPLACE FUNCTION delete_user_tasks() RETURNS TRIGGER AS $$
DECLARE 
    task_id INT;
BEGIN
    FOR task_id IN
        SELECT task_.id 
        FROM account_task at
        JOIN task task_ ON at.task = task_.id
        JOIN task_table tasktable ON task_.task_table = tasktable.id 
        WHERE at.account = OLD.account 
        AND tasktable.project = OLD.project
    LOOP
        DELETE FROM account_task
        WHERE account = OLD.account AND task = task_id;
        INSERT INTO project_event (account, task, eventType) 
        VALUES (OLD.account, task_id, 'Task_Unassigned'); 
    END LOOP;

    RETURN OLD;
END 
$$ LANGUAGE plpgsql;

CREATE TRIGGER delete_project_member
AFTER DELETE ON project_member
FOR EACH ROW
EXECUTE FUNCTION delete_user_tasks();
```

---

| **Trigger**      | TRIGGER04                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR09, ensuring that users can only be assigned tasks within projects they are members of. It raises an exception if a user is assigned a task in a project they do not belong to. |
> SQL CODE
      
```sql
CREATE OR REPLACE FUNCTION check_account_membership_in_project() RETURNS TRIGGER AS $$
BEGIN
   IF NOT EXISTS (
     SELECT 1 
     FROM project_member pm
     JOIN task t ON t.id = NEW.task
     JOIN task_table tt ON t.task_table = tt.id
     WHERE pm.account = NEW.account 
       AND pm.project = tt.project
   ) THEN
      RAISE EXCEPTION 'User is not a member of the project related to this task';
   END IF;
   RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER check_account_membership_in_project
   BEFORE INSERT OR UPDATE ON account_task
   FOR EACH ROW
   EXECUTE PROCEDURE check_account_membership_in_project();
```

---

| **Trigger**      | TRIGGER05                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR11, ensuring that a user cannot have multiple components in the same position within the same project. It raises an exception if a component layout position is already in use by another component within the same project. |
> SQL CODE
      
```sql
CREATE OR REPLACE FUNCTION check_unique_component_layout() RETURNS TRIGGER AS $$
BEGIN
   IF NEW.forum_component != 'None' AND 
      (NEW.forum_component = NEW.analytics_component 
       OR NEW.forum_component = NEW.members_component 
       OR NEW.forum_component = NEW.productivity_component) THEN
      RAISE EXCEPTION 'Forum component layout is already in use by another component in this project';
   END IF;

   IF NEW.analytics_component != 'None' AND 
      (NEW.analytics_component = NEW.forum_component 
       OR NEW.analytics_component = NEW.members_component 
       OR NEW.analytics_component = NEW.productivity_component) THEN
      RAISE EXCEPTION 'Analytics component layout is already in use by another component in this project';
   END IF;

   IF NEW.members_component != 'None' AND 
      (NEW.members_component = NEW.forum_component 
       OR NEW.members_component = NEW.analytics_component 
       OR NEW.members_component = NEW.productivity_component) THEN
      RAISE EXCEPTION 'Members component layout is already in use by another component in this project';
   END IF;

   IF NEW.productivity_component != 'None' AND 
      (NEW.productivity_component = NEW.forum_component 
       OR NEW.productivity_component = NEW.analytics_component 
       OR NEW.productivity_component = NEW.members_component) THEN
      RAISE EXCEPTION 'Productivity component layout is already in use by another component in this project';
   END IF;
   RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER check_unique_component_layout
   BEFORE INSERT OR UPDATE ON project_member
   FOR EACH ROW
   EXECUTE PROCEDURE check_unique_component_layout();
```

---

| **Trigger**      | TRIGGER06                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR12, ensuring that when a project coordinator exits a project, a new coordinator is assigned if there are other project members, or the project is archived if no members remain. |
> SQL CODE
      
```sql
CREATE OR REPLACE FUNCTION handle_coordinator_exit() RETURNS TRIGGER AS $$
DECLARE
 remaining_member INT;
BEGIN
 IF OLD.account IN (SELECT project_coordinator_id
                   FROM project
                   WHERE id = OLD.project) THEN
    SELECT account INTO remaining_member
    FROM project_member
    WHERE project = OLD.project AND account != OLD.account 
    LIMIT 1;

   IF remaining_member IS NOT NULL THEN
       UPDATE project
       SET project_coordinator_id = remaining_member
       WHERE id = OLD.project;
   ELSE
       UPDATE project
       SET archived = TRUE
       WHERE id = OLD.project;
   END IF;
 END IF;

 RETURN OLD;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER coordinator_exit_trigger
   BEFORE DELETE ON project_member
   FOR EACH ROW
   EXECUTE FUNCTION handle_coordinator_exit();
```

---

| **Trigger**      | TRIGGER07                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR13, ensuring that a user cannot be assigned the same task more than once. It raises an exception if a duplicate assignment is attempted. |
> SQL CODE

```sql
CREATE OR REPLACE FUNCTION check_account_assigned_once() RETURNS TRIGGER AS $$
BEGIN
   IF EXISTS (
      SELECT 1 
      FROM account_task 
      WHERE account = NEW.account AND task = NEW.task
   ) THEN
      RAISE EXCEPTION 'User is already assigned to this task';
   END IF;
   RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER check_account_assigned_once
   BEFORE INSERT ON account_task
   FOR EACH ROW
   EXECUTE PROCEDURE check_account_assigned_once();
```

---

| **Trigger**      | TRIGGER08                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR18, ensuring that notifications are sent to all assigned members when a task is completed. It also logs a project event with `eventType = 'Task_Completed'`. |
> SQL CODE
      
```sql
CREATE OR REPLACE FUNCTION notify_task_update() RETURNS TRIGGER AS $$
DECLARE
   notification_id INT;
   account INT;
   rec INT;
   event INT;
BEGIN
   IF NEW.finish_date IS NOT NULL AND OLD.finish_date IS NULL THEN
	FOR rec IN
		SELECT account_task.account 
		FROM account_task
		WHERE task = NEW.id
	LOOP
		INSERT INTO notification (create_date, viewed, emitted_to)
      	VALUES (CURRENT_DATE, FALSE, rec)
      	RETURNING id INTO notification_id;
		
		INSERT INTO project_event(account, task, eventType)
		VALUES (rec, NEW.id, 'Task_Completed')
		RETURNING id INTO event;

		INSERT INTO task_completed_notification (id, notifies)
         	VALUES (notification_id, event);
      END LOOP;
   END IF;
   RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER notify_task_update
   AFTER UPDATE ON task
   FOR EACH ROW
   EXECUTE PROCEDURE notify_task_update();
```

---

| **Trigger**      | TRIGGER09                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR19, ensuring that invitations are only sent to users who are not current project members. It raises an exception if an invitation is sent to a current member. |
> SQL CODE
      
```sql
CREATE OR REPLACE FUNCTION check_proj_member() RETURNS TRIGGER AS $BODY$
BEGIN
   IF EXISTS (SELECT 1 FROM project_member WHERE NEW.account = project_member.account AND NEW.project = project_member.project) THEN
       RAISE EXCEPTION 'User is already a project member';
   END IF;
RETURN NEW;
END
$BODY$ LANGUAGE plpgsql;

CREATE TRIGGER check_proj_member
   BEFORE INSERT ON invitation
   FOR EACH ROW
   EXECUTE PROCEDURE check_proj_member();
```

---


| **Trigger**      | TRIGGER10                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR21, ensuring that notifications are sent to all assigned members and a project event with eventType = 'Task_Completed' is created when a task is completed. It raises an exception if the new project coordinator is not a member of the project. |
> SQL CODE      
```sql
CREATE OR REPLACE FUNCTION notify_project_coordinator_change() RETURNS TRIGGER AS $$
DECLARE
   notification_id INT;
   rec INT;
BEGIN
   IF EXISTS (
      SELECT 1 
      FROM project_member
      WHERE account = NEW.project_coordinator_id
      AND project = NEW.id
   ) THEN
      IF OLD.project_coordinator_id IS DISTINCT FROM NEW.project_coordinator_id THEN
         FOR rec IN
            SELECT project_member.account FROM project_member WHERE project = NEW.id
         LOOP
            INSERT INTO notification (create_date, viewed, emitted_to)
            VALUES (CURRENT_DATE, FALSE, rec)
            RETURNING id INTO notification_id;
         
            INSERT INTO coordinator_notification (id, notifies)
            VALUES (notification_id, NEW.id);
         END LOOP;
      END IF;
      RETURN NEW;
   END IF;
   RAISE EXCEPTION 'Project coordinator is not member of project';
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER notify_project_coordinator_change
   BEFORE UPDATE OF project_coordinator_id ON project
   FOR EACH ROW
   EXECUTE PROCEDURE notify_project_coordinator_change();
```

---

| **Trigger**      | TRIGGER11                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR22, ensuring that notifications are sent when a task is completed and a project event with eventType = 'Task_Assigned' is created when a task is assigned to a project member. |
> SQL CODE      
```sql
CREATE OR REPLACE FUNCTION notify_task_assigned() RETURNS TRIGGER AS $$
DECLARE
   notification_id INT;
   event INT;
BEGIN
   IF NOT EXISTS(
      SELECT 1 
      FROM account_task
      WHERE account = NEW.account
      AND task = NEW.task
   ) THEN
      INSERT INTO notification (create_date, viewed, emitted_to)
      VALUES (CURRENT_DATE, FALSE, NEW.account)
      RETURNING id INTO notification_id;
      
      INSERT INTO project_event(account, task, eventType)
      VALUES (NEW.account, NEW.task, 'Task_Assigned')
      RETURNING id INTO event;

      INSERT INTO assigned_task_notification (id, notifies)
      VALUES (notification_id, event);
   END IF;
   RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER notify_task_assigned
   BEFORE INSERT ON account_task
   FOR EACH ROW
   EXECUTE PROCEDURE notify_task_assigned();
```

---

| **Trigger**      | TRIGGER12                              |
|------------------|----------------------------------------|
| **Description**  | This trigger enforces BR23, ensuring that when an invitation is accepted, a notification is sent to all current project members to inform them of the new member’s addition to the project. |
> SQL CODE      
```sql
CREATE OR REPLACE FUNCTION notify_accepted_invitation() RETURNS TRIGGER AS $$
DECLARE
   notification_id INT;
   rec INT;
BEGIN
   INSERT INTO project_member(account, project) VALUES (NEW.account, NEW.project);
  
   FOR rec IN
      SELECT project_member.account FROM project_member WHERE project = NEW.project
   LOOP
      INSERT INTO notification (emitted_to) VALUES (rec)
      RETURNING id INTO notification_id;
      INSERT INTO accepted_invite_notification (id, notifies) VALUES (notification_id, NEW.project);
   END LOOP;
   RETURN NEW;
END
$$ LANGUAGE plpgsql;

CREATE TRIGGER notify_accepted_invitation
   AFTER UPDATE OF accepted
   ON "invitation"
   FOR EACH ROW
   EXECUTE PROCEDURE notify_accepted_invitation();
```


### 4. Transactions
 
> Transactions needed to assure the integrity of the data.  

| TRANS01   | Delete account                    |
| --------------- | ----------------------------------- |
| Justification   | When a user account is deleted, all  account information must be removed from the system, however the public interactions made by the user, such as forum messages and comments,  must be kept and associated with an anonymous account. The isolation level is READ COMMITED to prevent potential issues like concurrent transaction modifing or inserting data related to $account_id such as notifications or tasks.  |
| Isolation level | READ COMMITED |
> Complete SQL Code   
```sql
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL READ COMMITTED;


UPDATE comment
SET account = (SELECT id FROM account WHERE username = 'unknown')
WHERE account = $account_id;

UPDATE forum_message
SET account = (SELECT id FROM account WHERE username = 'unknown')
WHERE account = $account_id;

UPDATE project_event
SET account = (SELECT id FROM account WHERE username = 'unknown')
WHERE account = $account_id;


DELETE FROM notifications WHERE emittedTo = $account_id;

DELETE FROM invitation WHERE account = $account_id;
DELETE FROM project_member WHERE account = $account_id;
DELETE FROM account_task WHERE account = $account_id;
DELETE FROM account_image WHERE account = $account_id;
DELETE FROM account WHERE id = $account_id;

COMMIT;

```                        


| TRANS02   | Create task                    |
| --------------- | ----------------------------------- |
| Justification   | This transaction ensures that when a new task is created the project event is also stored. The isolation level is set to SERIAZABLE to ensure that no other concurrent transaction can insert tasks with the same position in the same task_table, which guarantees consistent task ordering.  |
| Isolation level | SERIAZABLE |
> Complete SQL Code   
```sql
BEGIN TRANSACTION;


SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;


WITH new_task AS (
   INSERT INTO task (task_table, name, description, start_date, delivery_date, finish_date, priority, position)
   VALUES ($task_table, $name, $description, $start_date, $delivery_date, $finish_date, $priority,
   (SELECT (COALESCE(MAX(position), 0) + 1) FROM task WHERE task_table = $task_table)
)
   RETURNING id
)


-- Insert a corresponding project event record for the task creation
INSERT INTO project_event (account, task, date, event_type)
VALUES ($account, (SELECT id FROM new_task), $date, 'Task_Created');


COMMIT;

```      

| TRANS03   | Create new project                    |
| --------------- | ----------------------------------- |
| Justification   | This transaction ensures that the user that creates a project is its project coordinator. Isolation level is set to REPEATABLE READ to prevent issues like phantom reads, ensuring that no other transactions can add or modify project data within this transaction, for example, it ensures the project ID generated in new_project is reliably used in subsequent inserts to project_member and task_table.  |
| Isolation level | REPETABLE READ |
> Complete SQL Code   
```sql
BEGIN TRANSACTION;


TRANSACTION ISOLATION LEVEL REPEATABLE READ


WITH new_project AS(
INSERT INTO project (name, description, project_coordinator) VALUES ($name, $description, $new_member_id)
RETURNING id)


INSERT INTO project_member (account, project) VALUES ($new_member_id, (SELECT id FROM new_project));


INSERT INTO task_table (name, project, position) VALUES ("Deleted Tasks", (SELECT id FROM new_project), 1);


COMMIT;

```          

| TRANS04   | Delete task                    |
| --------------- | ----------------------------------- |
| Justification   | This transaction ensures that once a task is removed a project event related to this change is created. The READ COMMITTED isolation level ensures that only committed data is read during the transaction, which prevents inconsistent reads while maintaining performance. This level is appropriate here, as no complex dependencies or sequential updates require stricter isolation.   |
| Isolation level | READ COMMITED |    
       
> Complete SQL Code   
```sql
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL READ COMMITTED

UPDATE task
SET task_table = (SELECT id FROM task_table WHERE project = $project_id AND name = "Deleted Tasks")
WHERE id = $task_id;


INSERT INTO project_event (account, task, date, event_type) ($account_id, $task_id, CURRENT_DATE, 'Task_Deactivated');

COMMIT;

``` 

| TRANS05   | Change priority                    |
| --------------- | ----------------------------------- |
| Justification   | Changing the priority of a task requires creating a new project event related to this change. The READ COMMITTED isolation level is suitable here because it allows for the transaction to read only committed data, ensuring that the priority update and project event creation operate on stable data without blocking other transactions unnecessarily.|
| Isolation level | READ COMMITED |    
       
> Complete SQL Code   
```sql
BEGIN TRANSACTION;

SET TRANSACTION ISOLATION LEVEL READ COMMITTED;

UPDATE task
SET priority = $new_priority
WHERE id = $task_id ;

INSERT INTO project_event (account, task, date, eventType) VALUES ($account_id, $task_id, CURRENT_DATE, 'Task_Priority_Changed');

COMMIT;

``` 


| TRANS06   | Change task position in the same task table                   |
| --------------- | ----------------------------------- |
| Justification   | When a task changes its position in the same task table we need to ensure that others tasks from the same table changes its position accordingly. REPEATABLE READ ensures that once the transaction reads the positions and task table data, it will see the same data throughout the transaction, even if other transactions are running concurrently, helping to prevent "phantom reads".|
| Isolation level | REPEATABLE READ |    
       
> Complete SQL Code   
```sql
BEGIN TRANSACTION;
SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
DO $$
DECLARE
	old_task_table INT;
	old_position INT;
  i INT;


begin
	select task_table into old_task_table from task where id = $task_id;
	select position into old_position from task where id = $task_id;


UPDATE task
SET position = (SELECT (COALESCE(MAX(position), 0) + 1) FROM task WHERE task_table = $task_table)
WHERE id = $task_id;


for i in (SELECT id FROM task
		  WHERE task_table = $task_table
		  AND position >= $new_position
		  AND position < old_position
		  ORDER BY position DESC)
loop
	UPDATE task 
	SET position = task.position + 1
	WHERE id = i;
end loop;


for i in (SELECT id FROM task
		  WHERE task_table = old_task_table
		  AND position <= $new_position
		  AND position > old_position
		  ORDER BY position ASC)
loop
	UPDATE task 
	SET position = task.position - 1
	WHERE id = i;
end loop;


UPDATE task
SET position = $new_position
WHERE id = $task_id;


END $$;
COMMIT;

``` 

| TRANS07   | Change task position to other tasktable                |
| --------------- | ----------------------------------- |
| Justification   | When a task changes its task table position we need to ensure that others tasks from the same table changes its position accordingly. By utilizing REPEATABLE READ, the transaction prevents other concurrent transactions from modifying the task positions or task tables.|
| Isolation level | REPEATABLE READ |    
       
> Complete SQL Code   
```sql
BEGIN TRANSACTION;
SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
DO $$
DECLARE
	old_task_table INT;
	old_position INT;
  i INT;


begin
	select task_table into old_task_table from task where id = $task_id;
	select position into old_position from task where id = $task_id;


for i in (SELECT id FROM task
		  WHERE task_table = $new_task_table
		  AND position >= $new_position
		  ORDER BY position DESC)
loop
	UPDATE task 
	SET position = task.position + 1
	WHERE id = i;
end loop;


UPDATE task 
SET task_table = $new_task_table, position = $new_position
WHERE id = $task_id;




for i in (SELECT id FROM task
		  WHERE task_table = old_task_table
		  AND position > old_position
		  ORDER BY position ASC)
loop
	UPDATE task 
	SET position = task.position - 1
	WHERE id = i;
end loop;


END $$;
COMMIT;


```

| TRANS08   | Change task table position                   |
| --------------- | ----------------------------------- |
| Justification   | When the project coordinator changes the position of a task table we need to ensure that others tasks tables from the same poject changes its position accordingly. By using REPEATABLE READ, we prevent phantom reads and ensure that no other concurrent transactions can modify the task table positions until this transaction is completed.|
| Isolation level | REPEATABLE READ |    
       
> Complete SQL Code   
```sql
BEGIN TRANSACTION;
SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
DO $$
DECLARE
	old_position INT;
	project_coordinator INT;
	i INT;


BEGIN
	SELECT project_coordinator_id INTO project_coordinator
	FROM project
	WHERE id = $project_id;


	IF project_coordinator <> $account_id THEN 
		RAISE EXCEPTION 'Only the project coordinator can change task table positions.'; 
END IF;


SELECT position INTO old_position
FROM task_table
WHERE id = $task_table_id
AND project =  $project_id;


UPDATE task_table
		SET position = (SELECT (COALESCE(MAX(position), 0) + 1)FROM task_table WHERE project = $project_id)
		WHERE id = $task_table_id;


for i in (SELECT id FROM task_table
		  WHERE project = $project_id
		  AND position >= $new_position 
        AND position < old_position
		  ORDER BY position DESC)
loop
	UPDATE task_table
	SET position = task_table.position + 1
	WHERE id = i;
end loop;


for i in (SELECT id FROM task_table
		  WHERE project = $project_id
		  AND position > old_position 
		  AND position <= $new_position
		  ORDER BY position ASC)
loop
	UPDATE task_table
	SET position = task_table.position - 1
	WHERE id = i;
end loop;


UPDATE task_table
SET position = $new_position
WHERE id = $task_table_id;


END $$;		
COMMIT;

``` 
## Annex A. SQL Code

> The database scripts are included in this annex to the EBD component.
> 
> The database creation script and the population script should be presented as separate elements.
> The creation script includes the code necessary to build (and rebuild) the database.
> The population script includes an amount of tuples suitable for testing and with plausible values for the fields of the database.
>
> The complete code of each script must be included in the group's git repository and links added here.

### A.1. Database schema

```sql
-- Cleaning up the current database state
DROP SCHEMA IF EXISTS lbaw2444 CASCADE;
CREATE SCHEMA IF NOT EXISTS lbaw2444;
SET search_path TO lbaw2444;




-- Layout type definition
CREATE TYPE Layout AS ENUM('None', 'RightUp', 'RightDown', 'LeftUp', 'LeftDown');




-- Priority domain definition
CREATE TYPE Priority AS ENUM('High', 'Medium', 'Low');




-- EventType domain definition
CREATE TYPE EventType AS ENUM('Task_Created', 'Task_Completed', 'Task_Priority_Changed', 'Task_Deactivated', 'Task_Assigned', 'Task_Unassigned');




-- NotificationType domain definition
CREATE TYPE NotificationType AS ENUM('Coordinator_Change', 'Accepted_Invite', 'Task_Completed', 'Assigned_Task');






-- R01: country
CREATE TABLE country (
   id SERIAL PRIMARY KEY,
   name VARCHAR(255) NOT NULL
);




-- R02: city
CREATE TABLE city (
   id SERIAL PRIMARY KEY,
   name VARCHAR(255) NOT NULL,
   country INT NOT NULL REFERENCES country(id) ON DELETE CASCADE
);




-- R03: account_image
CREATE TABLE account_image (
   id serial PRIMARY KEY,
   image BYTEA NOT NULL
);








-- R04: account
CREATE TABLE account (
   id SERIAL PRIMARY KEY,
   username VARCHAR(255) UNIQUE NOT NULL,
   password VARCHAR(255) NOT NULL,
   name VARCHAR(255) NOT NULL,
   email VARCHAR(255) UNIQUE NOT NULL,
   workfield VARCHAR(255),
   city INT REFERENCES city(id) ON DELETE SET NULL,
   blocked BOOLEAN NOT NULL DEFAULT FALSE,
   admin BOOLEAN NOT NULL DEFAULT FALSE,
   account_image_id INT UNIQUE REFERENCES account_image(id) ON DELETE SET NULL
);




-- R05: project
CREATE TABLE project (
   id SERIAL PRIMARY KEY,
   name VARCHAR(255) NOT NULL,
   description TEXT,
   isPublic BOOLEAN NOT NULL DEFAULT FALSE,
   archived BOOLEAN NOT NULL DEFAULT FALSE,
   createDate DATE NOT NULL DEFAULT CURRENT_DATE CHECK (createDate <= CURRENT_DATE),
   finishDate DATE CHECK (finishDate IS NULL OR createDate < finishDate),
   project_coordinator_id INT NOT NULL REFERENCES account(id)
);




-- R06: project_member
CREATE TABLE project_member (
   account INT REFERENCES account(id) ON DELETE CASCADE,
   project INT REFERENCES project(id) ON DELETE CASCADE,
   is_favourite BOOLEAN NOT NULL DEFAULT FALSE,
   forum_component Layout NOT NULL DEFAULT 'None',
   analytics_component Layout NOT NULL DEFAULT 'None',
   members_component Layout NOT NULL DEFAULT 'None',
   productivity_component Layout NOT NULL DEFAULT 'None',
   PRIMARY KEY (account, project)
);




-- R07: invitation
CREATE TABLE invitation (
   id SERIAL PRIMARY KEY,
   project INT NOT NULL REFERENCES project(id) ON DELETE CASCADE,
   account INT NOT NULL REFERENCES account(id) ON DELETE CASCADE,
   accepted BOOLEAN NOT NULL DEFAULT FALSE,
   CONSTRAINT unique_project_account UNIQUE (project, account)
);




-- R08: task_table
CREATE TABLE task_table (
   id SERIAL PRIMARY KEY,
   name VARCHAR(255) NOT NULL,
   project INT NOT NULL REFERENCES project(id) ON DELETE CASCADE,
   position INT NOT NULL CHECK (position >= 0),
   CONSTRAINT unique_project_position UNIQUE (project, position),
   CONSTRAINT unique_project_name UNIQUE (project, name)
);




-- R09: task
CREATE TABLE task (
   id SERIAL PRIMARY KEY,
   task_table INT NOT NULL REFERENCES task_table(id),
   name VARCHAR(255) NOT NULL,
   description TEXT,
   start_date DATE NOT NULL DEFAULT CURRENT_DATE CHECK (start_date <= CURRENT_DATE),
   delivery_date DATE CHECK (delivery_date IS NULL OR start_date <= delivery_date),
  finish_date DATE CHECK (finish_date IS NULL OR start_date <= finish_date),
   priority Priority NOT NULL,
   position INT NOT NULL CHECK (position >= 0),
   CONSTRAINT unique_task_table_position UNIQUE (task_table, position)
);




-- R10: account_task
CREATE TABLE account_task (
   account INT REFERENCES account(id) ON DELETE CASCADE,
   task INT REFERENCES task(id) ON DELETE CASCADE,
   PRIMARY KEY (account, task)
);




-- R11: project_event
CREATE TABLE project_event (
   id SERIAL PRIMARY KEY,
   account INT NOT NULL REFERENCES account(id),
   task INT NOT NULL REFERENCES task(id),
   date DATE NOT NULL DEFAULT CURRENT_DATE CHECK (date <= CURRENT_DATE),
   eventType VARCHAR(255) NOT NULL
);




-- R12: comment
CREATE TABLE comment (
   id SERIAL PRIMARY KEY,
   account INT NOT NULL REFERENCES account(id),
   project INT NOT NULL REFERENCES project(id),
   content TEXT NOT NULL,
   create_date DATE NOT NULL DEFAULT CURRENT_DATE CHECK (create_date <= CURRENT_DATE),
   task INT NOT NULL REFERENCES task(id)
);




-- R13: forum_message
CREATE TABLE forum_message (
   id SERIAL PRIMARY KEY,
   account INT NOT NULL REFERENCES account(id),
   project INT NOT NULL REFERENCES project(id),
   content TEXT NOT NULL,
   create_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (create_date <= CURRENT_TIMESTAMP)
);




-- R14: notification
CREATE TABLE notification (
   id SERIAL PRIMARY KEY,
   type NotificationType NOT NULL,
   create_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (create_date <= CURRENT_TIMESTAMP),
   viewed BOOLEAN NOT NULL DEFAULT FALSE,
   emitted_to INT NOT NULL REFERENCES account(id) ON DELETE CASCADE,
   project INT REFERENCES project(id) CHECK ((type IN('Coordinator_Change', 'Accepted_Invite') AND project != NULL) OR project IS NULL),
   project_event INT REFERENCES project_event(id) CHECK ((type IN('Task_Completed', 'Assigned_Task') AND project_event != NULL) OR project_event IS NULL)
);




-----------------------------------------
-- INDEXES
-----------------------------------------




CREATE INDEX notification_user ON notification USING btree(emitted_to);




CREATE INDEX comments_task ON comment USING btree(task);




CREATE INDEX projectEvent_task ON project_event USING btree(task);




CREATE INDEX task_taskTable ON task USING btree(task_table);




CREATE INDEX taskTable_project ON task_table USING btree(project);




-----------------------------------------
-- FULL-TEXT SEARCH INDEXES
----------------------------------------
--Full-text search index 1




-- Add the tsvectors column of type TSVECTOR
ALTER TABLE project ADD COLUMN tsvectors TSVECTOR;




-- Function to update the tsvectors field
DROP FUNCTION IF EXISTS project_search_update_tsvector();
CREATE FUNCTION project_search_update_tsvector() RETURNS TRIGGER AS $$
BEGIN
  IF TG_OP = 'INSERT' THEN
     NEW.tsvectors :=
        setweight(to_tsvector('english', coalesce(NEW.name, ' ')), 'A') ||
        setweight(to_tsvector('english', coalesce(NEW.description, ' ')), 'B');
     RETURN NEW;
  END IF;




  IF TG_OP = 'UPDATE' THEN
     IF NEW.name <> OLD.name OR NEW.description <> OLD.description THEN
        NEW.tsvectors :=
           setweight(to_tsvector('english', coalesce(NEW.name, ' ')), 'A') ||
           setweight(to_tsvector('english', coalesce(NEW.description, ' ')), 'B');
     END IF;
     RETURN NEW;
  END IF;




  RETURN NULL;
END;
$$ LANGUAGE plpgsql;




-- Trigger to update tsvectors before INSERT or UPDATE on the project table
CREATE TRIGGER project_search_update
  BEFORE INSERT OR UPDATE ON project
  FOR EACH ROW
  EXECUTE PROCEDURE project_search_update_tsvector();




-- Create a GiST index on the tsvectors column
CREATE INDEX project_search_idx ON project USING GiST(tsvectors);




--Full-text search index 2




-- Add the tsvectors column of type TSVECTOR to the task table
ALTER TABLE task ADD COLUMN tsvectors TSVECTOR;




-- Function to update the tsvectors field for task
DROP FUNCTION IF EXISTS task_search_update_tsvector();
CREATE FUNCTION task_search_update_tsvector() RETURNS TRIGGER AS $$
BEGIN
  IF TG_OP = 'INSERT' THEN
     NEW.tsvectors :=
        setweight(to_tsvector('english', coalesce(NEW.name, '')), 'A') ||
        setweight(to_tsvector('english', coalesce(NEW.description, '')), 'B');
     RETURN NEW;
  END IF;




  IF TG_OP = 'UPDATE' THEN
     IF NEW.name <> OLD.name OR NEW.description <> OLD.description THEN
        NEW.tsvectors :=
           setweight(to_tsvector('english', coalesce(NEW.name, '')), 'A') ||
           setweight(to_tsvector('english', coalesce(NEW.description, '')), 'B');
       END IF;
   RETURN NEW;
  END IF;




  RETURN NULL;
END;
$$ LANGUAGE plpgsql;




-- Trigger to update tsvectors before INSERT or UPDATE on the task table
CREATE TRIGGER task_search_update
  BEFORE INSERT OR UPDATE ON task
  FOR EACH ROW
  EXECUTE PROCEDURE task_search_update_tsvector();




-- Create a GiST index on the tsvectors column
CREATE INDEX task_search_idx ON task USING GIN(tsvectors);








-----------------------------------------
-- TRIGGERS
-----------------------------------------




--TRIGGER01: Administrators can not be banned ->BR01
CREATE OR REPLACE FUNCTION check_account_ban() RETURNS TRIGGER AS $BODY$
BEGIN
  IF NEW.blocked = TRUE AND NEW.admin = TRUE AND OLD.blocked IS DISTINCT FROM NEW.blocked THEN
     RAISE EXCEPTION 'Administrators cannot be banned';
  END IF;
  IF NEW.admin = TRUE AND NEW.blocked = TRUE AND OLD.admin IS DISTINCT FROM NEW.admin THEN
     RAISE EXCEPTION 'A banned account cannot be set as an administrator';
  END IF;
RETURN NEW;
END
$BODY$ LANGUAGE plpgsql;




CREATE TRIGGER check_account_ban
  BEFORE UPDATE OF blocked, admin
  ON "account"
  FOR EACH ROW
  EXECUTE PROCEDURE check_account_ban();




--TRIGGER02: A user can only send messages in forums related to projects they are members of -> BR04
 CREATE OR REPLACE FUNCTION check_account_in_project_for_forum_message() RETURNS TRIGGER AS $$
BEGIN
  IF NOT EXISTS (
     SELECT 1
     FROM project_member
     WHERE account = NEW.account AND project = NEW.project
  ) THEN
     RAISE EXCEPTION 'User can only send messages in forums related to projects they are members of';
  END IF;
  RETURN NEW;
END
$$ LANGUAGE plpgsql;




CREATE TRIGGER check_account_in_project_for_forum_message
  BEFORE INSERT ON forum_message
  FOR EACH ROW
  EXECUTE PROCEDURE check_account_in_project_for_forum_message();






--TRIGGER03: When project members are removed from a project, they must also be removed from all task assignments related to that project. -> BR05
CREATE OR REPLACE FUNCTION delete_user_tasks() RETURNS TRIGGER AS $$
DECLARE
   task_id INT;
BEGIN
   FOR task_id IN
       SELECT task_.id
       FROM account_task at
       JOIN task task_ ON at.task = task_.id
       JOIN task_table tasktable ON task_.task_table = tasktable.id
       WHERE at.account = OLD.account
       AND tasktable.project = OLD.project
   LOOP
       DELETE FROM account_task
       WHERE account = OLD.account AND task = task_id;
       INSERT INTO project_event (account, task, eventType)
       VALUES (OLD.account, task_id, 'Task_Unassigned');
   END LOOP;


   RETURN OLD;
END
$$ LANGUAGE plpgsql;


CREATE TRIGGER delete_project_member
AFTER DELETE ON project_member
FOR EACH ROW
EXECUTE FUNCTION delete_user_tasks();






--TRIGGER04: Users can only be assigned tasks in projects they are members of -> BR09
CREATE OR REPLACE FUNCTION check_account_membership_in_project() RETURNS TRIGGER AS $$
BEGIN
  IF NOT EXISTS (
    SELECT 1
    FROM project_member pm
    JOIN task t ON t.id = NEW.task
    JOIN task_table tt ON t.task_table = tt.id
    WHERE pm.account = NEW.account
      AND pm.project = tt.project
  ) THEN
     RAISE EXCEPTION 'User is not a member of the project related to this task';
  END IF;
  RETURN NEW;
END
$$ LANGUAGE plpgsql;




CREATE TRIGGER check_account_membership_in_project
  BEFORE INSERT OR UPDATE ON account_task
  FOR EACH ROW
  EXECUTE PROCEDURE check_account_membership_in_project();






--TRIGGER05: A user cannot have more than one component in the same position within the same project -> BR11
CREATE OR REPLACE FUNCTION check_unique_component_layout() RETURNS TRIGGER AS $$
BEGIN
  IF NEW.forum_component != 'None' AND
     (NEW.forum_component = NEW.analytics_component
      OR NEW.forum_component = NEW.members_component
      OR NEW.forum_component = NEW.productivity_component) THEN
     RAISE EXCEPTION 'Forum component layout is already in use by another component in this project';
  END IF;




  IF NEW.analytics_component != 'None' AND
     (NEW.analytics_component = NEW.forum_component
      OR NEW.analytics_component = NEW.members_component
      OR NEW.analytics_component = NEW.productivity_component) THEN
     RAISE EXCEPTION 'Analytics component layout is already in use by another component in this project';
  END IF;




  IF NEW.members_component != 'None' AND
     (NEW.members_component = NEW.forum_component
      OR NEW.members_component = NEW.analytics_component
      OR NEW.members_component = NEW.productivity_component) THEN
     RAISE EXCEPTION 'Members component layout is already in use by another component in this project';
  END IF;




  IF NEW.productivity_component != 'None' AND
     (NEW.productivity_component = NEW.forum_component
      OR NEW.productivity_component = NEW.analytics_component
      OR NEW.productivity_component = NEW.members_component) THEN
     RAISE EXCEPTION 'Productivity component layout is already in use by another component in this project';
  END IF;
  RETURN NEW;
END
$$ LANGUAGE plpgsql;




CREATE TRIGGER check_unique_component_layout
  BEFORE INSERT OR UPDATE ON project_member
  FOR EACH ROW
  EXECUTE PROCEDURE check_unique_component_layout();




--TRIGGER06: When a project coordinator exits the project, a new coordinator is assigned if there are other project members. If no members remain, the project is automatically archived -> BR12
CREATE OR REPLACE FUNCTION handle_coordinator_exit() RETURNS TRIGGER AS $$
DECLARE
remaining_member INT;
BEGIN
IF OLD.account IN (SELECT project_coordinator_id
                  FROM project
                  WHERE id = OLD.project) THEN
   SELECT account INTO remaining_member
   FROM project_member
   WHERE project = OLD.project AND account != OLD.account
   LIMIT 1;




  IF remaining_member IS NOT NULL THEN
      UPDATE project
      SET project_coordinator_id = remaining_member
      WHERE id = OLD.project;
  ELSE
      UPDATE project
      SET archived = TRUE
      WHERE id = OLD.project;
  END IF;




END IF;




RETURN OLD;
END;
$$ LANGUAGE plpgsql;




CREATE TRIGGER coordinator_exit_trigger
BEFORE DELETE ON project_member
FOR EACH ROW
EXECUTE FUNCTION handle_coordinator_exit();






--TRIGGER07: A user cannot be assigned the same task more than once -> BR13
CREATE OR REPLACE FUNCTION check_account_assigned_once() RETURNS TRIGGER AS $$
BEGIN
  IF EXISTS (
     SELECT 1
     FROM account_task
     WHERE account = NEW.account AND task = NEW.task
  ) THEN
     RAISE EXCEPTION 'User is already assigned to this task';
  END IF;
  RETURN NEW;
END
$$ LANGUAGE plpgsql;




CREATE TRIGGER check_account_assigned_once
  BEFORE INSERT ON account_task
  FOR EACH ROW
  EXECUTE PROCEDURE check_account_assigned_once();








--TRIGGER08: Notifications must be sent when a task is completed -> BR18
CREATE OR REPLACE FUNCTION notify_task_update() RETURNS TRIGGER AS $$
DECLARE
  notification_id INT;
  account INT;
  rec INT;
  event INT;
BEGIN
  IF NEW.finish_date IS NOT NULL AND OLD.finish_date IS NULL THEN
   FOR rec IN
       SELECT account_task.account
       FROM account_task
       WHERE task = NEW.id
   LOOP


       INSERT INTO project_event(account, task, eventType)
       VALUES (rec, NEW.id, 'Task_Completed')
       RETURNING id INTO event;


       INSERT INTO notification (type, create_date, viewed, emitted_to, project_event)
       VALUES ('Task_Completed', CURRENT_DATE, FALSE, rec, event);


     END LOOP;
  END IF;
  RETURN NEW;
END
$$ LANGUAGE plpgsql;




CREATE TRIGGER notify_task_update
  AFTER UPDATE ON task
  FOR EACH ROW
  EXECUTE PROCEDURE notify_task_update();






--TRIGGER09: Invitations can only be sent to users that currently are not project members ->BR19
CREATE OR REPLACE FUNCTION check_proj_member() RETURNS TRIGGER AS $BODY$
BEGIN
  IF EXISTS (SELECT 1 FROM project_member WHERE NEW.account = project_member.account AND NEW.project = project_member.project) THEN
      RAISE EXCEPTION 'User is already a project member';
  END IF;
RETURN NEW;
END
$BODY$ LANGUAGE plpgsql;




CREATE TRIGGER check_proj_member
  BEFORE INSERT ON invitation
  FOR EACH ROW
  EXECUTE PROCEDURE check_proj_member();






--TRIGGER10: Notifications must be sent to all project members when the project coordinator changes. -> BR20
CREATE OR REPLACE FUNCTION notify_project_coordinator_change() RETURNS TRIGGER AS $$
DECLARE
  notification_id INT;
  rec INT;


BEGIN
  IF EXISTS (
   SELECT 1
   FROM project_member
   WHERE account = NEW.project_coordinator_id
   AND project = NEW.id
) THEN
  IF OLD.project_coordinator_id IS DISTINCT FROM NEW.project_coordinator_id THEN
    FOR rec IN


       SELECT project_member.account FROM project_member WHERE project = NEW.id
    LOOP
     INSERT INTO notification (type, create_date, viewed, emitted_to, project)
     VALUES ('Coordinator_Change', CURRENT_DATE, FALSE, rec, NEW.id);


     END LOOP;
  END IF;
  RETURN NEW;
  END IF;
  RAISE EXCEPTION 'Project coordinator is not member of project';
END
$$ LANGUAGE plpgsql;




CREATE TRIGGER notify_project_coordinator_change
  BEFORE UPDATE OF project_coordinator_id ON project
  FOR EACH ROW
  EXECUTE PROCEDURE notify_project_coordinator_change();




--TRIGGER11: Notifications must be sent and a project event with eventType = 'Task_Assigned' must be created when a task is assigned to a project member. -> BR22
CREATE OR REPLACE FUNCTION notify_task_assigned() RETURNS TRIGGER AS $$
DECLARE
  notification_id INT;
  event INT;
BEGIN
   IF NOT EXISTS(
   SELECT 1
   FROM account_task
   WHERE account = NEW.account
   AND task = NEW.task
   ) THEN
   
   INSERT INTO project_event(account, task, eventType)
   VALUES (NEW.account, NEW.task, 'Task_Assigned')
   RETURNING id INTO event;


   INSERT INTO notification (type, create_date, viewed, emitted_to, project_event)
   VALUES ('Assigned_Task', CURRENT_DATE, FALSE, NEW.account, event);


  END IF;
  RETURN NEW;
END
$$ LANGUAGE plpgsql;




CREATE TRIGGER notify_task_assigned
  BEFORE INSERT ON account_task
  FOR EACH ROW
  EXECUTE PROCEDURE notify_task_assigned();








--TRIGGER12: When an invitation is accepted, a notification must be sent to all current project members to inform them of the new member’s addition to the project. -> BR23


CREATE OR REPLACE FUNCTION notify_accepted_invitation() RETURNS TRIGGER AS $$
DECLARE
 notification_id INT;
 rec INT;


BEGIN


  INSERT INTO project_member(account, project) VALUES (NEW.account, NEW.project);
   FOR rec IN


      SELECT project_member.account FROM project_member WHERE project = NEW.project
  LOOP
      INSERT INTO notification (type, create_date, viewed, emitted_to, project)
VALUES ('Accepted_Invite', CURRENT_DATE, FALSE, rec, NEW.project);


  END LOOP;
  RETURN NEW;




END
$$ LANGUAGE plpgsql;




--
CREATE TRIGGER notify_accepted_invitation
 AFTER UPDATE OF accepted
 ON "invitation"
 FOR EACH ROW
 EXECUTE PROCEDURE notify_accepted_invitation();

```

---

### A.2. Database population

```sql
-----------------------------------------
-- Populate the database
-----------------------------------------


INSERT INTO account (id,username, password, name, email, workfield, city, blocked, admin, account_image_id)
VALUES
    (0,'unknown', 'hashed_password_1', 'Unknown', 'unknown@example.com', NULL, NULL, false, false, NULL), 
    (1,'admin_1', 'hashed_password_1', 'Alice Smith', 'alice.smith@example.com', 'Software Developer', NULL, false, true, NULL), 
    (2,'adriana_almeida', 'hashed_password_2', 'Adriana Almeida', 'adriana.almeida@example.com', 'Software Developer', NULL, false, false, NULL),
    (3,'bruno_aguiar', 'hashed_password_3', 'Bruno Aguiar', 'bruno.aguiar@example.com', 'Software Developer', NULL, false, false, NULL),
    (4,'marta_silva', 'hashed_password_4', 'Marta Silva', 'marta.silva@example.com', 'Software Developer', NULL, false, false, NULL),
    (5,'pedro_oliveira', 'hashed_password_5', 'Pedro Gonçalo Oliveira', 'pedro.oliveira@example.com', 'Software Developer', NULL, false, false, NULL),
    (6,'marketing_user', 'hashed_password_6', 'Bob Johnson', 'bob.johnson@example.com', 'Marketing Specialist', NULL, false, false, NULL),
    (7,'tom_davis', 'hashed_password_7', 'Tom Davis', 'tom.davis@example.com', 'Task Manager', NULL, false, false, NULL),
    (8,'jessica_turner', 'hashed_password_8', 'Jessica Turner', 'jessica.turner@example.com', 'Marketing Specialist', NULL, false, false, NULL),
    (9,'michael_green', 'hashed_password_9', 'Michael Green', 'michael.green@example.com', 'Content Creator', NULL, false, false, NULL),
    (10,'emily_walker', 'hashed_password_10', 'Emily Walker', 'emily.walker@example.com', 'Ad Placements Specialist', NULL, false, false, NULL),
    (11,'dev_user', 'hashed_password_7', 'Charlie Brown', 'charlie.brown@example.com', 'Software Developer', NULL, false, false, NULL),

-- removed for brevity

-----------------------------------------
-- end
-----------------------------------------
```
---


## Revision history

Changes made to the first submission:
1. 14/11/2024 Changed notifications in sql

***
GROUP 44, 05/11/2024

* Adriana Almeida, up202109752@g.uporto.pt 
* Bruno Aguiar, up202205619@g.uporto.pt (Editor)
* Marta Silva, up202208258@g.uporto.pt
* Pedro Oliveira, up202208345@g.uporto.pt
