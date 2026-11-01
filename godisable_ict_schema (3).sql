-- =====================================================================
-- God Is Able ICT Training and Skilling Centre Limited
-- Website Database Schema
-- For import via phpMyAdmin (XAMPP) — MySQL / MariaDB
-- =====================================================================

CREATE DATABASE IF NOT EXISTS godisable_ict
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE godisable_ict;

-- ---------------------------------------------------------------------
-- 1. ADMINS
-- Only the administrator logs in here. Password is ALWAYS hashed
-- (PHP password_hash / bcrypt). Never store or display plain text.
-- ---------------------------------------------------------------------
CREATE TABLE admins (
    admin_id        INT AUTO_INCREMENT PRIMARY KEY,
    full_name       VARCHAR(100) NOT NULL,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    email           VARCHAR(150) NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,   -- bcrypt hash only
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 2. COURSES
-- ---------------------------------------------------------------------
CREATE TABLE courses (
    course_id       INT AUTO_INCREMENT PRIMARY KEY,
    course_name     VARCHAR(150) NOT NULL,
    description     TEXT,
    duration        VARCHAR(50),              -- e.g. "3 Months", "6 Weeks"
    delivery_mode   ENUM('online','physical','both') NOT NULL DEFAULT 'both',
    is_active       TINYINT(1) NOT NULL DEFAULT 1,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 3. FEES
-- Tuition fee for a course, split by mode (online/physical) and
-- billing period (monthly, per-term, full course).
-- ---------------------------------------------------------------------
CREATE TABLE fees (
    fee_id          INT AUTO_INCREMENT PRIMARY KEY,
    course_id       INT NOT NULL,
    delivery_mode   ENUM('online','physical') NOT NULL,
    billing_period  ENUM('monthly','termly','full_course') NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    currency        VARCHAR(10) NOT NULL DEFAULT 'UGX',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
    UNIQUE KEY unique_fee (course_id, delivery_mode, billing_period)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 4. STUDENTS
-- password_hash stores a bcrypt hash ONLY. The admin can trigger a
-- reset (generate a new temp password / reset link) but the app must
-- never display this column anywhere, including in the admin panel.
-- ---------------------------------------------------------------------
CREATE TABLE students (
    student_id      INT AUTO_INCREMENT PRIMARY KEY,
    reg_number      VARCHAR(20) NOT NULL UNIQUE,   -- e.g. GIA-2026-0001
    first_name      VARCHAR(80) NOT NULL,
    last_name       VARCHAR(80) NOT NULL,
    email           VARCHAR(150) NOT NULL UNIQUE,
    phone           VARCHAR(20)  NOT NULL,
    course_id       INT NOT NULL,
    delivery_mode   ENUM('online','physical') NOT NULL,
    password_hash   VARCHAR(255) NOT NULL,          -- bcrypt hash only, never displayed
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    registered_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    approved_at     DATETIME NULL,
    FOREIGN KEY (course_id) REFERENCES courses(course_id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 5. NOTIFICATIONS
-- In-admin-panel notifications (e.g. "New student registered").
-- ---------------------------------------------------------------------
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    type            ENUM('new_registration','general') NOT NULL DEFAULT 'new_registration',
    student_id      INT NULL,
    message         VARCHAR(255) NOT NULL,
    is_read         TINYINT(1) NOT NULL DEFAULT 0,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 6. MESSAGE LOG
-- Logs every confirmation message sent to a student, by channel.
-- ---------------------------------------------------------------------
CREATE TABLE message_log (
    message_id      INT AUTO_INCREMENT PRIMARY KEY,
    student_id      INT NOT NULL,
    channel         ENUM('email','sms') NOT NULL,
    subject         VARCHAR(150),
    message_body    TEXT NOT NULL,
    status          ENUM('sent','failed','pending') NOT NULL DEFAULT 'pending',
    sent_at         DATETIME NULL,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 7. NOTES (downloadable course material — approved students only)
-- ---------------------------------------------------------------------
CREATE TABLE notes (
    note_id         INT AUTO_INCREMENT PRIMARY KEY,
    course_id       INT NOT NULL,
    title           VARCHAR(150) NOT NULL,
    file_path       VARCHAR(255) NOT NULL,
    uploaded_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- 8. LECTURES (online lecture links/recordings — approved students only)
-- ---------------------------------------------------------------------
CREATE TABLE lectures (
    lecture_id      INT AUTO_INCREMENT PRIMARY KEY,
    course_id       INT NOT NULL,
    title           VARCHAR(150) NOT NULL,
    video_url       VARCHAR(255),          -- e.g. YouTube/Zoom recording link
    description     TEXT,
    uploaded_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- SAMPLE DATA (safe placeholders — replace with real course/fee info)
-- =====================================================================

INSERT INTO courses (course_name, description, duration, delivery_mode) VALUES
('Computer Basics & Digital Literacy', 'Introduction to computers, MS Office, and internet use.', '2 Months', 'both'),
('Web Design & Development', 'HTML, CSS, JavaScript, and building real websites.', '3 Months', 'both'),
('Graphic Design', 'Adobe Photoshop, CorelDRAW, and design fundamentals.', '2 Months', 'both');

INSERT INTO fees (course_id, delivery_mode, billing_period, amount) VALUES
(1, 'physical', 'monthly', 100000.00),
(1, 'online',   'monthly', 70000.00),
(1, 'physical', 'full_course', 180000.00),
(1, 'online',   'full_course', 130000.00),
(2, 'physical', 'monthly', 150000.00),
(2, 'online',   'monthly', 100000.00),
(2, 'physical', 'full_course', 400000.00),
(2, 'online',   'full_course', 280000.00);

-- Note: An admin account should be created via a PHP registration script
-- using password_hash(), NOT inserted here in plain text.
