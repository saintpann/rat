USE themouse_db;

-- === CLEANUP EXISTING DATA ===
-- Temporarily disable foreign key checks to allow deletions in any order
SET FOREIGN_KEY_CHECKS = 0;

-- Remove tickets and bookings first (they reference showings/seats/users)
DELETE FROM ticket;
DELETE FROM booking;

-- Remove showings, seats, movies, rooms (seed data)
DELETE FROM showing;
DELETE FROM seat;
DELETE FROM movie;
DELETE FROM room;

-- Reset AUTO_INCREMENT counters for a clean slate
ALTER TABLE room AUTO_INCREMENT = 1;
ALTER TABLE movie AUTO_INCREMENT = 1;
ALTER TABLE seat AUTO_INCREMENT = 1;
ALTER TABLE showing AUTO_INCREMENT = 1;
ALTER TABLE booking AUTO_INCREMENT = 1;
ALTER TABLE ticket AUTO_INCREMENT = 1;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- 1) Rooms
INSERT INTO room (RM_NUMBER, RM_CAPACITY) VALUES
('R1', 40),
('R2', 40),
('R3', 40);

-- 2) Seats (Rows A-E, 8 seats each) for each room
-- Room R1
INSERT INTO seat (RM_ID, ST_LABEL, ST_TYPE)
SELECT r.RM_ID, CONCAT(l.letter, n.num) AS label, 'Regular'
FROM (SELECT 'A' AS letter UNION ALL SELECT 'B' UNION ALL SELECT 'C' UNION ALL SELECT 'D' UNION ALL SELECT 'E') l
CROSS JOIN (SELECT 1 AS num UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8) n
CROSS JOIN (SELECT RM_ID FROM room WHERE RM_NUMBER = 'R1') r;

-- Room R2
INSERT INTO seat (RM_ID, ST_LABEL, ST_TYPE)
SELECT r.RM_ID, CONCAT(l.letter, n.num) AS label, 'Regular'
FROM (SELECT 'A' AS letter UNION ALL SELECT 'B' UNION ALL SELECT 'C' UNION ALL SELECT 'D' UNION ALL SELECT 'E') l
CROSS JOIN (SELECT 1 AS num UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8) n
CROSS JOIN (SELECT RM_ID FROM room WHERE RM_NUMBER = 'R2') r;

-- Room R3
INSERT INTO seat (RM_ID, ST_LABEL, ST_TYPE)
SELECT r.RM_ID, CONCAT(l.letter, n.num) AS label, 'Regular'
FROM (SELECT 'A' AS letter UNION ALL SELECT 'B' UNION ALL SELECT 'C' UNION ALL SELECT 'D' UNION ALL SELECT 'E') l
CROSS JOIN (SELECT 1 AS num UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8) n
CROSS JOIN (SELECT RM_ID FROM room WHERE RM_NUMBER = 'R3') r;

-- 3) Movies (use the schema fields you provided)
INSERT INTO movie (MOV_TITLE, MOV_GENRE, MOV_DURATION, MOV_RATING) VALUES
('Wicked', 'Musical/Drama', 155, 'PG'),
('Beautiful Boy', 'Drama/Biography', 112, 'R-16'),
('La La Land', 'Musical/Romance', 128, 'PG'),
('Avengers: Endgame', 'Action/Adventure', 181, 'R-13');

-- 4) Capture IDs into variables to avoid fragile subqueries in VALUES lists
SELECT MOV_ID INTO @wicked FROM movie WHERE MOV_TITLE = 'Wicked' LIMIT 1;
SELECT MOV_ID INTO @beautiful FROM movie WHERE MOV_TITLE = 'Beautiful Boy' LIMIT 1;
SELECT MOV_ID INTO @lalaland FROM movie WHERE MOV_TITLE = 'La La Land' LIMIT 1;
SELECT MOV_ID INTO @avengers FROM movie WHERE MOV_TITLE LIKE 'Avengers%' LIMIT 1;

SELECT RM_ID INTO @r1 FROM room WHERE RM_NUMBER = 'R1' LIMIT 1;
SELECT RM_ID INTO @r2 FROM room WHERE RM_NUMBER = 'R2' LIMIT 1;
SELECT RM_ID INTO @r3 FROM room WHERE RM_NUMBER = 'R3' LIMIT 1;

-- 5) Insert showings for two dates (2025-12-05 and 2025-12-06) matching the site times
-- WICKED (R1): 10:30, 14:00, 17:30, 21:00
INSERT INTO showing (MOV_ID, RM_ID, SHW_START_TIME, SHW_END_TIME) VALUES
(@wicked, @r1, '2025-12-05 10:30:00', '2025-12-05 13:05:00'),
(@wicked, @r1, '2025-12-05 14:00:00', '2025-12-05 16:35:00'),
(@wicked, @r1, '2025-12-05 17:30:00', '2025-12-05 20:05:00'),
(@wicked, @r1, '2025-12-05 21:00:00', '2025-12-05 23:35:00'),
(@wicked, @r1, '2025-12-06 10:30:00', '2025-12-06 13:05:00'),
(@wicked, @r1, '2025-12-06 14:00:00', '2025-12-06 16:35:00'),
(@wicked, @r1, '2025-12-06 17:30:00', '2025-12-06 20:05:00'),
(@wicked, @r1, '2025-12-06 21:00:00', '2025-12-06 23:35:00');

