-- ============================================================
-- ENROLLMENT TRACKER DATABASE SCHEMA
-- ============================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS casDB;
USE casDB;

-- ============================================================
-- CORE TABLES
-- ============================================================

-- Programs table
CREATE TABLE IF NOT EXISTS casPrograms (
    id INT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    department VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Historical enrollments
CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    semester INT NOT NULL,
    male INT DEFAULT 0,
    female INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES casPrograms(id),
    INDEX idx_program_year (program_id, academic_year),
    UNIQUE KEY unique_enrollment (program_id, academic_year, semester)
);

-- ============================================================
-- PREDICTION TABLES
-- ============================================================

-- Ensemble predictions (from combining 3 models)
CREATE TABLE IF NOT EXISTS predictions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    semester INT NOT NULL,
    predicted_total INT NOT NULL,
    predicted_male INT,
    predicted_female INT,
    confidence DECIMAL(5, 4),
    model_ensemble VARCHAR(255) DEFAULT 'SARMAX+Prophet+LSTM',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES casPrograms(id),
    INDEX idx_program_year (program_id, academic_year),
    INDEX idx_model_ensemble (model_ensemble)
);

-- ============================================================
-- MODEL METRICS TABLES
-- ============================================================

-- Individual model evaluation metrics (6 metrics × 3 models × 8 programs)
CREATE TABLE IF NOT EXISTS model_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT NOT NULL,
    model_name VARCHAR(50) NOT NULL,
    metric_name VARCHAR(50) NOT NULL,
    metric_value DECIMAL(10, 4) NOT NULL,
    prediction_year VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (program_id) REFERENCES casPrograms(id),
    INDEX idx_program_model (program_id, model_name),
    INDEX idx_metric_name (metric_name),
    INDEX idx_prediction_year (prediction_year)
);

-- ============================================================
-- COMMENTS FOR CLARITY
-- ============================================================

-- predictions.model_ensemble VALUES:
-- 'SARMAX+Prophet+LSTM' - All 3 models successful
-- 'SARMAX+Prophet' - LSTM failed
-- 'SARMAX+LSTM' - Prophet failed
-- 'Prophet+LSTM' - SARMAX failed
-- (etc.)

-- model_metrics.metric_name VALUES:
-- 'MAE' - Mean Absolute Error
-- 'RMSE' - Root Mean Squared Error
-- 'MAPE' - Mean Absolute Percentage Error
-- 'R²' - Coefficient of Determination
-- 'RMSLE' - Root Mean Squared Log Error
-- 'Theil_U' - Theil Inequality Coefficient

-- model_metrics.model_name VALUES:
-- 'SARMAX' - Seasonal ARIMA
-- 'Prophet' - Facebook Prophet
-- 'LSTM' - Neural Network

-- ============================================================
-- INSERT PROGRAM DATA
-- ============================================================

INSERT INTO casPrograms (id, name, department) VALUES
(1, 'BA Communication', 'CAS'),
(2, 'BA English', 'CAS'),
(3, 'BA Political Science', 'CAS'),
(4, 'BLIS', 'CAS'),
(5, 'BM Music Education', 'CAS'),
(6, 'BS Biology', 'CAS'),
(7, 'BS Information Technology', 'CAS'),
(8, 'BS Social Work', 'CAS')
ON DUPLICATE KEY UPDATE name=VALUES(name);