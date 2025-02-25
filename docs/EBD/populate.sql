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
(10, 'Mexico');

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
(27, 'Guadalajara', 10);

INSERT INTO account (id,username, password, name, email, workfield, city, blocked, admin, account_image_id)
VALUES
    (0, 'unknown', '$2y$10$sDG7/jtFTLrnUY3KVwXPzOBB5S50MmOZMpzeUNikh9rIZ9SWuTKWa', 'Unknown', 'unknown@example.com', NULL, NULL, false, false, NULL), -- Password: hashed_password_1
    (1, 'admin_1', '$2y$10$4ZZyLt7L1lN.rsFXhiXdmOk1tbeYahNx887YIU99KNcCcnmvMVao2', 'Alice Smith', 'alice.smith@example.com', 'Software Developer', NULL, false, true, NULL), -- Password: hashed_password_1
    (2, 'adriana_almeida', '$2y$10$SlJHnh57wWgP3nzG3q.nJe8RxA53o5d9VYyAwEQAQgY3FtxBvYOfW', 'Adriana Almeida', 'adriana.almeida@example.com', 'Software Developer', NULL, false, false, NULL), -- Password: hashed_password_2
    (3, 'bruno_aguiar', '$2y$10$9fC32elQagBGfuRYs8mzf.wBopVjanVZqsPXp4yv7xFGimxdkWdq.', 'Bruno Aguiar', 'bruno.aguiar@example.com', 'Software Developer', NULL, false, false, NULL), -- Password: hashed_password_3
    (4, 'marta_silva', '$2y$10$H2ewvYnZalWlQ80hrT3ntuS4qVaRmi/0ZhPxCC49Zq5Pjxg9KzUka', 'Marta Silva', 'marta.silva@example.com', 'Software Developer', NULL, false, false, NULL), -- Password: hashed_password_4
    (5, 'pedro_oliveira', '$2y$10$Nz7JO66Immiedb7SJK0YrOag1nrJMUq6kL5hbOdUdTkKzp9ZpRGVK', 'Pedro Gonçalo Oliveira', 'pedro.oliveira@example.com', 'Software Developer', NULL, false, false, NULL), -- Password: hashed_password_5
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
    (16, 'architecture_user', '$2y$10$zg2FjEpBO9XhCoxPlSnBrOAt/2BiHQ3tFAW.KKvSF.1CHhzP/g8sa', 'Eve White', 'eve.white@example.com', 'System Architect', NULL, false, false, NULL), -- Password: hashed_password16
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


INSERT INTO project (id,name, description, isPublic, archived, createDate, finishDate, project_coordinator_id)
VALUES
    (0,'Project Management System', 'A system to manage tasks for different projects including status tracking', false, false, CURRENT_DATE, NULL, 4),
    (1,'Marketing Campaign Project', 'A project to plan, design, and execute a marketing campaign', false, false, CURRENT_DATE, NULL, 5),
    (2,'Software Development Project', 'A project to design, develop, and deploy a software application', false, false, CURRENT_DATE, NULL, 10),
    (3,'System Architecture Design Project', 'A project to design the architecture and models for a new system', false, false, CURRENT_DATE, NULL, 15);


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
    (12, 2, false, 'None', 'None', 'None', 'None'), -- Daniel King
    (13, 2, false, 'None', 'None', 'None', 'None'), -- Olivia Clark
    (14, 2, false, 'None', 'None', 'None', 'None'), -- Noah Wright
    (16, 3, false, 'None', 'None', 'None', 'None'), -- Project 3: Eve White
    (17, 3, false, 'None', 'None', 'None', 'None'), -- Lucas Perez
    (18, 3, false, 'None', 'None', 'None', 'None'), -- Ethan Carter
    (19, 3, false, 'None', 'None', 'None', 'None'), -- Mia Robinson
    (20, 3, false, 'None', 'None', 'None', 'None'); -- Samuel Harris


