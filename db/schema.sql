DROP DATABASE IF EXISTS enrollment_tracker;
CREATE DATABASE enrollment_tracker;

USE enrollment_tracker;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

INSERT INTO departments (name) VALUES
    ('College of Information Technology'),
    ('College of Arts and Sciences'),
    ('College of Social Sciences');

CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    dept_id INT,
    FOREIGN KEY (dept_id) REFERENCES departments(id)
);

INSERT INTO programs (name, dept_id) VALUES
    ('BSIT', 1),
    ('BaComm', 2),
    ('BSPolSci', 3);

CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,   -- '2023–2024'
    semester INT NOT NULL,                -- 1, 2, 3 (3 = summer)
    male INT DEFAULT 0,
    female INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id),
    UNIQUE KEY uk_program_year_sem (program_id, academic_year, semester)
);

CREATE TABLE predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    academic_year VARCHAR(20),
    semester INT,
    predicted_total INT,
    predicted_male INT,
    predicted_female INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES programs(id),
    UNIQUE KEY uk_program_pred_sem (program_id, academic_year, semester)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','viewer') DEFAULT 'viewer'
);

INSERT INTO users (username, password, role) VALUES
    ('admin', '$2y$10$v.3Bz/8pT.aJW1pD6YbJdeGJ3LqZw6e5a4J.q9E3lqnasjW0UXH92', 'admin');
-- password = 'admin123'