-- BEAUTIFUL BOY (R2): 11:00, 14:15, 17:30, 20:45
INSERT INTO showing (MOV_ID, RM_ID, SHW_START_TIME, SHW_END_TIME) VALUES
(@beautiful, @r2, '2025-12-05 11:00:00', '2025-12-05 12:52:00'),
(@beautiful, @r2, '2025-12-05 14:15:00', '2025-12-05 16:07:00'),
(@beautiful, @r2, '2025-12-05 17:30:00', '2025-12-05 19:22:00'),
(@beautiful, @r2, '2025-12-05 20:45:00', '2025-12-05 22:37:00'),
(@beautiful, @r2, '2025-12-06 11:00:00', '2025-12-06 12:52:00'),
(@beautiful, @r2, '2025-12-06 14:15:00', '2025-12-06 16:07:00'),
(@beautiful, @r2, '2025-12-06 17:30:00', '2025-12-06 19:22:00'),
(@beautiful, @r2, '2025-12-06 20:45:00', '2025-12-06 22:37:00');

-- LA LA LAND (R3): 12:00, 15:00, 18:00, 21:00
INSERT INTO showing (MOV_ID, RM_ID, SHW_START_TIME, SHW_END_TIME) VALUES
(@lalaland, @r3, '2025-12-05 12:00:00', '2025-12-05 14:08:00'),
(@lalaland, @r3, '2025-12-05 15:00:00', '2025-12-05 17:08:00'),
(@lalaland, @r3, '2025-12-05 18:00:00', '2025-12-05 20:08:00'),
(@lalaland, @r3, '2025-12-05 21:00:00', '2025-12-05 23:08:00'),
(@lalaland, @r3, '2025-12-06 12:00:00', '2025-12-06 14:08:00'),
(@lalaland, @r3, '2025-12-06 15:00:00', '2025-12-06 17:08:00'),
(@lalaland, @r3, '2025-12-06 18:00:00', '2025-12-06 20:08:00'),
(@lalaland, @r3, '2025-12-06 21:00:00', '2025-12-06 23:08:00');

-- AVENGERS (R1): 10:00, 13:30, 16:45, 20:00
INSERT INTO showing (MOV_ID, RM_ID, SHW_START_TIME, SHW_END_TIME) VALUES
(@avengers, @r1, '2025-12-05 10:00:00', '2025-12-05 13:01:00'),
(@avengers, @r1, '2025-12-05 13:30:00', '2025-12-05 16:31:00'),
(@avengers, @r1, '2025-12-05 16:45:00', '2025-12-05 19:46:00'),
(@avengers, @r1, '2025-12-05 20:00:00', '2025-12-05 23:01:00'),
(@avengers, @r1, '2025-12-06 10:00:00', '2025-12-06 13:01:00'),
(@avengers, @r1, '2025-12-06 13:30:00', '2025-12-06 16:31:00'),
(@avengers, @r1, '2025-12-06 16:45:00', '2025-12-06 19:46:00'),
(@avengers, @r1, '2025-12-06 20:00:00', '2025-12-06 23:01:00');

-- ADD: Showings for 2025-12-04 (today) so site "Today" links resolve
-- WICKED (R1) - Today: 10:30, 14:00, 17:30, 21:00
INSERT INTO showing (MOV_ID, RM_ID, SHW_START_TIME, SHW_END_TIME) VALUES
(@wicked, @r1, '2025-12-04 10:30:00', '2025-12-04 13:05:00'),
(@wicked, @r1, '2025-12-04 14:00:00', '2025-12-04 16:35:00'),
(@wicked, @r1, '2025-12-04 17:30:00', '2025-12-04 20:05:00'),
(@wicked, @r1, '2025-12-04 21:00:00', '2025-12-04 23:35:00');

-- BEAUTIFUL BOY (R2) - Today: 11:00, 14:15, 17:30, 20:45
INSERT INTO showing (MOV_ID, RM_ID, SHW_START_TIME, SHW_END_TIME) VALUES
(@beautiful, @r2, '2025-12-04 11:00:00', '2025-12-04 12:52:00'),
(@beautiful, @r2, '2025-12-04 14:15:00', '2025-12-04 16:07:00'),
(@beautiful, @r2, '2025-12-04 17:30:00', '2025-12-04 19:22:00'),
(@beautiful, @r2, '2025-12-04 20:45:00', '2025-12-04 22:37:00');

-- LA LA LAND (R3) - Today: 12:00, 15:00, 18:00, 21:00
INSERT INTO showing (MOV_ID, RM_ID, SHW_START_TIME, SHW_END_TIME) VALUES
(@lalaland, @r3, '2025-12-04 12:00:00', '2025-12-04 14:08:00'),
(@lalaland, @r3, '2025-12-04 15:00:00', '2025-12-04 17:08:00'),
(@lalaland, @r3, '2025-12-04 18:00:00', '2025-12-04 20:08:00'),
(@lalaland, @r3, '2025-12-04 21:00:00', '2025-12-04 23:08:00');

-- AVENGERS (R1) - Today: 10:00, 13:30, 16:45, 20:00
INSERT INTO showing (MOV_ID, RM_ID, SHW_START_TIME, SHW_END_TIME) VALUES
(@avengers, @r1, '2025-12-04 10:00:00', '2025-12-04 13:01:00'),
(@avengers, @r1, '2025-12-04 13:30:00', '2025-12-04 16:31:00'),
(@avengers, @r1, '2025-12-04 16:45:00', '2025-12-04 19:46:00'),
(@avengers, @r1, '2025-12-04 20:00:00', '2025-12-04 23:01:00');

-- Optional: seed a staff user (password hashed with password_hash('password', PASSWORD_DEFAULT) before inserting)
-- Example (replace hash):
-- INSERT INTO usr (USR_EMAIL, USR_PASSWORD, USR_FNAME, USR_LNAME, USR_ROLE) VALUES ('staff@example.com', '$2y$10$...', 'Staff', 'Member', 'Staff');

-- Done
