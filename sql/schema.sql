-- CREATE DATABASE ATTENDIFY;
-- USE ATTENDIFY;

-- CREATE TABLE USERS (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     FIRST_NAME VARCHAR(50) NOT NULL,
--     LAST_NAME VARCHAR(50) NOT NULL,
--     ID_NUMBER VARCHAR(20) NOT NULL UNIQUE,
--     EMAIL VARCHAR(100) NOT NULL UNIQUE,
--     DEPARTMENT VARCHAR(100) NOT NULL,
-- 	YEAR_LEVEL VARCHAR(20) NOT NULL,
--     SECTION VARCHAR(20) NOT NULL,
--     PASSWORD VARCHAR(255) NOT NULL,
--     ROLE ENUM('student','teacher','admin') DEFAULT 'student',
--     CREATED_AT TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- );

-- Create the database (if not exists)
CREATE DATABASE IF NOT EXISTS ATTENDIFY;
USE ATTENDIFY;

-- ───────────────────────────────────────────────
-- 1. Users (students, teachers, admins)
-- ───────────────────────────────────────────────
CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    first_name      VARCHAR(50) NOT NULL,
    last_name       VARCHAR(50) NOT NULL,
    id_number       VARCHAR(20) NOT NULL UNIQUE,          -- STU-2024-001234, FAC-2023-045, etc.
    email           VARCHAR(100) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,                 -- MUST use password_hash() in PHP
    department      VARCHAR(100) NOT NULL,                 -- "Computer Science", "Nursing", etc.
    year_level      VARCHAR(20) NOT NULL,                  -- "1st Year", "2nd Year", "Grade 11", etc.
    section         VARCHAR(20) NOT NULL,                  -- "A", "B", "STEM-1", etc.
    role            ENUM('student', 'teacher', 'admin') NOT NULL DEFAULT 'student',
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_role         (role),
    INDEX idx_id_number    (id_number)
);

-- ───────────────────────────────────────────────
-- 2. Subjects / Courses
-- ───────────────────────────────────────────────
CREATE TABLE subjects (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    subject_code    VARCHAR(20) NOT NULL UNIQUE,           -- MATH101, PHYS102, etc.
    subject_name    VARCHAR(100) NOT NULL,
    description     TEXT,
    department      VARCHAR(100) NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY uk_code_name (subject_code, subject_name)
);

-- ───────────────────────────────────────────────
-- 3. Classes / Sections being taught (teacher → subject → section)
-- ───────────────────────────────────────────────
CREATE TABLE class_sections (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    subject_id      INT NOT NULL,
    teacher_id      INT NOT NULL,                         -- who teaches this class
    academic_year   VARCHAR(20) NOT NULL,                 -- "2025-2026"
    semester        ENUM('1st Semester', '2nd Semester', 'Summer') NOT NULL,
    section_name    VARCHAR(20) NOT NULL,                 -- "A", "B", "STEM-101"
    schedule        VARCHAR(100) DEFAULT NULL,            -- "MWF 9:00-10:30 AM", "TTh 1:00-2:30 PM"
    room            VARCHAR(50) DEFAULT NULL,
    is_active       TINYINT(1) DEFAULT 1,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE RESTRICT,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE RESTRICT,
    
    UNIQUE KEY uk_class (subject_id, academic_year, semester, section_name)
);

-- ───────────────────────────────────────────────
-- 4. Students enrolled in classes (many-to-many)
-- ───────────────────────────────────────────────
CREATE TABLE class_enrollments (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT NOT NULL,
    class_section_id INT NOT NULL,
    enrolled_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('active', 'dropped', 'completed') DEFAULT 'active',
    
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_section_id) REFERENCES class_sections(id) ON DELETE CASCADE,
    
    UNIQUE KEY uk_enrollment (student_id, class_section_id)
);

-- ───────────────────────────────────────────────
-- 5. Attendance Records (core table for your dashboard)
-- ───────────────────────────────────────────────
CREATE TABLE attendance_records (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT NOT NULL,
    class_section_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status          ENUM('present', 'late', 'absent', 'excused') NOT NULL,
    check_in_time   TIME NULL,                            -- when student checked in
    check_out_time  TIME NULL,
    remarks         TEXT,                                  -- "Medical certificate", "Traffic", etc.
    marked_by       INT NULL,                             -- teacher who marked (or system)
    marked_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id)      REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_section_id) REFERENCES class_sections(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by)       REFERENCES users(id) ON DELETE SET NULL,
    
    UNIQUE KEY uk_attendance (student_id, class_section_id, attendance_date),
    INDEX idx_date_student (attendance_date, student_id),
    INDEX idx_class_date   (class_section_id, attendance_date)
);

-- ───────────────────────────────────────────────
-- 6. Announcements (school-wide or per class)
-- ───────────────────────────────────────────────
CREATE TABLE announcements (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    title           VARCHAR(150) NOT NULL,
    content         TEXT NOT NULL,
    author_id       INT NOT NULL,                         -- usually teacher or admin
    class_section_id INT NULL,                            -- NULL = school-wide
    priority        ENUM('normal', 'important', 'urgent') DEFAULT 'normal',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at      DATE NULL,
    
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_section_id) REFERENCES class_sections(id) ON DELETE SET NULL
);

-- Optional bonus tables (can add later)
-- announcements_read (track who read which announcement)
-- notifications (system/user notifications)
-- attendance_settings (late threshold per class, etc.)

CREATE TABLE attendance_appeals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_section_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    reason TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);