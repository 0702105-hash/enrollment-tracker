-- =====================================================
-- 🎓 CAS ENROLLMENT TRACKER - PRODUCTION SCHEMA
-- =====================================================

DROP DATABASE IF EXISTS casDB;
CREATE DATABASE casDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE casDB;

-- =====================================================
-- 1. PROGRAMS TABLE
-- =====================================================
CREATE TABLE casPrograms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- All 8 CAS Programs
INSERT INTO casPrograms (name) VALUES
    ('BACHELOR OF ARTS IN COMMUNICATION'),
    ('BACHELOR OF ARTS IN ENGLISH LANGUAGE'),
    ('BACHELOR OF ARTS IN POLITICAL SCIENCE'),
    ('BACHELOR OF LIBRARY AND INFORMATION SCIENCE'),
    ('BACHELOR OF MUSIC IN MUSIC EDUCATION'),
    ('BACHELOR OF SCIENCE IN BIOLOGY'),
    ('BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY'),
    ('BACHELOR OF SCIENCE IN SOCIAL WORK');

-- =====================================================
-- 2. ENROLLMENTS TABLE
-- =====================================================
CREATE TABLE enrollments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    semester TINYINT UNSIGNED NOT NULL CHECK (semester IN (1,2,3)),
    male INT UNSIGNED DEFAULT 0,
    female INT UNSIGNED DEFAULT 0,
    total INT UNSIGNED AS (male + female) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- ✅ CORRECT FOREIGN KEY → casPrograms
    FOREIGN KEY (program_id) REFERENCES casPrograms(id) ON DELETE CASCADE,
    
    -- Prevent duplicates per program/year/semester
    UNIQUE KEY uk_program_year_sem (program_id, academic_year, semester),
    
    -- Performance indexes
    INDEX idx_year_sem (academic_year, semester),
    INDEX idx_program (program_id)
) ENGINE=InnoDB;

-- =====================================================
-- 3. PREDICTIONS TABLE  
-- =====================================================
CREATE TABLE predictions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    program_id INT UNSIGNED NOT NULL,
    academic_year VARCHAR(20),
    semester TINYINT UNSIGNED,
    predicted_total INT UNSIGNED,
    predicted_male INT UNSIGNED DEFAULT 0,
    predicted_female INT UNSIGNED DEFAULT 0,
    confidence FLOAT DEFAULT 0.85,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    -- ✅ CORRECT FOREIGN KEY → casPrograms
    FOREIGN KEY (program_id) REFERENCES casPrograms(id) ON DELETE CASCADE,
    
    UNIQUE KEY uk_program_pred_sem (program_id, academic_year, semester)
) ENGINE=InnoDB;

-- =====================================================
-- 4. USERS TABLE
-- =====================================================
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','viewer') DEFAULT 'viewer',
    last_login TIMESTAMP NULL
) ENGINE=InnoDB;

-- admin / admin123
INSERT INTO users (username, password, role) VALUES
    ('admin', '$2y$10$v.3Bz/8pT.aJW1pD6YbJdeGJ3LqZw6e5a4J.q9E3lqnasjW0UXH92', 'admin');
