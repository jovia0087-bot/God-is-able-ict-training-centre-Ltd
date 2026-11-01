-- ============================================================
-- God Is Able ICT Training Centre - Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS godisable_ict;
USE godisable_ict;

-- ------------------------------------------------------------
-- Courses offered by the centre
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS courses (
    course_id INT AUTO_INCREMENT PRIMARY KEY,
    course_name VARCHAR(150) NOT NULL,
    description TEXT,
    duration VARCHAR(50),          -- e.g. "3 Months"
    fee DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Admin / staff accounts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,   -- stored as password_hash()
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ------------------------------------------------------------
-- Students
-- status: pending (just registered) -> approved (can log in) -> rejected
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30),
    password VARCHAR(255) NOT NULL,   -- stored as password_hash()
    course_id INT,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE SET NULL
);

-- ------------------------------------------------------------
-- Course materials (notes / PDFs) uploaded by admin
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS materials (
    material_id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);

-- ------------------------------------------------------------
-- Sample courses to get started (edit/remove as needed)
-- ------------------------------------------------------------
INSERT INTO courses (course_name, description, duration, fee) VALUES
('Computer Basics', 'Introduction to computers, MS Office, and internet use.', '1 Month', 150000.00),
('Web Development', 'HTML, CSS, PHP and MySQL for building websites.', '3 Months', 400000.00),
('Networking Fundamentals', 'Basics of computer networking and hardware.', '2 Months', 300000.00);

-- ------------------------------------------------------------
-- Default admin account (email: admin@godisable.com / password: Admin@123)
-- CHANGE THIS PASSWORD after first login in production.
-- ------------------------------------------------------------
-- Password hash generated (bcrypt) for the password: Admin@123
-- Verified to work with PHP's password_verify()
INSERT INTO admins (full_name, email, password) VALUES
('Administrator', 'admin@godisable.com', '$2b$10$CTNOZaJYFNBmqps8CI6eNuVzZs4YUGGYOEWAqymWGGOO9lCTpVt5e');
