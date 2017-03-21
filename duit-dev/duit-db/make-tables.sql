-- Delete databases if they are set and recreate blank new ones
DROP DATABASE IF EXISTS dumi_dev;
-- DROP DATABASE IF EXISTS dumi;
CREATE DATABASE dumi_dev;
-- CREATE DATABASE dumi;

GRANT ALL PRIVILEGES ON dumi_dev.* to 'global'@'localhost' IDENTIFIED BY 'temp' WITH GRANT OPTION;
-- GRANT ALL PRIVILEGES ON dumi.* to 'global'@'localhost' IDENTIFIED BY 'temp' WITH GRANT OPTION;


USE dumi_dev;

-- Create table for du's
CREATE TABLE dus 
  ( 
     -- Identifier: du id 
     du_id               INT UNSIGNED NOT NULL auto_increment PRIMARY KEY, 
     -- Time that du was created 
     du_timestamp        TIMESTAMP NOT NULL, 
     -- Name of du, required 
     du_name             VARCHAR(100) NOT NULL, 
     -- Du can be associated with a date, deadline, duration (start and end time), or none of the above
     du_has_date         BOOL NOT NULL DEFAULT false, 
     du_has_deadline     BOOL NOT NULL DEFAULT false, 
     du_has_duration     BOOL NOT NULL DEFAULT false, 
     -- Date, deadline, or start time, optional 
     du_time_start       DATETIME, 
     -- End time, optional 
     du_time_end         DATETIME, 
     -- Priority will take a value between 1 and 4 where 1 is most critical, optional (defaults to 4 if unspecified)
     du_priority         INT NOT NULL DEFAULT 4, 
     -- Indicate if du priority was specified 
     du_enforce_priority BOOL NOT NULL DEFAULT false, 
     -- Longer description of du, optional 
     du_note             VARCHAR(256) 
  ); 

-- Create table for tags, used to relate multiple du's to the same project, class, category, media, etc.
CREATE TABLE tags 
  ( 
     -- Identifier: tag id 
     tag_id       INT UNSIGNED NOT NULL auto_increment PRIMARY KEY, 
     -- Name of tag, required 
     tag_name     VARCHAR(24) UNIQUE NOT NULL, 
     -- Priority will take a value between 1 and 4 where 1 is most critical, optional (defaults to 4 if unspecified)
     tag_priority INT NOT NULL DEFAULT 4, 
     -- Longer description of tag, optional 
     tag_note     VARCHAR(256) 
  );

-- Create table for cataloguing which tags are associated with which du's where each line represents one such correspondance
CREATE TABLE du_tag_pairs 
  ( 
     du_id  INT UNSIGNED NOT NULL, 
          FOREIGN KEY (du_id) REFERENCES dus(du_id), 
          tag_id INT UNSIGNED NOT NULL, 
     FOREIGN KEY (tag_id) REFERENCES tags(tag_id) 
  ); 

-- Create table for statuses, used to track states of du's
CREATE TABLE statuses 
  ( 
     -- Linked identifier: du id 
     du_id             INT UNSIGNED NOT NULL, 
          FOREIGN KEY (du_id) REFERENCES dus(du_id), 
     -- Status type, required 
     status_type       ENUM('open', 'active', 'completed') NOT NULL DEFAULT 
     'open', 
     -- Time at which du is updated to 'active' status, optional (set automatically on status change)
     status_time_start DATETIME, 
     -- Time at which du is updated to 'completed' status, optional (set automatically on status change)
     status_time_end   DATETIME, 
     -- Score, assigned on completion to reflect if task was completed/when task was completed with respect to deadline
     score             DECIMAL(5, 2)-- accepts values from -999.99 to 999.99 
  ); 


-- SAMPLE DATA

INSERT INTO dus 
            (du_name, 
             du_priority, 
             du_enforce_priority) 
VALUES      ('Buy groceries', 
             4, 
             true), 
            ('Reserve campground', 
             4, 
             true); 

INSERT INTO dus 
            (du_name, 
             du_has_duration, 
             du_time_start, 
             du_time_end, 
             du_note) 
VALUES      ('Cook dinner', 
             true, 
             '2016-03-14 17:00:00', 
             '2016-03-14 18:00:00', 
             'Make it extra yummy'); 

INSERT INTO dus 
            (du_name, 
             du_has_deadline, 
             du_time_start, 
             du_priority, 
             du_enforce_priority, 
             du_note) 
VALUES      ('Study for test', 
             true, 
             '2016-03-15 13:00:00', 
             1, 
             true, 
             'Tbh you\'ll probably fail tho'); 

INSERT INTO tags 
            (tag_name) 
VALUES      ('food'); 

INSERT INTO tags 
            (tag_name, 
             tag_priority) 
VALUES      ('spanish', 
             3), 
            ('errands', 
             3); 

INSERT INTO du_tag_pairs 
            (du_id, 
             tag_id) 
VALUES      (1, 
             3), 
            (1, 
             1), 
            (3, 
             1), 
            (4, 
             2); 

INSERT INTO statuses 
            (du_id) 
VALUES      (1), 
            (2); 

INSERT INTO statuses 
            (du_id, 
             status_type, 
             status_time_start, 
             status_time_end, 
             score) 
VALUES      (3, 
             'completed', 
             '2016-03-14 17:05:00,', 
             '2016-03-14 17:58:00', 
             0); 

INSERT INTO statuses 
            (du_id, 
             status_type, 
             status_time_start) 
VALUES      (4, 
             'active', 
             '2016-03-14 21:35:00');


-- SAMPLE SELECTS 

SELECT 'TABLE OF DU\'S' AS ''; 

SELECT * 
FROM   dus; 

SELECT 'TABLE OF TAGS' AS ''; 

SELECT * 
FROM   tags; 

SELECT 'CONSOLIDATED AGGREGATE VIEW OF DU\'S' AS ''; 
SELECT du_id, 
       -- du_timestamp, 
       du_name, 
       -- du_has_date, 
       -- du_has_deadline, 
       -- du_has_duration, 
       -- du_time_start, 
       -- du_time_end, 
       du_priority, 
       -- du_enforce_priority, 
       ( Group_concat(tag_priority SEPARATOR ', ') ) AS tag_priorities, 
       -- du_note, 
       ( Group_concat(tag_name SEPARATOR ', ') )     AS du_tags, 
       status_type -- , 
       -- status_time_start, 
       -- status_time_end, 
       -- score 
FROM   (SELECT d.du_id, 
               d.du_timestamp, 
               d.du_name, 
               d.du_has_date, 
               d.du_has_deadline, 
               d.du_has_duration, 
               d.du_time_start, 
               d.du_time_end, 
               d.du_priority, 
               d.du_enforce_priority, 
               d.du_note, 
               t.tag_id, 
               t.tag_name, 
               t.tag_priority, 
               t.tag_note, 
               u.status_type, 
               u.status_time_start, 
               u.status_time_end, 
               u.score 
        FROM   dus AS d 
               LEFT JOIN du_tag_pairs AS p 
                      ON d.du_id = p.du_id 
               LEFT JOIN tags AS t 
                      ON p.tag_id = t.tag_id 
               LEFT JOIN statuses AS u 
                      ON d.du_id = u.du_id) AS subq 
GROUP  BY du_name 
ORDER  BY du_id ASC 


