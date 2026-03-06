-- Add new columns to predictions table to support multi-model ensemble

ALTER TABLE predictions ADD COLUMN IF NOT EXISTS model_ensemble VARCHAR(255) DEFAULT 'SARMAX+Prophet+LSTM';

-- Create a detailed model_metrics table to store per-model evaluation metrics

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
    INDEX idx_metric (metric_name)
);

-- Create table for storing prediction confidence intervals

CREATE TABLE IF NOT EXISTS prediction_confidence (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prediction_id INT NOT NULL,
    lower_bound DECIMAL(10, 2),
    upper_bound DECIMAL(10, 2),
    confidence_level INT DEFAULT 95,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (prediction_id) REFERENCES predictions(id),
    INDEX idx_prediction (prediction_id)
);

-- Add indexes for faster queries

ALTER TABLE predictions ADD INDEX idx_year_semester (academic_year, semester);
ALTER TABLE predictions ADD INDEX idx_model_ensemble (model_ensemble);