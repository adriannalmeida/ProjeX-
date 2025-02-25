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


-- NotificationType domain definition
CREATE TYPE NotificationType AS ENUM('Coordinator_Change', 'Accepted_Invite', 'Task_Completed', 'Assigned_Task');

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
    image VARCHAR(255) NOT NULL
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
    project_coordinator_id INT NOT NULL REFERENCES account(id),
    add_deadline_permission BOOLEAN NOT NULL DEFAULT FALSE,
    create_task_permission BOOLEAN NOT NULL DEFAULT TRUE,
    edit_task_permission BOOLEAN NOT NULL DEFAULT TRUE,
    assign_tasks_permission BOOLEAN NOT NULL DEFAULT TRUE,
    create_tasktable_permission BOOLEAN NOT NULL DEFAULT FALSE,
    add_member_permission BOOLEAN NOT NULL DEFAULT FALSE,
    view_deleted_tasks_permission BOOLEAN NOT NULL DEFAULT FALSE
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
    last_accessed TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (last_accessed <= CURRENT_TIMESTAMP),
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
    deadline_date DATE CHECK (deadline_date IS NULL OR start_date <= deadline_date),
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
    time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (time <= CURRENT_TIMESTAMP),
    event_type EventType NOT NULL
);


-- R12: comment
CREATE TABLE comment (
    id SERIAL PRIMARY KEY,
    account INT NOT NULL REFERENCES account(id),
    content TEXT NOT NULL,
    create_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP CHECK (create_date <= CURRENT_TIMESTAMP),
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
    checked BOOLEAN NOT NULL DEFAULT FALSE,
    project INT REFERENCES project(id) CHECK ((type IN('Coordinator_Change', 'Accepted_Invite') AND project != NULL) OR project IS NULL),
    project_event INT REFERENCES project_event(id) CHECK ((type IN('Task_Completed', 'Assigned_Task') AND project_event != NULL) OR project_event IS NULL)
);

-- R15: recover password
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP WITHOUT TIME ZONE
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
         setweight(to_tsvector('english', coalesce(NEW.name, ' ')), 'A') ||
         setweight(to_tsvector('english', coalesce(NEW.description, ' ')), 'B') ||
         setweight(to_tsvector('english', coalesce((
                                            select string_agg(content, ' ')
                                            from comment
                                            where task = NEW.id
                                            ), ' ')), 'C');
      RETURN NEW;
   END IF;


   IF TG_OP = 'UPDATE' THEN
      IF NEW.name <> OLD.name OR NEW.description <> OLD.description THEN
         NEW.tsvectors :=
            setweight(to_tsvector('english', coalesce(NEW.name, ' ')), 'A') ||
            setweight(to_tsvector('english', coalesce(NEW.description, ' ')), 'B') ||
             setweight(to_tsvector('english', coalesce((
                                                select string_agg(content, ' ')
                                                from comment
                                                where task = NEW.id
                                                ), ' ')), 'C');
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
        INSERT INTO project_event (account, task, event_type) 
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
            VALUES ('Coordinator_Change', CURRENT_TIMESTAMP, FALSE, rec, NEW.id);

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



--TRIGGER11: Notifications must be sent and a project event with event_type = 'Task_Assigned' must be created when a task is assigned to a project member. -> BR22
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

    INSERT INTO project_event(account, task, event_type)
    VALUES (NEW.account, NEW.task, 'Task_Assigned')
    RETURNING id INTO event;

INSERT INTO notification (type, create_date, viewed, emitted_to, project_event)
VALUES ('Assigned_Task', CURRENT_TIMESTAMP, FALSE, NEW.account, event);

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
FOR rec IN
SELECT project_member.account FROM project_member WHERE project = NEW.project
    LOOP
INSERT INTO notification (type, create_date, viewed, emitted_to, project)
VALUES ('Accepted_Invite', CURRENT_TIMESTAMP, FALSE, rec, NEW.project);
END LOOP;
INSERT INTO project_member (account, project) VALUES (NEW.account, NEW.project);
RETURN NEW;
END
$$ LANGUAGE plpgsql;


CREATE TRIGGER notify_accepted_invitation
    AFTER UPDATE OF accepted
    ON "invitation"
    FOR EACH ROW
    EXECUTE PROCEDURE notify_accepted_invitation();

-----------------------------------------
-- end
-----------------------------------------


-----------------------------------------
-- Populate the database
-----------------------------------------


INSERT INTO country (id, name) 
VALUES 
(1, 'United States'),
(2, 'Canada'),
(3, 'Portugal'),
(4, 'Spain'),
(5, 'France'),
(6, 'Brazil'),
(7, 'Italy'),
(8, 'Germany'),
(9, 'Australia'),
(10, 'Mexico'),
(11, 'United Kingdom'),
(12, 'Russia'),
(13, 'China'),
(14, 'India'),
(15, 'Japan'),
(16, 'South Korea'),
(17, 'South Africa'),
(18, 'Argentina'),
(19, 'Chile'),
(20, 'Colombia'),
(21, 'Egypt'),
(22, 'Nigeria'),
(23, 'Turkey'),
(24, 'Saudi Arabia'),
(25, 'Iran'),
(26, 'Thailand'),
(27, 'Vietnam'),
(28, 'Malaysia'),
(29, 'Philippines'),
(30, 'Indonesia'),
(31, 'Singapore'),
(32, 'Pakistan'),
(33, 'Bangladesh'),
(34, 'Australia'),
(35, 'New Zealand'),
(36, 'Sweden'),
(37, 'Norway'),
(38, 'Finland'),
(39, 'Denmark'),
(40, 'Netherlands'),
(41, 'Belgium'),
(42, 'Austria'),
(43, 'Switzerland'),
(44, 'Poland'),
(45, 'Czech Republic'),
(46, 'Hungary'),
(47, 'Greece'),
(48, 'Ireland'),
(49, 'Iceland'),
(50, 'Ukraine'),
(51, 'Belarus'),
(52, 'Romania'),
(53, 'Bulgaria'),
(54, 'Croatia'),
(55, 'Serbia'),
(56, 'Slovenia'),
(57, 'Slovakia'),
(58, 'Luxembourg'),
(59, 'Malta'),
(60, 'Monaco');

SELECT SETVAL('country_id_seq', (SELECT MAX(id) FROM country) + 1);

INSERT INTO city (id, name, country)
VALUES 
(1, 'New York', 1),
(2, 'Los Angeles', 1),
(3, 'Chicago', 1), 
(4, 'Toronto', 2),
(5, 'Vancouver', 2),
(6, 'Lisbon', 3),
(7, 'Porto', 3),
(8, 'Coimbra', 3),
(9, 'Braga', 3),
(10, 'Funchal', 3),
(11, 'Madrid', 4),
(12, 'Barcelona', 4),
(13, 'Seville', 4),
(14, 'Paris', 5),
(15, 'Lyon', 5),
(16, 'Marseille', 5), 
(17, 'São Paulo', 6),
(18, 'Rio de Janeiro', 6),
(19, 'Brasília', 6),
(20, 'Rome', 7),
(21, 'Milan', 7),
(22, 'Berlin', 8),
(23, 'Munich', 8),
(24, 'Sydney', 9),
(25, 'Melbourne', 9),
(26, 'Mexico City', 10),
(27, 'Guadalajara', 10),
(28, 'London', 11),
(29, 'Manchester', 11),
(30, 'Moscow', 12),
(31, 'Saint Petersburg', 12),
(32, 'Beijing', 13),
(33, 'Shanghai', 13),
(34, 'New Delhi', 14),
(35, 'Mumbai', 14),
(36, 'Tokyo', 15),
(37, 'Osaka', 15),
(38, 'Seoul', 16),
(39, 'Busan', 16),
(40, 'Cape Town', 17),
(41, 'Johannesburg', 17),
(42, 'Buenos Aires', 18),
(43, 'Cordoba', 18),
(44, 'Santiago', 19),
(45, 'Valparaiso', 19),
(46, 'Bogota', 20),
(47, 'Medellin', 20),
(48, 'Cairo', 21),
(49, 'Alexandria', 21),
(50, 'Lagos', 22),
(51, 'Abuja', 22),
(52, 'Istanbul', 23),
(53, 'Ankara', 23),
(54, 'Riyadh', 24),
(55, 'Jeddah', 24),
(56, 'Tehran', 25),
(57, 'Mashhad', 25),
(58, 'Bangkok', 26),
(59, 'Phuket', 26),
(60, 'Hanoi', 27),
(61, 'Ho Chi Minh City', 27),
(62, 'Kuala Lumpur', 28),
(63, 'George Town', 28),
(64, 'Manila', 29),
(65, 'Cebu', 29),
(66, 'Jakarta', 30),
(67, 'Surabaya', 30),
(68, 'Singapore', 31),
(69, 'Karachi', 32),
(70, 'Lahore', 32),
(71, 'Dhaka', 33),
(72, 'Chittagong', 33),
(73, 'Wellington', 35),
(74, 'Auckland', 35),
(75, 'Stockholm', 36),
(76, 'Oslo', 37),
(77, 'Helsinki', 38),
(78, 'Copenhagen', 39),
(79, 'Amsterdam', 40),
(80, 'Brussels', 41),
(81, 'Vienna', 42),
(82, 'Zurich', 43),
(83, 'Warsaw', 44),
(84, 'Prague', 45),
(85, 'Budapest', 46),
(86, 'Athens', 47),
(87, 'Dublin', 48),
(88, 'Reykjavik', 49),
(89, 'Kyiv', 50),
(90, 'Minsk', 51),
(91, 'Bucharest', 52),
(92, 'Sofia', 53),
(93, 'Zagreb', 54),
(94, 'Belgrade', 55),
(95, 'Ljubljana', 56),
(96, 'Bratislava', 57),
(97, 'Luxembourg', 58),
(98, 'Valletta', 59),
(99, 'Monaco', 60);

SELECT SETVAL('city_id_seq', (SELECT MAX(id) FROM city) + 1);

INSERT INTO account_image(image)
VALUES
    ('hjHPVnLS9GgeuRYBJRhtmR2uZjY0u7LszI0RPaJD.png'),
    ('fDStNEHbEJPCjs7eQ4n2fgUcgxvuvzjQaKPbpMFj.png'),
    ('Swz7OV9U4jTUUNP0RvvNuF1nwVmE6h6m6z3vLvQN.png');

INSERT INTO account (id,username, password, name, email, workfield, city, blocked, admin, account_image_id)
VALUES
    (0, 'unknown_user', '$2y$10$sDG7/jtFTLrnUY3KVwXPzOBB5S50MmOZMpzeUNikh9rIZ9SWuTKWa', 'Unknown', 'unknown@example.com', NULL, NULL, false, false, NULL), -- Password: hashed_password_1
    (1, 'admin_alice', '$2y$10$4ZZyLt7L1lN.rsFXhiXdmOk1tbeYahNx887YIU99KNcCcnmvMVao2', 'Alice Smith', 'alice.smith@example.com', 'Software Developer', NULL, false, true, NULL), -- Password: hashed_password_1
    (2, 'adriana_almeida', '$2y$10$SlJHnh57wWgP3nzG3q.nJe8RxA53o5d9VYyAwEQAQgY3FtxBvYOfW', 'Adriana Almeida', 'adriana.almeida@example.com', 'Software Developer', NULL, false, false, 3), -- Password: hashed_password_2
    (3, 'bruno_aguiar', '$2y$10$9fC32elQagBGfuRYs8mzf.wBopVjanVZqsPXp4yv7xFGimxdkWdq.', 'Bruno Aguiar', 'bruno.aguiar@example.com', 'Software Developer', NULL, false, false, NULL), -- Password: hashed_password_3
    (4, 'marta_silva', '$2y$10$H2ewvYnZalWlQ80hrT3ntuS4qVaRmi/0ZhPxCC49Zq5Pjxg9KzUka', 'Marta Silva', 'marta.silva@example.com', 'Software Developer', NULL, false, false, 2), -- Password: hashed_password_4
    (5, 'pedro_oliveira', '$2y$10$Nz7JO66Immiedb7SJK0YrOag1nrJMUq6kL5hbOdUdTkKzp9ZpRGVK', 'Pedro Gonçalo Oliveira', 'pedro.oliveira@example.com', 'Software Developer', NULL, false, false, 1), -- Password: hashed_password_5
    (6, 'marketing_user', '$2y$10$me6s8Uz7iq0E1KyMSEPpmeHoovtm12rM7k9Zcb4AFd1MosBZDeB/2', 'Bob Johnson', 'bob.johnson@example.com', 'Marketing Specialist', NULL, false, false, NULL), -- Password: hashed_password_6
    (7, 'tom_davis', '$2y$10$lOzdbZYZ3vz.CFIVvs8Zu.kvFAgpDHG5V9jJTRi5ik/7K36Jia3pi', 'Tom Davis', 'tom.davis@example.com', 'Task Manager', NULL, false, false, NULL), -- Password: hashed_password_7
    (8, 'jessica_turner', '$2y$10$bvo8jhK3bxcZWsvezc16.uDqNm3v.njGgCsr.ChHDT9f.HvmsT16C', 'Jessica Turner', 'jessica.turner@example.com', 'Marketing Specialist', NULL, false, false, NULL), -- Password: hashed_password_8
    (9, 'michael_green', '$2y$10$53ISNe9gZsapx9cxIYqMburf.5N4/VkdohJeU9/SJkO0pkKYpDW.S', 'Michael Green', 'michael.green@example.com', 'Content Creator', NULL, false, false, NULL), -- Password: hashed_password_9
    (10, 'emily_walker', '$2y$10$y.uOu58OX5h.xMcwHNW71.HYtXc/zuA2WEvv/p4xAKZRvzJgLnHIu', 'Emily Walker', 'emily.walker@example.com', 'Ad Placements Specialist', NULL, false, false, NULL), -- Password: hashed_password_10
    (11, 'dev_user', '$2y$10$1VycM8VwKoYZLsA8j8gI4e7l4oWujSKlc51IATMd9sflRxhkBqOuS', 'Charlie Brown', 'charlie.brown@example.com', 'Software Developer', NULL, false, false, NULL), -- Password: hashed_password_11
    (12, 'daniel_king', '$2y$10$AxcRkeQrhRzvSIEj0kySMuATQdR54IYVfCOGKDkoDToHsOGGMidDS', 'Daniel King', 'daniel.king@example.com', 'Software Developer', NULL, false, false, NULL), -- Password: hashed_password_12
    (13, 'olivia_clark', '$2y$10$82V3zShtHBOvGWqOYNReJOqUsOe7kZVo.GVtAdltimLWfEE4pmIA.', 'Olivia Clark', 'olivia.clark@example.com', 'QA Tester', NULL, false, false, NULL), -- Password: hashed_password_13
    (14, 'noah_wright', '$2y$10$gj5BI2BuJk8g4oJrpamhheYJqBV0CDdoBLC6kiWK4cMOm04FBRX46', 'Noah Wright', 'noah.wright@example.com', 'Backend Developer', NULL, false, false, NULL), -- Password: hashed_password_14
    (15, 'ava_scott', '$2y$10$bgolGf1bHK8qRgGQfRbGu.6YeDC/COyAcrpA0KKbFuhmREKvBBGo.', 'Ava Scott', 'ava.scott@example.com', 'Frontend Developer', NULL, false, false, NULL), -- Password: hashed_password_15
    (16, 'architecture_user', '$2y$10$zg2FjEpBO9XhCoxPlSnBrOAt/2BiHQ3tFAW.KKvSF.1CHhzP/g8sa', 'Eve White', 'eve.white@example.com', 'System Architect', NULL, false, false, NULL), -- Password: hashed_password_16
    (17, 'lucas_perez', '$2y$10$1dFAOjPKhcFcEtMX3D7ayetip43W4d7eDaUYb6pFtYIJmZ8yYN/SS', 'Lucas Perez', 'lucas.perez@example.com', 'Deployment Specialist', NULL, false, false, NULL), -- Password: hashed_password_17
    (18, 'ethan_carter', '$2y$10$I6jabJbBEnnmjTHY9ymyOegnHTI6ZnG96t/c.Y7RDLFQhVT5cLBnm', 'Ethan Carter', 'ethan.carter@example.com', 'System Architect', NULL, false, false, NULL), -- Password: hashed_password_18
    (19, 'mia_robinson', '$2y$10$9s69051B.7TbI0gwLKmwI.CQMntkfrelFONWQIlRar4kYZ6SUf5P6', 'Mia Robinson', 'mia.robinson@example.com', 'Systems Designer', NULL, false, false, NULL), -- Password: hashed_password_19
    (20, 'samuel_harris', '$2y$10$l/n7zT910pEJFBR1tIGoJ..DKr4SYMY2PgWeDB3ElTJ3g9G0/xzku', 'Samuel Harris', 'samuel.harris@example.com', 'System Architect', NULL, false, false, NULL), -- Password: hashed_password_20
    (21, 'chloe_martinez', '$2y$10$SYCQ473PgaymiAxuKVKn5uAWUMGMfJD755RPeFgLbIFDPxMBZVnrO', 'Chloe Martinez', 'chloe.martinez@example.com', 'System Design Specialist', NULL, false, false, NULL), -- Password: hashed_password_21
    (22, 'henry_douglas', '$2y$10$SBgfY9OpBwi.45sxw/UMLeD9rKl9botWNIE1lmPQqStTSYD/8.omq', 'Henry Douglas', 'henry.douglas@example.com', 'Data Analyst', NULL, false, false, NULL), -- Password: hashed_password_22
    (23, 'sophia_lee', '$2y$10$wB71t7995BoT5/tXRbNTN.afipvvZ2.TSzME87k3cwLHq1fpgQPBu', 'Sophia Lee', 'sophia.lee@example.com', 'Business Analyst', NULL, false, false, NULL), -- Password: hashed_password_23
    (24, 'william_jones', '$2y$10$MTWVEIgdUyLyRFA4SSZ.PO9DUacHaxRScmMkbqgsWCoISFZZTU/nq', 'William Jones', 'william.jones@example.com', 'Software Developer', NULL, false, false, NULL), -- Password: hashed_password_24
    (25, 'amelia_moore', '$2y$10$wB71t7995BoT5/tXRbNTN.afipvvZ2.TSzME87k3cwLHq1fpgQPBu', 'Amelia Moore', 'amelia.moore@example.com', 'QA Engineer', NULL, false, false, NULL), -- Password: hashed_password_23
    (26, 'nathan_taylor', '$2y$10$MTWVEIgdUyLyRFA4SSZ.PO9DUacHaxRScmMkbqgsWCoISFZZTU/nq', 'Nathan Taylor', 'nathan.taylor@example.com', 'System Administrator', NULL, false, false, NULL); -- Password: hashed_password_24

