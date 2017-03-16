-- Delete databases if they are set and recreate blank new ones
DROP DATABASE IF EXISTS dumi_dev;
-- DROP DATABASE IF EXISTS dumi;
CREATE DATABASE dumi_dev;
-- CREATE DATABASE dumi;

GRANT ALL PRIVILEGES ON dumi_dev.* to 'global'@'localhost' IDENTIFIED BY 'temp' WITH GRANT OPTION;
--GRANT ALL PRIVILEGES ON dumi.* to 'global'@'localhost' IDENTIFIED BY 'temp' WITH GRANT OPTION;


USE dumi_dev;

-- Create table for du's
CREATE TABLE Dus (
  -- Identifier: du id
	du_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  -- Time that du was created
  du_timestamp TIMESTAMP NOT NULL,
  -- Name of du, required
	du_name VARCHAR(100) NOT NULL,
	-- Du can be associated with a date, deadline, duration (start and end time), or none of the above
  du_has_date BOOL NOT NULL DEFAULT FALSE,
  du_has_deadline BOOL NOT NULL DEFAULT FALSE,
  du_has_duration BOOL NOT NULL DEFAULT FALSE,
  -- Date, deadline, or start time, optional
  du_time_start DATETIME,
  -- End time, optional
  du_time_end DATETIME,
  -- Priority will take a value between 1 and 4 where 1 is most critical, optional (defaults to 4 if unspecified)
  du_priority INT NOT NULL DEFAULT 4,
  -- Longer description of du, optional
  du_note VARCHAR(256)
);

-- Create table for tags, used to relate multiple du's to the same project, class, category, media, etc.
CREATE TABLE Tags (
  -- Identifier: tag id
  tag_id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  -- Name of tag, required
  tag_name VARCHAR(24) NOT NULL,
  -- Priority will take a value between 1 and 4 where 1 is most critical, optional (defaults to 4 if unspecified)
  tag_priority INT NOT NULL DEFAULT 4,
  -- Longer description of tag, optional
  tag_note VARCHAR(256)
);

-- Create table for cataloguing which tags are associated with which du's where each line represents one such correspondance
CREATE TABLE Du_Tag_Pairs (
  du_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (du_id) REFERENCES Dus(du_id),
  tag_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (tag_id) REFERENCES Tags(tag_id)
);

-- Create table for statuses, used to track states of du's
CREATE TABLE Statuses (
  -- Linked identifier: du id
  du_id INT UNSIGNED NOT NULL,  
  FOREIGN KEY (du_id) REFERENCES Dus(du_id),
  -- Status type, required
  status_type ENUM('open','active','completed') NOT NULL DEFAULT 'open',
  -- Time at which du is updated to 'active' status, optional (set automatically on status change)
  status_time_start DATETIME,
  -- Time at which du is updated to 'completed' status, optional (set automatically on status change)
  status_time_end DATETIME,
  -- Score, assigned on completion to reflect if task was completed/when task was completed with respect to deadline
  score DECIMAL(5,2) -- accepts values from -999.99 to 999.99
);


-- SAMPLE DATA

INSERT INTO Dus (du_name, du_priority)
  VALUES ('Buy groceries', 2),
    ('Reserve campground', 4);
INSERT INTO Dus (du_name, du_has_duration, du_time_start, du_time_end, du_priority, du_note)
  VALUES ('Cook dinner', TRUE, '2016-03-14 17:00:00', '2016-03-14 18:00:00', 4, 'Make it extra yummy');
INSERT INTO Dus (du_name, du_has_deadline, du_time_start, du_priority, du_note)
  VALUES ('Study for test', TRUE, '2016-03-15 13:00:00', 1, 'Tbh you\'ll probably fail tho');

INSERT INTO Tags (tag_name)
  VALUES ('food'),
    ('errands');
INSERT INTO Tags (tag_name, tag_priority)
  VALUES ('spanish', 3);

INSERT INTO Du_Tag_Pairs (du_id, tag_id)
  VALUES (1, 1),
    (1, 2),
    (3, 1),
    (4, 3);

INSERT INTO Statuses (du_id)
  VALUES (1),
    (2);
INSERT INTO Statuses (du_id, status_type, status_time_start, status_time_end, score)
  VALUES (3, 'completed', '2016-03-14 17:05:00,', '2016-03-14 17:58:00', 0);
INSERT INTO Statuses (du_id, status_type, status_time_start)
  VALUES (4, 'active', '2016-03-14 21:35:00');

SELECT 'TABLE OF DU\'S' AS '';
SELECT * FROM Dus;

SELECT 'TABLE OF TAGS' AS '';
SELECT * FROM Tags;

SELECT 'CONSOLIDATED AGGREGATE VIEW OF DU\'S' AS '';
SELECT
  CASE WHEN du_priority < tag_priority OR tag_priority IS NULL THEN du_priority ELSE tag_priority END AS Priority,
  du_name AS Du,
  du_note AS Note,
  GROUP_CONCAT(tag_name separator ', ') AS Tag,
  status_type as Status
FROM
  (
  SELECT
    d.du_name,
    d.du_note,
    t.tag_name,
    d.du_priority,
    t.tag_priority,
    u.status_type
  FROM Dus as d
  LEFT JOIN
    Du_Tag_Pairs AS p
      ON d.du_id = p.du_id
  LEFT JOIN
    Tags AS t
      ON p.tag_id = t.tag_id
  LEFT JOIN
    Statuses AS u
      ON d.du_id = u.du_id
  ) AS subq
GROUP BY du_name
ORDER BY Priority;