INSERT INTO invitation (id,project, account, accepted)
VALUES
    (0,2, 15, false), -- Invitation for Ava Scott to Project 2
    (1,3, 21, false), -- Invitation for Chloe Martinez to Project 3
    (2,0, 21, false), -- Invitation for Chloe Martinez to Project 0
    (3, 1, 22, false), -- Invitation for Henry Douglas to Project 1
    (4, 1, 23, false), -- Invitation for Sophia Lee to Project 1
    (5, 2, 24, false), -- Invitation for William Jones to Project 2
    (6, 2, 25, false), -- Invitation for Amelia Moore to Project 2
    (7, 3, 26, false); -- Invitation for Nathan Taylor to Project 3


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
    (27,'Completed', 3, 6); 


INSERT INTO task (id, task_table, name, description, start_date, finish_date, priority, position)
VALUES
    -- Tasks for Project 0
    (0, 1, 'Implement High-Priority User Stories', 'Work on all user stories with high priority', CURRENT_DATE, NULL, 'High', 0), --Project 0
    (1, 1, 'Project Setup and Initialization', 'Initialize the project with necessary tables, roles, and initial data', CURRENT_DATE,NULL, 'High', 1),
    (2, 2, 'Deliver EBD Artifact', 'Prepare and submit the EBD artifact as required', CURRENT_DATE, CURRENT_DATE, 'High', 0),
    (3, 2, 'Complete Artifact Checklist', 'Review and complete all required items on the artifact checklist.', CURRENT_DATE, NULL, 'High', 1),
    (4, 3, 'Creating SQL', 'Developing SQL statements for project database schema and data population', CURRENT_DATE, NULL, 'High', 1),
    (5, 3, 'Writing SQL Population Script', 'Develop SQL to populate tables with initial data', CURRENT_DATE, NULL, 'High', 0),
    (6, 3, 'Implementing Triggers', 'Define and create triggers to enforce business rules', CURRENT_DATE, NULL, 'High', 3),
    (7, 3, 'Review Business Requirements', 'Analyze requirements and confirm they match implementation goals', CURRENT_DATE, NULL, 'High', 2),
    (8, 3, 'Configuring Transactions', 'Setting up transactions with appropriate isolation levels', CURRENT_DATE, NULL, 'High', 4),
    (9, 4, 'User Story Implementation', 'Complete implementation of high-priority user stories', CURRENT_DATE, NULL, 'High', 0),
    (10, 4, 'Design Wireframes', 'Develop wireframes for application UI based on requirements', CURRENT_DATE, CURRENT_DATE, 'High', 1),
    (11, 4, 'UML Class Model', 'Design and document the UML class model', CURRENT_DATE, NULL, 'High', 2),
    (12, 4, 'Relational Model', 'Design and document the relational model', CURRENT_DATE, NULL, 'High', 3),
    -- Tasks for Project 1
    (13, 6, 'Conduct Initial Market Research', 'Research market trends and competitor analysis.', CURRENT_DATE, NULL, 'Medium', 0),
    (14, 7, 'Design Campaign Assets', 'Create visual and text assets for the campaign.', CURRENT_DATE, NULL, 'High', 0),
    (15, 8, 'Produce Content for Ads', 'Develop engaging content for ad placements.', CURRENT_DATE, NULL, 'High', 0),
    (16, 9, 'Secure Ad Placement Deals', 'Negotiate ad placements across platforms.', CURRENT_DATE, NULL, 'High', 0),
    (17, 10, 'Execute Social Media Campaign', 'Launch and monitor social media push.', CURRENT_DATE, NULL, 'High', 0),
    (18, 11, 'Analyze Campaign Performance', 'Track and report campaign analytics.', CURRENT_DATE, NULL, 'High', 0),
    -- Tasks for Project 2
    (19, 14, 'Define Project Scope', 'Outline project objectives and deliverables.', CURRENT_DATE, NULL, 'High', 0),
    (20, 15, 'Create Initial Design Mockups', 'Develop early design mockups for feedback.', CURRENT_DATE, NULL, 'Medium', 0),
    (21, 16, 'Develop Core Features', 'Implement core functionalities.', CURRENT_DATE, NULL, 'High', 0),
    (22, 17, 'Perform Unit Testing', 'Test individual components for errors.', CURRENT_DATE, NULL, 'High', 0),
    (23, 18, 'Deploy to Staging Environment', 'Prepare project for staging deployment.', CURRENT_DATE, NULL, 'High', 0),
    (24, 19, 'Gather Stakeholder Feedback', 'Review project with stakeholders for adjustments.', CURRENT_DATE, NULL, 'Medium', 0),
    -- Tasks for Project 3
    (25, 22, 'Gather Initial Requirements', 'Collect requirements from key stakeholders.', CURRENT_DATE, NULL, 'High', 0),
    (26, 23, 'Assign Development Tasks', 'Distribute tasks to development team.', CURRENT_DATE, NULL, 'Medium', 0),
    (27, 24, 'Track Progress', 'Monitor progress on assigned tasks.', CURRENT_DATE, NULL, 'Medium', 0),
    (28, 25, 'Review Task Completion', 'Ensure tasks meet quality standards.', CURRENT_DATE, NULL, 'High', 0),
    (29, 26, 'Approval Meeting', 'Hold a meeting to approve project milestones.', CURRENT_DATE, NULL, 'High', 0);


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
    (4, 12), -- Marta Silva assigned to "UML Class Model" 
    (5, 12), -- Pedro Oliveira assigned to "User Story Implementation"
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
    (20, 29);  -- Samuel Harris assigned to "Approval Meeting"