SELECT SETVAL('account_id_seq', (SELECT MAX(id) FROM account) + 1);

INSERT INTO project (id,name, description, isPublic, archived, createDate, finishDate, project_coordinator_id)
VALUES
    (0,'Project Management System', 'A system to manage tasks for different projects including status tracking', false, false, CURRENT_DATE, NULL, 4),
    (1,'Marketing Campaign Project', 'A project to plan, design, and execute a marketing campaign', false, false, CURRENT_DATE, NULL, 6),
    (2,'Software Development Project', 'A project to design, develop, and deploy a software application', false, false, CURRENT_DATE, NULL, 10),
    (3,'System Architecture Design Project', 'A project to design the architecture and models for a new system', false, false, CURRENT_DATE, NULL, 15),
    (4,'Data Analysis Project', 'A project to analyze and interpret data for business insights', false, false, CURRENT_DATE, NULL, 22),
    (5,'Business Process Optimization', 'A project to optimize and streamline business processes', false, false, CURRENT_DATE, NULL, 23),
    (6,'Network Infrastructure Upgrade', 'A project to upgrade and enhance the corporate network infrastructure', false, false, CURRENT_DATE, NULL, 26),
    (7,'Customer Relationship Management (CRM)', 'A project to implement a CRM system for managing customer interactions', false, false, CURRENT_DATE, NULL, 24),
    (8,'E-commerce Platform Development', 'A project to develop an e-commerce platform for online sales', true, false, CURRENT_DATE, NULL, 4),
    (9,'Mobile App Development', 'A project to develop a mobile application for iOS and Android', false, false, CURRENT_DATE, NULL, 10),
    (10,'Data Migration Project', 'A project to migrate data from legacy systems to new platforms', false, false, CURRENT_DATE, NULL, 15),
    (11, 'AI Chatbot', 'Developing an AI-powered chatbot for customer service', false, false, CURRENT_DATE, NULL, 5),
    (12, 'Cybersecurity Initiative', 'Strengthening the cybersecurity framework of the organization', false, false, CURRENT_DATE, NULL, 16),
    (13, 'Data Analytics Dashboard', 'Building a dashboard for real-time data analytics', false, false, CURRENT_DATE, NULL, 2),
    (14, 'Employee Training Program', 'Designing and implementing employee training materials', false, false, CURRENT_DATE, NULL, 19),
    (15, 'Website Redesign Project', 'Redesigning the corporate website for improved UX/UI', true, false, CURRENT_DATE, NULL, 4),
    (16, 'IoT Integration Project', 'Integrating IoT devices into business operations', false, false, CURRENT_DATE, NULL, 3),
    (17, 'Big Data Processing Project', 'Processing and analyzing large datasets for insights', false, false, CURRENT_DATE, NULL, 14),
    (18, 'Marketing Analytics Platform', 'Developing a platform for marketing data analysis', false, false, CURRENT_DATE, NULL, 12),
    (19, 'Supply Chain Optimization', 'Optimizing the supply chain for cost and efficiency', false, false, CURRENT_DATE, NULL, 6),
    (20, 'Healthcare Management System', 'Developing a system to manage patient records', true, false, CURRENT_DATE, NULL, 10),
    (21, 'Online Education Platform', 'Creating an online education platform for learners', false, false, CURRENT_DATE, NULL, 18),
    (22, 'Blockchain Integration', 'Integrating blockchain for secure transactions', true, false, CURRENT_DATE, NULL, 7),
    (23, 'CRM System Enhancement', 'Enhancing the current CRM system features', false, false, CURRENT_DATE, NULL, 9),
    (24, 'Energy Management Project', 'Designing an energy-efficient management system', true, true, CURRENT_DATE, NULL, 8),
    (25, 'Automated Testing Framework', 'Developing a framework for automated software testing', true, true, CURRENT_DATE, NULL, 15),
    (26, 'Social Media Marketing Campaign', 'Planning and executing a social media marketing campaign', true, true, CURRENT_DATE, NULL, 6),
    (27, 'Cloud Migration Project', 'Migrating on-premises systems to cloud infrastructure', true, true, CURRENT_DATE, NULL, 10),
    (28, 'AI Image Recognition Project', 'Developing an AI system for image recognition', false, false, CURRENT_DATE, NULL, 15),
    (29, 'Customer Feedback System', 'Implementing a system for collecting customer feedback', false, true, CURRENT_DATE, NULL, 24),
    (30, 'Digital Transformation Initiative', 'Initiating a digital transformation strategy for the organization', true, true, CURRENT_DATE, NULL, 4),
    (31, 'Mobile Payment App Development', 'Developing a mobile app for payments and transactions', true, false, CURRENT_DATE, NULL, 10),
    (32, 'Predictive Analytics Platform', 'Building a platform for predictive analytics and forecasting', true, false, CURRENT_DATE, NULL, 14),
    (33, 'Employee Wellness Program', 'Implementing a program to improve employee wellness', true, true, CURRENT_DATE, NULL, 19),
    (34, 'Online Marketplace Development', 'Developing an online marketplace for buyers and sellers', true, false, CURRENT_DATE, NULL, 10),
    (35, 'AI Chatbot Integration', 'Integrating an AI chatbot for customer support', true, false, CURRENT_DATE, NULL, 5),
    (36, 'Cybersecurity Training Program', 'Training employees on cybersecurity best practices', true, true, CURRENT_DATE, NULL, 16),
    (37, 'Data Analytics Dashboard Upgrade', 'Upgrading the data analytics dashboard with new features', false, false, CURRENT_DATE, NULL, 2),
    (38, 'Employee Training Portal', 'Creating a portal for employee training and development', false, false, CURRENT_DATE, NULL, 19),
    (39, 'Website Improvement', 'Improve the website and optimizing features', false, false, CURRENT_DATE, NULL, 4),
    (40, 'IoT Device Management', 'Managing and monitoring IoT devices in the organization', true, true, CURRENT_DATE, NULL, 3);


