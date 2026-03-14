-- ============================================================
-- TEST DATA SEEDING SCRIPT FOR ATTENDIFY
-- ============================================================
-- This script adds test data to make the dashboard functional
-- Run this in phpMyAdmin after creating the schema

-- 1. ADD SUBJECTS (for teacher to teach)
INSERT INTO `subjects` (`subject_code`, `subject_name`, `description`, `department`) VALUES
('WST101', 'Web Systems and Technologies', 'Introduction to web development', 'Web Systems and Technologies'),
('DB101', 'Database Management', 'SQL and database design', 'Web Systems and Technologies'),
('JAVA101', 'Java Programming', 'Object-oriented programming with Java', 'BSIT');

-- 2. ADD CLASS SECTIONS (link teacher to subjects)
INSERT INTO `class_sections` (`subject_id`, `teacher_id`, `academic_year`, `semester`, `section_name`, `schedule`, `room`, `is_active`) VALUES
-- Sir Bernce teaching Web Systems
(1, 3, '2025-2026', '1st Semester', 'A', 'MWF 09:00-10:30', '101', 1),
(1, 3, '2025-2026', '1st Semester', 'B', 'TTh 10:00-11:30', '102', 1),
-- Sir Bernce teaching Database Management
(2, 3, '2025-2026', '1st Semester', 'A', 'MWF 11:00-12:30', '201', 1);

-- 3. ENROLL STUDENTS IN CLASSES
-- Student 1 (Raul) enrolled in classes
INSERT INTO `class_enrollments` (`student_id`, `class_section_id`, `status`) VALUES
(1, 1, 'active'),  -- Raul in WST101 Section A
(1, 3, 'active'),  -- Raul in DB101 Section A

-- Student 4 (Joshua) enrolled in classes
(4, 1, 'active'),  -- Joshua in WST101 Section A
(4, 2, 'active'),  -- Joshua in WST101 Section B

-- Student 5 (Jellian) enrolled in classes
(5, 2, 'active'),  -- Jellian in WST101 Section B
(5, 3, 'active');  -- Jellian in DB101 Section A

-- 4. ADD SAMPLE ATTENDANCE RECORDS (for today's date)
INSERT INTO `attendance_records` (`student_id`, `class_section_id`, `attendance_date`, `status`, `check_in_time`, `marked_by`) VALUES
-- Today's attendance for class 1 (WST101 Section A)
(1, 1, CURDATE(), 'present', '09:05:00', 3),  -- Raul present
(4, 1, CURDATE(), 'late', '09:15:00', 3),     -- Joshua late

-- Today's attendance for class 2 (WST101 Section B)
(5, 2, CURDATE(), 'present', '10:05:00', 3),  -- Jellian present

-- Today's attendance for class 3 (DB101 Section A)
(1, 3, CURDATE(), 'absent', NULL, 3);         -- Raul absent

-- 5. ADD SAMPLE ANNOUNCEMENTS
INSERT INTO `announcements` (`title`, `content`, `author_id`, `class_section_id`, `priority`, `created_at`) VALUES
('Welcome to Class', 'Welcome everyone to this semester!', 3, 1, 'normal', NOW()),
('Midterm Exam Schedule', 'Midterm exams will be held starting March 20, 2026', 3, NULL, 'important', NOW()),
('Assignment Submission', 'Please submit your assignments by March 15, 2026', 3, 1, 'normal', NOW());
