-- Delete databases if they are set and recreate blank new ones
DROP DATABASE IF EXISTS duit_dev;
-- DROP DATABASE IF EXISTS duit;
CREATE DATABASE duit_dev;
-- CREATE DATABASE duit;

GRANT ALL PRIVILEGES ON duit_dev.* to 'global'@'localhost' IDENTIFIED BY 'temp' WITH GRANT OPTION;
-- GRANT ALL PRIVILEGES ON duit.* to 'global'@'localhost' IDENTIFIED BY 'temp' WITH GRANT OPTION;


USE duit_dev;

-- Create table for storing, associating users dus
CREATE TABLE users
  (
    user_id   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(255) UNIQUE NOT NULL -- DO NOT EXCEED 255 IN LENGTH WHEN UNIQUE
  );
-- TODO: link to dus.

-- Create table for du's
CREATE TABLE dus 
  ( 
     -- Identifier: du id 
     du_id               INT UNSIGNED NOT NULL auto_increment PRIMARY KEY, 
     -- Time that du was created 
     du_timestamp        TIMESTAMP NOT NULL, 
     -- Name of du, required 
     du_name             VARCHAR(256) NOT NULL, 
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
     du_note             VARCHAR(256),
     -- Status of du, Open Completed or Active
     du_status           VARCHAR(256) NOT NULL,
     -- Tied to a user ID
     user_id             INT UNSIGNED NOT NULL,
     FOREIGN KEY (user_id) REFERENCES users(user_id)
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
     tag_note     VARCHAR(256),
     -- Tied to a user ID
     user_id      INT UNSIGNED NOT NULL,
     FOREIGN KEY (user_id) REFERENCES users(user_id)
  );

-- Create table for cataloguing which tags are associated with which du's where each line represents one such correspondance
CREATE TABLE du_tag_pairs 
  ( 
     du_id  INT UNSIGNED NOT NULL, 
          FOREIGN KEY (du_id) REFERENCES dus(du_id), 
          tag_id INT UNSIGNED NOT NULL, 
     FOREIGN KEY (tag_id) REFERENCES tags(tag_id) 
  ); 


-- SAMPLE DATA

INSERT INTO users
             (user_name)
VALUES       ('Spongebob'),
             ('Patrick'),
             ('7KlhTHGlsjQhXbvJDiW8toS0gtG3');

INSERT INTO dus 
            (du_name, 
             du_priority, 
             du_enforce_priority,
             du_status,
             user_id) 
VALUES      ('Buy groceries', 
             4, 
             true,
             'Open',
             1), 
            ('Reserve campground', 
             4, 
             true,
             'Open',
             1); 

INSERT INTO dus 
            (du_name, 
             du_has_duration, 
             du_time_start, 
             du_time_end, 
             du_note,
             du_status,
             user_id) 
VALUES      ('Cook dinner', 
             true, 
             '2016-03-14 17:00:00', 
             '2016-03-14 18:00:00', 
             'Make it extra yummy',
             'Active',
             1); 

INSERT INTO dus 
            (du_name, 
             du_has_deadline, 
             du_time_start, 
             du_priority, 
             du_enforce_priority, 
             du_note,
             du_status,
             user_id) 
VALUES      ('Study for test', 
             true, 
             '2016-03-15 13:00:00', 
             1, 
             true, 
             'Tbh you\'ll probably fail tho',
             'Completed',
             2); 

INSERT INTO tags 
            (tag_name,
            user_id) 
VALUES      ('food',
            1); 

INSERT INTO tags 
            (tag_name, 
             tag_priority,
            user_id) 
VALUES      ('spanish', 
             3,
             2), 
            ('errands', 
             3,
             1); 

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

-- SAMPLE SELECTS 

SELECT 'TABLE OF DU\'S' AS ''; 

SELECT * 
FROM   dus; 

SELECT 'TABLE OF TAGS' AS ''; 

SELECT * 
FROM   tags; 

SELECT 'TABLE OF USERS' AS '';

SELECT *
FROM users;

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
       -- du_status,
       ( Group_concat(tag_name SEPARATOR ', ') )     AS du_tags
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
               d.du_status,
               t.tag_id, 
               t.tag_name, 
               t.tag_priority, 
               t.tag_note
        FROM   dus AS d 
               LEFT JOIN du_tag_pairs AS p 
                      ON d.du_id = p.du_id 
               LEFT JOIN tags AS t 
                      ON p.tag_id = t.tag_id ) AS subq
GROUP  BY du_name 
ORDER  BY du_id ASC 