INSERT INTO project_member (account, project, is_favourite, forum_component, analytics_component, members_component, productivity_component)
VALUES
    (1, 0, true, 'None', 'None', 'None', 'None'),   -- Project 0: Admin User (Alice Smith)
    (2, 0, false, 'None', 'None', 'None', 'None'),  -- Adriana Almeida
    (3, 0, false, 'None', 'None', 'None', 'None'),  -- Bruno Aguiar
    (4, 0, true, 'None', 'None', 'None', 'None'),   -- Marta Silva
    (5, 0, false, 'None', 'None', 'None', 'None'),  -- Pedro Gonçalo Oliveira
    (6, 1, false, 'None', 'None', 'None', 'None'),  -- Project 1: Bob Johnson
    (7, 1, false, 'None', 'None', 'None', 'None'),  -- Tom Davis
    (8, 1, false, 'None', 'None', 'None', 'None'),  -- Jessica Turner
    (9, 1, false, 'None', 'None', 'None', 'None'),  -- Michael Green
    (10, 1, false, 'None', 'None', 'None', 'None'),  -- Emily Walker
    (11, 2, false, 'None', 'None', 'None', 'None'), -- Project 2: Charlie Brown
    (2, 2, false, 'None', 'None', 'None', 'None'),  -- Adriana Almeida
    (3, 2, false, 'None', 'None', 'None', 'None'),  -- Bruno Aguiar
    (4, 2, true,'None', 'None', 'None', 'None'),    --Marta Silva
    (5, 2, false, 'None', 'None', 'None', 'None'),  -- Pedro Gonçalo Oliveira
    (6,2,true, 'None','None','None','None'), -- Bob Johnson
    (7, 2, false, 'None', 'None', 'None', 'None'), -- Tom Davis
    (8, 2, false, 'None', 'None', 'None', 'None'), -- Jessica Turner
    (9, 2, false, 'None', 'None', 'None', 'None'), -- Michael Green
    (10, 2, false, 'None', 'None', 'None', 'None'), -- Emily Walker
    (12, 2, false, 'None', 'None', 'None', 'None'), -- Daniel King
    (13, 2, false, 'None', 'None', 'None', 'None'), -- Olivia Clark
    (14, 2, false, 'None', 'None', 'None', 'None'), -- Noah Wright
    (16, 3, false, 'None', 'None', 'None', 'None'), -- Project 3: Eve White
    (17, 3, false, 'None', 'None', 'None', 'None'), -- Lucas Perez
    (18, 3, false, 'None', 'None', 'None', 'None'), -- Ethan Carter
    (19, 3, false, 'None', 'None', 'None', 'None'), -- Mia Robinson
    (20, 3, false, 'None', 'None', 'None', 'None'), -- Samuel Harris
    (22, 4, true, 'None', 'None', 'None', 'None'),   -- Project 4: Henry Douglas
    (8, 4, false, 'None', 'None', 'None', 'None'),  -- Jessica Turner
    (3, 4, false, 'None', 'None', 'None', 'None'),  -- Bruno Aguiar
    (4, 4, true, 'None', 'None', 'None', 'None'),   -- Marta Silva
    (23, 5, true, 'None', 'None', 'None', 'None'),   -- Project 5: Sophia Lee
    (10, 5, true, 'None', 'None', 'None', 'None'),  -- Emily Walker
    (14, 5, true, 'None', 'None', 'None', 'None'),  -- Noah Wright
    (19, 5, true, 'None', 'None', 'None', 'None'),  -- Mia Robinson
    (26, 6, true, 'None', 'None', 'None', 'None'),   -- Project 6: Nathan Taylor
    (8, 6, true, 'None', 'None', 'None', 'None'),   -- Jessica Turner
    (15, 6, true, 'None', 'None', 'None', 'None'),  -- Ava Scott
    (24, 7, true, 'None', 'None', 'None', 'None'),   -- Project 7: William Jones
    (4, 7, false, 'None', 'None', 'None', 'None'),  -- Marta Silva
    (12, 7, false, 'None', 'None', 'None', 'None'), -- Daniel King
    (4, 8, true, 'None', 'None', 'None', 'None'),    -- Project 8: Marta Silva
    (10, 8, true, 'None', 'None', 'None', 'None'),   -- Emily Walker
    (15, 8, true, 'None', 'None', 'None', 'None'),  -- Ava Scott
    (24, 8, true, 'None', 'None', 'None', 'None'),  -- William Jones
    (10, 9, true, 'None', 'None', 'None', 'None'),   -- Project 9: Emily Walker
    (17, 9, false, 'None', 'None', 'None', 'None'), -- Lucas Perez
    (18, 9, false, 'None', 'None', 'None', 'None'), -- Ethan Carter
    (2, 9, true, 'None', 'None', 'None', 'None'),  -- Adriana Almeida
    (19, 9, false, 'None', 'None', 'None', 'None'), -- Mia Robinson
    (3, 9, false, 'None', 'None', 'None', 'None'),  -- Bruno Aguiar
    (20, 9, false, 'None', 'None', 'None', 'None'), -- Samuel Harris
    (15, 10, true, 'None', 'None', 'None', 'None'),  -- Project 10: Ava Scott
    (24, 10, true, 'None', 'None', 'None', 'None'),  -- William Jones
    (5, 11, true, 'None', 'None', 'None', 'None'),   -- Project 11: Pedro Gonçalo Oliveira
    (16, 12, true, 'None', 'None', 'None', 'None'),  -- Project 12: Eve White
    (15, 12, true, 'None', 'None', 'None', 'None'),  -- Ava Scott
    (6, 12, true, 'None', 'None', 'None', 'None'),   -- Bob Johnson
    (24, 12, false, 'None', 'None', 'None', 'None'),  -- William Jones
    (19, 12, true, 'None', 'None', 'None', 'None'),  -- Mia Robinson
    (10, 12, false, 'None', 'None', 'None', 'None'),  -- Emily Walker
    (14, 12, true, 'None', 'None', 'None', 'None'),  -- Noah Wright
    (2, 13, true, 'None', 'None', 'None', 'None'),   -- Project 13: Adriana Almeida
    (10, 13, true, 'None', 'None', 'None', 'None'),  -- Emily Walker
    (19, 14, true, 'None', 'None', 'None', 'None'),  -- Project 14: Mia Robinson
    (17, 14, false, 'None', 'None', 'None', 'None'), -- Lucas Perez
    (18, 14, false, 'None', 'None', 'None', 'None'), -- Ethan Carter
    (20, 14, false, 'None', 'None', 'None', 'None'), -- Samuel Harris
    (4, 15, true, 'None', 'None', 'None', 'None'),   -- Project 15: Marta Silva
    (9, 15, false, 'None', 'None', 'None', 'None'),   -- Michael Green
    (12, 15, false,'None', 'None', 'None', 'None'), -- Daniel King
    (3, 16, true, 'None', 'None', 'None', 'None'),   -- Project 16: Bruno Aguiar
    (17, 16, false, 'None', 'None', 'None', 'None'), -- Lucas Perez
    (18, 16, false, 'None', 'None', 'None', 'None'), -- Ethan Carter
    (20, 16, false, 'None', 'None', 'None', 'None'), -- Samuel Harris
    (14, 17, true, 'None', 'None', 'None', 'None'),  -- Project 17: Noah Wright
    (1, 17, false, 'None', 'None', 'None', 'None'), -- Alice Smith
    (12, 17, false, 'None', 'None', 'None', 'None'), -- Daniel King
    (12, 18, true, 'None', 'None', 'None', 'None'),  -- Project 18: Daniel King
    (19, 18, true, 'None', 'None', 'None', 'None'),  -- Mia Robinson
    (20, 18, false, 'None', 'None', 'None', 'None'),  -- Samuel Harris
    (24, 18, false, 'None', 'None', 'None', 'None'),  -- William Jones
    (6, 19, true, 'None', 'None', 'None', 'None'),   -- Project 19: Bob Johnson
    (10, 20, true, 'None', 'None', 'None', 'None'),  -- Project 20: Emily Walker
    (4, 20, false, 'None', 'None', 'None', 'None'), -- Marta Silva
    (2, 20, true, 'None', 'None', 'None', 'None'),  -- Adriana Almeida
    (18, 21, true, 'None', 'None', 'None', 'None'),  -- Project 21: Ethan Carter
    (10, 21, true, 'None', 'None', 'None', 'None'),  -- Emily Walker
    (7, 22, true, 'None', 'None', 'None', 'None'),   -- Project 22: Tom Davis
    (2, 22, true, 'None', 'None', 'None', 'None'),  -- Adriana Almeida
    (19, 22, false, 'None', 'None', 'None', 'None'), -- Mia Robinson
    (3, 22, false, 'None', 'None', 'None', 'None'),  -- Bruno Aguiar
    (20, 22, false, 'None', 'None', 'None', 'None'), -- Samuel Harris
    (9, 23, true, 'None', 'None', 'None', 'None'),   -- Project 23: Michael Green
    (8, 24, true, 'None', 'None', 'None', 'None'),   -- Project 24: Jessica Turner
    (2, 24, true, 'None', 'None', 'None', 'None'),  -- Adriana Almeida
    (19, 24, false, 'None', 'None', 'None', 'None'), -- Mia Robinson
    (3, 24, false, 'None', 'None', 'None', 'None'),  -- Bruno Aguiar
    (20, 24, false, 'None', 'None', 'None', 'None'), -- Samuel Harris
    (15, 25, true, 'None', 'None', 'None', 'None'),  -- Project 25: Ava Scott
    (8, 25, false ,  'None', 'None', 'None', 'None'), -- Jessica Turner
    (16, 25, true,  'None', 'None', 'None', 'None'), -- Eve White
    (6, 26, true, 'None', 'None', 'None', 'None'),   -- Project 26: Bob Johnson
    (15, 26, true, 'None', 'None', 'None', 'None'),  -- Ava Scott
    (10, 26, false, 'None', 'None', 'None', 'None'), -- Emily Walker
    (12, 26, false, 'None', 'None', 'None', 'None'), -- Daniel King
    (10, 27, true, 'None', 'None', 'None', 'None'),  -- Project 27: Emily Walker
    (15, 28, true, 'None', 'None', 'None', 'None'),  -- Project 28: Ava Scott
    (24, 29, false, 'None', 'None', 'None', 'None'),  -- Project 29: William Jones
    (4, 30, true, 'None', 'None', 'None', 'None'),   -- Project 30: Marta Silva
    (19, 30, false, 'None', 'None', 'None', 'None'),
    (10, 30, true, 'None', 'None', 'None', 'None'),  -- Emily Walker
    (10, 31, false, 'None', 'None', 'None', 'None'),  -- Project 31: Emily Walker
    (14, 32, false, 'None', 'None', 'None', 'None'),  -- Project 32: Noah Wright
    (15, 32, false, 'None', 'None', 'None', 'None'),  -- Ava Scott
    (19, 32, false, 'None', 'None', 'None', 'None'),  -- Mia Robinson
    (20, 32, false, 'None', 'None', 'None', 'None'),  -- Samuel Harris
    (24, 32, false, 'None', 'None', 'None', 'None'),  -- William Jones
    (19, 33, false, 'None', 'None', 'None', 'None'),  -- Project 33: Mia Robinson
    (10, 34, false, 'None', 'None', 'None', 'None'),  -- Project 34: Emily Walker
    (5, 35, true, 'None', 'None', 'None', 'None'),   -- Project 35: Pedro Gonçalo Oliveira
    (1, 35, true, 'None', 'None', 'None', 'None') ,  -- Alice Smith
    (6, 35, true, 'None', 'None', 'None', 'None'),   -- Bob Johnson
    (16, 36, true, 'None', 'None', 'None', 'None'),  -- Project 36: Eve White
    (2, 36, true, 'None', 'None', 'None', 'None'),   -- Adriana Almeida
    (2, 37, true, 'None', 'None', 'None', 'None'),   -- Project 37: Adriana Almeida
    (10, 37, false, 'None', 'None', 'None', 'None'), -- Emily Walker
    (12, 37, false, 'None', 'None', 'None', 'None'), -- Daniel King
    (19, 38, true, 'None', 'None', 'None', 'None'),  -- Project 38: Mia Robinson
    (4, 39, true, 'None', 'None', 'None', 'None'),   -- Project 39: Marta Silva
    (1, 39, false, 'None', 'None', 'None', 'None'),  -- Alice Smith
    (3, 39, false, 'None', 'None', 'None', 'None'),  -- Bruno Aguiar
    (3, 40, true, 'None', 'None', 'None', 'None'),   -- Project 40: Bruno Aguiar
    (9, 40, false, 'None', 'None', 'None', 'None'),   -- Michael Green
    (20, 40, false, 'None', 'None', 'None', 'None'), -- Samuel Harris
    (21, 40, false, 'None', 'None', 'None', 'None'), -- Chloe Martinez
    (22, 40, false, 'None', 'None', 'None', 'None'), -- Henry Douglas
    (23, 40, false, 'None', 'None', 'None', 'None'), -- Sophia Lee
    (24, 40, false, 'None', 'None', 'None', 'None'), -- William Jones
    (25, 40, false, 'None', 'None', 'None', 'None'), -- Amelia Moore
    (26, 40, false, 'None', 'None', 'None', 'None'), -- Nathan Taylor
    (5, 28, false, 'None', 'None', 'None', 'None'),   -- Project 36: Pedro Gonçalo Oliveira
    (5, 31, false, 'None', 'None', 'None', 'None'),   -- Project 36: Pedro Gonçalo Oliveira
    (5, 32, false, 'None', 'None', 'None', 'None'),   -- Project 37: Pedro Gonçalo Oliveira
    (5, 33, false, 'None', 'None', 'None', 'None'),   -- Project 36: Pedro Gonçalo Oliveira
    (5, 34, false, 'None', 'None', 'None', 'None'),   -- Project 37: Pedro Gonçalo Oliveira
    (5, 36, false, 'None', 'None', 'None', 'None'),   -- Project 36: Pedro Gonçalo Oliveira
    (5, 37, false, 'None', 'None', 'None', 'None'),   -- Project 37: Pedro Gonçalo Oliveira
    (5, 39, false, 'None', 'None', 'None', 'None'),   -- Project 36: Pedro Gonçalo Oliveira
    (5, 40, true, 'None', 'None', 'None', 'None');   -- Project 37: Pedro Gonçalo Oliveira


INSERT INTO invitation (id,project, account, accepted)
VALUES
    (0,2, 15, false), -- Invitation for Ava Scott to Project 2
    (1,3, 21, false), -- Invitation for Chloe Martinez to Project 3
    (2,0, 21, false), -- Invitation for Chloe Martinez to Project 0
    (3, 1, 22, false), -- Invitation for Henry Douglas to Project 1
    (4, 1, 23, false), -- Invitation for Sophia Lee to Project 1
    (5, 2, 24, false), -- Invitation for William Jones to Project 2
    (6, 2, 25, false), -- Invitation for Amelia Moore to Project 2
    (7, 3, 26, false), -- Invitation for Nathan Taylor to Project 3
    (8, 3, 22, false), -- Invitation for Henry Douglas to Project 3
    (9, 4, 1, false), -- Invitation for Alice Smith to Project 4
    (10, 4, 23, false), -- Invitation for Sophia Lee to Project 4
    (11, 4, 24, false), -- Invitation for William Jones to Project 4
    (12, 4, 25, false), -- Invitation for Amelia Moore to Project 4
    (13, 4, 26, false), -- Invitation for Nathan Taylor to Project 4
    (14, 5, 22, false), -- Invitation for Henry Douglas to Project 5
    (15, 5, 2, false), -- Invitation for Adriana Almeida to Project 5
    (16, 5, 24, false), -- Invitation for William Jones to Project 5
    (17, 5, 25, false), -- Invitation for Amelia Moore to Project 5
    (18, 5, 26, false), -- Invitation for Nathan Taylor to Project 5
    (19, 6, 22, false), -- Invitation for Henry Douglas to Project 6
    (20, 6, 23, false), -- Invitation for Sophia Lee to Project 6
    (21, 6, 24, false), -- Invitation for William Jones to Project 6
    (22, 6, 25, false), -- Invitation for Amelia Moore to Project 6
    (23, 6, 2, false), -- Invitation for Adriana Almeida to Project 6
    (24, 8, 12, false), -- Invitation for Henry Douglas to Project 8
    (25, 8, 2, false), -- Invitation for Adriana Almeida to Project 8
    (26, 10, 2, false), -- Invitation for Henry Douglas to Project 10
    (27, 10, 23, false), -- Invitation for Sophia Lee to Project 10
    (28, 10, 26, false), -- Invitation for Nathan Taylor to Project 10
    (29, 10, 25, false), -- Invitation for Amelia Moore to Project 10
    (30, 11, 6, false), -- Invitation for Bob Johnson to Project 11
    (31, 11, 16, false), -- Invitation for Eve White to Project 11
    (32, 11, 24, false), -- Invitation for William Jones to Project 11
    (33, 11, 25, false), -- Invitation for Amelia Moore to Project 11
    (34, 11, 26, false), -- Invitation for Nathan Taylor to Project 11
    (35 , 12, 17, false), -- Invitation for Lucas Perez to Project 12
    (36 , 12, 25, false), -- Invitation for Amelia Moore to Project 12
    (37 , 12, 26, false), -- Invitation for Nathan Taylor to Project 12
    (38, 13, 7, false), -- Invitation for Tom Davis to Project 13
    (39, 13, 17, false), -- Invitation for Lucas Perez to Project 13
    (40, 14, 1, false), --
    (41, 14, 24 , false), -- Invitation for William Jones to Project 14
    (42, 15, 1, false), -- Invitation for Alice Smith to Project 15
    (43, 15, 10, false), -- Invitation for Emily Walker to Project 15
    (44, 15, 13, false), -- Invitation for Olivia Clark to Project 15
    (45, 15, 24, false), -- Invitation for William Jones to Project 15
    (46, 15, 25, false), -- Invitation for Amelia Moore to Project 15
    (47, 15, 26, false), -- Invitation for Nathan Taylor to Project 15
    (48, 16, 2, false), -- Invitation for Adriana Almeida to Project 16
    (49, 16, 10, false), -- Invitation for Emily Walker to Project 16
    (50, 16, 13, false), -- Invitation for Olivia Clark to Project 16
    (51, 16, 24, false), -- Invitation for William Jones to Project 16
    (52, 16, 25, false), -- Invitation for Amelia Moore to Project 16
    (53, 16, 26, false), -- Invitation for Nathan Taylor to Project 16
    (54, 17, 2, false), -- Invitation for Adriana Almeida to Project 17
    (55, 17, 10, false), -- Invitation for Emily Walker to
    (56, 18, 2, false), -- Invitation for Adriana Almeida to Project 18
    (57, 18, 10, false), -- Invitation for Emily Walker to Project 18
    (58, 18, 13, false), -- Invitation for Olivia Clark to Project 18
    (59, 18, 25, false), -- Invitation for Amelia Moore to Project 18
    (60, 18, 26, false), -- Invitation for Nathan Taylor to Project 18
    (61, 19, 7, false), --  Invitation for Tom Davis to Project 19
    (62, 19, 10, false), -- Invitation for Emily Walker to
    (63, 20, 3, false), -- Invitation for Bruno Aguiar to Project 20
    (64, 20, 11, false), -- Invitation for Charlie Brown to Project 20
    (65, 20, 13, false), -- Invitation for Olivia Clark to Project 20
    (66, 20, 24, false), -- Invitation for William Jones to Project 20
    (67, 20, 25, false), -- Invitation for Amelia Moore to Project 20
    (68, 20, 26, false), -- Invitation for Nathan Taylor to Project 20
    (69, 21, 2, false), -- Invitation for Adriana Almeida to Project 21
    (70, 21, 11, false), -- Invitation for Charlie Brown to Project 21
    (71, 21, 13, false), -- Invitation for Olivia Clark to Project 21
    (72, 21, 24, false), -- Invitation for William Jones to Project 21
    (73, 21, 25, false), -- Invitation for Amelia Moore to Project 21
    (74, 21, 26, false), -- Invitation for Nathan Taylor to Project 21
    (75, 22, 4, false), -- Invitation for Marta Silva to Project 22
    (76, 22, 10, false), -- Invitation for Emily Walker to Project 22
    (77, 22, 13, false), -- Invitation for Olivia Clark to Project 22
    (78, 22, 24, false), -- Invitation for William Jones to Project 22
    (79, 22, 25, false), -- Invitation for Amelia Moore to Project 22
    (80, 22, 26, false), -- Invitation for Nathan Taylor to Project 22
    (81, 23, 2, false), -- Invitation for Adriana Almeida to Project 23
    (82, 23, 10, false), -- Invitation for Emily Walker to Project 23
    (83, 23, 14, false), -- Invitation for Noah Wright to Project 23
    (84 , 26, 2, false), -- Invitation for Adriana Almeida to Project 26
    (85 , 26, 11, false), -- Invitation for Charlie Brown to Project 26
    (86 , 26, 13, false), -- Invitation for Olivia Clark to Project 26
    (87, 28, 8, false),
    (88, 28, 24, false),
    (89, 28, 26, false),
    (90, 29, 1, false),
    (91, 29, 19, false),
    (92, 31, 16, false),
    (93, 31, 2, false),
    (94, 31, 11, false),
    (95, 32, 2, false),
    (96, 32, 10, false),
    (97, 32, 13, false),
    (98, 32, 25, false),
    (99, 32, 26, false),
    (100, 33, 2, false),
    (101, 33, 10, false),
    (102, 33, 13, false),
    (103, 33, 24, false),
    (104, 33, 25, false),
    (105, 33, 26, false),
    (106, 34, 2, false),
    (107, 34, 11, false),
    (108, 34, 13, false),
    (109, 34, 14, false),
    (110, 34, 15, false),
    (111, 34, 16, false),
    (112, 35, 8, false),
    (113, 35, 9, false),
    (114, 35, 19, false),
    (115, 36, 3, false),
    (116, 36, 10, false),
    (117, 36, 13, false),
    (118, 36, 24, false),
    (119, 36, 25, false),
    (120, 36, 26, false),
    (121, 37, 23, false),
    (122, 37, 13, false),
    (123, 37, 18, false),
    (124, 37, 21, false),
    (125, 37, 22, false),
    (126, 38, 2, false),
    (127, 38, 3, false),
    (128, 38, 4, false),
    (129, 38, 5, false),
    (130, 38, 6, false),
    (131, 38, 7, false),
    (132, 39, 8, false),
    (133, 39, 9, false),
    (134, 39, 10, false),
    (135, 39, 11, false);


