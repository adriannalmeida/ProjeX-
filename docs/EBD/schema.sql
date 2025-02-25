-----------------------------------------
-- Drop old schema
-----------------------------------------

DROP SCHEMA IF EXISTS lbaw2444 CASCADE;
CREATE SCHEMA IF NOT EXISTS lbaw2444;
SET search_path TO lbaw2444;

-----------------------------------------
-- Types
-----------------------------------------

-- Layout type definition
CREATE TYPE Layout AS ENUM('None', 'RightUp', 'RightDown', 'LeftUp', 'LeftDown');


-- Priority domain definition
CREATE TYPE Priority AS ENUM('High', 'Medium', 'Low');


-- EventType domain definition
CREATE TYPE EventType AS ENUM('Task_Created', 'Task_Completed', 'Task_Priority_Changed', 'Task_Deactivated', 'Task_Assigned', 'Task_Unassigned');



-----------------------------------------
-- Tables
-----------------------------------------


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
    account_image_id INT UNIQUE REFERENCES account_image(id) ON DELETE SET NULL,
    remember_token VARCHAR
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
    position INT NOT NULL,
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
    create_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (create_date <= CURRENT_TIMESTAMP),
    viewed BOOLEAN NOT NULL DEFAULT FALSE,
    emitted_to INT NOT NULL REFERENCES account(id) ON DELETE CASCADE
);


-- R15: coordinator_notification
CREATE TABLE coordinator_notification (
    id INT REFERENCES notification(id) ON DELETE CASCADE,
    notifies INT NOT NULL REFERENCES project(id),
    PRIMARY KEY (id)
);


-- R16: accepted_invite_notification
CREATE TABLE accepted_invite_notification (
    id INT REFERENCES notification(id) ON DELETE CASCADE,
    notifies INT NOT NULL REFERENCES project(id),
    PRIMARY KEY (id)
);


-- R17: task_completed_notification
CREATE TABLE task_completed_notification (
    id INT REFERENCES notification(id) ON DELETE CASCADE,
    notifies INT NOT NULL REFERENCES project_event(id),
    PRIMARY KEY (id)
);


-- R18: assigned_task_notification
CREATE TABLE assigned_task_notification (
    id INT REFERENCES notification(id) ON DELETE CASCADE,
    notifies INT NOT NULL REFERENCES project_event(id),
    PRIMARY KEY (id)
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
-- TRIGGERS and UDFs
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



--TRIGGER10: Notifications must be sent to all assigned members and a project event with eventType = ‘Task_Completed’ must be created when a task is completed. -> BR21
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


--TRIGGER11: Notifications must be sent when a task is completed, and a project event with eventType = 'Task_Assigned' must be created when a task is assigned to a project member.-> BR22
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
       INSERT INTO notification (emitted_to) VALUES (rec)
       RETURNING id INTO notification_id;
   INSERT INTO accepted_invite_notification (id, notifies) VALUES (notification_id, NEW.project);
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

-----------------------------------------
-- end
-----------------------------------------