INSERT INTO comment (id, account, project, content, create_date, task)
VALUES 
    (0, 1, 0, 'Initial discussion on requirements for user stories.', CURRENT_DATE, 0),  -- Alice Smith on task 0 in Project 0
    (1, 2, 0, 'Setup is complete. Ready for the next steps.', CURRENT_DATE, 1),          -- Adriana Almeida on task 1 in Project 0
    (2, 4, 0, 'Checklist is being updated as requested.', CURRENT_DATE, 3),              -- Marta Silva on task 3 in Project 0
    (3, 6, 1, 'Campaign design draft is under review.', CURRENT_DATE, 5),                -- Bob Johnson on task 5 in Project 1
    (4, 8, 1, 'Content creation is progressing well.', CURRENT_DATE, 6),                 -- Jessica Turner on task 6 in Project 1
    (5, 11, 2, 'Wireframes for the main modules are ready.', CURRENT_DATE, 10),          -- Charlie Brown on task 10 in Project 2
    (6, 16, 3, 'UML diagrams completed for the main classes.', CURRENT_DATE, 12);        -- Eve White on task 12 in Project 3

INSERT INTO forum_message (id, account, project, content, create_date)
VALUES 
    (0, 1, 0, 'Welcome to the Project Management System! Let’s get started!', CURRENT_TIMESTAMP), -- Alice Smith in Project 0
    (1, 2, 0, 'Looking forward to working with you all on this project.', CURRENT_TIMESTAMP),      -- Adriana Almeida in Project 0
    (2, 6, 1, 'The campaign project is shaping up nicely.', CURRENT_TIMESTAMP),                    -- Bob Johnson in Project 1
    (3, 9, 1, 'Ads are ready to be placed. Waiting for approvals.', CURRENT_TIMESTAMP),            -- Michael Green in Project 1
    (4, 11, 2, 'Please review the wireframes I uploaded.', CURRENT_TIMESTAMP),                     -- Charlie Brown in Project 2
    (5, 16, 3, 'Architecture design phase is almost complete.', CURRENT_TIMESTAMP);                -- Eve White in Project 3



-----------------------------------------
-- end
-----------------------------------------