INSERT INTO task_table (id, name, project, position)
VALUES
    (0,'Deleted tasks', 0, 0),      -- Project 0
    (1,'To Do', 0, 1),
    (2,'Assigned', 0, 2),
    (3, 'In Progress', 0, 3),
    (4,'Completed', 0, 4),
    (5,'Deleted tasks', 1, 0),      -- Project 1
    (6,'Initial Research', 1, 1),
    (7,'Campaign Design', 1, 2),
    (8,'Content Creation', 1, 3),
    (9,'Ad Placements', 1, 4),
    (10,'Social Media Push', 1, 5),
    (11,'Analytics & Reporting', 1, 6),
    (12,'Completed', 1, 7),
    (13,'Deleted tasks', 2, 0),      -- Project 2
    (14,'Planning', 2, 1),
    (15,'Design', 2, 2),
    (16,'Development', 2, 3),
    (17,'Testing', 2, 4),
    (18,'Deployment', 2, 5),
    (19,'Review', 2, 6),
    (20,'Completed', 2, 7),
    (21,'Deleted tasks', 3, 0),      -- Project 3
    (22,'To Do', 3, 1),
    (23,'Assigned', 3, 2),
    (24,'In Progress', 3, 3),
    (25,'Waiting for Review', 3, 4),
    (26,'Pending Approval', 3, 5),
    (27,'Completed', 3, 6),
    (28, 'Collect data requirements', 4, 1),
    (29, 'Analyze data sets', 4, 2),
    (30, 'Generate insights', 4, 3),
    (32, 'Prepare presentation', 4, 4),
    (33, 'Identify processes to optimize', 4, 5),
    (34, 'Analyze current workflows', 5, 1),
    (35, 'Propose optimization solutions', 5, 2),
    (36, 'Implement solutions', 5, 3),
    (37, 'Assess current network', 6, 1),
    (38, 'Plan upgrade strategy', 6, 2),
    (39, 'Execute upgrades', 7, 1),
    (40, 'Test', 7, 2),
    (41, 'Define CRM requirements', 7, 3),
    (42, 'Select CRM software', 7, 4),
    (43, 'Implement CRM system', 7, 5),
    (44, 'Final Product', 7, 6),
    (45, 'Design platform architecture', 8, 1),
    (46, 'Develop core features', 8, 2),
    (47, 'Perform usability testing', 8, 3),
    (48, 'Launch platform', 8, 4),
    (49, 'Gather app requirements', 9, 1),
    (50, 'Develop mobile app', 9, 2),
    (51, 'Test app functionality', 9, 3),
    (52, 'Deploy app to stores', 9, 4),
    (53, 'Incomplete', 10, 1),
    (54, 'Complete', 10, 2),
    (55, 'To-Do', 10, 3),
    (56, 'Tests', 10, 4),
    (57, 'Define chatbot use cases', 11, 1),
    (58, 'Develop AI models', 11, 2),
    (59, 'Integrate chatbot with systems', 11, 3),
    (60, 'Test chatbot interactions', 11, 4),
    (61, 'Assess current cybersecurity', 12, 1),
    (62, 'Identify vulnerabilities', 12, 2),
    (63, 'Implement cybersecurity measures', 12, 3),
    (64, 'Monitor and improve security', 12, 4),
    (65, 'Identify training needs', 14, 1),
    (66, 'Develop training materials', 14, 2),
    (67, 'Conduct training sessions', 14, 3),
    (68, 'Evaluate training effectiveness', 14, 4),
    (69, 'Plan website redesign', 15, 1),
    (70, 'Develop new UI/UX', 15, 2),
    (71, 'Migrate content to new design', 15, 3),
    (72, 'Launch redesigned website', 15, 4),
    (73, 'Completed', 15, 5),
    (74, 'Develop integration strategy', 16, 1),
    (75, 'Implement IoT devices', 16, 3),
    (76, 'Monitor', 16, 4),
    (77, 'Define data processing goals', 17, 1),
    (78, 'Set up big data infrastructure', 17, 2),
    (79, 'Process large datasets', 17, 3),
    (80, 'Generate data insights', 17, 4),
    (81, 'Define system requirements', 20, 1),
    (82, 'Develop healthcare modules', 20, 2),
    (83, 'Test system functionality', 20, 3),
    (84, 'Discussion', 21, 1),
    (85, 'Plan platform design', 21, 2),
    (86, 'Develop platform features', 21, 3),
    (87, 'Test platform usability', 21, 4),
    (88, 'Launch education platform', 21, 5),
    (89, 'Assess blockchain use cases', 22, 1),
    (90, 'Design blockchain framework', 22, 2),
    (91, 'Implement blockchain features', 22, 3),
    (92, 'Test blockchain functionality', 22, 4),
    (93, 'Identify CRM enhancement needs', 23, 1),
    (94, 'Develop enhanced features', 23, 3),
    (95, 'Tasks', 24, 1),
    (96, 'Define project scope', 25, 1),
    (97, 'Create initial features', 25, 2),
    (98, 'Develop core features', 25, 3),
    (99, 'Perform unit testing', 25, 4),
    (100, 'Deploy to staging environment', 25, 5),
    (101, 'Gather stakeholder feedback', 25, 6),
    (102, 'Completed', 25, 7),
    (103, 'Define project scope', 26, 1),
    (104, 'Create initial design mockups', 26, 2),
    (105, 'Develop core features', 26, 3),
    (106, 'Perform unit testing', 26, 4),
    (107, 'Deploy to staging environment', 26, 5),
    (108, 'Gather stakeholder feedback', 26, 6),
    (109, 'Completed', 26, 7),
    (110, 'To Do', 27, 1),
    (111, 'Create initial design mockups', 28, 1),
    (112, 'Develop core features', 28, 2),
    (113, 'Perform unit testing', 28, 3),
    (114, 'Deploy to staging environment', 28, 4),
    (115, 'Gather stakeholder feedback', 28, 5),
    (116, 'Completed', 28, 6),
    (117, 'Define project scope', 29, 1),
    (118, 'Create initial design mockups', 29, 2),
    (119, 'Develop core features', 29, 3),
    (120, 'Completed', 29, 4),
    (121, 'Define project scope', 30, 1),
    (122, 'Create initial design mockups', 30, 2),
    (123, 'Develop core features', 30, 3),
    (124, 'Perform unit testing', 30, 4),
    (125, 'In Progress', 31, 1),
    (126, 'Deleted', 31, 2),
    (127, 'Completed', 31, 3),
    (128, 'To Do', 32, 1),
    (129, 'In Progress', 32, 2),
    (130, 'Completed', 32, 3),
    (131, 'Testing', 32, 4),
    (132, 'Create initial design mockups', 34, 1),
    (133, 'Features in Progress', 34, 2),
    (134, 'Deploy to staging environment', 34, 3),
    (135, 'Gather feedback', 34, 4),
    (136, 'Define goals', 35, 1),
    (137, 'Create initial features', 35, 2),
    (138, 'Develop', 35, 3),
    (139, 'Completed', 35, 4),
    (140, 'Define project scope', 36, 1),
    (141, 'Create initial design mockups', 36, 2),
    (142, 'Develop core features', 36, 3),
    (143, 'Deploy to staging environment', 36, 4),
    (144, 'Initial Set Up', 37, 1),
    (145, 'In Progress', 37, 2),
    (146, 'Completed', 37, 3),
    (147, 'Develop core features', 38, 1),
    (148, 'Testing', 38, 2),
    (149, 'To Do', 39, 1),
    (150, 'In Progress', 39, 2),
    (151, 'Completed', 39, 3),
    (152,'Deleted tasks', 23, 0),
    (153,'Deleted tasks', 24, 0),
    (154,'Deleted tasks', 25, 0),
    (155,'Deleted tasks', 26, 0),
    (156,'Deleted tasks', 27, 0),
    (157,'Deleted tasks', 28, 0),
    (158,'Deleted tasks', 29, 0),
    (159,'Deleted tasks', 30, 0),
    (160,'Deleted tasks', 31, 0),
    (161,'Deleted tasks', 32, 0),
    (163,'Deleted tasks', 33, 0),
    (164,'Deleted tasks', 35, 0),
    (165,'Deleted tasks', 36, 0),
    (166,'Deleted tasks', 37, 0),
    (167,'Deleted tasks', 38, 0),
    (168,'Deleted tasks', 39, 0),
    (169,'Deleted tasks', 40, 0),
    (170,'Deleted tasks', 18, 0),
    (171,'Deleted tasks', 19, 0),
    (172,'Deleted tasks', 5, 0),
    (173,'Deleted tasks', 6, 0),
    (174,'Deleted tasks', 7, 0),
    (175,'Deleted tasks', 8, 0),
    (176,'Deleted tasks', 9, 0),
    (177,'Deleted tasks', 10, 0),
    (178,'Deleted tasks', 11, 0),
    (179,'Deleted tasks', 12, 0),
    (180,'Deleted tasks', 13, 0),
    (181,'Deleted tasks', 14, 0),
    (182,'Deleted tasks', 15, 0),
    (183,'Deleted tasks', 16, 0),
    (184,'Deleted tasks', 17, 0);




INSERT INTO task (id, task_table, name, description, start_date, deadline_date, finish_date, priority, position)
VALUES
    -- Tasks for Project 0
    (0, 1, 'Implement High-Priority User Stories', 'Work on all user stories with high priority', '2024-11-25', NULL, NULL, 'High', 0), -- Project 0
    (1, 1, 'Project Setup and Initialization', 'Initialize the project with necessary tables, roles, and initial data', '2024-11-20', NULL, NULL, 'High', 1),
    (2, 2, 'Deliver EBD Artifact', 'Prepare and submit the EBD artifact as required', '2024-12-02','2024-12-10', '2024-12-02', 'High', 0),
    (3, 2, 'Complete Artifact Checklist', 'Review and complete all required items on the artifact checklist.', '2024-11-28', '2024-12-20',NULL, 'High', 1),
    (4, 3, 'Creating SQL', 'Developing SQL statements for project database schema and data population', '2024-12-01', NULL, NULL, 'Medium', 1),
    (5, 3, 'Writing SQL Population Script', 'Develop SQL to populate tables with initial data', '2024-11-25', '2024-12-10', NULL, 'Low', 0),
    (6, 3, 'Implementing Triggers', 'Define and create triggers to enforce business rules', '2024-11-29','2024-12-10', NULL, 'High', 3),
    (7, 3, 'Review Business Requirements', 'Analyze requirements and confirm they match implementation goals', '2024-12-02',NULL, NULL, 'Low', 2),
    (8, 3, 'Configuring Transactions', 'Setting up transactions with appropriate isolation levels', '2024-11-05', '2024-12-20', NULL, 'Medium', 4),
    (9, 4, 'User Story Implementation', 'Complete implementation of high-priority user stories', '2024-12-01','2024-12-30', NULL, 'High', 0),
    (10, 4, 'Design Wireframes', 'Develop wireframes for application UI based on requirements', '2024-12-01', '2024-12-10','2024-12-01', 'High', 1),
    (11, 4, 'UML Class Model', 'Design and document the UML class model', '2024-12-03',NULL, NULL, 'Low', 2),
    (12, 4, 'Relational Model', 'Design and document the relational model', '2024-12-04',NULL, NULL, 'High', 3),
    -- Tasks for Project 1
    (13, 6, 'Conduct Initial Market Research', 'Research market trends and competitor analysis.', '2024-11-22', NULL, '2024-12-03', 'Medium', 0),
    (14, 7, 'Design Campaign Assets', 'Create visual and text assets for the campaign.', '2024-12-02', NULL, NULL, 'High', 0),
    (15, 8, 'Produce Content for Ads', 'Develop engaging content for ad placements.', '2024-12-03', NULL, NULL, 'Low', 0),
    (16, 9, 'Secure Ad Placement Deals', 'Negotiate ad placements across platforms.', '2024-12-01', NULL, NULL, 'High', 0),
    (17, 10, 'Execute Social Media Campaign', 'Launch and monitor social media push.', '2024-12-02', NULL, NULL, 'High', 0),
    (18, 11, 'Analyze Campaign Performance', 'Track and report campaign analytics.', '2024-12-04', NULL, NULL, 'High', 0),
    -- Tasks for Project 2
    (19, 14, 'Define Project Scope', 'Outline project objectives and deliverables.', '2024-11-20', NULL, '2024-11-28', 'High', 0),
    (20, 15, 'Create Initial Design Mockups', 'Develop early design mockups for feedback.', '2024-12-02', NULL, NULL, 'Medium', 0),
    (21, 16, 'Develop Core Features', 'Implement core functionalities.', '2024-12-01', NULL, NULL, 'High', 0),
    (22, 17, 'Perform Unit Testing', 'Test individual components for errors.', '2024-12-03', NULL, NULL, 'High', 0),
    (23, 18, 'Deploy to Staging Environment', 'Prepare project for staging deployment.', '2024-12-04', NULL, NULL, 'High', 0),
    (24, 19, 'Gather Stakeholder Feedback', 'Review project with stakeholders for adjustments.', '2024-12-04', NULL, NULL, 'Medium', 0),
    -- Tasks for Project 3
    (25, 22, 'Gather Initial Requirements', 'Collect requirements from key stakeholders.', '2024-11-21', NULL, '2024-11-29', 'High', 0),
    (26, 23, 'Assign Development Tasks', 'Distribute tasks to development team.', '2024-12-02', NULL, NULL, 'Medium', 0),
    (27, 24, 'Track Progress', 'Monitor progress on assigned tasks.', '2024-12-03', NULL, NULL, 'Medium', 0),
    (28, 25, 'Review Task Completion', 'Ensure tasks meet quality standards.', '2024-12-04', NULL, NULL, 'High', 0),
    (29, 26, 'Approval Meeting', 'Hold a meeting to approve project milestones.', '2024-11-28', NULL, '2024-11-28', 'High', 0),
    (30, 26, 'Generate insights', 'Compile analysis findings into actionable insights.', '2024-10-28', NULL, '2024-11-28', 'High', 1),
    -- Tasks for Project 4
    (31, 28, 'Collect data requirements', 'Gather data requirements from stakeholders.', '2024-11-21', NULL, NULL, 'High', 0),
    (32, 29, 'Analyze data sets', 'Analyze data sets to identify trends and patterns.', '2024-12-02', NULL, NULL, 'Medium', 0),
    (33, 30, 'Generate insights', 'Develop insights based on data analysis.', '2024-12-01', NULL, NULL, 'High', 0),
    (34, 32, 'Prepare presentation', 'Create a presentation to share insights with stakeholders.', '2024-12-03', NULL, NULL, 'High', 0),
    (35, 33, 'Identify processes to optimize', 'Identify processes that can be optimized based on insights.', '2024-12-04', NULL, NULL, 'High', 0),
    -- Tasks for Project 5
    (36, 34, 'Analyze current workflows', 'Review current workflows and identify bottlenecks.', '2024-11-21', NULL, NULL, 'High', 0),
    (37, 34, 'Gather app requirements', 'Identify the key features and requirements for the mobile app.', '2024-12-03',  NULL, NULL, 'High', 1),
    (38, 34, 'Develop mobile app', 'Start coding the mobile app based on the gathered requirements.', '2024-12-03', NULL, NULL, 'Medium', 2),
    (39, 34, 'Test app functionality', 'Ensure that the app works as expected and fix any issues that arise.', '2024-12-03', NULL, NULL, 'Medium', 3),
    (40, 35, 'Propose optimization solutions', 'Develop solutions to optimize identified workflows.', '2024-12-02', NULL, NULL, 'Medium', 0),
    (41, 35, 'Plan migration', 'Plan the steps for migrating data from legacy systems to new platforms.', '2024-11-21', '2024-12-01', NULL, 'High', 1),
    (42, 35, 'Prepare data for migration', 'Prepare and clean the data for the migration process.', '2024-12-01', '2024-12-21', NULL, 'High', 2),
    (43, 36, 'Implement solutions', 'Implement proposed solutions to optimize workflows.', '2024-12-01', NULL, NULL, 'High', 0),
    -- Tasks for Project 6
    (44, 37, 'Assess current network', 'Review the current network infrastructure and identify areas for improvement.', '2024-11-21', NULL, NULL, 'High', 0),
    (45, 37, 'Plan upgrade strategy', 'Develop a strategy for upgrading the network infrastructure.', '2024-12-03', NULL, NULL, 'High', 1),
    (46, 37, 'Execute upgrades', 'Implement the planned upgrades to the network infrastructure.', '2024-12-03', NULL, NULL, 'Medium', 2),
    (47, 37, 'Perform tests', 'Test the upgraded network to ensure it meets performance requirements.', '2024-12-03', NULL, NULL, 'Medium', 3),
    (48, 37, 'Define CRM requirements', 'Gather requirements for the new CRM system.', '2024-11-21', NULL, NULL, 'High', 4),
    (49, 37, 'Select CRM software', 'Research and select the CRM software that best meets the requirements.', '2024-12-03', NULL, NULL, 'Low', 5),
    (50, 38, 'Implement CRM system', 'Install and configure the selected CRM software.', '2024-12-03', NULL, NULL, 'High', 0),
    (51, 38, 'Train employees on CRM', 'Provide training to employees on how to use the new CRM system.', '2024-12-03', NULL, NULL, 'High', 1),
    (52, 38, 'Define system requirements', 'Gather requirements for the new platform architecture.', '2024-11-21', NULL, NULL, 'High', 2),
    (53, 38, 'Develop core features', 'Implement the core features of the new platform.', '2024-12-03', NULL, NULL, 'High', 3),
    (54, 38, 'Perform usability testing', 'Test the usability of the new platform with real users.', '2024-12-03', NULL, NULL, 'Low', 4),
    (55, 38, 'Launch platform', 'Deploy the new platform to production.', '2024-12-03', NULL, NULL, 'High', 5),
    -- Tasks for Project 7
    (56, 39, 'Define Project Goals', 'Outline the main objectives and deliverables for the project.', '2024-11-25', NULL, NULL, 'High', 0),
    (57, 39, 'Develop Initial Wireframes', 'Create wireframes for the user interface.', '2024-11-26', NULL, NULL, 'Medium', 1),
    (58, 39, 'Set Up Development Environment', 'Configure the development environment and tools.', '2024-11-27', NULL, NULL, 'High', 2),
    (59, 40, 'Implement Core Features', 'Develop the core functionalities of the project.', '2024-11-28', NULL, NULL, 'High', 0),
    (60, 40, 'Conduct Code Review', 'Review the code for quality and adherence to standards.', '2024-11-29', NULL, NULL, 'Medium', 1),
    (61, 40, 'Perform Integration Testing', 'Test the integration of different components.', '2024-11-30', NULL, NULL, 'High', 2),
    (62, 40, 'Deploy to Staging', 'Deploy the project to the staging environment for further testing.', '2024-12-01', NULL, NULL, 'High', 3),
    (63, 40, 'Gather User Feedback', 'Collect feedback from users to identify areas for improvement.', '2024-12-02', NULL, NULL, 'Medium', 4),
    (64, 41, 'Optimize Performance', 'Improve the performance of the project based on feedback.', '2024-12-03', NULL, NULL, 'High', 0),
    (65, 44, 'Prepare for Production Release', 'Finalize the project for production release.', '2024-12-04', NULL, NULL, 'High', 0),
    -- Tasks for Project 8
    (66, 45, 'Define system requirements', 'Outline the functional and non-functional requirements for the platform.', '2024-11-21', NULL, NULL, 'High', 0),
    (67, 45, 'Design data flow diagram', 'Create a diagram illustrating the flow of data within the platform.', '2024-11-21', NULL, NULL, 'High', 1),
    (68, 45, 'Create a security framework', 'Design the security measures for the platform, including authentication and encryption.','2024-11-21', NULL, NULL, 'High', 2),
    (69, 46, 'Develop user profile management', 'Implement features for managing user profiles', '2024-12-03', NULL, NULL, 'High', 0),
    (70, 46, 'Search functionality', 'Design and implement search functionality', '2024-12-03', NULL, NULL, 'Medium', 1),
    (71, 47, 'Perform usability testing', 'Test the usability of the platform with real users.', '2024-12-03', NULL, NULL, 'High', 0),
    (72, 47, 'Analyze usability results', ' Review the collected data to identify patterns and usability issues.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (73, 48, 'Launch platform', 'Deploy the platform to production.', '2024-12-03', NULL, NULL, 'High', 0),
    -- Tasks for Project 9
    (74, 49, 'Gather app requirements', 'Identify the key features and requirements for the mobile app.', '2024-12-03', NULL, NULL, 'High', 0),
    (75, 50, 'Develop mobile app', 'Start coding the mobile app based on the gathered requirements.', '2024-12-03', NULL, NULL, 'Medium', 0),
    (76, 51, 'Prepare test environment', 'Set up the platform for testing, ensuring it''s stable and functioning properly.', '2024-12-03', NULL, NULL, 'Low', 0),
    (77, 51, 'Test Product', 'Ensure that the app works as expected and fix any issues that arise.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (78, 52, 'Deploy app to stores', 'Release the app to app stores for public use.', '2024-12-03', NULL, NULL, 'High', 0),
    -- Tasks for Project 10
    (79, 53, 'Define testing goals', 'Set clear objectives for testing.', '2024-11-21', '2024-12-01', NULL, 'High', 0),
    (80, 54, 'Prepare data for migration', 'Prepare and clean the data for the migration process.', '2024-12-01', '2024-12-21', NULL, 'High', 0),
    (81, 55, 'Design database schema', 'Create a schema design for the database, including tables and relationships.', '2024-12-03', NULL, NULL, 'High', 0),
    (82, 56, 'Conduct first round of testing', 'Ensure that the data is correct and is accurate.', '2024-12-03', NULL, NULL, 'High', 0),
    (83, 56, 'Record user interactions', 'Capture data on how users interact with the platform.', '2024-12-03', NULL, NULL, 'High', 1),
    (84, 56, 'Analyze usability results', 'Review the collected data to identify patterns and usability issues..', '2024-12-03', NULL, NULL, 'High', 2),
    (85, 56, 'Prepare usability report', 'Create a report with recommendations for improving the platform’s usability based on finding.', '2024-12-03', NULL, NULL, 'High', 3),
    (86, 56, 'Validate data integrity', 'Ensure that the data has been migrated correctly and is accurate.', '2024-12-03', NULL, NULL, 'High', 4),
    --Tasks for Project 11
    (87, 57, 'Define chatbot use cases', 'Identify the key use cases for the chatbot.', '2024-11-21', NULL, NULL, 'High', 0),
    (88, 58, 'Develop AI models', 'Create the AI models that will power the chatbot.', '2024-12-03', NULL, NULL, 'Medium', 0),
    (89, 59, 'Integrate chatbot with systems', 'Connect the chatbot to the necessary systems and databases.', '2024-12-03', NULL, NULL, 'High', 0),
    (90, 60, 'Test chatbot interactions', 'Test the chatbot’s interactions with users.', '2024-12-03', NULL, NULL, 'High', 0),
    (91, 60, 'Conduct Code Review', 'Review the code for quality and adherence to standards.', '2024-11-29', NULL, NULL, 'Medium', 1),
    (92, 60, 'Perform Integration Testing', 'Test the integration of different components.', '2024-11-30', NULL, NULL, 'High', 2),
    (93, 58, 'Deploy to Staging', 'Deploy the project to the staging environment for further testing.', '2024-12-01', NULL, NULL, 'Low', 1),
    (94, 58, 'Gather User Feedback', 'Collect feedback from users to identify areas for improvement.', '2024-12-02', NULL, NULL, 'Medium', 2),
    -- Tasks for Project 12
    (95, 61, 'Review existing cybersecurity policies', 'Evaluate the current cybersecurity policies and procedures.', '2024-12-01', '2024-12-03', NULL, 'High', 0),
    (96, 61, 'Conduct risk assessment', 'Analyze potential security risks to the organization.', '2024-12-03', '2024-12-06', NULL, 'High', 1),
    (97, 61, 'Evaluate current infrastructure', 'Assess the existing network and system infrastructure for weaknesses.', '2024-12-06', '2024-12-08', NULL, 'High', 2),
    (98, 61, 'Create cybersecurity inventory', 'Catalog all systems, software, and assets for cybersecurity tracking.', '2024-12-08', '2024-12-10', NULL, 'Medium', 3),
    (99, 61, 'Define security objectives', 'Set clear cybersecurity goals and objectives.', '2024-12-10', '2024-12-12', NULL, 'Medium', 4),
    (100, 62, 'Perform vulnerability scan', 'Use tools to identify vulnerabilities across systems.', '2024-12-13', '2024-12-15', NULL, 'High', 0),
    (101, 62, 'Analyze scan results', 'Interpret the results from vulnerability scans and prioritize issues.', '2024-12-15', '2024-12-16', NULL, 'Low', 1),
    (102, 62, 'Assess business impact', 'Determine the potential business impact of identified vulnerabilities.', '2024-12-15', '2024-12-16', NULL, 'High', 2),
    (103, 63, 'Monitor network traffic', 'Continuously monitor network traffic for anomalies.', '2024-12-15', NULL, NULL, 'High', 0),
    (104, 63, 'Perform regular audits', 'Schedule and conduct regular security audits.', '2024-12-15', NULL, NULL, 'Medium', 1),
    (105, 63, 'Train employees on security', 'Provide training to employees on best cybersecurity practices.', '2024-12-15', NULL, NULL, 'Low', 2),
    (106, 63, 'Test incident response plan', 'Simulate incidents to test the effectiveness of the response plan.', '2024-12-15', NULL, NULL, 'Medium', 3),
    (107, 63, 'Update security tools', 'Upgrade or replace outdated security tools and systems.', '2024-12-15', NULL, NULL, 'Low', 4),
    (108, 63, 'Review security logs', 'Regularly review security logs for suspicious activity.', '2024-12-08', '2024-12-08', NULL, 'Low', 5),
    (109, 63, 'Implement multi-factor authentication', 'Enable MFA for all critical systems and applications.', '2024-12-08', NULL, NULL, 'High', 6),
    (110, 63, 'Update cybersecurity policies', 'Revise policies to align with new cybersecurity threats.', '2024-12-08', NULL, NULL, 'Medium', 7),
    (111, 63, 'Perform threat intelligence analysis', 'Gather and analyze threat intelligence to anticipate risks.', '2024-12-08', NULL, NULL, 'Medium', 8),
    (112, 63, 'Develop a cybersecurity roadmap', 'Create a long-term plan for continuous cybersecurity improvement.', '2024-12-08', NULL, NULL, 'Medium', 9),
    -- Tasks for Project 14
    (113, 65, 'Identification', 'Assess the training needs of the organization.', '2024-11-21', NULL, NULL, 'High', 0),
    (114, 65, 'Development', 'Create training materials based on identified needs.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (115, 65, 'First training session', 'Deliver training sessions to employees.', '2024-12-03', NULL, NULL, 'High', 2),
    (116, 65, 'Identify training needs', 'Assess the training needs of the organization.', '2024-11-21', NULL, NULL, 'Low', 3),
    (117, 66, 'Develop training materials', 'Create training materials based on identified needs.', '2024-12-03', NULL, NULL, 'Medium', 0),
    (118, 66, 'Conduct training sessions', 'Deliver training sessions to employees.', '2024-12-03', NULL, NULL, 'High', 1),
    (119, 66, 'Evaluate training effectiveness', 'Evaluate the effectiveness of the training sessions.', '2024-12-10', NULL, NULL, 'Medium', 2),
    (120, 67, 'Plan website redesign', 'Outline the plan for redesigning the company website.', '2024-11-25', NULL, NULL, 'High', 0),
    (121, 67, 'Develop new UI/UX', 'Create new UI/UX designs for the website.', '2024-12-01', NULL, NULL, 'High', 1),
    (122, 67, 'Migrate content to new design', 'Migrate existing content to the new website design.', '2024-12-05', NULL, NULL, 'Low', 2),
    (123, 67, 'Launch redesigned website', 'Launch the newly redesigned website.', '2024-12-10', NULL, NULL, 'High', 3),
    (124, 67, 'Gather user feedback', 'Collect feedback from users on the new website design.', '2024-12-15', NULL, NULL, 'Low', 4),
    -- Tasks for Project 15
    (125, 69, 'Plan platform design', 'Create a design plan for the new platform.', '2024-11-21', NULL, NULL, 'High', 0),
    (126, 69, 'Develop platform features', 'Implement the core features of the new platform.', '2024-12-03', NULL, NULL, 'High', 1),
    (127, 69, 'Migrate content to new design', 'Migrate existing content to the new platform design.', '2024-12-03', NULL, NULL, 'Low', 2),
    (128, 69, 'Launch education platform', 'Deploy the new platform to production.', '2024-12-03', NULL, NULL, 'High', 3),
    (129, 69, 'Assess platform performance', 'Evaluate the performance of the new platform.', '2024-12-03', NULL, NULL, 'Medium', 4),
    -- Tasks for Project 16
    (130, 73, 'Develop integration strategy', 'Create a plan for integrating IoT devices with existing systems.', '2024-11-21', NULL, NULL, 'High', 0),
    (131, 73, 'Implement IoT devices', 'Install and configure IoT devices according to the integration strategy.', '2024-12-03', NULL, NULL, 'High', 1),
    (132, 73, 'Monitor', 'Monitor the performance and data generated by IoT devices.', '2024-12-03', NULL, NULL, 'Medium', 2),
    (133, 74, 'Define data processing goals', 'Set clear objectives for processing large datasets.', '2024-11-21', NULL, NULL, 'High', 0),
    (134, 74, 'Set up big data infrastructure', 'Configure the infrastructure needed for processing large datasets.', '2024-12-03', NULL, NULL, 'Low', 1),
    (135, 74, 'Process large datasets', 'Analyze and process large datasets to extract insights.', '2024-12-03', NULL, NULL, 'High', 2),
    (136, 74, 'Generate data insights', 'Create actionable insights from the processed data.', '2024-12-03', NULL, NULL, 'Low', 3),
    (137, 75, 'Define system requirements', 'Gather requirements for the new healthcare system.', '2024-11-21', NULL, NULL, 'High', 0),
    (138, 75, 'Develop healthcare modules', 'Create modules for managing patient data and appointments.', '2024-12-03', NULL, NULL, 'High', 1),
    (139, 75, 'Test system functionality', 'Ensure that the healthcare system works as expected.', '2024-12-03', NULL, NULL, 'Low', 2),
    (140, 75, 'Discussion', 'Discuss the project requirements and goals with the team.', '2024-11-21', NULL, NULL, 'High', 3),
    -- Tasks fro Project 17
    (141, 77, 'Define data processing goals', 'Set clear objectives for processing large datasets.', '2024-11-21', NULL, NULL, 'High', 0),
    (142, 77, 'Set up big data infrastructure', 'Configure the infrastructure needed for processing large datasets.', '2024-12-03', NULL, NULL, 'High', 1),
    (143, 77, 'Process large datasets', 'Analyze and process large datasets to extract insights.', '2024-12-03', NULL, NULL, 'Medium', 2),
    (144, 77, 'Generate data insights', 'Create actionable insights from the processed data.', '2024-12-03', NULL, NULL, 'High', 3),
    (145, 78, 'Define system requirements', 'Gather requirements for the new healthcare system.', '2024-11-21', NULL, NULL, 'Low', 0),
    (146, 78, 'Develop healthcare modules', 'Create modules for managing patient data and appointments.', '2024-12-03', NULL, NULL, 'High', 1),
    (147, 78, 'Test system functionality', 'Ensure that the healthcare system works as expected.', '2024-12-03', NULL, NULL, 'Low', 2),
    (148, 78, 'Discussion', 'Discuss the project requirements and goals with the team.', '2024-11-21', NULL, NULL, 'High', 3),
    -- Tasks for Project 20
    (149, 81, 'Define project scope', 'Outline the objectives and deliverables for the project.', '2024-11-21', NULL, NULL, 'High', 0),
    (150, 81, 'Develop initial wireframes', 'Create wireframes for the user interface.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (151, 81, 'Set up development environment', 'Configure the development environment and tools.', '2024-12-03', NULL, NULL, 'High', 2),
    (152, 82, 'Implement core features', 'Develop the core functionalities of the project.', '2024-12-03', NULL, NULL, 'High', 0),
    (153, 82, 'Conduct code review', 'Review the code for quality and adherence to standards.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (154, 82, 'Perform integration testing', 'Test the integration of different components.', '2024-12-03', NULL, NULL, 'High', 2),
    (155, 82, 'Deploy to staging', 'Deploy the project to the staging environment for further testing.', '2024-12-03', NULL, NULL, 'Low', 3),
    (156, 82, 'Gather user feedback', 'Collect feedback from users to identify areas for improvement.', '2024-12-03', NULL, NULL, 'Medium', 4),
    (157, 83, 'Optimize performance', 'Improve the performance of the project based on feedback.', '2024-12-03', NULL, NULL, 'High', 0),
    (158, 83, 'Prepare for production release', 'Finalize the project for production release.', '2024-12-03', NULL, NULL, 'High', 1),
    -- Tasks for Project 21
    (159, 84, 'Define system requirements', 'Outline the functional and non-functional requirements for the platform.', '2024-11-21', NULL, NULL, 'High', 0),
    (160, 84, 'Design data flow diagram', 'Create a diagram illustrating the flow of data within the platform.', '2024-11-21', NULL, NULL, 'High', 1),
    (161, 84, 'Create a security framework', 'Design the security measures for the platform, including authentication and encryption.', '2024-11-21', NULL, NULL, 'Low', 2),
    (162, 85, 'Develop user profile management', 'Implement features for managing user profiles', '2024-12-03', NULL, NULL, 'High', 0),
    (163, 85, 'Search functionality', 'Design and implement search functionality', '2024-12-03', NULL, NULL, 'Medium', 1),
    (164, 86, 'Perform usability testing', 'Test the usability of the platform with real users.', '2024-12-03', NULL, NULL, 'High', 0),
    (165, 86, 'Analyze usability results', ' Review the collected data to identify patterns and usability issues.', '2024-12-03', NULL, NULL, 'Low', 1),
    (166, 88, 'Launch platform', 'Deploy the platform to production.', '2024-12-03', NULL, NULL, 'High', 0),
    -- Tasks for Project 22
    (167, 89, 'Gather app requirements', 'Identify the key features and requirements for the mobile app.', '2024-12-03', NULL, NULL, 'Low', 0),
    (168, 90, 'Develop mobile app', 'Start coding the mobile app based on the gathered requirements.', '2024-12-03', NULL, NULL, 'Medium', 0),
    (169, 91, 'Prepare test environment', 'Set up the platform for testing, ensuring it''s stable and functioning properly.', '2024-12-03', NULL, NULL, 'Low', 0),
    (170, 91, 'Test Product', 'Ensure that the app works as expected and fix any issues that arise.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (171, 92, 'Deploy app to stores', 'Release the app to app stores for public use.', '2024-12-03', NULL, NULL, 'High', 0),
    -- Tasks for Project 24
    (172, 95, 'Define testing goals', 'Set clear objectives for testing.', '2024-11-21', '2024-12-01', NULL, 'High', 0),
    (173, 95, 'Prepare data for migration', 'Prepare and clean the data for the migration process.', '2024-12-01', '2024-12-21', NULL, 'Low', 1),
    (174, 95, 'Design database schema', 'Create a schema design for the database, including tables and relationships.', '2024-12-03', NULL, NULL, 'High', 2),
    (175, 95, 'Conduct first round of testing', 'Ensure that the data is correct and is accurate.', '2024-12-03', NULL, NULL, 'High', 3),
    (176, 95, 'Record user interactions', 'Capture data on how users interact with the platform.', '2024-12-03', NULL, NULL, 'Low', 4),
    (177, 95, 'Analyze usability results', 'Review the collected data to identify patterns and usability issues..', '2024-12-03', NULL, NULL, 'High', 5),
    (178, 95, 'Prepare usability report', 'Create a report with recommendations for improving the platform’s usability based on finding.', '2024-12-03', NULL, NULL, 'Medium', 6),
    (179, 95, 'Validate data integrity', 'Ensure that the data has been migrated correctly and is accurate.', '2024-12-03', NULL, NULL, 'Low', 7),
    -- Tasks for Project 25
    (180, 97, 'Define chatbot use cases', 'Identify the key use cases for the chatbot.', '2024-11-21', NULL, NULL, 'High', 0),
    (181, 98, 'Develop AI models', 'Create the AI models that will power the chatbot.', '2024-12-03', NULL, NULL, 'Medium', 0),
    (182, 99, 'Integrate chatbot with systems', 'Connect the chatbot to the necessary systems and databases.', '2024-12-03', NULL, NULL, 'Low', 0),
    (183, 100, 'Test chatbot interactions', 'Test the chatbot’s interactions with users.', '2024-12-03', NULL, NULL, 'High', 0),
    (184, 100, 'Conduct Code Review', 'Review the code for quality and adherence to standards.', '2024-11-29', NULL, NULL, 'Medium', 1),
    (185, 100, 'Perform Integration Testing', 'Test the integration of different components.', '2024-11-30', NULL, NULL, 'Low', 2),
    (186, 98, 'Deploy to Staging', 'Deploy the project to the staging environment for further testing.', '2024-12-01', NULL, NULL, 'High', 1),
    (187, 98, 'Gather User Feedback', 'Collect feedback from users to identify areas for improvement.', '2024-12-02', NULL, NULL, 'Medium', 2),
    -- Tasks for Project 26
    (188, 104, 'Conduct risk assessment', 'Analyze potential security risks to the organization.', '2024-12-03', '2024-12-06', NULL, 'High', 0),
    (189, 104, 'Evaluate current infrastructure', 'Assess the existing network and system infrastructure for weaknesses.', '2024-12-06', '2024-12-08', NULL, 'High', 1),
    (190, 105, 'Perform vulnerability scan', 'Use tools to identify vulnerabilities across systems.', '2024-12-13', '2024-12-15', NULL, 'Low', 0),
    (191, 105, 'Analyze results', 'Interpret the results.', '2024-12-15', '2024-12-16', NULL, 'High', 1),
    (192, 105, 'Assess business impact', 'Determine the potential business impact.', '2024-12-15', '2024-12-16', NULL, 'High', 2),
    -- Tasks for Project 27
    (193, 110, 'Assess current infrastructure', 'Evaluate the current infrastructure and identify areas for improvement.', '2024-11-21', NULL, NULL, 'High', 0),
    (194, 110, 'Plan migration strategy', 'Develop a strategy for migrating to the cloud.', '2024-12-03', NULL, NULL, 'High', 1),
    (195, 110, 'Migrate data to the cloud', 'Transfer data and applications to the cloud environment.', '2024-12-03', NULL, NULL, 'High', 2),
    (196, 110, 'Test cloud environment', 'Verify that the cloud environment is functioning as expected.', '2024-12-03', NULL, NULL, 'High', 3),
    (197, 110, 'Optimize cloud resources', 'Fine-tune cloud resources for performance and cost efficiency.', '2024-12-03', NULL, NULL, 'Low', 4),
    (198, 110, 'Train staff on cloud tools', 'Provide training to staff on using cloud services and tools.', '2024-12-03', NULL, NULL, 'Low', 5),
    (199, 110, 'Monitor cloud performance', 'Monitor the performance and security of the cloud environment.', '2024-12-03', NULL, NULL, 'Low', 6),
    (200, 110, 'Review cloud costs', 'Analyze and optimize cloud costs to ensure cost-effectiveness.', '2024-12-03', NULL, NULL, 'Medium', 7),
    (201, 110, 'Implement cloud security', 'Enhance security measures for the cloud environment.', '2024-12-03', NULL, NULL, 'High', 8),
    (202, 110, 'Document cloud architecture', 'Document the cloud architecture and configurations for future reference.', '2024-12-03', NULL, NULL, 'High', 9),
    (203, 110, 'Review cloud migration', 'Evaluate the success of the cloud migration project and identify lessons learned.', '2024-12-03', NULL, NULL, 'High', 10),
    -- Tasks for Project 28
    (204, 113, 'Define project scope', 'Outline the objectives and deliverables for the project.', '2024-11-21', NULL, NULL, 'High', 0),
    (205, 113, 'Develop initial wireframes', 'Create wireframes for the user interface.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (206, 113, 'Set up development environment', 'Configure the development environment and tools.', '2024-12-03', NULL, NULL, 'High', 2),
    (207, 114, 'Implement core features', 'Develop the core functionalities of the project.', '2024-12-03', NULL, NULL, 'High', 0),
    (208, 114, 'Conduct code review', 'Review the code for quality and adherence to standards.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (209, 114, 'Perform integration testing', 'Test the integration of different components.', '2024-12-03', NULL, NULL, 'High', 2),
    (210, 114, 'Deploy to staging', 'Deploy the project to the staging environment for further testing.', '2024-12-03', NULL, NULL, 'Low', 3),
    (211, 114, 'Gather user feedback', 'Collect feedback from users to identify areas for improvement.', '2024-12-03', NULL, NULL, 'Medium', 4),
    (212, 115, 'Optimize performance', 'Improve the performance of the project based on feedback.', '2024-12-03', NULL, NULL, 'High', 0),
    (213, 115, 'Prepare for production release', 'Finalize the project for production release.', '2024-12-03', NULL, NULL, 'High', 1),
    -- Tasks for Project 29
    (214, 117, 'Define system requirements', 'Outline the functional and non-functional requirements for the platform.', '2024-11-21', NULL, NULL, 'High', 0),
    (215, 117, 'Design data flow diagram', 'Create a diagram illustrating the flow of data within the platform.', '2024-11-21', NULL, NULL, 'Low', 1),
    (216, 117, 'Create a security framework', 'Design the security measures for the platform, including authentication and encryption.', '2024-11-21', NULL, NULL, 'High', 2),
    (217, 117, 'Develop user profile management', 'Implement features for managing user profiles', '2024-12-03', NULL, NULL, 'Low', 3),
    (218, 117, 'Search functionality', 'Design and implement search functionality', '2024-12-03', NULL, NULL, 'Medium', 4),
    (219, 118, 'Perform usability testing', 'Test the usability of the platform with real users.', '2024-12-03', NULL, NULL, 'High', 0),
    (220, 118, 'Analyze usability results', ' Review the collected data to identify patterns and usability issues.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (221, 120, 'Launch platform', 'Deploy the platform to production.', '2024-12-03', NULL, NULL, 'High', 0),
    -- Tasks for Project 30
    (222, 121, 'Define digital transformation goals', 'Set clear objectives for the digital transformation initiative.', '2024-11-21', NULL, NULL, 'High', 0),
    (223, 121, 'Assess current digital capabilities', 'Evaluate the organization’s current digital infrastructure and capabilities.', '2024-12-03', NULL, NULL, 'High', 1),
    (224, 121, 'Identify digital transformation opportunities', 'Identify areas where digital technologies can improve business processes.', '2024-12-03', NULL, NULL, 'High', 2),
    (225, 121, 'Develop digital transformation roadmap', 'Create a plan for implementing digital initiatives and technologies.', '2024-12-03', NULL, NULL, 'High', 3),
    (226, 121, 'Implement digital solutions', 'Deploy digital tools and technologies to support business operations.', '2024-12-03', NULL, NULL, 'High', 4),
    (227, 121, 'Monitor digital transformation progress', 'Track the implementation and impact of digital initiatives.', '2024-12-03', NULL, NULL, 'High', 5),
    (228, 121, 'Evaluate digital transformation outcomes', 'Assess the results and benefits of the digital transformation initiative.', '2024-12-03', NULL, NULL, 'High', 6),
    (229, 121, 'Adjust digital strategy', 'Make adjustments to the digital transformation strategy based on feedback and outcomes.', '2024-12-03', NULL, NULL, 'High', 7),
    -- Tasks for project 31
    (230, 125, 'Define project scope', 'Outline the objectives and deliverables for the project.', '2024-11-21', NULL, NULL, 'High', 0),
    (231, 125, 'Develop initial wireframes', 'Create wireframes for the user interface.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (232, 125, 'Set up development environment', 'Configure the development environment and tools.', '2024-12-03', NULL, NULL, 'High', 2),
    (233, 126, 'Implement core features', 'Develop the core functionalities of the project.', '2024-12-03', NULL, NULL, 'High', 0),
    (234, 126, 'Conduct code review', 'Review the code for quality and adherence to standards.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (235, 126, 'Perform integration testing', 'Test the integration of different components.', '2024-12-03', NULL, NULL, 'High', 2),
    (236, 126, 'Deploy to staging', 'Deploy the project to the staging environment for further testing.', '2024-12-03', NULL, NULL, 'High', 3),
    (237, 126, 'Gather user feedback', 'Collect feedback from users to identify areas for improvement.', '2024-12-03', NULL, NULL, 'Low', 4),
    (238, 127, 'Optimize performance', 'Improve the performance of the project based on feedback.', '2024-12-03', NULL, NULL, 'High', 0),
    (239, 127, 'Prepare for production release', 'Finalize the project for production release.', '2024-12-03', NULL, NULL, 'High', 1),
    --Tasks for Project 32
    (240, 128, 'Define system requirements', 'Outline the functional and non-functional requirements for the platform.', '2024-11-21', NULL, NULL, 'High', 0),
    (241, 128, 'Design data flow diagram', 'Create a diagram illustrating the flow of data within the platform.', '2024-11-21', NULL, NULL, 'High', 1),
    (242, 128, 'Create a security framework', 'Design the security measures for the platform, including authentication and encryption.', '2024-11-21', NULL, NULL, 'High', 2),
    (243, 128, 'Search functionality', 'Design and implement search functionality', '2024-12-03', NULL, NULL, 'Medium', 4),
    (244, 130, 'Perform usability testing', 'Test the usability of the platform with real users.', '2024-12-03', NULL, NULL, 'High', 0),
    (245, 130, 'Analyze usability results', ' Review the collected data to identify patterns and usability issues.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (246, 131, 'Launch platform', 'Deploy the platform to production.', '2024-12-03', NULL, NULL, 'High', 0),
    -- Tasks for Project 34
    (247, 132, 'Define project scope', 'Outline the objectives and deliverables for the project.', '2024-11-21', NULL, NULL, 'High', 0),
    (248, 133, 'Develop initial wireframes', 'Create wireframes for the user interface.', '2024-12-03', NULL, NULL, 'Medium', 0),
    (249, 133, 'Set up development environment', 'Configure the development environment and tools.', '2024-12-03', NULL, NULL, 'Low', 1),
    (250, 134, 'Implement core features', 'Develop the core functionalities of the project.', '2024-12-03', NULL, NULL, 'High', 0),
    (251, 134, 'Conduct code review', 'Review the code for quality and adherence to standards.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (252, 134, 'Perform integration testing', 'Test the integration of different components.', '2024-12-03', NULL, NULL, 'High', 2),
    (253, 134, 'Deploy to staging', 'Deploy the project to the staging environment for further testing.', '2024-12-03', NULL, NULL, 'Low', 3),
    (254, 134, 'Gather user feedback', 'Collect feedback from users to identify areas for improvement.', '2024-12-03', NULL, NULL, 'Medium', 4),
    (255, 135, 'Optimize performance', 'Improve the performance of the project based on feedback.', '2024-12-03', NULL, NULL, 'High', 0),
    (256, 135, 'Prepare for production release', 'Finalize the project for production release.', '2024-12-03', NULL, NULL, 'High', 1),
    -- Tasks for Project 35
    (257, 136, 'Define chatbot use cases', 'Identify the key use cases for the chatbot.', '2024-11-21', NULL, NULL, 'High', 0),
    (258, 137, 'Develop AI models', 'Create the AI models that will power the chatbot.', '2024-12-03', NULL, NULL, 'Medium', 0),
    (259, 138, 'Integrate chatbot with systems', 'Connect the chatbot to the necessary systems and databases.', '2024-12-03', NULL, NULL, 'High', 0),
    (260, 139, 'Test chatbot interactions', 'Test the chatbot’s interactions with users.', '2024-12-03', NULL, NULL, 'High', 0),
    (261, 139, 'Conduct Code Review', 'Review the code for quality and adherence to standards.', '2024-11-29', NULL, NULL, 'Medium', 1),
    (262, 139, 'Perform Integration Testing', 'Test the integration of different components.', '2024-11-30', NULL, NULL, 'High', 2),
    (263, 139, 'Deploy to Staging', 'Deploy the project to the staging environment for further testing.', '2024-12-01', NULL, NULL, 'High', 3),
    (264, 137, 'Gather User Feedback', 'Collect feedback from users to identify areas for improvement.', '2024-12-02', NULL, NULL, 'Medium', 1),
    -- Tasks for Projects 36
    (265, 142, 'Perform analysis', 'Gather and analyze threat intelligence to anticipate risks.', '2024-12-08', NULL, NULL, 'Medium', 0),
    (266, 142, 'Develop a cybersecurity roadmap', 'Create a long-term plan for continuous cybersecurity improvement.', '2024-12-08', NULL, NULL, 'Medium', 1),
    -- Tasks for Projects 37
    (267, 144, 'Define project scope', 'Outline the objectives and deliverables for the project.', '2024-11-21', NULL, NULL, 'High', 0),
    (268, 144, 'Develop initial wireframes', 'Create wireframes for the user interface.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (269, 144, 'Set up development environment', 'Configure the development environment and tools.', '2024-12-03', NULL, NULL, 'High', 2),
    (270, 145, 'Implement core features', 'Develop the core functionalities of the project.', '2024-12-03', NULL, NULL, 'High', 0),
    (271, 145, 'Conduct code review', 'Review the code for quality and adherence to standards.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (272, 145, 'Perform integration testing', 'Test the integration of different components.', '2024-12-03', NULL, NULL, 'High', 2),
    (273, 145, 'Deploy to staging', 'Deploy the project to the staging environment for further testing.', '2024-12-03', NULL, NULL, 'High', 3),
    (274, 145, 'Gather user feedback', 'Collect feedback from users to identify areas for improvement.', '2024-12-03', NULL, NULL, 'Low', 4),
    (275, 145, 'Optimize performance', 'Improve the performance of the project based on feedback.', '2024-12-03', NULL, NULL, 'High', 5),
    (276, 146, 'Prepare for production release', 'Finalize the project for production release.', '2024-12-03', NULL, NULL, 'High', 0),
    -- Tasks for Project 39
    (277, 149, 'Define project scope', 'Outline the objectives and deliverables for the project.', '2024-11-21', NULL, NULL, 'High', 0),
    (278, 149, 'Develop initial wireframes', 'Create wireframes for the user interface.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (279, 149, 'Set up development environment', 'Configure the development environment and tools.', '2024-12-03', NULL, NULL, 'High', 2),
    (280, 150, 'Implement core features', 'Develop the core functionalities of the project.', '2024-12-03', NULL, NULL, 'High', 0),
    (281, 150, 'Conduct code review', 'Review the code for quality and adherence to standards.', '2024-12-03', NULL, NULL, 'Medium', 1),
    (282, 150, 'Perform integration testing', 'Test the integration of different components.', '2024-12-03', NULL, NULL, 'High', 2),
    (283, 150, 'Deploy to staging', 'Deploy the project to the staging environment for further testing.', '2024-12-03', NULL, NULL, 'Low', 3),
    (284, 150, 'Gather user feedback', 'Collect feedback from users to identify areas for improvement.', '2024-12-03', NULL, NULL, 'Medium', 4),
    (285, 151, 'Optimize performance', 'Improve the performance of the project based on feedback.', '2024-12-03', NULL, NULL, 'High', 5),
    (286, 151, 'Prepare for production release', 'Finalize the project for production release.', '2024-12-03', NULL, NULL, 'High', 6);



INSERT INTO account_task (account, task)
VALUES 
    -- Project 0
    (1, 0),  -- Alice Smith assigned to "Implement High-Priority User Stories" 
    (2, 1),  -- Adriana Almeida assigned to "Project Setup and Initialization" 
    (3, 2),  -- Bruno Aguiar assigned to "Deliver EBD Artifact" 
    (4, 3),  -- Marta Silva assigned to "Complete Artifact Checklist" 
    (1, 4),  -- Alice Smith assigned to "Creating SQL" 
    (2, 8),  -- Adriana Almeida assigned to "Configuring Transactions"
    (3, 10), -- Bruno Aguiar assigned to "Design Wireframes" 
    (4, 12), -- Marta Silva assigned to "Relational Model"
    (5, 12), -- Pedro Oliveira assigned to "Relational Model"
    (5, 9), -- Pedro Oliveira assigned to "User Story Implementation"
    (5, 8), -- Pedro Oliveira assigned to "Configuring Transactions"
    (5, 5), -- Pedro Oliveira assigned to "Creating SQL"
    (5, 2), -- Pedro Oliveira assigned to "Deliver EBD Artifact"
    -- Project 1
    (6, 13),   -- Bob Johnson assigned to "Conduct Initial Market Research" 
    (7, 14),   -- Tom Davis assigned to "Design Campaign Assets"
    (8, 15),   -- Jessica Turner assigned to "Produce Content for Ads"
    (9, 16),   -- Michael Green assigned to "Secure Ad Placement Deals"
    (10, 17),   -- Emily Walker assigned to "Execute Social Media Campaign"
    (6, 18),   -- Bob Johnson assigned to "Analyze Campaign Performance"
    -- Project 2 
    (11, 19),  -- Charlie Brown assigned to "Define Project Scope"
    (12, 20),  -- Daniel King assigned to "Create Initial Design Mockups"
    (13, 21),  -- Olivia Clark assigned to "Develop Core Features"
    (14, 22),  -- Noah Wright assigned to "Perform Unit Testing"
    (11, 23),  -- Charlie Brown assigned to "Deploy to Staging Environment"
    (12, 24),  -- Daniel King assigned to "Gather Stakeholder Feedback"
    -- Project 3 
    (16, 25),  -- Eve White assigned to "Gather Initial Requirements"
    (17, 26),  -- Lucas Perez assigned to "Assign Development Tasks"
    (18, 27),  -- Ethan Carter assigned to "Track Progress"
    (19, 28),  -- Mia Robinson assigned to "Review Task Completion"
    (20, 29),  -- Samuel Harris assigned to "Approval Meeting"
    -- Project 4
    (22, 31),
    (8, 34),
    (3, 35),
    (8, 35),
    (4, 33),
    (4, 31),
    (3, 33),
    -- Project 5
    (23,43),
    (14,39),
    (19,40),
    (14,41),

    -- Project 6
    (15,44),
    (26,44),
    (15,45),
    (26,47),
    (8,53),
    (15,53),
    (26,55),
    -- Project 7
    (24,56),
    (24,57),
    (12,58),
    (12,59),
    (4,61),
    -- Project 8
    (10,67),
    (10,68),
    (4,69),
    (15,70),
    (4,70),
    (24,70),
    --Project 9
    (10,74),
    (17,74),
    (2,74),
    (19,75),
    (3,75),
    (18,75),
    (10,75),
    (18,77),
    (20,77),
    (20,78),
    (3,78),
    --Project 10
    (15,79),
    (15,81),
    --Project 12
    (14,95),
    (16,95),
    (14,96),
    (24,97),
    (24,99),
    (14,101),
    (6, 102),
    (19,103),
    (10,108),
    --Project 14
    (19,113),
    (17,113),
    (17,114),
    (20,115),
    (18,117),
    --Project 15
    (4,129),
    (4,127),
    (9,128),
    (12,126),
    (4,125),
    (12,125),
    --Project 16

    (3,137),
    (17,137),
    (20,138),
    (3,139),
    (17,139),
    (20,140),
    --Project 17
    (1,144),
    (14,141),
    (12,145),
    (1,145),
    (12,148),
    (14,148),
    --Project 20
    (10, 149),
    (10,151),
    (4,152),
    (2,155),
    (10,155),
    --Project 21
    (10,159),
    (10,160),
    (18, 160),
    (18,164),
    (10,165),
    --Project 22
    (19,168),
    (3,169),
    (2,170),
    (20,171),
    --Project 24
    (8,172),
    (3,173),
    (8,174),
    (20,176),
    (20,177),
    (3,178),
    (3,179),
    --Project 25
    (15,180),
    (16,181),
    (8,184),
    --Project 26
    (6,188),
    (15,189),
    (12,191),
    (10,191),
    --Project 27
    (10,193),
    --Project 30
    (10,222),
    (19,224),
    (4,225),
    (19,226),
    (19,227),
    (10,228),
    --Project 31
    (10,230),
    (10,231),
    --Project 32
    (24,240),
    (19,241),
    (20,242),
    (20,243),
    (15,243),
    (24,243),
    (15,244),
    (14,246),
    --Project 34
    (10,247),
    (10,248),
    --Project 35
    (5,258),
    (1,259),
    (5,260),
    (6,261),
    (1,262),
    (5,262),
    --Project 36
    (16,265),
    (2,265),
    (16,266),
    --Project 37
    (12,267),
    (2,269),
    (12,270),
    --Project 39
    (4,277),
    (4,278),
    (3,280),
    (3,281),
    (4,282),
    (3,282),
    (1,284),
    (1,285);


INSERT INTO comment (id, account, content, create_date, task)
VALUES
    (0, 1, 'Initial discussion on requirements for user stories.', '2024-11-26 09:15:00', 0),  -- Alice Smith on task 0 in Project 0
    (1, 2, 'Setup is complete. Ready for the next steps.', '2024-12-05 14:30:00', 1),          -- Adriana Almeida on task 1 in Project 0
    (2, 4, 'Checklist is being updated as requested.', '2024-12-03 11:45:00', 3),             -- Marta Silva on task 3 in Project 0
    (3, 6, 'Campaign design draft is under review.', '2024-11-30 16:20:00', 5),               -- Bob Johnson on task 5 in Project 1
    (4, 8, 'Content creation is progressing well.', '2024-11-26 10:05:00', 6),                -- Jessica Turner on task 6 in Project 1
    (5, 11, 'Wireframes for the main modules are ready.', '2024-12-05 13:40:00', 10),         -- Charlie Brown on task 10 in Project 2
    (6, 16, 'UML diagrams completed for the main classes.', '2024-12-02 17:15:00', 12);       -- Eve White on task 12 in Project 3


INSERT INTO forum_message (id, account, project, content, create_date)
VALUES 
    (0, 1, 0, 'Welcome to the Project Management System! Let’s get started!', CURRENT_TIMESTAMP), -- Alice Smith in Project 0
    (1, 2, 0, 'Looking forward to working with you all on this project.', CURRENT_TIMESTAMP),      -- Adriana Almeida in Project 0
    (2, 6, 1, 'The campaign project is shaping up nicely.', CURRENT_TIMESTAMP),                    -- Bob Johnson in Project 1
    (3, 9, 1, 'Ads are ready to be placed. Waiting for approvals.', CURRENT_TIMESTAMP),            -- Michael Green in Project 1
    (4, 11, 2, 'Please review the wireframes I uploaded.', CURRENT_TIMESTAMP),                     -- Charlie Brown in Project 2
    (5, 16, 3, 'Architecture design phase is almost complete.', CURRENT_TIMESTAMP);                -- Eve White in Project 3


INSERT INTO project_event (account, task, time, event_type)
VALUES
    -- Project 0
    (2, 1, '2024-11-20 09:00:00', 'Task_Completed'),  -- Task: Project Setup and Initialization
    (3, 3, '2024-11-28 10:30:00', 'Task_Completed'),  -- Task: Complete Artifact Checklist
    (5, 7, '2024-12-02 14:00:00', 'Task_Completed'),  -- Task: Review Business Requirements
    (4, 10, '2024-12-01 16:15:00', 'Task_Completed'), -- Task: Design Wireframes
    -- Project 1
    (7, 13, '2024-12-03 08:45:00', 'Task_Completed'), -- Task: Conduct Initial Market Research
    -- Project 2
    (14, 19, '2024-11-28 11:00:00', 'Task_Completed'), -- Task: Define Project Scope
    -- Project 3
    (18, 25, '2024-11-29 13:30:00', 'Task_Completed'), -- Task: Gather Initial Requirements
    (20, 29, '2024-11-28 15:00:00', 'Task_Completed'), -- Task: Approval Meeting
    (23,42, '2024-11-25 18:00:00', 'Task_Unassigned'),
    -- Project 6
    (26,44, '2024-11-29 15:00:00', 'Task_Unassigned'),
    (8,44, '2024-11-30 15:00:00', 'Task_Unassigned'),
    -- Project 8
    (4,66, '2024-11-28 17:00:00','Task_Unassigned' ),

    -- Project 9
    (2, 78, '2024-12-08 12:00:00','Task_Unassigned'),
    (10, 78, '2024-12-09 12:00:00','Task_Unassigned'),

    --Project 14
    (19, 114, '2024-12-06 19:00:00','Task_Unassigned'),
    -- Project 15
    (9,125, '2024-11-11 15:00:00','Task_Unassigned'),
    (12,125, '2024-11-12 15:00:00','Task_Unassigned'),
    -- Project 16
    (20, 135,'2024-11-27 13:00:00','Task_Unassigned' ),
    -- Project 17
    (14, 144,'2024-11-23 15:00:00', 'Task_Unassigned'),
    (1, 141,'2024-11-24 15:00:00', 'Task_Unassigned'),

    --Project 22
    (7,167, '2024-11-20 09:00:00','Task_Unassigned'),
    --Project 24
    (3,175, '2024-11-20 19:00:00','Task_Unassigned'),
    --Project 30
    (19,224, '2024-11-20 18:00:00','Task_Unassigned'),
    (4,229, '2024-11-24 15:00:00','Task_Unassigned'),

    --Project 32
    (24,244, '2024 11-27 19:00:00','Task_Unassigned'),
    (19,245, '2024 11-28 20:00:00','Task_Unassigned'),
    --Project 37
    (10, 268, '2024-11-21 15:00:00', 'Task_Unassigned'),

    --Project 39
    (4, 279, '2024-11-23 15:00:00', 'Task_Unassigned');


-- Project Presentation
-- Insert project
INSERT INTO project (id, name, description, isPublic, project_coordinator_id) VALUES
                                                                              (41, 'Arraial d''Engenharia', 'An engineering festival celebrating culture, music, and entertainment.', FALSE, 1);
SELECT SETVAL('project_id_seq', (SELECT MAX(id) FROM project) + 1);
-- Insert project members
INSERT INTO project_member (account, project, is_favourite) VALUES
(1, 41,false), (2, 41,false), (3, 41,false), (4, 41,false), (5, 41,true), (6, 41,false), (7, 41,false), (8, 41,false), (9, 41,false), (10, 41,false);

-- Insert task tables
INSERT INTO task_table (id, name, project, position) VALUES
(185,'Deleted tasks', 41, 0),
(186,'Performers and Entertainment', 41, 1),
(187,'Logistics and Operations', 41, 2),
(188,'Audience Experience', 41, 3),
(189,'Promotion and Outreach', 41, 4),
(190,'Sponsorship and Finance', 41, 5);

SELECT SETVAL('task_table_id_seq', (SELECT MAX(id) FROM task_table) + 1);

-- Insert tasks
INSERT INTO task (id, task_table, name, description, priority, position, start_date, deadline_date, finish_date) VALUES
-- Performers and Entertainment
(287,185, 'Book the main musical act', 'Confirm and sign a contract with the main performer.', 'High', 0, '2024-01-01', '2025-01-15', '2024-12-15'),
(288,185, 'Schedule DJs', 'Coordinate performance times for DJs.', 'Medium', 1, '2024-11-01', '2025-02-01', NULL),
(289,185, 'Organize cultural showcases', 'Plan traditional dances and performances.', 'Medium', 2, '2024-10-15', '2025-01-20', '2024-12-10'),
(290,185, 'Plan interactive activities', 'Include games and workshops for engagement.', 'Low', 3, '2024-12-01', '2025-03-10', NULL),
(291,185, 'Create performance schedule', 'Develop a timetable for all acts.', 'High', 4, '2024-11-01', '2025-02-25', '2024-12-18'),
(292,185, 'Arrange artist accommodations', 'Book hotels and transportation for performers.', 'Medium', 5, '2024-11-15', '2025-02-28', NULL),
(293,185, 'Coordinate sound checks', 'Schedule rehearsal times for all acts.', 'High', 6, '2024-12-20', '2025-03-01', NULL),
(305,186, 'Hire backup performers', 'Identify and contract backup performers.', 'Medium', 7, '2024-12-15', '2025-03-30', NULL),
(306,186, 'Design stage layout', 'Create layout designs for the stage and performance areas.', 'High', 8, '2024-12-20', '2024-12-30', '2024-12-22'),

-- Logistics and Operations
(294,186, 'Rent sound equipment', 'Book sound systems and backup equipment.', 'High', 0, '2024-09-05', '2025-01-10', '2024-12-01'),
(295,186, 'Set up food stands', 'Organize vendors for food and beverages.', 'Medium', 1, '2024-10-01', '2025-02-15', NULL),
(296,186, 'Arrange waste management', 'Hire cleaning crews and place trash bins.', 'Medium', 2, '2024-11-01', '2025-02-01', '2024-11-30'),
(297,186, 'Secure permits', 'Obtain all necessary permits from authorities.', 'High', 3, '2024-09-01', '2024-11-15', '2024-11-10'),
(298,186, 'Rent lighting equipment', 'Book lighting systems for stage and venue.', 'Medium', 4, '2024-10-15', '2025-01-30', NULL),
(299,186, 'Set up emergency services', 'Coordinate with medical and security services.', 'High', 5, '2024-11-20', '2025-03-05', NULL),
(300,186, 'Organize parking facilities', 'Provide parking solutions for attendees.', 'Medium', 6, '2024-12-10', '2025-03-20', NULL),
(307,187, 'Order portable toilets', 'Ensure sufficient facilities for guests.', 'Medium', 7, '2024-12-01', '2025-03-25', NULL),
(308,187, 'Coordinate volunteers', 'Recruit and assign volunteer roles.', 'High', 8, '2024-12-10', '2025-03-15', NULL),

-- Audience Experience
(301,187, 'Set up ticketing system', 'Design and test ticketing distribution process.', 'High', 0, '2024-11-10', '2025-01-20', '2024-12-12'),
(302,187, 'Create festival map', 'Develop and print maps for attendees.', 'Medium', 1, '2024-10-15', '2025-02-10', NULL),
(303,187, 'Organize photo booth area', 'Design and decorate the photo booth zone.', 'Low', 2, '2024-11-20', '2025-03-01', NULL),
(304,187, 'Provide accessibility options', 'Ensure ramps and seating for differently-abled guests.', 'High', 3, '2024-10-25', '2025-01-30', '2024-11-15'),
(309,188, 'Develop festival app', 'Create an app for schedules, maps, and updates.', 'High', 4, '2024-11-15', '2025-03-25', NULL),
(310,188, 'Prepare gift bags', 'Assemble and distribute welcome kits for attendees.', 'Low', 5, '2024-11-20', '2025-04-05', NULL),

-- Sponsorship and Finance
(311,190, 'Secure sponsorship contracts', 'Sign agreements with sponsors.', 'High', 0, '2024-12-01', '2025-01-01', '2024-12-21'),
(312,190, 'Budget finalization', 'Approve and finalize the event budget.', 'High', 1, '2024-12-10', '2025-03-15', NULL),

-- Promotion and Outreach
(313,189, 'Launch social media campaign', 'Promote the event on social media platforms.', 'High', 0, '2024-11-10', '2025-01-01', '2024-12-20'),
(314,189, 'Design and distribute flyers', 'Create and distribute promotional materials.', 'Medium', 1, '2024-12-15', '2025-01-10', '2024-12-20'),
(315,189, 'Engage with influencers', 'Collaborate with influencers to boost promotion.', 'Medium', 2, '2024-12-01', '2025-02-20', NULL),
(316,189, 'Organize press releases', 'Coordinate with media for event coverage.', 'High', 3, '2024-11-25', '2025-02-15', NULL);

SELECT SETVAL('task_id_seq', (SELECT MAX(id) FROM task) + 1);

-- Insert comments
INSERT INTO comment (id, account, content, task, create_date) VALUES
(7,1, 'Finalized the DJ schedule, waiting for approvals.', 288, '2024-11-15 14:35:00'), -- Schedule DJs
(8,2, 'Need suggestions for food vendors.', 295, '2024-10-20 09:15:00'), -- Set up food stands
(9,3, 'Added new poster designs for review.', 302, '2024-11-25 16:45:00'), -- Create festival map
(10,4, 'Budget report draft completed.', 300, '2024-12-05 11:30:00'), -- Organize parking facilities
(11,5, 'Confirmed waste management contracts.', 296, '2024-11-10 08:50:00'), -- Arrange waste management
(12,6, 'Sound checks scheduled for next week.', 293, '2024-12-20 10:20:00'), -- Coordinate sound checks
(13,7, 'VIP lounge setup completed.', 303, '2024-12-01 14:00:00'), -- Organize photo booth area
(14,8, 'Emergency services confirmed.', 299, '2024-12-10 12:10:00'), -- Set up emergency services
(15,10, 'Ticket sales update: 70% sold.', 301, '2024-12-15 18:45:00'), -- Set up ticketing system
(16,9, 'Backup performers list updated.', 305, '2024-12-02 13:45:00'),
(17,10, 'Stage layout designs ready for review.', 306, '2024-12-05 11:30:00'),
(18,1, 'Portable toilets ordered, awaiting delivery.', 307, '2024-12-10 09:00:00'),
(19,2, 'Volunteer recruitment drive successful.', 308, '2024-12-12 14:15:00'),
(20,3, 'Festival app UI/UX design completed.', 309, '2024-12-15 16:45:00'),
(21,4, 'Gift bag designs approved, production started.', 310, '2024-12-18 10:20:00'),
(22,5, 'All sponsorship contracts signed.', 311, '2024-12-01 15:45:00'),
(23,6, 'Budget adjustments submitted for approval.', 312, '2024-12-10 12:30:00'),
(24,7, 'Social media ads scheduled for release.', 313, '2024-12-15 10:00:00'),
(25,8, 'Flyer designs approved, printing started.', 314, '2024-12-01 12:00:00'),
(26,9, 'Influencers list finalized. Outreach ongoing.', 315, '2024-12-10 14:30:00'),
(27,10, 'Press release drafts submitted for review.', 316, '2024-12-12 11:45:00');


SELECT SETVAL('comment_id_seq', (SELECT MAX(id) FROM comment) + 1);

-- Insert forum messages
INSERT INTO forum_message (id, account, project, content, create_date) VALUES
(6,1, 41, 'Welcome to the Arraial d''Engenharia project forum! Let''s make this a great event!', '2024-09-01 09:00:00'),
(7,2, 41, 'Any suggestions for interactive activities?', '2024-09-15 10:30:00'),
(8,3, 41, 'Reminder: Permit applications are due next week.', '2024-10-01 14:45:00'),
(9,4, 41, 'Ticket sales are going live tomorrow!', '2024-10-20 11:00:00'),
(10,5, 41, 'Sponsorship packages are being finalized.', '2024-11-01 15:30:00'),
(11,6, 41, 'Don''t forget to test the sound system before setup.', '2024-11-20 09:45:00'),
(12,7, 41, 'VIP area design concepts ready for review.', '2024-12-01 10:15:00'),
(13,8, 41, 'Medical teams confirmed their schedules.', '2024-12-05 13:20:00'),
(14,9, 41, 'Great job on social media reach. Keep pushing ads.', '2024-12-10 15:50:00'),
(15,10, 41, 'Budget review meeting scheduled for next Friday.', '2024-12-15 16:30:00'),
(16,1, 41, 'Backup performers confirmed. Let''s prepare schedules.', '2024-12-05 09:15:00'),
(17,2, 41, 'Volunteer orientation scheduled for March 20th.', '2024-12-10 14:00:00'),
(18,3, 41, 'App testing phase begins next week.', '2024-12-12 10:30:00'),
(19,4, 41, 'Reminder to submit budget approvals before March 15th.', '2024-12-14 11:45:00'),
(20,5, 41, 'Sponsors have requested logo placements. Review pending.', '2024-12-15 16:00:00');

SELECT SETVAL('forum_message_id_seq', (SELECT MAX(id) FROM forum_message) + 1);

-- Task Assignees
INSERT INTO account_task (account, task) VALUES
(1, 305), (2, 306), (3, 307), (5, 308), (5, 309), (5, 311), (7, 311),
(1, 315), (3, 287), (4, 288), (5, 289), (6, 305), (5, 294), (5, 295), (10, 294),
(1, 313), (2, 313), (5, 315), (7, 316), (8, 316);

-- Task Completed Events
INSERT INTO project_event (account, task, event_type, time) VALUES
(1, 287, 'Task_Completed', '2024-12-15 14:30:00'),
(1, 289, 'Task_Completed', '2024-12-10 09:15:00'),
(1, 291, 'Task_Completed', '2024-12-18 11:45:00'),
(1, 294, 'Task_Completed', '2024-12-01 16:20:00'),
(1, 296, 'Task_Completed', '2024-11-30 08:50:00'),
(1, 297, 'Task_Completed', '2024-11-10 13:40:00'),
(1, 301, 'Task_Completed', '2024-12-12 10:25:00'),
(1, 304, 'Task_Completed', '2024-11-15 17:10:00'),
(1, 306, 'Task_Completed', '2024-12-22 15:45:00'),
(1, 311, 'Task_Completed', '2024-12-21 12:30:00'),
(1, 313, 'Task_Completed', '2024-12-20 14:15:00'),
(1, 314, 'Task_Completed', '2024-12-20 18:00:00');

-- Task_Created Events
INSERT INTO project_event (account, task, event_type, time) VALUES
(1, 287, 'Task_Created', '2024-11-01 08:00:00'),
(1, 288, 'Task_Created', '2024-11-01 09:00:00'),
(1, 289, 'Task_Created', '2024-10-15 10:00:00'),
(1, 290, 'Task_Created', '2024-12-01 11:00:00'),
(1, 291, 'Task_Created', '2024-11-01 12:00:00'),
(1, 292, 'Task_Created', '2024-11-15 13:00:00'),
(1, 293, 'Task_Created', '2024-12-20 14:00:00'),
(1, 305, 'Task_Created', '2024-12-15 15:00:00'),
(1, 306, 'Task_Created', '2024-12-20 16:00:00'),
(1, 294, 'Task_Created', '2024-09-05 08:15:00'),
(1, 295, 'Task_Created', '2024-10-01 09:15:00'),
(1, 296, 'Task_Created', '2024-11-01 10:15:00'),
(1, 297, 'Task_Created', '2024-09-01 11:15:00'),
(1, 298, 'Task_Created', '2024-10-15 12:15:00'),
(1, 299, 'Task_Created', '2024-11-20 13:15:00'),
(1, 300, 'Task_Created', '2024-12-10 14:15:00'),
(1, 307, 'Task_Created', '2024-12-01 15:15:00'),
(1, 308, 'Task_Created', '2024-12-10 16:15:00'),
(1, 301, 'Task_Created', '2024-11-10 17:15:00'),
(1, 302, 'Task_Created', '2024-10-15 08:30:00'),
(1, 303, 'Task_Created', '2024-11-20 09:30:00'),
(1, 304, 'Task_Created', '2024-10-25 10:30:00'),
(1, 309, 'Task_Created', '2024-11-15 11:30:00'),
(1, 310, 'Task_Created', '2024-11-20 12:30:00'),
(1, 311, 'Task_Created', '2024-12-01 13:30:00'),
(1, 312, 'Task_Created', '2024-12-10 14:30:00'),
(1, 313, 'Task_Created', '2024-11-10 15:30:00'),
(1, 314, 'Task_Created', '2024-12-15 16:30:00'),
(1, 315, 'Task_Created', '2024-12-01 17:30:00'),
(1, 316, 'Task_Created', '2024-11-25 08:45:00');


-- Task_Priority_Changed Events
INSERT INTO project_event (account, task, event_type, time) VALUES
(1, 287, 'Task_Priority_Changed', '2024-12-01 09:00:00'),
(1, 288, 'Task_Priority_Changed', '2024-12-02 10:00:00'),
(1, 289, 'Task_Priority_Changed', '2024-12-03 11:00:00');


-- Task_Unassigned Events
INSERT INTO project_event (account, task, event_type, time) VALUES
(1, 290, 'Task_Unassigned', '2024-12-05 12:00:00'),
(1, 291, 'Task_Unassigned', '2024-12-06 13:00:00'),
(1, 292, 'Task_Unassigned', '2024-12-07 14:00:00');

-- Invitations
INSERT INTO invitation (id,project, account)
VALUES
(136,41,11),
(137,41,12);

SELECT SETVAL('invitation_id_seq', (SELECT MAX(id) FROM invitation) + 1);

-----------------------------------------
-- end
-----------------------------------------
